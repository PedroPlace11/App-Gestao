<script setup lang="ts">
import SimpleConfigTable from '../componenjs/SimpleConfigTable.vue';
</script>

<template>
  <SimpleConfigTable
    title="Países"
    endpoint="/countries"
    :columns="[
      { key: 'name', label: 'Nome' },
      { key: 'code', label: 'Código' },
    ]"
  />
</template>
