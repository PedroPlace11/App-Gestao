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

interface Entity {
  id: number;
  name: string;
}

interface Proposal {
  id: number;
  number: string;
  date: string;
  client_id: number | null;
  validity: string;
  total_value: number | string;
  status: 'draft' | 'closed';
  entity?: Entity | null;
}

interface PaginatedResponse<T> {
  data?: T[];
}

const { get, post, put } = useApi();
const { toast } = useToast();

const exampleEntities: Entity[] = [
  { id: 1, name: 'Cliente Exemplo, Lda.' },
  { id: 2, name: 'Inovcorp Group' },
  { id: 3, name: 'Serviços Atlântico, Lda.' },
];

const exampleProposals: Proposal[] = [
  {
    id: 1,
    number: 'PROP-2026-001',
    date: '2026-05-08',
    client_id: 1,
    validity: '2026-06-08',
    total_value: 1450,
    status: 'draft',
    entity: exampleEntities[0],
  },
  {
    id: 2,
    number: 'PROP-2026-002',
    date: '2026-05-09',
    client_id: 2,
    validity: '2026-06-09',
    total_value: 2890.5,
    status: 'closed',
    entity: exampleEntities[1],
  },
  {
    id: 3,
    number: 'PROP-2026-003',
    date: '2026-05-12',
    client_id: 3,
    validity: '2026-06-12',
    total_value: 760,
    status: 'draft',
    entity: exampleEntities[2],
  },
];

const proposals = ref<Proposal[]>(exampleProposals);
const entities = ref<Entity[]>(exampleEntities);
const isLoading = ref(false);
const isFormOpen = ref(false);
const isDetailOpen = ref(false);
const selectedProposal = ref<Proposal | null>(null);
const isSubmitting = ref(false);

const form = ref({
  number: '',
  date: '',
  client_id: null as number | null,
  validity: '',
  total_value: '',
  status: 'draft' as 'draft' | 'closed',
});

const { searchQuery, page, paginatedRows, totalPages, setSearch, setPage } =
  usePaginatedTable<Proposal>(
    () => proposals.value,
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

const statusLabel = (status: string) =>
  status === 'draft' ? 'Rascunho' : 'Fechada';

const fetchData = async () => {
  isLoading.value = true;
  try {
    const [prResponse, enResponse] = await Promise.all([
      get<PaginatedResponse<Proposal>>('/proposals', { per_page: 1000 }),
      get<PaginatedResponse<Entity>>('/entities', { per_page: 1000, type: 'client' }),
    ]);

    proposals.value = (prResponse.data?.length ? prResponse.data : exampleProposals).map((proposal) => ({
      ...proposal,
      entity: proposal.entity ?? exampleEntities.find((entity) => entity.id === proposal.client_id) ?? null,
    }));
    entities.value = enResponse.data?.length ? enResponse.data : exampleEntities;
  } catch {
    proposals.value = exampleProposals;
    entities.value = exampleEntities;
    toast({ title: 'Erro ao carregar propostas', variant: 'destructive' });
  } finally {
    isLoading.value = false;
  }
};

const openCreate = () => {
  selectedProposal.value = null;
  form.value = { number: '', date: '', client_id: null, validity: '', total_value: '', status: 'draft' };
  isFormOpen.value = true;
};

const openEdit = (proposal: Proposal) => {
  selectedProposal.value = proposal;
  form.value = {
    number: proposal.number,
    date: proposal.date,
    client_id: proposal.client_id,
    validity: proposal.validity,
    total_value: String(proposal.total_value),
    status: proposal.status,
  };
  isFormOpen.value = true;
};

const openDetail = (proposal: Proposal) => {
  selectedProposal.value = proposal;
  isDetailOpen.value = true;
};

const submitForm = async () => {
  isSubmitting.value = true;
  const payload = { ...form.value, total_value: Number(form.value.total_value) };
  try {
    if (selectedProposal.value) {
      const updated = await put<Proposal>(`/proposals/${selectedProposal.value.id}`, payload);
      const idx = proposals.value.findIndex((p) => p.id === updated.id);
      if (idx >= 0) proposals.value[idx] = updated;
    } else {
      const { number, ...createPayload } = payload;
      const created = await post<Proposal>('/proposals', createPayload);
      proposals.value.unshift(created);
    }
    toast({ title: selectedProposal.value ? 'Proposta atualizada' : 'Proposta criada' });
    isFormOpen.value = false;
  } catch {
    toast({ title: 'Erro ao guardar proposta', variant: 'destructive' });
  } finally {
    isSubmitting.value = false;
  }
};

const isDownloadingPdf = ref(false);

const downloadPdf = async (proposal: Proposal) => {
  if (!proposal?.id) {
    toast({ title: 'Erro', description: 'Proposta inválida.', variant: 'destructive' });
    return;
  }

  isDownloadingPdf.value = true;
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    const response = await fetch(`/api/v1/proposals/${proposal.id}/download-pdf`, {
      method: 'GET',
      credentials: 'include',
      headers: {
        Accept: 'application/pdf',
        'X-CSRF-TOKEN': csrfToken,
      },
    });

    if (!response.ok) throw new Error(`HTTP ${response.status}`);

    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `proposta-${proposal.number}.pdf`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    window.URL.revokeObjectURL(url);
  } catch {
    toast({ title: 'Erro ao gerar PDF', description: 'Não foi possível descarregar o PDF.', variant: 'destructive' });
  } finally {
    isDownloadingPdf.value = false;
  }
};

const convertToOrder = async (proposal: Proposal) => {
  if (!proposal) {
    toast({ title: 'Erro', description: 'Selecione uma proposta primeiro.', variant: 'destructive' });
    return;
  }

  const orderNumber = window.prompt('Número da encomenda:', `ENC-${new Date().getFullYear()}-001`);
  if (!orderNumber) return;

  try {
    const result = await post(`/proposals/${proposal.id}/convert-to-order`, {
      order_number: orderNumber,
      date: new Date().toISOString().slice(0, 10),
    });

    // Remove da lista após conversão
    proposals.value = proposals.value.filter(p => p.id !== proposal.id);

    toast({
      title: 'Proposta convertida',
      description: `Encomenda ${orderNumber} criada com sucesso.`,
    });
  } catch (error: any) {
    toast({
      title: 'Erro ao converter',
      description: error?.message ?? 'Não foi possível converter a proposta.',
      variant: 'destructive',
    });
  }
};

onMounted(fetchData);
</script>

<template>
  <Card>
    <CardHeader class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <CardTitle>Propostas</CardTitle>
      <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
        <Input
          class="md:w-72"
          :model-value="searchQuery"
          placeholder="Buscar por numero ou cliente"
          @update:model-value="setSearch(String($event))"
        />
        <Button @click="openCreate">Nova Proposta</Button>
      </div>
    </CardHeader>

    <CardContent>
      <div v-if="isLoading" class="py-8 text-center text-sm text-muted-foreground">A carregar propostas...</div>

      <div v-else>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Numero</TableHead>
              <TableHead>Data</TableHead>
              <TableHead>Cliente</TableHead>
              <TableHead>Validade</TableHead>
              <TableHead>Total</TableHead>
              <TableHead>Estado</TableHead>
              <TableHead class="text-right">Acoes</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="proposal in paginatedRows" :key="proposal.id">
              <TableCell class="font-mono">{{ proposal.number }}</TableCell>
              <TableCell>{{ proposal.date }}</TableCell>
              <TableCell>{{ proposal.entity?.name ?? proposal.client_id }}</TableCell>
              <TableCell>{{ proposal.validity }}</TableCell>
              <TableCell>{{ Number(proposal.total_value).toFixed(2) }} €</TableCell>
              <TableCell>
                <span :class="proposal.status === 'draft' ? 'text-amber-600' : 'text-green-600'">
                  {{ statusLabel(proposal.status) }}
                </span>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-1 flex-wrap">
                  <Button variant="outline" size="sm" @click="openDetail(proposal)">Visualizar</Button>
                  <Button variant="outline" size="sm" @click="openEdit(proposal)">Editar</Button>
                  <Button variant="outline" size="sm" :disabled="isDownloadingPdf" @click="downloadPdf(proposal)">
                    {{ isDownloadingPdf ? '...' : 'PDF' }}
                  </Button>
                  <Button variant="secondary" size="sm" @click="convertToOrder(proposal)">Converter</Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="!hasRows">
              <TableCell colspan="7" class="text-center text-sm text-muted-foreground">
                Nenhuma proposta encontrada.
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
          <DialogTitle>{{ selectedProposal ? 'Editar Proposta' : 'Nova Proposta' }}</DialogTitle>
        </DialogHeader>
        <form class="grid grid-cols-1 gap-4 md:grid-cols-2" @submit.prevent="submitForm">
          <div>
            <Label>Numero</Label>
            <Input
              v-model="form.number"
              :disabled="!selectedProposal"
              :placeholder="selectedProposal ? 'PROP-001' : 'Gerado automaticamente ao guardar'"
            />
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
            <Label>Validade</Label>
            <Input v-model="form.validity" type="date" />
          </div>
          <div>
            <Label>Valor Total (€)</Label>
            <Input v-model="form.total_value" type="number" min="0" step="0.01" />
          </div>
          <div class="md:col-span-2">
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
          <DialogTitle>Proposta {{ selectedProposal?.number }}</DialogTitle>
        </DialogHeader>
        <div v-if="selectedProposal" class="grid grid-cols-2 gap-3 text-sm">
          <div><span class="font-medium">Numero:</span> {{ selectedProposal.number }}</div>
          <div><span class="font-medium">Data:</span> {{ selectedProposal.date }}</div>
          <div><span class="font-medium">Cliente:</span> {{ selectedProposal.entity?.name ?? selectedProposal.client_id }}</div>
          <div><span class="font-medium">Validade:</span> {{ selectedProposal.validity }}</div>
          <div><span class="font-medium">Total:</span> {{ Number(selectedProposal.total_value).toFixed(2) }} €</div>
          <div><span class="font-medium">Estado:</span> {{ statusLabel(selectedProposal.status) }}</div>
        </div>
        <div class="flex justify-end gap-2 mt-4">
          <Button variant="outline" @click="isDetailOpen = false">Fechar</Button>
          <Button variant="outline" @click="downloadPdf">Download PDF</Button>
          <Button variant="secondary" @click="convertToOrder">Converter para Encomenda</Button>
        </div>
      </DialogContent>
    </Dialog>
  </Card>
</template>
