<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\SubscriptionChangeLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    public function plans(): JsonResponse
    {
        $plans = Plan::query()
            ->orderByDesc('is_active')
            ->orderBy('monthly_price', 'asc')
            ->get();

        return response()->json([
            'plans' => $plans,
        ]);
    }

    public function subscription(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');

        if (!$tenant) {
            return response()->json(['message' => 'Tenant ativo nao encontrado.'], 404);
        }

        $subscription = Subscription::query()
            ->with(['plan', 'scheduledPlan'])
            ->where('company_id', $tenant->id)
            ->first();

        return response()->json([
            'subscription' => $subscription,
            'usage' => $this->usageData($tenant->id, $subscription),
            'trial_notification' => $this->trialNotification($subscription),
        ]);
    }

    public function changePlan(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');

        if (!$tenant) {
            return response()->json(['message' => 'Tenant ativo nao encontrado.'], 404);
        }

        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $targetPlan = Plan::query()->findOrFail((int) $validated['plan_id']);

        $subscription = Subscription::query()->firstOrCreate(
            ['company_id' => $tenant->id],
            [
                'status' => 'active',
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]
        );

        $fromPlan = $subscription->plan;
        $fromPrice = (float) ($fromPlan?->monthly_price ?? 0);
        $toPrice = (float) ($targetPlan->monthly_price ?? 0);

        $changeType = 'upgrade';
        $proratedAmount = 0;

        if ($fromPlan && $toPrice < $fromPrice) {
            $changeType = 'downgrade';
            $subscription->scheduled_plan_id = $targetPlan->id;
        } else {
            $changeType = $fromPlan ? 'upgrade' : 'create';
            $proratedAmount = $this->calculateProratedAmount($subscription, $fromPrice, $toPrice);
            $subscription->plan_id = $targetPlan->id;
            $subscription->scheduled_plan_id = null;
            $subscription->status = $subscription->status === 'trialing' ? 'trialing' : 'active';
        }

        $subscription->save();

        SubscriptionChangeLog::create([
            'subscription_id' => $subscription->id,
            'company_id' => $tenant->id,
            'user_id' => $request->user()?->id,
            'from_plan_id' => $fromPlan?->id,
            'to_plan_id' => $targetPlan->id,
            'change_type' => $changeType,
            'prorated_amount' => $proratedAmount,
            'effective_at' => $changeType === 'downgrade' ? $subscription->current_period_end : now(),
            'meta' => [
                'from_price' => $fromPrice,
                'to_price' => $toPrice,
            ],
        ]);

        return response()->json([
            'message' => $changeType === 'downgrade'
                ? 'Downgrade agendado para o proximo ciclo.'
                : 'Plano atualizado imediatamente.',
            'subscription' => $subscription->fresh(['plan', 'scheduledPlan']),
            'prorated_amount' => round($proratedAmount, 2),
        ]);
    }

    public function cancel(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');

        if (!$tenant) {
            return response()->json(['message' => 'Tenant ativo nao encontrado.'], 404);
        }

        $subscription = Subscription::query()->where('company_id', $tenant->id)->first();

        if (!$subscription) {
            return response()->json(['message' => 'Subscricao nao encontrada.'], 404);
        }

        $subscription->update([
            'cancel_at_period_end' => true,
            'canceled_at' => now(),
            'status' => 'canceled',
        ]);

        SubscriptionChangeLog::create([
            'subscription_id' => $subscription->id,
            'company_id' => $tenant->id,
            'user_id' => $request->user()?->id,
            'from_plan_id' => $subscription->plan_id,
            'to_plan_id' => $subscription->plan_id,
            'change_type' => 'cancel',
            'prorated_amount' => null,
            'effective_at' => $subscription->current_period_end,
            'meta' => [
                'cancel_at_period_end' => true,
            ],
        ]);

        return response()->json([
            'message' => 'Cancelamento agendado para o final do ciclo atual.',
            'subscription' => $subscription,
        ]);
    }

    public function usage(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');

        if (!$tenant) {
            return response()->json(['message' => 'Tenant ativo nao encontrado.'], 404);
        }

        $subscription = Subscription::query()->with('plan')->where('company_id', $tenant->id)->first();

        return response()->json($this->usageData($tenant->id, $subscription));
    }

    public function auditLogs(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');

        if (!$tenant) {
            return response()->json(['message' => 'Tenant ativo nao encontrado.'], 404);
        }

        $perPage = min($request->integer('per_page', 25), 100);

        $logs = SubscriptionChangeLog::query()
            ->with(['fromPlan', 'toPlan', 'user'])
            ->where('company_id', $tenant->id)
            ->orderByDesc('effective_at')
            ->paginate($perPage);

        return response()->json($logs);
    }

    private function usageData(int $companyId, ?Subscription $subscription): array
    {
        $plan = $subscription?->plan;
        $usersCount = Company::query()
            ->whereKey($companyId)
            ->withCount('users')
            ->value('users_count') ?? 0;

        $entitiesCount = DB::table('entities')
            ->where('company_id', $companyId)
            ->count('id');

        return [
            'users' => [
                'used' => $usersCount,
                'limit' => $plan?->max_users,
            ],
            'entities' => [
                'used' => $entitiesCount,
                'limit' => $plan?->max_entities,
            ],
            'premium_features' => $plan?->features ?? [],
        ];
    }

    private function trialNotification(?Subscription $subscription): ?array
    {
        if (!$subscription || !$subscription->isTrialActive()) {
            return null;
        }

        $daysLeft = (int) now()->diffInDays($subscription->trial_ends_at, false);

        return [
            'days_left' => $daysLeft,
            'should_notify' => $daysLeft <= 3,
        ];
    }

    private function calculateProratedAmount(Subscription $subscription, float $fromPrice, float $toPrice): float
    {
        if ($toPrice <= $fromPrice) {
            return 0;
        }

        $periodStart = $subscription->current_period_start ?? now();
        $periodEnd = $subscription->current_period_end ?? now()->addMonth();

        $totalSeconds = max($periodEnd->diffInSeconds($periodStart), 1);
        $remainingSeconds = max($periodEnd->diffInSeconds(now(), false), 0);

        $remainingRatio = $remainingSeconds / $totalSeconds;

        return ($toPrice - $fromPrice) * $remainingRatio;
    }

    public function enforceBillingLifecycle(): void
    {
        $subscriptions = Subscription::query()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNotNull('scheduled_plan_id')
                      ->orWhere('cancel_at_period_end', true);
            })
            ->get();

        foreach ($subscriptions as $subscription) {
            $subscription->applyScheduledPlan();
            $subscription->cancelIfScheduled();
        }
    }
}
