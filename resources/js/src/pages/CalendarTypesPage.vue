<script setup lang="ts">
import SimpleConfigTable from '../componenjs/SimpleConfigTable.vue';
</script>

<template>
  <SimpleConfigTable
    title="Tipos de Calendário"
    endpoint="/calendar-types"
    :columns="[
      { key: 'name', label: 'Nome' },
    ]"
  />
</template>
