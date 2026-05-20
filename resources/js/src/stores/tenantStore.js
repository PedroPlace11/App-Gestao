import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { useApi } from '../composables/useApi';

const ACTIVE_TENANT_STORAGE_KEY = 'active-tenant-id';

export const useTenantStore = defineStore('tenant-store', () => {
  const { get, post, put, remove } = useApi();

  const tenants = ref([]);
  const activeTenantId = ref(Number(window.__TENANT_CONTEXT__?.active_tenant_id ?? localStorage.getItem(ACTIVE_TENANT_STORAGE_KEY) ?? 0));
  const loading = ref(false);

  const activeTenant = computed(() => tenants.value.find((tenant) => tenant.id === activeTenantId.value) ?? null);

  const saveActiveTenant = (tenantId) => {
    activeTenantId.value = Number(tenantId);

    if (activeTenantId.value > 0) {
      localStorage.setItem(ACTIVE_TENANT_STORAGE_KEY, String(activeTenantId.value));
    } else {
      localStorage.removeItem(ACTIVE_TENANT_STORAGE_KEY);
    }
  };

  const fetchTenants = async () => {
    loading.value = true;

    try {
      const data = await get('/tenants');
      tenants.value = Array.isArray(data?.tenants) ? data.tenants : [];

      const serverActiveId = Number(data?.active_tenant_id ?? 0);
      if (serverActiveId > 0) {
        saveActiveTenant(serverActiveId);
      }
    } finally {
      loading.value = false;
    }
  };

  const switchTenant = async (tenantId) => {
    if (!tenantId || Number(tenantId) === activeTenantId.value) {
      return;
    }

    await post('/tenants/switch', { tenant_id: Number(tenantId) });
    saveActiveTenant(Number(tenantId));

    window.location.reload();
  };

  return {
    tenants,
    activeTenantId,
    activeTenant,
    loading,
    fetchTenants,
    switchTenant,
    setActiveTenant(tenantId) {
      saveActiveTenant(tenantId);
    },
    async createTenant(payload) {
      return await post('/tenants', payload);
    },
    async updateTenant(id, payload) {
      return await put(`/tenants/${id}`, payload);
    },
    async deleteTenant(id) {
      return await remove(`/tenants/${id}`);
    },
    // Onboarding self-service
    async createTenantOnboarding(payload) {
      return await post('/tenants', payload);
    },
    async updateOnboardingChecklist(payload) {
      return await put('/tenants/onboarding-checklist', { onboarding: payload });
    },
    async getSubscription() {
      return await get('/billing/subscription');
    },
    async getPlans() {
      return await get('/billing/plans');
    },
    async changePlan(planId) {
      return await post('/billing/change-plan', { plan_id: planId });
    },
    async getPlanLogs() {
      return await get('/billing/logs');
    },
  };
});
