<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CountryController extends Controller
{
    /**
     * Display a paginated listing of countries.
     * Filters: search (name, code)
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->filled('search')
            ? '%' . $request->string('search')->trim()->lower() . '%'
            : null;

        $countries = DB::select(
            $search
                ? 'select * from countries where lower(name) like ? or lower(code) like ? order by name'
                : 'select * from countries order by name',
            $search ? [$search, $search] : []
        );

        return response()->json(['data' => $countries]);
    }

    /**
     * Store a newly created country.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:countries,name',
            'code' => 'required|string|max:3|unique:countries,code',
        ]);

        $country = Country::create($validated);

        return response()->json($country, 201);
    }

    /**
     * Display the specified country.
     */
    public function show(Country $country): JsonResponse
    {
        return response()->json($country);
    }

    /**
     * Update the specified country.
     */
    public function update(Request $request, Country $country): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:countries,name,' . $country->id,
            'code' => 'sometimes|string|max:3|unique:countries,code,' . $country->id,
        ]);

        $country->update($validated);

        return response()->json($country);
    }

    /**
     * Remove the specified country.
     */
    public function destroy(Country $country): JsonResponse
    {
        // Check if country is in use
        if ($country->entities()->exists()) {
            return response()->json(['message' => 'Não é possível eliminar um país que está em uso.'], 422);
        }

        DB::delete('delete from countries where id = ?', [$country->id]);

        return response()->json(null, 204);
    }
}
