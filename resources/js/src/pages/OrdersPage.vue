<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useApi } from '../composables/useApi';
import { usePaginatedTable } from '../composables/usePaginatedTable';
import { useToast } from '@/components/ui/toast/use-toast';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

const { get, post, put } = useApi();
const { toast } = useToast();

const orders = ref<Order[]>([]);
const entities = ref<Entity[]>([]);
const isLoading = ref(false);
const isFormOpen = ref(false);
const isDetailOpen = ref(false);
const selectedOrder = ref<Order | null>(null);
const isSubmitting = ref(false);

const form = ref({
  number: '',
  date: '',
  client_id: null as number | null,
  status: 'draft' as 'draft' | 'closed',
  total_value: '',
});

const { searchQuery, page, paginatedRows, totalPages, setSearch, setPage } =
  usePaginatedTable<Order>(
    () => orders.value,
    (row, query) => {
      const q = query.toLowerCase();
      return (
        row.number.toLowerCase().includes(q) ||
        (row.entity?.name ?? '').toLowerCase().includes(q)
      );
    },
    10,
  );

const hasRows = computed(() => paginatedRows.value.length > 0);

const statusLabel = (status: string) => (status === 'draft' ? 'Rascunho' : 'Fechada');

const fetchData = async () => {
  isLoading.value = true;
  try {
    const [orResponse, enResponse] = await Promise.all([
      get<PaginatedResponse<Order>>('/orders', { per_page: 1000 }),
      get<PaginatedResponse<Entity>>('/entities', { per_page: 1000, type: 'client' }),
    ]);
    orders.value = orResponse.data ?? [];
    entities.value = enResponse.data ?? [];
  } catch {
    toast({ title: 'Erro ao carregar encomendas', variant: 'destructive' });
  } finally {
    isLoading.value = false;
  }
};

const openCreate = () => {
  selectedOrder.value = null;
  form.value = { number: '', date: '', client_id: null, status: 'draft', total_value: '' };
  isFormOpen.value = true;
};

const openEdit = (order: Order) => {
  selectedOrder.value = order;
  form.value = {
    number: order.number,
    date: order.date,
    client_id: order.client_id,
    status: order.status,
    total_value: String(order.total_value),
  };
  isFormOpen.value = true;
};

const openDetail = (order: Order) => {
  selectedOrder.value = order;
  isDetailOpen.value = true;
};

const submitForm = async () => {
  isSubmitting.value = true;
  const payload = { ...form.value, total_value: Number(form.value.total_value) };
  try {
    if (selectedOrder.value) {
      const updated = await put<Order>(`/orders/${selectedOrder.value.id}`, payload);
      const idx = orders.value.findIndex((o) => o.id === updated.id);
      if (idx >= 0) orders.value[idx] = updated;
    } else {
      const created = await post<Order>('/orders', payload);
      orders.value.unshift(created);
    }
    toast({ title: selectedOrder.value ? 'Encomenda atualizada' : 'Encomenda criada' });
    isFormOpen.value = false;
  } catch {
    toast({ title: 'Erro ao guardar encomenda', variant: 'destructive' });
  } finally {
    isSubmitting.value = false;
  }
};

const downloadPdf = () =>
  toast({ title: 'PDF', description: 'Funcionalidade em desenvolvimento.' });

onMounted(fetchData);
</script>

<template>
  <Card>
    <CardHeader class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <CardTitle>Encomendas</CardTitle>
      <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
        <Input
          class="md:w-72"
          :model-value="searchQuery"
          placeholder="Buscar por numero ou cliente"
          @update:model-value="setSearch(String($event))"
        />
        <Button @click="openCreate">Nova Encomenda</Button>
      </div>
    </CardHeader>

    <CardContent>
      <div v-if="isLoading" class="py-8 text-center text-sm text-muted-foreground">A carregar encomendas...</div>

      <div v-else>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Numero</TableHead>
              <TableHead>Data</TableHead>
              <TableHead>Cliente</TableHead>
              <TableHead>Total</TableHead>
              <TableHead>Estado</TableHead>
              <TableHead class="text-right">Acoes</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="order in paginatedRows" :key="order.id">
              <TableCell class="font-mono">{{ order.number }}</TableCell>
              <TableCell>{{ order.date }}</TableCell>
              <TableCell>{{ order.entity?.name ?? order.client_id }}</TableCell>
              <TableCell>{{ Number(order.total_value).toFixed(2) }} €</TableCell>
              <TableCell>
                <span :class="order.status === 'draft' ? 'text-amber-600' : 'text-green-600'">
                  {{ statusLabel(order.status) }}
                </span>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-1 flex-wrap">
                  <Button variant="outline" size="sm" @click="openDetail(order)">Visualizar</Button>
                  <Button variant="outline" size="sm" @click="openEdit(order)">Editar</Button>
                  <Button variant="outline" size="sm" @click="downloadPdf">PDF</Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="!hasRows">
              <TableCell colspan="6" class="text-center text-sm text-muted-foreground">
                Nenhuma encomenda encontrada.
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <div class="mt-4 flex items-center justify-between">
          <p class="text-sm text-muted-foreground">Pagina {{ page }} de {{ totalPages }}</p>
          <div class="flex items-center gap-2">
            <Button variant="outline" size="sm" :disabled="page <= 1" @click="setPage(page - 1)">Anterior</Button>
            <Button variant="outline" size="sm" :disabled="page >= totalPages" @click="setPage(page + 1)">Proxima</Button>
          </div>
        </div>
      </div>
    </CardContent>

    <!-- Create / Edit Form -->
    <Dialog :open="isFormOpen" @update:open="(v) => { if (!v) isFormOpen = false; }">
      <DialogContent class="max-w-lg">
        <DialogHeader>
          <DialogTitle>{{ selectedOrder ? 'Editar Encomenda' : 'Nova Encomenda' }}</DialogTitle>
        </DialogHeader>
        <form class="grid grid-cols-1 gap-4 md:grid-cols-2" @submit.prevent="submitForm">
          <div>
            <Label>Numero</Label>
            <Input v-model="form.number" placeholder="ENC-001" />
          </div>
          <div>
            <Label>Data</Label>
            <Input v-model="form.date" type="date" />
          </div>
          <div class="md:col-span-2">
            <Label>Cliente</Label>
            <Select v-model="form.client_id">
              <SelectTrigger><SelectValue placeholder="Selecione o cliente" /></SelectTrigger>
              <SelectContent>
                <SelectItem v-for="e in entities" :key="e.id" :value="e.id">{{ e.name }}</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div>
            <Label>Total (€)</Label>
            <Input v-model="form.total_value" type="number" min="0" step="0.01" />
          </div>
          <div>
            <Label>Estado</Label>
            <Select v-model="form.status">
              <SelectTrigger><SelectValue /></SelectTrigger>
              <SelectContent>
                <SelectItem value="draft">Rascunho</SelectItem>
                <SelectItem value="closed">Fechada</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="md:col-span-2 flex justify-end gap-2">
            <Button type="button" variant="outline" @click="isFormOpen = false">Cancelar</Button>
            <Button type="submit" :disabled="isSubmitting">{{ isSubmitting ? 'A guardar...' : 'Guardar' }}</Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Detail View -->
    <Dialog :open="isDetailOpen" @update:open="(v) => { if (!v) isDetailOpen = false; }">
      <DialogContent class="max-w-lg">
        <DialogHeader>
          <DialogTitle>Encomenda {{ selectedOrder?.number }}</DialogTitle>
        </DialogHeader>
        <div v-if="selectedOrder" class="grid grid-cols-2 gap-3 text-sm">
          <div><span class="font-medium">Numero:</span> {{ selectedOrder.number }}</div>
          <div><span class="font-medium">Data:</span> {{ selectedOrder.date }}</div>
          <div><span class="font-medium">Cliente:</span> {{ selectedOrder.entity?.name ?? selectedOrder.client_id }}</div>
          <div><span class="font-medium">Total:</span> {{ Number(selectedOrder.total_value).toFixed(2) }} €</div>
          <div><span class="font-medium">Estado:</span> {{ statusLabel(selectedOrder.status) }}</div>
        </div>
        <div class="flex justify-end mt-4">
          <Button variant="outline" @click="isDetailOpen = false">Fechar</Button>
        </div>
      </DialogContent>
    </Dialog>
  </Card>
</template>
