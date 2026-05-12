<script setup lang="ts">
import { type HTMLAttributes } from 'vue'
import { SelectContent as RekaSelectContent, type SelectContentProps, SelectPortal, SelectViewport } from 'reka-ui'
import { cn } from '@/lib/utils'
const props = defineProps<SelectContentProps & { class?: HTMLAttributes['class'] }>()
</script>
<template>
  <SelectPortal>
    <RekaSelectContent v-bind="props" :class="cn('relative z-50 min-w-[8rem] overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95', props.class)">
      <SelectViewport class="p-1">
        <slot />
      </SelectViewport>
    </RekaSelectContent>
  </SelectPortal>
</template>
