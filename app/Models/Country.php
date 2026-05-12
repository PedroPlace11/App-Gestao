<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends Model
{
    use HasFactory;

    /**
     * Relationships
     */
    public function entities()
    {
        return $this->hasMany(Entity::class);
    }

    public function companies()
    {
        return $this->hasMany(Company::class);
    }
}
