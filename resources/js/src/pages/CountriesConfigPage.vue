<script setup lang="ts">
import SimpleConfigTable from '../componenjs/SimpleConfigTable.vue';

const exampleCountries = [
  { id: 1, name: 'Portugal', code: 'PT' },
  { id: 2, name: 'Espanha', code: 'ES' },
  { id: 3, name: 'França', code: 'FR' },
  { id: 4, name: 'Alemanha', code: 'DE' },
  { id: 5, name: 'Angola', code: 'AO' },
  { id: 6, name: 'Moçambique', code: 'MZ' },
  { id: 7, name: 'Brasil', code: 'BR' },
];
</script>

<template>
  <SimpleConfigTable
    title="Países"
    endpoint="/countries"
    :example-items="exampleCountries"
    :columns="[
      { key: 'name', label: 'Nome' },
      { key: 'code', label: 'Código' },
    ]"
  />
</template>
