<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $activeTenantId = (int) ($request->attributes->get('tenant_id') ?? $user?->active_company_id ?? 0);
        $hasMembershipTable = Schema::hasTable('company_user');

        $query = Company::query()->orderBy('name', 'asc');

        if ($hasMembershipTable) {
            $query = $user->companies()->orderBy('name', 'asc');
        }

        $tenants = $query
            ->get(['companies.id', 'companies.name', 'companies.slug'])
            ->map(function (Company $company) use ($activeTenantId, $hasMembershipTable) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'slug' => $company->slug,
                    'is_active' => $company->id === $activeTenantId,
                    'role' => $hasMembershipTable ? ($company->pivot?->role) : null,
                    'is_owner' => $hasMembershipTable ? (bool) ($company->pivot?->is_owner ?? false) : false,
                ];
            });

        return response()->json([
            'active_tenant_id' => $activeTenantId,
            'tenants' => $tenants,
        ]);
    }

    public function current(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');

        if (!$tenant instanceof Company) {
            return response()->json(['message' => 'Nenhum tenant ativo.'], 404);
        }

        return response()->json($tenant);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:80|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/|unique:companies,slug',
            'settings' => 'nullable|array',
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        if (empty($slug)) {
            $slug = 'tenant';
        }

        $baseSlug = $slug;
        $suffix = 2;

        while (Company::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        $company = Company::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'address' => '',
            'postal_code' => '',
            'city' => '',
            'tax_id' => null,
            'owner_user_id' => $user->id,
            'settings' => array_merge([
                'onboarding' => [
                    'branding' => false,
                    'users' => false,
                    'permissions' => false,
                ],
                'preferences' => [
                    'default_landing_page' => '/dashboard',
                ],
            ], $validated['settings'] ?? []),
        ]);

        if (Schema::hasTable('company_user')) {
            $company->users()->attach($user->id, ['role' => 'owner', 'is_owner' => true]);
        }

        if (Schema::hasColumn('users', 'active_company_id')) {
            $user->forceFill(['active_company_id' => $company->id])->save();
        }
        $request->session()->put('active_company_id', $company->id);

        $this->createTrialSubscription($company);

        return response()->json($company, 201);
    }

    public function switch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:companies,id',
        ]);

        $user = $request->user();

        if (Schema::hasTable('company_user')) {
            $tenant = $user->companies()->whereKey((int) $validated['tenant_id'])->first();
        } else {
            $tenant = Company::query()->whereKey((int) $validated['tenant_id'])->first();
        }

        if (!$tenant) {
            return response()->json(['message' => 'Sem permissao para este tenant.'], 403);
        }

        $request->session()->put('active_company_id', $tenant->id);
        if (Schema::hasColumn('users', 'active_company_id')) {
            $user->forceFill(['active_company_id' => $tenant->id])->save();
        }

        return response()->json([
            'message' => 'Tenant ativo atualizado.',
            'tenant' => $tenant,
        ]);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');

        if (!$tenant instanceof Company) {
            return response()->json(['message' => 'Tenant ativo nao encontrado.'], 404);
        }

        $validated = $request->validate([
            'preferences' => 'required|array',
        ]);

        $settings = $tenant->settings ?? [];
        $settings['preferences'] = array_merge($settings['preferences'] ?? [], $validated['preferences']);

        $tenant->update(['settings' => $settings]);

        return response()->json([
            'message' => 'Preferencias do tenant atualizadas.',
            'settings' => $tenant->settings,
        ]);
    }

    public function updateOnboardingChecklist(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');

        if (!$tenant instanceof Company) {
            return response()->json(['message' => 'Tenant ativo nao encontrado.'], 404);
        }

        $validated = $request->validate([
            'onboarding' => 'required|array',
        ]);

        $settings = $tenant->settings ?? [];
        $settings['onboarding'] = array_merge($settings['onboarding'] ?? [], $validated['onboarding']);

        $tenant->update(['settings' => $settings]);

        return response()->json([
            'message' => 'Checklist de onboarding atualizada.',
            'settings' => $tenant->settings,
        ]);
    }

    private function createTrialSubscription(Company $company): void
    {
        if (!Schema::hasTable('subscriptions')) {
            return;
        }

        $plan = Plan::query()->where('is_active', true)->orderBy('monthly_price', 'asc')->first();

        Subscription::query()->updateOrCreate(
            ['company_id' => $company->id],
            [
                'plan_id' => $plan?->id,
                'status' => 'trialing',
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
                'trial_ends_at' => Carbon::now()->addDays(14),
                'cancel_at_period_end' => false,
            ]
        );
    }
}
