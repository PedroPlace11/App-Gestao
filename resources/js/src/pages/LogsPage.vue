<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useApi } from '../composables/useApi';
import { useToast } from '@/components/ui/toast/use-toast';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

const { get } = useApi();
const { toast } = useToast();

const logs = ref<any[]>([]);
const isLoading = ref(false);

const fetchLogs = async () => {
  isLoading.value = true;
  try {
    const response = await get<any[]>('/activity-logs');
    logs.value = response ?? [];
  } catch {
    toast({
      title: 'Erro ao carregar',
      description: 'Não foi possível obter os logs.',
      variant: 'destructive',
    });
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchLogs);
</script>

<template>
  <Card>
    <CardHeader class="flex flex-row items-center justify-between">
      <CardTitle>Logs de Atividade</CardTitle>
      <Button variant="outline" @click="fetchLogs">Atualizar</Button>
    </CardHeader>

    <CardContent>
      <div v-if="isLoading" class="py-8 text-center text-sm text-muted-foreground">
        A carregar logs...
      </div>

      <div v-else>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Data</TableHead>
              <TableHead>Hora</TableHead>
              <TableHead>Utilizador</TableHead>
              <TableHead>Menu</TableHead>
              <TableHead>Ação</TableHead>
              <TableHead>Dispositivo</TableHead>
              <TableHead>IP</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="log in logs" :key="log.id">
              <TableCell>{{ new Date(log.created_at).toLocaleDateString('pt-PT') }}</TableCell>
              <TableCell>{{ new Date(log.created_at).toLocaleTimeString('pt-PT') }}</TableCell>
              <TableCell>{{ log.user?.name || '-' }}</TableCell>
              <TableCell>{{ log.subject_type || '-' }}</TableCell>
              <TableCell>{{ log.description }}</TableCell>
              <TableCell>{{ log.user_agent?.split(' ')[0] || '-' }}</TableCell>
              <TableCell>{{ log.ip_address || '-' }}</TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <div v-if="logs.length === 0" class="py-8 text-center text-sm text-muted-foreground">
          Nenhum log encontrado.
        </div>
      </div>
    </CardContent>
  </Card>
</template>
