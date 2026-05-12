<script setup lang="ts">
import { cn } from '@/lib/utils'
defineProps<{ variant?: 'default' | 'secondary' | 'destructive' | 'outline'; class?: string }>()
</script>
<template>
  <span :class="cn('inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors', variant === 'destructive' ? 'border-transparent bg-destructive text-destructive-foreground' : variant === 'secondary' ? 'border-transparent bg-secondary text-secondary-foreground' : variant === 'outline' ? 'text-foreground' : 'border-transparent bg-primary text-primary-foreground', $props.class)">
    <slot />
  </span>
</template>
