<script setup lang="ts">
import { type HTMLAttributes } from 'vue'
import { SelectItem as RekaSelectItem, type SelectItemProps, SelectItemText } from 'reka-ui'
import { cn } from '@/lib/utils'
const props = defineProps<SelectItemProps & { class?: HTMLAttributes['class'] }>()
</script>
<template>
  <RekaSelectItem v-bind="props" :class="cn('relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50', props.class)">
    <SelectItemText><slot /></SelectItemText>
  </RekaSelectItem>
</template>
