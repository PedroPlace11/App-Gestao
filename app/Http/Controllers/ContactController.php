<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Display a paginated listing of contacts.
     * Filters: entity_id, search (first_name, last_name, email, phone)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Contact::with(['entity:id,name,nif', 'contactFunction:id,name']);

        if ($request->filled('entity_id')) {
            $query->where('entity_id', $request->integer('entity_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->lower();
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(first_name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        if ($request->filled('active')) {
            $query->where('active', filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = min($request->integer('per_page', 15), 100);

        return response()->json($query->latest()->paginate($perPage));
    }

    /**
     * Store a newly created contact.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entity_id'    => 'required|exists:entities,id',
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'function_id'  => 'nullable|exists:contact_functions,id',
            'phone'        => 'nullable|string|max:30',
            'mobile'       => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:255',
            'rgpd_consent' => 'boolean',
            'observations' => 'nullable|string',
            'active'       => 'boolean',
        ]);

        // Auto-generate contact number: C-{entity_id}-{sequence}
        $count = Contact::where('entity_id', $validated['entity_id'])->count() + 1;
        $validated['number'] = sprintf('C-%d-%03d', $validated['entity_id'], $count);

        $contact = Contact::create($validated);

        return response()->json($contact->load(['entity:id,name,nif', 'contactFunction:id,name']), 201);
    }

    /**
     * Display the specified contact.
     */
    public function show(Contact $contact): JsonResponse
    {
        return response()->json($contact->load(['entity:id,name,nif', 'contactFunction:id,name']));
    }

    /**
     * Update the specified contact.
     */
    public function update(Request $request, Contact $contact): JsonResponse
    {
        $validated = $request->validate([
            'entity_id'    => 'sometimes|exists:entities,id',
            'first_name'   => 'sometimes|string|max:255',
            'last_name'    => 'sometimes|string|max:255',
            'function_id'  => 'nullable|exists:contact_functions,id',
            'phone'        => 'nullable|string|max:30',
            'mobile'       => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:255',
            'rgpd_consent' => 'boolean',
            'observations' => 'nullable|string',
            'active'       => 'boolean',
        ]);

        $contact->update($validated);

        return response()->json($contact->load(['entity:id,name,nif', 'contactFunction:id,name']));
    }

    /**
     * Remove the specified contact.
     */
    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();

        return response()->json(null, 204);
    }
}
