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

const { get, post, remove } = useApi();
const { toast } = useToast();

const exampleSuppliers = [
  { id: 1, name: 'Fornecedor Exemplo, Lda.' },
  { id: 2, name: 'Suprimentos Atlântico, S.A.' },
  { id: 3, name: 'Material Office, Lda.' },
];

const exampleSupplierOrders = [
  {
    id: 1,
    date: '2026-05-06',
    number: 'ECF-2026-001',
    supplier_id: 1,
    total_value: 1280.75,
    status: 'draft',
    supplier: exampleSuppliers[0],
  },
  {
    id: 2,
    date: '2026-05-09',
    number: 'ECF-2026-002',
    supplier_id: 2,
    total_value: 540.0,
    status: 'closed',
    supplier: exampleSuppliers[1],
  },
  {
    id: 3,
    date: '2026-05-12',
    number: 'ECF-2026-003',
    supplier_id: 3,
    total_value: 219.9,
    status: 'invoiced',
    supplier: exampleSuppliers[2],
  },
];

const supplierOrders = ref<any[]>(exampleSupplierOrders);
const suppliers = ref<any[]>(exampleSuppliers);
const isLoading = ref(false);
const statusFilter = ref('');
const isFormOpen = ref(false);
const isDetailOpen = ref(false);
const selectedOrder = ref<any | null>(null);
const isSubmitting = ref(false);

const form = ref({
  number: '',
  date: '',
  supplier_id: null as number | null,
  total_value: '',
  status: 'draft' as 'draft' | 'closed' | 'invoiced',
});

const { searchQuery, page, paginatedRows, totalPages, setSearch, setPage } =
  usePaginatedTable<any>(
    () => supplierOrders.value,
    (row, query) => {
      const q = query.toLowerCase();
      return (
        row.number?.toLowerCase().includes(q) ||
        row.supplier?.name?.toLowerCase().includes(q)
      );
    },
    10,
  );

const hasRows = computed(() => paginatedRows.value.length > 0);

const openCreate = () => {
  form.value = {
    number: '',
    date: new Date().toISOString().slice(0, 10),
    supplier_id: null,
    total_value: '',
    status: 'draft',
  };
  isFormOpen.value = true;
};

const openDetail = (order: any) => {
  selectedOrder.value = order;
  isDetailOpen.value = true;
};

const statusLabel = (status: string) => {
  if (status === 'draft') return 'Rascunho';
  if (status === 'closed') return 'Fechada';
  if (status === 'invoiced') return 'Faturada';
  return status;
};

const fetchSupplierOrders = async () => {
  isLoading.value = true;
  try {
    const [ordersResponse, suppliersResponse] = await Promise.all([
      get<PaginatedResponse<any>>('/v1/supplier-orders', {
        per_page: 1000,
        ...(statusFilter.value && { status: statusFilter.value }),
      }),
      get<PaginatedResponse<any>>('/v1/entities', { per_page: 1000, type: 'supplier' }),
    ]);

    suppliers.value = suppliersResponse.data?.length ? suppliersResponse.data : exampleSuppliers;
    supplierOrders.value = ordersResponse.data?.length ? ordersResponse.data : exampleSupplierOrders;
    supplierOrders.value = supplierOrders.value.map((order) => ({
      ...order,
      supplier: order.supplier ?? suppliers.value.find((supplier) => supplier.id === order.supplier_id) ?? null,
    }));
  } catch {
    supplierOrders.value = exampleSupplierOrders;
    suppliers.value = exampleSuppliers;
    toast({
      title: 'Erro ao carregar',
      description: 'Não foi possível obter as encomendas de fornecedor.',
      variant: 'destructive',
    });
  } finally {
    isLoading.value = false;
  }
};

const submitForm = async () => {
  isSubmitting.value = true;

  try {
    const { number, ...createPayload } = form.value;
    const created = await post<any>('/v1/supplier-orders', {
      ...createPayload,
      total_value: Number(form.value.total_value),
    });

    supplierOrders.value.unshift({
      ...created,
      supplier: created.supplier ?? suppliers.value.find((supplier) => supplier.id === created.supplier_id) ?? null,
    });

    toast({
      title: 'Encomenda criada',
      description: 'Encomenda a fornecedor criada com sucesso.',
    });

    isFormOpen.value = false;
  } catch (err: any) {
    toast({
      title: 'Erro ao guardar',
      description: err?.response?.data?.message ?? 'Não foi possível criar a encomenda.',
      variant: 'destructive',
    });
  } finally {
    isSubmitting.value = false;
  }
};

const handleDelete = async (order: any) => {
  if (!window.confirm(`Eliminar encomenda "${order.number}"?`)) return;
  try {
    await remove(`/v1/supplier-orders/${order.id}`);
    supplierOrders.value = supplierOrders.value.filter(o => o.id !== order.id);
    toast({
      title: 'Removido',
      description: 'Encomenda eliminada com sucesso.',
    });
  } catch {
    toast({
      title: 'Erro ao remover',
      description: 'Não foi possível eliminar a encomenda.',
      variant: 'destructive',
    });
  }
};

onMounted(fetchSupplierOrders);
</script>

<template>
  <Card>
    <CardHeader class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <CardTitle>Encomendas a Fornecedores</CardTitle>
      <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row md:items-center">
        <Input
          class="md:w-72"
          :model-value="searchQuery"
          placeholder="Buscar por número ou fornecedor"
          @update:model-value="setSearch(String($event))"
        />
        <Select :model-value="statusFilter" @update:model-value="(v) => { statusFilter = String(v); fetchSupplierOrders(); }">
          <SelectTrigger class="w-48">
            <SelectValue placeholder="Estado" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="">Todos</SelectItem>
            <SelectItem value="draft">Rascunho</SelectItem>
            <SelectItem value="closed">Fechada</SelectItem>
            <SelectItem value="invoiced">Faturada</SelectItem>
          </SelectContent>
        </Select>
        <div class="flex-1"></div>
        <Button @click="openCreate" class="md:ml-4">Nova Encomenda</Button>
      </div>
    </CardHeader>

    <CardContent>
      <div v-if="isLoading" class="py-8 text-center text-sm text-muted-foreground">
        A carregar encomendas...
      </div>

      <div v-else>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Data</TableHead>
              <TableHead>Número</TableHead>
              <TableHead>Fornecedor</TableHead>
              <TableHead>Valor Total</TableHead>
              <TableHead>Estado</TableHead>
              <TableHead class="text-right">Ações</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="order in paginatedRows" :key="order.id">
              <TableCell>{{ new Date(order.date).toLocaleDateString('pt-PT') }}</TableCell>
              <TableCell class="font-medium">{{ order.number }}</TableCell>
              <TableCell>{{ order.supplier?.name }}</TableCell>
              <TableCell>{{ order.total_value?.toFixed(2) }} €</TableCell>
              <TableCell>
                <span :class="{
                  'bg-gray-100 text-gray-800': order.status === 'draft',
                  'bg-green-100 text-green-800': order.status === 'closed',
                  'bg-blue-100 text-blue-800': order.status === 'invoiced',
                }" class="rounded px-2 py-1 text-xs font-medium">
                  {{ order.status === 'draft' ? 'Rascunho' : order.status === 'closed' ? 'Fechada' : 'Faturada' }}
                </span>
              </TableCell>
              <TableCell class="text-right">
                <Button size="sm" variant="ghost" @click="openDetail(order)">Ver</Button>
                <Button size="sm" variant="ghost" class="text-red-600 hover:text-red-700" @click="handleDelete(order)">
                  Eliminar
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <div v-if="!hasRows" class="py-8 text-center text-sm text-muted-foreground">
          Nenhuma encomenda encontrada.
        </div>

        <div v-else class="mt-4 flex items-center justify-between">
          <span class="text-sm text-muted-foreground">
            Página {{ page }} de {{ totalPages }}
          </span>
          <div class="flex gap-2">
            <Button
              size="sm"
              variant="outline"
              :disabled="page === 1"
              @click="setPage(page - 1)"
            >
              Anterior
            </Button>
            <Button
              size="sm"
              variant="outline"
              :disabled="page >= totalPages"
              @click="setPage(page + 1)"
            >
              Próximo
            </Button>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>

  <Dialog :open="isFormOpen" @update:open="(open) => { if (!open) isFormOpen = false; }">
    <DialogContent class="max-w-lg">
      <DialogHeader>
        <DialogTitle>Nova Encomenda a Fornecedor</DialogTitle>
      </DialogHeader>

      <form class="grid grid-cols-1 gap-4 md:grid-cols-2" @submit.prevent="submitForm">
        <div>
          <Label>Número</Label>
          <Input
            v-model="form.number"
            placeholder="Gerado automaticamente ao guardar"
            disabled
          />
        </div>
        <div>
          <Label>Data</Label>
          <Input v-model="form.date" type="date" required />
        </div>
        <div class="md:col-span-2">
          <Label>Fornecedor</Label>
          <Select v-model="form.supplier_id">
            <SelectTrigger>
              <SelectValue placeholder="Selecione o fornecedor" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div>
          <Label>Valor Total (€)</Label>
          <Input v-model="form.total_value" type="number" min="0" step="0.01" required />
        </div>
        <div>
          <Label>Estado</Label>
          <Select v-model="form.status">
            <SelectTrigger>
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="draft">Rascunho</SelectItem>
              <SelectItem value="closed">Fechada</SelectItem>
              <SelectItem value="invoiced">Faturada</SelectItem>
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

  <Dialog :open="isDetailOpen" @update:open="(open) => { if (!open) isDetailOpen = false; }">
    <DialogContent class="max-w-lg">
      <DialogHeader>
        <DialogTitle>Detalhe da Encomenda</DialogTitle>
      </DialogHeader>

      <div v-if="selectedOrder" class="grid grid-cols-2 gap-3 text-sm">
        <div><span class="font-medium">Número:</span> {{ selectedOrder.number }}</div>
        <div><span class="font-medium">Data:</span> {{ new Date(selectedOrder.date).toLocaleDateString('pt-PT') }}</div>
        <div><span class="font-medium">Fornecedor:</span> {{ selectedOrder.supplier?.name ?? '-' }}</div>
        <div><span class="font-medium">Estado:</span> {{ statusLabel(selectedOrder.status) }}</div>
        <div class="col-span-2"><span class="font-medium">Valor Total:</span> {{ Number(selectedOrder.total_value ?? 0).toFixed(2) }} €</div>
      </div>

      <div class="mt-4 flex justify-end">
        <Button variant="outline" @click="isDetailOpen = false">Fechar</Button>
      </div>
    </DialogContent>
  </Dialog>
</template>
