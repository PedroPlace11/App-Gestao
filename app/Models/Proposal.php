<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proposal extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id', 'number', 'date', 'client_id', 'validity', 'total_value',
        'status', 'user_id', 'items', 'notes',
    ];

    protected $casts = [
        'items' => 'array',
        'date'  => 'date',
        'validity' => 'date',
    ];

    /**
     * Relationships
     */
    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
