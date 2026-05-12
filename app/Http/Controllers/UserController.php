<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a paginated listing of users with optional search.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->lower();
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $perPage = min($request->integer('per_page', 15), 100);

        return response()->json(
            $query->orderBy('name')->paginate($perPage)
        );
    }

    /**
     * Store a newly created user and send welcome email.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', Password::min(8)->mixedCase()->numbers()],
            'active'   => 'boolean',
        ]);

        $plainPassword = $validated['password'];

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'active'   => $validated['active'] ?? true,
        ]);

        Mail::to($user->email)->queue(new WelcomeUserMail($user, $plainPassword));

        return response()->json($user, 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json($user->load('roles'));
    }

    /**
     * Update the specified user's basic data.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name'   => 'sometimes|string|max:255',
            'email'  => 'sometimes|email|unique:users,email,' . $user->id,
            'active' => 'sometimes|boolean',
        ]);

        $user->update($validated);

        return response()->json($user->fresh());
    }

    /**
     * Update the specified user's password.
     */
    public function updatePassword(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return response()->json(['message' => 'Password atualizada com sucesso.']);
    }

    /**
     * Toggle the active status of the specified user.
     */
    public function toggle(User $user): JsonResponse
    {
        // Prevent deactivating yourself
        if ($user->id === request()->user()->id) {
            return response()->json(['message' => 'Não pode desativar a sua própria conta.'], 422);
        }

        $user->update(['active' => ! $user->active]);

        return response()->json([
            'message' => $user->active ? 'Utilizador ativado.' : 'Utilizador desativado.',
            'user'    => $user,
        ]);
    }
}
