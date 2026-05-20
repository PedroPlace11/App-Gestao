<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id', 'reference', 'name', 'description', 'price', 'tax_rate_id',
        'photo', 'observations', 'active',
    ];

    /**
     * Relationships
     */
    public function taxRate()
    {
        return $this->belongsTo(TaxRate::class);
    }
}
