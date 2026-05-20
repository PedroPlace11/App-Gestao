<template>
  <div class="billing">
    <h1>Gerenciamento de Billing</h1>

    <h2>Planos Disponíveis</h2>
    <ul>
      <li v-for="plan in plans" :key="plan.id">
        <span>{{ plan.name }} - {{ plan.price | currency }}</span>
        <button @click="subscribeToPlan(plan.id)">Assinar</button>
      </li>
    </ul>

    <h2>Assinatura Atual</h2>
    <div v-if="currentSubscription">
      <p>Plano: {{ currentSubscription.plan.name }}</p>
      <p>Próximo ciclo: {{ currentSubscription.current_period_end | formatDate }}</p>
      <button @click="cancelSubscription">Cancelar Assinatura</button>
    </div>
    <div v-else>
      <p>Você não possui uma assinatura ativa.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useBillingStore } from '../stores/billingStore';

const billingStore = useBillingStore();
const plans = ref([]);
const currentSubscription = ref(null);

const fetchBillingData = async () => {
  plans.value = await billingStore.fetchPlans();
  currentSubscription.value = await billingStore.fetchCurrentSubscription();
};

const subscribeToPlan = async (planId) => {
  await billingStore.subscribeToPlan(planId);
  fetchBillingData();
};

const cancelSubscription = async () => {
  if (confirm('Tem certeza que deseja cancelar sua assinatura?')) {
    await billingStore.cancelSubscription();
    fetchBillingData();
  }
};

onMounted(fetchBillingData);
</script>

<style scoped>
.billing {
  padding: 20px;
}

.billing ul {
  list-style: none;
  padding: 0;
}

.billing li {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}
</style>
