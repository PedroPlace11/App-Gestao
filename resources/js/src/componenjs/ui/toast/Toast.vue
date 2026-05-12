<script setup lang="ts">
defineProps<{ title?: string; description?: string; variant?: 'default' | 'destructive' }>()
</script>
<template>
  <div :class="['rounded-md border p-4 shadow-md', variant === 'destructive' ? 'border-destructive bg-destructive text-destructive-foreground' : 'border-border bg-background text-foreground']">
    <p v-if="title" class="font-medium">{{ title }}</p>
    <p v-if="description" class="text-sm opacity-90">{{ description }}</p>
  </div>
</template>
