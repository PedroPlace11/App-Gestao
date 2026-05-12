<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'number', 'date', 'client_id', 'total_value', 'status',
        'proposal_id', 'user_id', 'items', 'notes',
    ];

    protected $casts = [
        'items' => 'array',
        'date'  => 'date',
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

    public function supplierOrders()
    {
        return $this->hasMany(SupplierOrder::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
