<script setup lang="ts">
import SimpleConfigTable from '../componenjs/SimpleConfigTable.vue';

const exampleCalendarTypes = [
  { id: 1, name: 'Reunião Interna' },
  { id: 2, name: 'Reunião com Cliente' },
  { id: 3, name: 'Visita Técnica' },
  { id: 4, name: 'Entrega de Projeto' },
  { id: 5, name: 'Formação' },
  { id: 6, name: 'Feriado' },
  { id: 7, name: 'Evento Corporativo' },
];
</script>

<template>
  <SimpleConfigTable
    title="Tipos de Calendário"
    endpoint="/calendar-types"
    :example-items="exampleCalendarTypes"
    :columns="[
      { key: 'name', label: 'Nome' },
    ]"
  />
</template>
