<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'address',
        'postal_code',
        'city',
        'tax_id',
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
}
