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

const exampleSuppliers = [
  { id: 1, name: 'Fornecedor Exemplo, Lda.' },
  { id: 2, name: 'Suprimentos Atlântico, S.A.' },
  { id: 3, name: 'Material Office, Lda.' },
];

const exampleInvoices = [
  {
    id: 1,
    is_example: true,
    number: 'FF-2026-001',
    issue_date: '2026-05-05',
    due_date: '2026-06-05',
    supplier_id: 1,
    supplier_order_id: 1,
    total_value: 1280.75,
    status: 'pending',
    entity: exampleSuppliers[0],
  },
  {
    id: 2,
    is_example: true,
    number: 'FF-2026-002',
    issue_date: '2026-05-09',
    due_date: '2026-06-09',
    supplier_id: 2,
    supplier_order_id: 2,
    total_value: 540.0,
    status: 'paid',
    entity: exampleSuppliers[1],
  },
  {
    id: 3,
    is_example: true,
    number: 'FF-2026-003',
    issue_date: '2026-05-12',
    due_date: '2026-06-12',
    supplier_id: 3,
    supplier_order_id: 3,
    total_value: 219.9,
    status: 'pending',
    entity: exampleSuppliers[2],
  },
];

const invoices = ref<Invoice[]>(exampleInvoices as Invoice[]);
const suppliers = ref<Entity[]>(exampleSuppliers as Entity[]);
const isLoading = ref(false);
const isFormOpen = ref(false);
const isDetailOpen = ref(false);
const selectedInvoice = ref<Invoice | null>(null);
const isSubmitting = ref(false);

const form = ref({
  number: '',
  issue_date: '',
  due_date: '',
  supplier_id: null as number | null,
  supplier_order_id: null as number | null,
  total_value: '',
  status: 'pending' as 'pending' | 'paid',
});

const { searchQuery, page, paginatedRows, totalPages, setSearch, setPage } =
  usePaginatedTable<Invoice>(
    () => invoices.value,
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

const statusLabel = (status: string) => (status === 'pending' ? 'Pendente' : 'Paga');

const fetchData = async () => {
  isLoading.value = true;
  try {
    const [invResponse, supResponse] = await Promise.all([
      get<PaginatedResponse<Invoice>>('/invoices', { per_page: 1000 }),
      get<PaginatedResponse<Entity>>('/entities', { per_page: 1000, type: 'supplier' }),
    ]);

    invoices.value = (invResponse.data?.length ? invResponse.data : exampleInvoices) as Invoice[];
    suppliers.value = (supResponse.data?.length ? supResponse.data : exampleSuppliers) as Entity[];

    invoices.value = invoices.value.map((invoice) => ({
      ...invoice,
      entity: invoice.entity ?? suppliers.value.find((supplier) => supplier.id === invoice.supplier_id) ?? null,
    }));
  } catch {
    invoices.value = exampleInvoices as Invoice[];
    suppliers.value = exampleSuppliers as Entity[];
    toast({ title: 'Erro ao carregar faturas', variant: 'destructive' });
  } finally {
    isLoading.value = false;
  }
};

const openCreate = () => {
  selectedInvoice.value = null;
  form.value = {
    number: '', issue_date: '', due_date: '',
    supplier_id: null, supplier_order_id: null, total_value: '', status: 'pending',
  };
  isFormOpen.value = true;
};

const openEdit = (invoice: Invoice) => {
  selectedInvoice.value = invoice;
  form.value = {
    number: invoice.number,
    issue_date: invoice.issue_date,
    due_date: invoice.due_date,
    supplier_id: invoice.supplier_id,
    supplier_order_id: invoice.supplier_order_id,
    total_value: String(invoice.total_value),
    status: invoice.status,
  };
  isFormOpen.value = true;
};

const openDetail = (invoice: Invoice) => {
  selectedInvoice.value = invoice;
  isDetailOpen.value = true;
};

const submitForm = async () => {
  isSubmitting.value = true;
  const payload = { ...form.value, total_value: Number(form.value.total_value) };
  try {
    if (selectedInvoice.value) {
      const updated = await put<Invoice>(`/invoices/${selectedInvoice.value.id}`, payload);
      const idx = invoices.value.findIndex((i) => i.id === updated.id);
      if (idx >= 0) invoices.value[idx] = updated;
    } else {
      const { number, ...createPayload } = payload;
      const created = await post<Invoice>('/invoices', createPayload);
      invoices.value.unshift(created);
    }
    toast({ title: selectedInvoice.value ? 'Fatura atualizada' : 'Fatura criada' });
    isFormOpen.value = false;
  } catch {
    toast({ title: 'Erro ao guardar fatura', variant: 'destructive' });
  } finally {
    isSubmitting.value = false;
  }
};

const downloadPdf = (invoice?: Invoice | null) => {
  if (!invoice) {
    toast({ title: 'PDF', description: 'Selecione uma fatura primeiro.', variant: 'destructive' });
    return;
  }

  if ((invoice as any).is_example) {
    const query = new URLSearchParams({
      number: String(invoice.number ?? 'FAT-EXEMPLO'),
      issue_date: String(invoice.issue_date ?? new Date().toISOString().slice(0, 10)),
      due_date: String(invoice.due_date ?? new Date().toISOString().slice(0, 10)),
      supplier_id: String(invoice.supplier_id ?? ''),
      supplier_name: String(invoice.entity?.name ?? 'Fornecedor'),
      supplier_nif: String((invoice as any).entity?.nif ?? ''),
      supplier_address: String((invoice as any).entity?.address ?? ''),
      supplier_postal_code: String((invoice as any).entity?.postal_code ?? ''),
      supplier_city: String((invoice as any).entity?.city ?? ''),
      supplier_email: String((invoice as any).entity?.email ?? ''),
      total_value: String(Number(invoice.total_value ?? 0)),
      status: String(invoice.status ?? 'pending'),
      item_name: 'Fornecimento geral',
      item_description: 'Fatura de exemplo gerada com template visual.',
    });

    window.open(`/api/v1/invoices-template-pdf?${query.toString()}`, '_blank');
    return;
  }

  window.open(`/api/v1/invoices/${invoice.id}/download-pdf`, '_blank');
};

onMounted(fetchData);
</script>

<template>
  <Card>
    <CardHeader class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <CardTitle>Faturas de Fornecedor</CardTitle>
      <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
        <Input
          class="md:w-72"
          :model-value="searchQuery"
          placeholder="Buscar por numero ou fornecedor"
          @update:model-value="setSearch(String($event))"
        />
        <Button @click="openCreate">Nova Fatura</Button>
      </div>
    </CardHeader>

    <CardContent>
      <div v-if="isLoading" class="py-8 text-center text-sm text-muted-foreground">A carregar faturas...</div>

      <div v-else>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Numero</TableHead>
              <TableHead>Data Emissao</TableHead>
              <TableHead>Vencimento</TableHead>
              <TableHead>Fornecedor</TableHead>
              <TableHead>Total</TableHead>
              <TableHead>Estado</TableHead>
              <TableHead class="text-right">Acoes</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="invoice in paginatedRows" :key="invoice.id">
              <TableCell class="font-mono">{{ invoice.number }}</TableCell>
              <TableCell>{{ invoice.issue_date }}</TableCell>
              <TableCell>{{ invoice.due_date }}</TableCell>
              <TableCell>{{ invoice.entity?.name ?? invoice.supplier_id }}</TableCell>
              <TableCell>{{ Number(invoice.total_value).toFixed(2) }} €</TableCell>
              <TableCell>
                <span :class="invoice.status === 'pending' ? 'text-amber-600 font-medium' : 'text-green-600 font-medium'">
                  {{ statusLabel(invoice.status) }}
                </span>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-1 flex-wrap">
                  <Button variant="outline" size="sm" @click="openDetail(invoice)">Visualizar</Button>
                  <Button variant="outline" size="sm" @click="openEdit(invoice)">Editar</Button>
                  <Button variant="outline" size="sm" @click="downloadPdf(invoice)">PDF</Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="!hasRows">
              <TableCell colspan="7" class="text-center text-sm text-muted-foreground">
                Nenhuma fatura encontrada.
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
          <DialogTitle>{{ selectedInvoice ? 'Editar Fatura' : 'Nova Fatura' }}</DialogTitle>
        </DialogHeader>
        <form class="grid grid-cols-1 gap-4 md:grid-cols-2" @submit.prevent="submitForm">
          <div>
            <Label>Numero</Label>
            <Input
              v-model="form.number"
              :disabled="!selectedInvoice"
              :placeholder="selectedInvoice ? 'FAT-001' : 'Gerado automaticamente ao guardar'"
            />
          </div>
          <div>
            <Label>Data Emissao</Label>
            <Input v-model="form.issue_date" type="date" />
          </div>
          <div>
            <Label>Vencimento</Label>
            <Input v-model="form.due_date" type="date" />
          </div>
          <div>
            <Label>Total (€)</Label>
            <Input v-model="form.total_value" type="number" min="0" step="0.01" />
          </div>
          <div class="md:col-span-2">
            <Label>Fornecedor</Label>
            <Select v-model="form.supplier_id">
              <SelectTrigger><SelectValue placeholder="Selecione o fornecedor" /></SelectTrigger>
              <SelectContent>
                <SelectItem v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="md:col-span-2">
            <Label>Estado</Label>
            <Select v-model="form.status">
              <SelectTrigger><SelectValue /></SelectTrigger>
              <SelectContent>
                <SelectItem value="pending">Pendente</SelectItem>
                <SelectItem value="paid">Paga</SelectItem>
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
          <DialogTitle>Fatura {{ selectedInvoice?.number }}</DialogTitle>
        </DialogHeader>
        <div v-if="selectedInvoice" class="grid grid-cols-2 gap-3 text-sm">
          <div><span class="font-medium">Numero:</span> {{ selectedInvoice.number }}</div>
          <div><span class="font-medium">Data Emissao:</span> {{ selectedInvoice.issue_date }}</div>
          <div><span class="font-medium">Vencimento:</span> {{ selectedInvoice.due_date }}</div>
          <div><span class="font-medium">Fornecedor:</span> {{ selectedInvoice.entity?.name ?? selectedInvoice.supplier_id }}</div>
          <div><span class="font-medium">Total:</span> {{ Number(selectedInvoice.total_value).toFixed(2) }} €</div>
          <div><span class="font-medium">Estado:</span> {{ statusLabel(selectedInvoice.status) }}</div>
        </div>
        <div class="flex justify-end gap-2 mt-4">
          <Button variant="outline" @click="isDetailOpen = false">Fechar</Button>
          <Button variant="outline" @click="downloadPdf(selectedInvoice)">Download PDF</Button>
        </div>
      </DialogContent>
    </Dialog>
  </Card>
</template>
