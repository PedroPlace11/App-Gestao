<script setup lang="ts">
import SimpleConfigTable from '../componenjs/SimpleConfigTable.vue';

const exampleContactFunctions = [
  { id: 1, name: 'Diretor Geral' },
  { id: 2, name: 'Gestor Comercial' },
  { id: 3, name: 'Comprador' },
  { id: 4, name: 'Responsável Financeiro' },
  { id: 5, name: 'Técnico de Manutenção' },
  { id: 6, name: 'Assistente Administrativo' },
];
</script>

<template>
  <SimpleConfigTable
    title="Funções de Contacto"
    endpoint="/contact-functions"
    :example-items="exampleContactFunctions"
    :columns="[
      { key: 'name', label: 'Nome' },
    ]"
  />
</template>
