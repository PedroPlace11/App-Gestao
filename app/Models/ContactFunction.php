<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactFunction extends Model
{
    use HasFactory;

    /**
     * Relationships
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}
