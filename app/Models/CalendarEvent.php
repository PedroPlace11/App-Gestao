<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 'time', 'duration', 'entity_id', 'type_id', 'action_id',
        'description', 'knowledge', 'shared_with', 'status', 'user_id',
    ];

    protected $casts = [
        'date'        => 'date',
        'shared_with' => 'array',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class)->nullable();
    }

    public function calendarType()
    {
        return $this->belongsTo(CalendarType::class);
    }

    public function calendarAction()
    {
        return $this->belongsTo(CalendarAction::class);
    }
}
