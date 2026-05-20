<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_user_id',
        'name',
        'slug',
        'logo',
        'address',
        'postal_code',
        'city',
        'tax_id',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Relationships
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role', 'is_owner'])
            ->withTimestamps();
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }
}
