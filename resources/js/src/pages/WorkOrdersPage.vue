<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
</script>

<template>
  <Card>
    <CardHeader class="flex flex-row items-center justify-between">
      <CardTitle>Ordens de Trabalho</CardTitle>
      <Button>Nova Ordem</Button>
    </CardHeader>
    <CardContent>
      <div class="py-12 text-center text-muted-foreground">
        <p class="mb-4">Funcionalidade em desenvolvimento</p>
        <p class="text-sm">As ordens de trabalho serão geridas aqui.</p>
      </div>
    </CardContent>
  </Card>
</template>
