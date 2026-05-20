<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionChangeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'company_id',
        'user_id',
        'from_plan_id',
        'to_plan_id',
        'change_type',
        'prorated_amount',
        'effective_at',
        'meta',
    ];

    protected $casts = [
        'prorated_amount' => 'decimal:2',
        'effective_at' => 'datetime',
        'meta' => 'array',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromPlan()
    {
        return $this->belongsTo(Plan::class, 'from_plan_id');
    }

    public function toPlan()
    {
        return $this->belongsTo(Plan::class, 'to_plan_id');
    }
}
