<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id', 'number', 'issue_date', 'due_date', 'supplier_id',
        'supplier_order_id', 'total_value', 'document',
        'payment_proof', 'status', 'user_id',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'due_date'    => 'date',
        'total_value' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function supplier()
    {
        return $this->belongsTo(Entity::class, 'supplier_id');
    }

    public function supplierOrder()
    {
        return $this->belongsTo(SupplierOrder::class)->nullable();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
