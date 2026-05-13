<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/components/ui/toast/use-toast';

interface WorkOrderItem {
  id: number;
  number: string;
  client: string;
  technician: string;
  status: 'open' | 'in_progress' | 'completed';
  scheduled_date: string;
  description: string;
}

const { toast } = useToast();
const isCreateOpen = ref(false);
const nextId = ref(4);

const workOrders = ref<WorkOrderItem[]>([
  {
    id: 1,
    number: 'OT-2026-001',
    client: 'Cliente Exemplo, Lda.',
    technician: 'João Martins',
    status: 'open',
    scheduled_date: '2026-05-14',
    description: 'Manutenção preventiva de equipamentos no cliente.',
  },
  {
    id: 2,
    number: 'OT-2026-002',
    client: 'Inovcorp Group',
    technician: 'Ana Ribeiro',
    status: 'in_progress',
    scheduled_date: '2026-05-15',
    description: 'Substituição de componentes e testes funcionais.',
  },
  {
    id: 3,
    number: 'OT-2026-003',
    client: 'Serviços Atlântico, Lda.',
    technician: 'Pedro Silva',
    status: 'completed',
    scheduled_date: '2026-05-11',
    description: 'Instalação concluída e validada com o cliente.',
  },
]);

const form = reactive({
  number: '',
  client: '',
  technician: '',
  status: 'open' as WorkOrderItem['status'],
  scheduled_date: '',
  description: '',
});

const errors = reactive<Record<string, string>>({});

const hasRows = computed(() => workOrders.value.length > 0);

const statusLabel = (status: WorkOrderItem['status']) => {
  if (status === 'open') return 'Aberta';
  if (status === 'in_progress') return 'Em curso';
  return 'Concluída';
};

const statusClass = (status: WorkOrderItem['status']) => {
  if (status === 'open') return 'bg-amber-100 text-amber-800';
  if (status === 'in_progress') return 'bg-blue-100 text-blue-800';
  return 'bg-green-100 text-green-800';
};

const resetForm = () => {
  form.number = '';
  form.client = '';
  form.technician = '';
  form.status = 'open';
  form.scheduled_date = '';
  form.description = '';
  Object.keys(errors).forEach((key) => delete errors[key]);
};

const openCreateModal = () => {
  resetForm();
  isCreateOpen.value = true;
};

const closeCreateModal = () => {
  isCreateOpen.value = false;
};

const validateForm = () => {
  Object.keys(errors).forEach((key) => delete errors[key]);

  if (!form.number.trim()) errors.number = 'O número é obrigatório.';
  if (!form.client.trim()) errors.client = 'O cliente é obrigatório.';
  if (!form.technician.trim()) errors.technician = 'O técnico é obrigatório.';
  if (!form.scheduled_date.trim()) errors.scheduled_date = 'A data é obrigatória.';
  if (!form.description.trim()) errors.description = 'A descrição é obrigatória.';

  const duplicated = workOrders.value.some((item) => item.number.toUpperCase() === form.number.trim().toUpperCase());
  if (duplicated) errors.number = 'Já existe uma ordem com esse número.';

  return Object.keys(errors).length === 0;
};

const submitCreate = () => {
  if (!validateForm()) {
    toast({
      title: 'Verifique os campos',
      description: Object.values(errors)[0],
      variant: 'destructive',
    });
    return;
  }

  workOrders.value.unshift({
    id: nextId.value++,
    number: form.number.trim().toUpperCase(),
    client: form.client.trim(),
    technician: form.technician.trim(),
    status: form.status,
    scheduled_date: form.scheduled_date,
    description: form.description.trim(),
  });

  toast({
    title: 'Ordem criada',
    description: 'A ordem de trabalho foi adicionada com sucesso.',
  });

  closeCreateModal();
};
</script>

<template>
  <Card>
    <CardHeader class="flex flex-row items-center justify-between">
      <CardTitle>Ordens de Trabalho</CardTitle>
      <Button @click="openCreateModal">Nova Ordem</Button>
    </CardHeader>

    <CardContent>
      <div v-if="!hasRows" class="py-12 text-center text-muted-foreground">
        <p class="mb-4">Ainda não existem ordens de trabalho registadas.</p>
        <p class="text-sm">Clique em "Nova Ordem" para adicionar uma ordem.</p>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="order in workOrders"
          :key="order.id"
          class="rounded-lg border p-4"
          style="border-color: var(--color-border);"
        >
          <div class="mb-2 flex items-center justify-between gap-2">
            <p class="font-medium">{{ order.number }} · {{ order.client }}</p>
            <span class="rounded px-2 py-1 text-xs font-medium" :class="statusClass(order.status)">
              {{ statusLabel(order.status) }}
            </span>
          </div>
          <p class="text-sm text-muted-foreground">Técnico: {{ order.technician }}</p>
          <p class="text-sm text-muted-foreground">Data: {{ order.scheduled_date }}</p>
          <p class="mt-2 text-sm">{{ order.description }}</p>
        </div>
      </div>
    </CardContent>
  </Card>

  <Dialog v-model:open="isCreateOpen">
    <DialogContent class="max-w-xl">
      <DialogHeader>
        <DialogTitle>Nova Ordem de Trabalho</DialogTitle>
      </DialogHeader>

      <form class="grid grid-cols-1 gap-4 md:grid-cols-2" @submit.prevent="submitCreate">
        <div class="space-y-2">
          <Label for="order-number">Número</Label>
          <Input id="order-number" v-model="form.number" placeholder="Ex: OT-2026-004" />
          <p v-if="errors.number" class="text-sm text-destructive">{{ errors.number }}</p>
        </div>

        <div class="space-y-2">
          <Label for="order-date">Data prevista</Label>
          <Input id="order-date" v-model="form.scheduled_date" type="date" />
          <p v-if="errors.scheduled_date" class="text-sm text-destructive">{{ errors.scheduled_date }}</p>
        </div>

        <div class="space-y-2 md:col-span-2">
          <Label for="order-client">Cliente</Label>
          <Input id="order-client" v-model="form.client" placeholder="Ex: Cliente Exemplo, Lda." />
          <p v-if="errors.client" class="text-sm text-destructive">{{ errors.client }}</p>
        </div>

        <div class="space-y-2">
          <Label for="order-technician">Técnico</Label>
          <Input id="order-technician" v-model="form.technician" placeholder="Ex: João Martins" />
          <p v-if="errors.technician" class="text-sm text-destructive">{{ errors.technician }}</p>
        </div>

        <div class="space-y-2">
          <Label for="order-status">Estado</Label>
          <select
            id="order-status"
            v-model="form.status"
            class="flex h-10 w-full rounded-md border bg-background px-3 py-2 text-sm"
            style="border-color: var(--color-input);"
          >
            <option value="open">Aberta</option>
            <option value="in_progress">Em curso</option>
            <option value="completed">Concluída</option>
          </select>
        </div>

        <div class="space-y-2 md:col-span-2">
          <Label for="order-description">Descrição</Label>
          <Textarea id="order-description" v-model="form.description" rows="3" placeholder="Descrição da intervenção" />
          <p v-if="errors.description" class="text-sm text-destructive">{{ errors.description }}</p>
        </div>

        <div class="md:col-span-2 flex justify-end gap-2">
          <Button type="button" variant="outline" @click="closeCreateModal">Cancelar</Button>
          <Button type="submit">Guardar Ordem</Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>
</template>
