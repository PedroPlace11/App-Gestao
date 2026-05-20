<template>
  <div class="tenant-management">
    <header class="page-header">
      <div>
        <h1>Gerenciamento de Tenants</h1>
        <p>Crie, organize e administre múltiplos tenants com onboarding e planos independentes.</p>
      </div>
      <button class="btn-primary" @click="openCreateTenantModal">Novo Tenant</button>
    </header>

    <div class="content-grid">
      <section class="panel panel-tenants">
        <h2>Tenants</h2>

        <ul v-if="tenants.length > 0" class="tenant-list">
          <li v-for="tenant in tenants" :key="tenant.id" class="tenant-item">
            <button
              class="tenant-item-btn"
              :class="{ 'tenant-item-btn--active': tenant.id === selectedTenantId }"
              @click="selectTenant(tenant.id)"
            >
              <span class="tenant-name">{{ tenant.name }}</span>
              <small class="tenant-slug">{{ tenant.slug }}</small>
            </button>
          </li>
        </ul>

        <div v-else class="empty-state">
          <p>Ainda não existem tenants para esta conta.</p>
          <button class="btn-secondary" @click="openCreateTenantModal">Criar Primeiro Tenant</button>
        </div>
      </section>

      <section class="panel panel-management">
        <h2>{{ selectedTenantTitle }}</h2>
        <OnboardingWizard v-if="showOnboarding || selectedTenantId" />
        <PlanDashboard v-if="selectedTenantId" :company-id="selectedTenantId" />
      </section>
    </div>

    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <h2>{{ modalTitle }}</h2>
        <form class="modal-form" @submit.prevent="submitTenant">
          <label for="tenant-name">Nome</label>
          <input id="tenant-name" v-model="tenantName" required />

          <label for="tenant-slug">Slug</label>
          <input id="tenant-slug" v-model="tenantSlug" required />

          <label for="tenant-plan">Plano Inicial</label>
          <select id="tenant-plan" v-model="selectedPlanId" required>
            <option v-for="plan in availablePlans" :key="plan.id" :value="plan.id">
              {{ plan.name || plan.slug || ('Plano #' + plan.id) }}{{ plan.is_active ? '' : ' (inativo)' }}
            </option>
          </select>
          <p v-if="availablePlans.length === 0" class="field-hint">Sem planos disponíveis. Crie planos para continuar.</p>

          <div class="modal-actions">
            <button class="btn-primary" type="submit">Criar Tenant</button>
            <button class="btn-secondary" type="button" @click="closeModal">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useTenantStore } from '../stores/tenantStore';
import OnboardingWizard from '../componenjs/OnboardingWizard.vue';
import PlanDashboard from '../componenjs/PlanDashboard.vue';

const tenantStore = useTenantStore();
const tenants = ref([]);
const showModal = ref(false);
const modalTitle = ref('');
const tenantName = ref('');
const tenantSlug = ref('');
const showOnboarding = ref(false);
const selectedTenantId = ref(null);
const availablePlans = ref([]);
const selectedPlanId = ref(null);
const selectedTenantTitle = computed(() => {
  const current = tenants.value.find((tenant) => tenant.id === selectedTenantId.value);
  return current ? `Gestão: ${current.name}` : 'Gestão do Tenant';
});

const fetchTenants = async () => {
  await tenantStore.fetchTenants();
  tenants.value = tenantStore.tenants;

  if (!selectedTenantId.value && tenantStore.activeTenantId) {
    selectedTenantId.value = tenantStore.activeTenantId;
  }
};

const openCreateTenantModal = () => {
  modalTitle.value = 'Criar Novo Tenant';
  tenantName.value = '';
  tenantSlug.value = '';
  showOnboarding.value = false;
  showModal.value = true;
};

const fetchPlans = async () => {
  const plansResponse = await tenantStore.getPlans();
  availablePlans.value = Array.isArray(plansResponse)
    ? plansResponse
    : (Array.isArray(plansResponse?.plans) ? plansResponse.plans : []);

  if (availablePlans.value.length > 0 && !selectedPlanId.value) {
    selectedPlanId.value = availablePlans.value[0].id;
  }
};

const selectTenant = async (tenantId) => {
  selectedTenantId.value = tenantId;
  if (tenantStore.activeTenantId !== tenantId) {
    await tenantStore.switchTenant(tenantId);
  }
};

const submitTenant = async () => {
  const createdTenant = await tenantStore.createTenantOnboarding({
    name: tenantName.value,
    slug: tenantSlug.value,
  });

  if (createdTenant?.id) {
    tenantStore.setActiveTenant(createdTenant.id);
  }

  // Após criar tenant, define plano inicial via billing.
  if (selectedPlanId.value) {
    await tenantStore.changePlan(selectedPlanId.value);
  }

  selectedTenantId.value = createdTenant?.id ?? tenantStore.activeTenantId;
  showOnboarding.value = true;
  await fetchTenants();
  closeModal();
};

const closeModal = () => {
  showModal.value = false;
};

fetchPlans();
fetchTenants();
</script>

<style scoped>
.tenant-management {
  padding: 24px;
  color: #e2e8f0;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 20px;
}

.page-header h1 {
  margin: 0;
  font-size: 1.6rem;
  font-weight: 700;
}

.page-header p {
  margin: 8px 0 0;
  color: #94a3b8;
}

.content-grid {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 16px;
}

.panel {
  background: linear-gradient(180deg, rgba(15, 23, 42, 0.92), rgba(15, 23, 42, 0.78));
  border: 1px solid rgba(148, 163, 184, 0.22);
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 16px 26px rgba(2, 6, 23, 0.24);
}

.panel h2 {
  margin: 0 0 12px;
  font-size: 1.05rem;
}

.tenant-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 10px;
}

.tenant-item-btn {
  width: 100%;
  text-align: left;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(30, 41, 59, 0.6);
  color: #e2e8f0;
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 10px;
  padding: 12px;
  transition: all 0.2s ease;
}

.tenant-item-btn:hover {
  border-color: rgba(59, 130, 246, 0.55);
  transform: translateY(-1px);
}

.tenant-item-btn--active {
  border-color: rgba(56, 189, 248, 0.75);
  background: rgba(30, 64, 175, 0.2);
}

.tenant-name {
  font-weight: 600;
}

.tenant-slug {
  color: #94a3b8;
}

.empty-state {
  border: 1px dashed rgba(148, 163, 184, 0.35);
  border-radius: 12px;
  padding: 14px;
}

.btn-primary,
.btn-secondary {
  border: 0;
  border-radius: 10px;
  padding: 10px 14px;
  cursor: pointer;
  font-weight: 600;
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

.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 40;
  background: rgba(2, 6, 23, 0.55);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal {
  width: min(520px, calc(100vw - 32px));
  background: #0f172a;
  border: 1px solid rgba(148, 163, 184, 0.35);
  border-radius: 14px;
  padding: 18px;
}

.modal-form {
  display: grid;
  gap: 10px;
}

.modal-form input,
.modal-form select {
  width: 100%;
  border: 1px solid rgba(148, 163, 184, 0.35);
  background: rgba(30, 41, 59, 0.7);
  color: #e2e8f0;
  border-radius: 10px;
  padding: 10px;
}

.modal-actions {
  margin-top: 8px;
  display: flex;
  gap: 10px;
}

.field-hint {
  margin: 2px 0 0;
  font-size: 0.82rem;
  color: #94a3b8;
}

@media (max-width: 1024px) {
  .content-grid {
    grid-template-columns: 1fr;
  }
}
</style>
