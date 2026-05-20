<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id', 'number', 'entity_id', 'first_name', 'last_name', 'function_id',
        'phone', 'mobile', 'email', 'rgpd_consent', 'observations', 'active',
    ];

    /**
     * Relationships
     */
    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function contactFunction()
    {
        return $this->belongsTo(ContactFunction::class);
    }
}
