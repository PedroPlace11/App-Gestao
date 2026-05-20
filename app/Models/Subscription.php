<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'plan_id',
        'scheduled_plan_id',
        'status',
        'current_period_start',
        'current_period_end',
        'trial_ends_at',
        'cancel_at_period_end',
        'canceled_at',
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancel_at_period_end' => 'boolean',
        'canceled_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function scheduledPlan()
    {
        return $this->belongsTo(Plan::class, 'scheduled_plan_id');
    }

    public function changeLogs()
    {
        return $this->hasMany(SubscriptionChangeLog::class);
    }

    public function isTrialActive(): bool
    {
        return $this->status === 'trialing'
            && $this->trial_ends_at instanceof Carbon
            && $this->trial_ends_at->isFuture();
    }

    public function applyScheduledPlan(): void
    {
        if ($this->scheduled_plan_id && $this->current_period_end <= now()) {
            $this->plan_id = $this->scheduled_plan_id;
            $this->scheduled_plan_id = null;
            $this->save();
        }
    }

    public function cancelIfScheduled(): void
    {
        if ($this->cancel_at_period_end && $this->current_period_end <= now()) {
            $this->status = 'canceled';
            $this->canceled_at = now();
            $this->save();
        }
    }
}
