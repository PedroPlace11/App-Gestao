<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\Entity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Display a listing of calendar events.
     * Filters: user_id, entity_id, date_from, date_to, status
     */
    public function index(Request $request): JsonResponse
    {
        $query = CalendarEvent::with([
            'user:id,name',
            'entity:id,name',
            'calendarType:id,name',
            'calendarAction:id,name',
        ]);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('entity_id')) {
            $query->where('entity_id', $request->integer('entity_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->input('date_to'));
        }

        // Support fetching all events for calendar view (no pagination)
        if ($request->boolean('all')) {
            return response()->json($query->orderBy('date')->orderBy('time')->get());
        }

        $perPage = min($request->integer('per_page', 50), 500);

        return response()->json($query->orderBy('date')->orderBy('time')->paginate($perPage));
    }

    /**
     * Store a newly created calendar event.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date'        => 'required|date',
            'time'        => 'required|date_format:H:i',
            'duration'    => 'required|integer|min:1',
            'entity_id'   => 'nullable|exists:entities,id',
            'type_id'     => 'required|exists:calendar_types,id',
            'action_id'   => 'required|exists:calendar_actions,id',
            'description' => 'nullable|string|max:1000',
            'knowledge'   => 'nullable|string',
            'shared_with' => 'nullable|array',
            'status'      => 'sometimes|in:active,closed',
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['status']  = $validated['status'] ?? 'active';

        $event = CalendarEvent::create($validated);

        return response()->json(
            $event->load(['user:id,name', 'entity:id,name', 'calendarType:id,name', 'calendarAction:id,name']),
            201
        );
    }

    /**
     * Display the specified calendar event.
     */
    public function show(CalendarEvent $calendarEvent): JsonResponse
    {
        return response()->json(
            $calendarEvent->load(['user:id,name', 'entity:id,name', 'calendarType:id,name', 'calendarAction:id,name'])
        );
    }

    /**
     * Update the specified calendar event.
     */
    public function update(Request $request, CalendarEvent $calendarEvent): JsonResponse
    {
        $validated = $request->validate([
            'date'        => 'sometimes|date',
            'time'        => 'sometimes|date_format:H:i',
            'duration'    => 'sometimes|integer|min:1',
            'entity_id'   => 'nullable|exists:entities,id',
            'type_id'     => 'sometimes|exists:calendar_types,id',
            'action_id'   => 'sometimes|exists:calendar_actions,id',
            'description' => 'nullable|string|max:1000',
            'knowledge'   => 'nullable|string',
            'shared_with' => 'nullable|array',
            'status'      => 'sometimes|in:active,closed',
        ]);

        $calendarEvent->update($validated);

        return response()->json(
            $calendarEvent->load(['user:id,name', 'entity:id,name', 'calendarType:id,name', 'calendarAction:id,name'])
        );
    }

    /**
     * Remove the specified calendar event.
     */
    public function destroy(CalendarEvent $calendarEvent): JsonResponse
    {
        $calendarEvent->delete();

        return response()->json(null, 204);
    }
}
