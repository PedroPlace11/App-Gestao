<script setup lang="ts">
import SimpleConfigTable from '../componenjs/SimpleConfigTable.vue';
</script>

<template>
  <SimpleConfigTable
    title="Ações de Calendário"
    endpoint="/calendar-actions"
    :columns="[
      { key: 'name', label: 'Nome' },
    ]"
  />
</template>
