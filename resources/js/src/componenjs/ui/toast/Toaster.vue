<script setup lang="ts">
import { useToast } from './use-toast'
import Toast from './Toast.vue'
const { toasts } = useToast()
</script>
<template>
  <div class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2">
    <Toast v-for="t in toasts" :key="t.id" v-bind="t" />
  </div>
</template>
