<script setup lang="ts">
import SimpleConfigTable from '../componenjs/SimpleConfigTable.vue';

const exampleCalendarActions = [
  { id: 1, name: 'Enviar Email' },
  { id: 2, name: 'Enviar SMS' },
  { id: 3, name: 'Notificação na Aplicação' },
  { id: 4, name: 'Ligação Telefónica' },
  { id: 5, name: 'Relatório' },
  { id: 6, name: 'Criar Tarefa' },
  { id: 7, name: 'Registar Atividade' },
];
</script>

<template>
  <SimpleConfigTable
    title="Ações de Calendário"
    endpoint="/calendar-actions"
    :example-items="exampleCalendarActions"
    :columns="[
      { key: 'name', label: 'Nome' },
    ]"
  />
</template>
