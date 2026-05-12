<script setup lang="ts">
import SimpleConfigTable from '../componenjs/SimpleConfigTable.vue';
</script>

<template>
  <SimpleConfigTable
    title="Funções de Contacto"
    endpoint="/contact-functions"
    :columns="[
      { key: 'name', label: 'Nome' },
    ]"
  />
</template>
