<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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
        $tenantId = (int) ($request->attributes->get('tenant_id') ?? 0);
        $hasMembershipTable = Schema::hasTable('company_user');

        $query = User::query()->with('roles');

        if ($hasMembershipTable) {
            $query->whereHas('companies', function ($companyQuery) use ($tenantId) {
                if ($tenantId > 0) {
                    $companyQuery->where('companies.id', $tenantId);
                }
            });
        }

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
            $query->orderBy('name', 'asc')->paginate($perPage)
        );
    }

    /**
     * Store a newly created user and send welcome email.
     */
    public function store(Request $request): JsonResponse
    {
        $tenantId = (int) ($request->attributes->get('tenant_id') ?? 0);
        $hasMembershipTable = Schema::hasTable('company_user');

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', Password::min(8)->mixedCase()->numbers()],
            'active'   => 'boolean',
        ]);

        $plainPassword = $validated['password'];

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'active' => $validated['active'] ?? true,
        ];

        if ($tenantId > 0 && Schema::hasColumn('users', 'active_company_id')) {
            $userData['active_company_id'] = $tenantId;
        }

        $user = User::create($userData);

        if ($tenantId > 0 && $hasMembershipTable) {
            $user->companies()->attach($tenantId, ['role' => 'member', 'is_owner' => false]);
        }

        Mail::to($user->email)->queue(new WelcomeUserMail($user, $plainPassword));

        return response()->json($user, 201);
    }

    /**
     * Display the specified user.
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $tenantId = (int) ($request->attributes->get('tenant_id') ?? 0);
        $hasMembershipTable = Schema::hasTable('company_user');

        if ($tenantId > 0 && $hasMembershipTable && !$user->companies()->where('companies.id', $tenantId)->exists()) {
            abort(404);
        }

        return response()->json($user->load('roles'));
    }

    /**
     * Update the specified user's basic data.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $tenantId = (int) ($request->attributes->get('tenant_id') ?? 0);
        $hasMembershipTable = Schema::hasTable('company_user');

        if ($tenantId > 0 && $hasMembershipTable && !$user->companies()->where('companies.id', $tenantId)->exists()) {
            abort(404);
        }

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
        $tenantId = (int) ($request->attributes->get('tenant_id') ?? 0);
        $hasMembershipTable = Schema::hasTable('company_user');

        if ($tenantId > 0 && $hasMembershipTable && !$user->companies()->where('companies.id', $tenantId)->exists()) {
            abort(404);
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return response()->json(['message' => 'Password atualizada com sucesso.']);
    }

    /**
     * Toggle the active status of the specified user.
     */
    public function toggle(Request $request, User $user): JsonResponse
    {
        $tenantId = (int) ($request->attributes->get('tenant_id') ?? 0);
        $hasMembershipTable = Schema::hasTable('company_user');

        if ($tenantId > 0 && $hasMembershipTable && !$user->companies()->where('companies.id', $tenantId)->exists()) {
            abort(404);
        }

        // Prevent deactivating yourself
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Não pode desativar a sua própria conta.'], 422);
        }

        $user->update(['active' => ! $user->active]);

        return response()->json([
            'message' => $user->active ? 'Utilizador ativado.' : 'Utilizador desativado.',
            'user'    => $user,
        ]);
    }
}
