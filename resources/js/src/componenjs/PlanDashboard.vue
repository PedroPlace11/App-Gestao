<template>
  <div class="plan-dashboard">
    <h2>Plano Atual</h2>

    <div v-if="subscription" class="plan-summary">
      <p><strong>Plano:</strong> {{ subscription.plan?.name ?? 'Sem plano' }}</p>
      <p><strong>Status:</strong> <span class="status-pill">{{ subscription.status }}</span></p>
      <p><strong>Período:</strong> {{ formatDate(subscription.current_period_start) }} - {{ formatDate(subscription.current_period_end) }}</p>
      <button class="btn-primary" @click="openChangePlan">Alterar Plano</button>
    </div>

    <div v-else class="empty-state">
      <p>Nenhum plano ativo.</p>
    </div>

    <div v-if="usage" class="usage-wrap">
      <h3>Limites e Utilização</h3>
      <div class="usage-grid">
        <p><strong>Utilizadores:</strong> {{ usage.users.used }} / {{ usage.users.limit ?? 'Ilimitado' }}</p>
        <p><strong>Entidades:</strong> {{ usage.entities.used }} / {{ usage.entities.limit ?? 'Ilimitado' }}</p>
      </div>
    </div>

    <div v-if="showChangePlan">
      <h3>Alterar Plano</h3>
      <select v-model="selectedPlanId" class="plan-select">
        <option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.name }}</option>
      </select>
      <div class="actions-row">
        <button class="btn-primary" @click="changePlan">Confirmar</button>
        <button class="btn-secondary" @click="() => showChangePlan = false">Cancelar</button>
      </div>
    </div>

    <h3>Logs de Alterações</h3>
    <ul class="logs-list">
      <li v-for="log in logs" :key="log.id">
        {{ log.change_type }} por {{ log.user?.name ?? ('utilizador #' + (log.user_id ?? '-')) }} em {{ formatDate(log.effective_at) }}
      </li>
      <li v-if="logs.length === 0" class="log-empty">Sem alterações registadas.</li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useTenantStore } from '../stores/tenantStore';

defineProps({
  companyId: { type: Number, required: false }
});

const tenantStore = useTenantStore();
const subscription = ref(null);
const usage = ref(null);
const plans = ref([]);
const logs = ref([]);
const showChangePlan = ref(false);
const selectedPlanId = ref(null);

const openChangePlan = () => {
  showChangePlan.value = true;
};

const formatDate = (dateValue) => {
  if (!dateValue) {
    return '-';
  }

  return new Date(dateValue).toLocaleDateString('pt-PT');
};

const changePlan = async () => {
  if (!selectedPlanId.value) {
    return;
  }

  await tenantStore.changePlan(selectedPlanId.value);
  showChangePlan.value = false;
  await fetchData();
};

const fetchData = async () => {
  const subData = await tenantStore.getSubscription();
  subscription.value = subData.subscription;
  usage.value = subData.usage;
  const plansData = await tenantStore.getPlans();
  plans.value = Array.isArray(plansData)
    ? plansData
    : (Array.isArray(plansData?.plans) ? plansData.plans : []);
  selectedPlanId.value = subscription.value?.plan_id ?? null;
  const logsResponse = await tenantStore.getPlanLogs();
  logs.value = Array.isArray(logsResponse?.data) ? logsResponse.data : [];
};

onMounted(fetchData);
</script>

<style scoped>
.plan-dashboard {
  margin-top: 14px;
  border: 1px solid rgba(148, 163, 184, 0.25);
  border-radius: 12px;
  padding: 14px;
  background: rgba(15, 23, 42, 0.6);
}

.plan-dashboard h2,
.plan-dashboard h3 {
  margin: 0 0 10px;
}

.plan-summary,
.usage-wrap {
  margin-bottom: 12px;
}

.status-pill {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 999px;
  background: rgba(56, 189, 248, 0.18);
  border: 1px solid rgba(56, 189, 248, 0.45);
}

.usage-grid {
  display: grid;
  gap: 6px;
}

.plan-select {
  width: 100%;
  background: rgba(30, 41, 59, 0.7);
  border: 1px solid rgba(148, 163, 184, 0.35);
  color: #e2e8f0;
  border-radius: 10px;
  padding: 9px;
}

.actions-row {
  display: flex;
  gap: 8px;
  margin-top: 10px;
}

.btn-primary,
.btn-secondary {
  border: 0;
  border-radius: 10px;
  padding: 8px 12px;
  cursor: pointer;
}

.btn-primary {
  background: linear-gradient(135deg, #38bdf8, #2563eb);
  color: #eff6ff;
}

.btn-secondary {
  background: rgba(30, 41, 59, 0.7);
  color: #cbd5e1;
  border: 1px solid rgba(148, 163, 184, 0.35);
}

.logs-list {
  margin: 0;
  padding-left: 18px;
  max-height: 170px;
  overflow: auto;
}

.log-empty {
  list-style: none;
  margin-left: -18px;
  color: #94a3b8;
}

.empty-state {
  color: #cbd5e1;
}
</style>
