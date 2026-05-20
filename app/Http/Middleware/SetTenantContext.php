<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;

class SetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $tenantIdFromHeader = $request->header('X-Tenant-Id');
        $tenantSlugFromHeader = $request->header('X-Tenant-Slug');
        $hasMembershipTable = Schema::hasTable('company_user');
        $hasCompanySlug = Schema::hasColumn('companies', 'slug');

        $candidate = null;

        if (!empty($tenantIdFromHeader)) {
            $tenantId = (int) $tenantIdFromHeader;
            $candidate = Company::query()->whereKey($tenantId)->first();

            if ($hasMembershipTable && $candidate && !$user->companies()->whereKey($tenantId)->exists()) {
                $candidate = null;
            }
        }

        if (!$candidate && $hasCompanySlug && !empty($tenantSlugFromHeader)) {
            $candidate = Company::query()->where('slug', $tenantSlugFromHeader)->first();

            if ($hasMembershipTable && $candidate && !$user->companies()->whereKey($candidate->id)->exists()) {
                $candidate = null;
            }
        }

        if (!$candidate && $request->session()->has('active_company_id')) {
            $candidate = Company::query()->whereKey((int) $request->session()->get('active_company_id'))->first();

            if ($hasMembershipTable && $candidate && !$user->companies()->whereKey($candidate->id)->exists()) {
                $candidate = null;
            }
        }

        if (!$candidate && !empty($user->active_company_id)) {
            $candidate = Company::query()->whereKey((int) $user->active_company_id)->first();

            if ($hasMembershipTable && $candidate && !$user->companies()->whereKey($candidate->id)->exists()) {
                $candidate = null;
            }
        }

        if (!$candidate) {
            $candidate = Company::query()->orderBy('name', 'asc')->first();

            if ($hasMembershipTable && $candidate && !$user->companies()->whereKey($candidate->id)->exists()) {
                $candidate = $user->companies()->orderBy('name', 'asc')->first();
            }
        }

        if (!$candidate) {
            return $next($request);
        }

        if ($candidate && !Gate::allows('access-tenant', $candidate)) {
            abort(403, 'Unauthorized access to tenant.');
        }

        $this->bindTenant($request, $candidate);

        return $next($request);
    }

    private function bindTenant(Request $request, Company $company): void
    {
        app()->instance('tenant', $company);
        app()->instance('tenant.id', $company->id);

        $request->attributes->set('tenant', $company);
        $request->attributes->set('tenant_id', $company->id);

        $request->session()->put('active_company_id', $company->id);

        $user = $request->user();

        if ($user && Schema::hasColumn('users', 'active_company_id') && (int) $user->active_company_id !== (int) $company->id) {
            $user->forceFill(['active_company_id' => $company->id])->save();
        }

        if (function_exists('setPermissionsTeamId')) {
            setPermissionsTeamId($company->id);
        }
    }
}
