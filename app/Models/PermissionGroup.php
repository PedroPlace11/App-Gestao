<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PermissionGroup extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'name',
        'active',
    ];

    /**
     * Relationships
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
