<script setup lang="ts">
import SimpleConfigTable from '../componenjs/SimpleConfigTable.vue';
</script>

<template>
  <SimpleConfigTable
    title="Taxas de IVA"
    endpoint="/tax-rates"
    :columns="[
      { key: 'name', label: 'Nome' },
      { key: 'rate', label: 'Taxa (%)' },
    ]"
  />
</template>
