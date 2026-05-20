<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        forward_static_call([static::class, 'addGlobalScope'], 'tenant', function (Builder $builder) {
            $tenantId = static::resolveTenantId();

            if ($tenantId !== null) {
                $builder->where($builder->qualifyColumn('company_id'), $tenantId);
                return;
            }

            // Fail closed to prevent cross-tenant data leaks when no tenant is set.
            $builder->whereRaw('1 = 0');
        });

        forward_static_call([static::class, 'creating'], function ($model) {
            if (!empty($model->company_id)) {
                return;
            }

            $tenantId = static::resolveTenantId();

            if ($tenantId !== null) {
                $model->company_id = $tenantId;
            }
        });
    }

    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query
            ->withoutGlobalScope('tenant')
            ->where($this->qualifyColumn('company_id'), $tenantId);
    }

    protected static function resolveTenantId(): ?int
    {
        if (app()->bound('tenant.id')) {
            return (int) app('tenant.id');
        }

        if (app()->bound('request')) {
            $request = app('request');

            if (!$request instanceof \Illuminate\Http\Request) {
                return null;
            }

            $tenantId = $request->attributes->get('tenant_id');

            if ($tenantId === null && $request->hasSession()) {
                $tenantId = $request->session()->get('active_company_id');
            }

            if ($tenantId !== null) {
                return (int) $tenantId;
            }
        }

        $user = auth()->user();

        return $user?->active_company_id !== null
            ? (int) $user->active_company_id
            : null;
    }
}
