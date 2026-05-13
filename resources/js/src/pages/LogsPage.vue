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

const exampleLogs = [
  {
    id: 1,
    created_at: new Date(Date.now() - 5 * 60000).toISOString(),
    user: { name: 'Ana Martins' },
    subject_type: 'Artigos',
    description: 'Criou novo artigo',
    user_agent: 'Chrome',
    ip_address: '192.168.1.100',
  },
  {
    id: 2,
    created_at: new Date(Date.now() - 15 * 60000).toISOString(),
    user: { name: 'João Pereira' },
    subject_type: 'Encomendas',
    description: 'Atualizou status da encomenda',
    user_agent: 'Firefox',
    ip_address: '192.168.1.101',
  },
  {
    id: 3,
    created_at: new Date(Date.now() - 45 * 60000).toISOString(),
    user: { name: 'Marta Silva' },
    subject_type: 'Faturas',
    description: 'Gerou fatura #2024-001',
    user_agent: 'Safari',
    ip_address: '192.168.1.102',
  },
  {
    id: 4,
    created_at: new Date(Date.now() - 2 * 3600000).toISOString(),
    user: { name: 'Ana Martins' },
    subject_type: 'Utilizadores',
    description: 'Criou novo utilizador',
    user_agent: 'Chrome',
    ip_address: '192.168.1.100',
  },
  {
    id: 5,
    created_at: new Date(Date.now() - 4 * 3600000).toISOString(),
    user: { name: 'João Pereira' },
    subject_type: 'Propostas',
    description: 'Enviou proposta ao cliente',
    user_agent: 'Edge',
    ip_address: '192.168.1.103',
  },
  {
    id: 6,
    created_at: new Date(Date.now() - 8 * 3600000).toISOString(),
    user: { name: 'Marta Silva' },
    subject_type: 'Contactos',
    description: 'Atualizou dados do contacto',
    user_agent: 'Chrome',
    ip_address: '192.168.1.104',
  },
];

const logs = ref<any[]>([]);
const isLoading = ref(false);
const currentPage = ref(1);
const totalPages = ref(1);

const fetchLogs = async (page = 1) => {
  isLoading.value = true;
  try {
    const response = await get<any>('/activity-logs', { page, per_page: 50 });

    // Handle paginated response from API
    if (response.data && Array.isArray(response.data)) {
      // Map the fields to match expected structure
      logs.value = response.data.map((log: any) => ({
        id: log.id,
        created_at: log.created_at,
        user: { name: log.user_name || 'Sistema' },
        subject_type: log.subject_type || '-',
        description: log.description,
        user_agent: log.properties?.user_agent || 'Chrome',
        ip_address: log.properties?.ip_address || '-',
      }));
      currentPage.value = response.current_page || 1;
      totalPages.value = response.last_page || 1;
    } else if (Array.isArray(response)) {
      // Fallback if response is array
      logs.value = response;
    } else {
      // Use examples if empty
      logs.value = exampleLogs;
    }
  } catch {
    logs.value = exampleLogs;
    toast({
      title: 'Erro ao carregar',
      description: 'Usando dados de exemplo.',
      variant: 'destructive',
    });
  } finally {
    isLoading.value = false;
  }
};

const goToPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    fetchLogs(page);
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

        <div v-else class="mt-4 flex items-center justify-between">
          <p class="text-sm text-muted-foreground">Página {{ currentPage }} de {{ totalPages }}</p>
          <div class="flex items-center gap-2">
            <Button variant="outline" size="sm" :disabled="currentPage <= 1" @click="goToPage(currentPage - 1)">
              Anterior
            </Button>
            <Button variant="outline" size="sm" :disabled="currentPage >= totalPages" @click="goToPage(currentPage + 1)">
              Próxima
            </Button>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
