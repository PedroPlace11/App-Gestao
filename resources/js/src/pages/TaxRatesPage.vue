<script setup lang="ts">
import SimpleConfigTable from '../componenjs/SimpleConfigTable.vue';

const exampleTaxRates = [
  { id: 1, name: 'IVA Reduzido', rate: 6 },
  { id: 2, name: 'IVA Intermediário', rate: 13 },
  { id: 3, name: 'IVA Normal', rate: 23 },
];
</script>

<template>
  <SimpleConfigTable
    title="Taxas de IVA"
    endpoint="/tax-rates"
    :example-items="exampleTaxRates"
    :columns="[
      { key: 'name', label: 'Nome' },
      { key: 'rate', label: 'Taxa (%)' },
    ]"
  />
</template>
