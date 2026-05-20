<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionChangeLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TenantOnboardingController extends Controller
{
    // Criação de novo tenant com wizard
    public function create(Request $request): JsonResponse
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:companies,slug',
            'plan_id' => 'required|exists:plans,id',
        ]);

        $user = $request->user();
        DB::beginTransaction();
        try {
            $company = Company::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'address' => $request->address ?? '',
                'postal_code' => $request->postal_code ?? '',
                'city' => $request->city ?? '',
                'nif' => $request->nif ?? '',
            ]);
            $company->users()->attach($user->id, ['role' => 'owner', 'is_owner' => true]);
            $plan = Plan::findOrFail($request->plan_id);
            $subscription = Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'starts_at' => now(),
                'ends_at' => null,
                'is_active' => true,
                'status' => 'active',
                'price' => $plan->monthly_price,
                'billing_cycle' => 'monthly',
            ]);
            DB::commit();
            return response()->json(['success' => true, 'company' => $company, 'subscription' => $subscription]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // Wizard de onboarding/checklist
    public function onboardingChecklist(Request $request, $companyId): JsonResponse
    {
        // Exemplo de checklist
        $steps = [
            ['id' => 1, 'label' => 'Configurar branding', 'done' => false],
            ['id' => 2, 'label' => 'Adicionar utilizadores', 'done' => false],
            ['id' => 3, 'label' => 'Definir permissões', 'done' => false],
        ];
        return response()->json(['steps' => $steps]);
    }

    // Upgrade/downgrade de plano
    public function changePlan(Request $request, $companyId): JsonResponse
    {
        $this->validate($request, [
            'plan_id' => 'required|exists:plans,id',
        ]);
        $user = $request->user();
        $company = Company::findOrFail($companyId);
        // Verificar se user é owner
        if (!$company->users()->wherePivot('user_id', $user->id)->wherePivot('is_owner', true)->exists()) {
            return response()->json(['success' => false, 'error' => 'Acesso negado'], 403);
        }
        $plan = Plan::findOrFail($request->plan_id);
        $subscription = $company->subscriptions()->latest()->first();
        $old = $subscription->toArray();
        $subscription->plan_id = $plan->id;
        $subscription->price = $plan->monthly_price;
        $subscription->save();
        SubscriptionChangeLog::create([
            'subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'action' => 'change_plan',
            'old_values' => $old,
            'new_values' => $subscription->toArray(),
        ]);
        return response()->json(['success' => true, 'subscription' => $subscription]);
    }

    // Logs de alterações de plano
    public function planLogs(Request $request, $companyId): JsonResponse
    {
        $company = Company::findOrFail($companyId);
        $logs = SubscriptionChangeLog::whereHas('subscription', function($q) use ($company) {
            $q->where('company_id', $company->id);
        })->latest()->get();
        return response()->json(['logs' => $logs]);
    }
}
