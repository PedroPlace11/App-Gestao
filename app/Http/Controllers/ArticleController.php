<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    /**
     * Display a paginated listing of articles.
     * Filters: search (reference, name), active
     */
    public function index(Request $request): JsonResponse
    {
        $query = Article::with('taxRate:id,name,rate');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->lower();
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(reference) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->filled('active')) {
            $query->where('active', filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = min($request->integer('per_page', 15), 100);

        return response()->json($query->orderBy('name')->paginate($perPage));
    }

    /**
     * Store a newly created article. Accepts multipart/form-data for photo upload.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reference'    => 'required|string|max:100|unique:articles,reference',
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'tax_rate_id'  => 'required|exists:tax_rates,id',
            'observations' => 'nullable|string',
            'active'       => 'boolean',
            'photo'        => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('articles', 'private');
        }

        $article = Article::create($validated);

        return response()->json($article->load('taxRate:id,name,rate'), 201);
    }

    /**
     * Display the specified article.
     */
    public function show(Article $article): JsonResponse
    {
        return response()->json($article->load('taxRate:id,name,rate'));
    }

    /**
     * Update the specified article. Accepts multipart/form-data for new photo.
     */
    public function update(Request $request, Article $article): JsonResponse
    {
        $validated = $request->validate([
            'reference'    => 'sometimes|string|max:100|unique:articles,reference,' . $article->id,
            'name'         => 'sometimes|string|max:255',
            'description'  => 'nullable|string',
            'price'        => 'sometimes|numeric|min:0',
            'tax_rate_id'  => 'sometimes|exists:tax_rates,id',
            'observations' => 'nullable|string',
            'active'       => 'boolean',
            'photo'        => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            // Remove old photo if exists
            if ($article->photo) {
                Storage::disk('private')->delete($article->photo);
            }
            $validated['photo'] = $request->file('photo')->store('articles', 'private');
        }

        $article->update($validated);

        return response()->json($article->load('taxRate:id,name,rate'));
    }

    /**
     * Remove the specified article and its photo.
     */
    public function destroy(Article $article): JsonResponse
    {
        if ($article->photo) {
            Storage::disk('private')->delete($article->photo);
        }

        $article->delete();

        return response()->json(null, 204);
    }
}
