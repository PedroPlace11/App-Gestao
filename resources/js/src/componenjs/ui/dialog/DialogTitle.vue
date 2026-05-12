<script setup lang="ts">
import { DialogTitle as RekaDialogTitle, type DialogTitleProps } from 'reka-ui'
const props = defineProps<DialogTitleProps>()
</script>
<template><RekaDialogTitle v-bind="props" class="text-lg font-semibold leading-none tracking-tight"><slot /></RekaDialogTitle></template>
