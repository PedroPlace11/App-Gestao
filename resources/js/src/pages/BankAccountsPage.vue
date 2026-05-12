<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
</script>

<template>
  <Card>
    <CardHeader class="flex flex-row items-center justify-between">
      <CardTitle>Contas Bancárias</CardTitle>
      <Button>Nova Conta</Button>
    </CardHeader>
    <CardContent>
      <div class="py-12 text-center text-muted-foreground">
        <p class="mb-4">Funcionalidade em desenvolvimento</p>
        <p class="text-sm">As contas bancárias serão geridas aqui.</p>
      </div>
    </CardContent>
  </Card>
</template>
