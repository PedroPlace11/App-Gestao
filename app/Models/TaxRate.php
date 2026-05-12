<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxRate extends Model
{
    use HasFactory;

    /**
     * Relationships
     */
    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
