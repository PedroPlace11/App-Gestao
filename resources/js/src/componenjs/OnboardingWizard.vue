<template>
  <div class="onboarding-wizard">
    <h2>Configuração Inicial do Tenant</h2>
    <ul class="steps-list">
      <li v-for="step in steps" :key="step.id">
        <label class="step-item" :class="{ 'step-item--done': step.done }">
          <input type="checkbox" v-model="step.done" @change="saveChecklist" />
          <span>{{ step.label }}</span>
        </label>
      </li>
    </ul>
    <div class="progress-track" aria-hidden="true">
      <div class="progress-bar" :style="{ width: progressPercent + '%' }" />
    </div>
    <p class="wizard-status">{{ completionText }}</p>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useTenantStore } from '../stores/tenantStore';

defineProps({
  companyId: { type: Number, required: false }
});

const steps = ref([
  { id: 1, key: 'branding', label: 'Configurar branding', done: false },
  { id: 2, key: 'users', label: 'Adicionar utilizadores', done: false },
  { id: 3, key: 'permissions', label: 'Definir permissões', done: false },
]);
const tenantStore = useTenantStore();

const completionText = computed(() => {
  const done = steps.value.filter((step) => step.done).length;
  return `${done}/${steps.value.length} passos concluídos`;
});

const progressPercent = computed(() => {
  const done = steps.value.filter((step) => step.done).length;
  return Math.round((done / steps.value.length) * 100);
});

const saveChecklist = async () => {
  const payload = steps.value.reduce((acc, step) => {
    acc[step.key] = step.done;
    return acc;
  }, {});

  await tenantStore.updateOnboardingChecklist(payload);
};
</script>

<style scoped>
.onboarding-wizard {
  border: 1px solid rgba(148, 163, 184, 0.25);
  border-radius: 12px;
  padding: 14px;
  background: rgba(15, 23, 42, 0.55);
  margin-bottom: 12px;
}

.steps-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 8px;
}

.step-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px;
  border: 1px solid rgba(148, 163, 184, 0.22);
  border-radius: 10px;
  background: rgba(30, 41, 59, 0.45);
}

.step-item--done {
  border-color: rgba(34, 197, 94, 0.55);
  background: rgba(34, 197, 94, 0.12);
}

.progress-track {
  height: 8px;
  border-radius: 999px;
  background: rgba(148, 163, 184, 0.25);
  margin-top: 12px;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #22d3ee, #3b82f6);
  transition: width 0.25s ease;
}

.wizard-status {
  margin-top: 10px;
  font-size: 0.9rem;
  color: #cbd5e1;
}
</style>
