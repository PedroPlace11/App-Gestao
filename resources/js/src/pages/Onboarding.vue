<template>
  <div class="onboarding">
    <h1>Bem-vindo ao Onboarding</h1>
    <form @submit.prevent="submitOnboarding">
      <div>
        <label for="tenant-name">Nome do Tenant</label>
        <input id="tenant-name" v-model="tenantName" required />
      </div>
      <div>
        <label for="tenant-slug">Slug do Tenant</label>
        <input id="tenant-slug" v-model="tenantSlug" required />
      </div>
      <button type="submit">Concluir</button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useTenantStore } from '../stores/tenantStore';

const tenantStore = useTenantStore();
const tenantName = ref('');
const tenantSlug = ref('');

const submitOnboarding = async () => {
  try {
    await tenantStore.createTenant({ name: tenantName.value, slug: tenantSlug.value });
    alert('Onboarding concluído com sucesso!');
  } catch (error) {
    console.error('Erro ao concluir o onboarding:', error);
    alert('Erro ao concluir o onboarding.');
  }
};
</script>

<style scoped>
.onboarding {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
  border: 1px solid #ccc;
  border-radius: 8px;
}

.onboarding h1 {
  text-align: center;
}

.onboarding form {
  display: flex;
  flex-direction: column;
}

.onboarding form div {
  margin-bottom: 15px;
}

.onboarding form label {
  display: block;
  margin-bottom: 5px;
}

.onboarding form input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.onboarding form button {
  padding: 10px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.onboarding form button:hover {
  background-color: #0056b3;
}
</style>
