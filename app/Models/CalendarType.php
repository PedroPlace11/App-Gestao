<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CalendarType extends Model
{
    use HasFactory;

    /**
     * Relationships
     */
    public function calendarEvents()
    {
        return $this->hasMany(CalendarEvent::class);
    }
}
