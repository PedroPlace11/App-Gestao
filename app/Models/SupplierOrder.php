<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupplierOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'number', 'date', 'supplier_id', 'order_id', 'status',
        'total_value', 'user_id', 'items', 'notes',
    ];

    protected $casts = [
        'items' => 'array',
        'date'  => 'date',
    ];

    /**
     * Relationships
     */
    public function supplier()
    {
        return $this->belongsTo(Entity::class, 'supplier_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class)->nullable();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
