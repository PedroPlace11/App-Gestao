<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/components/ui/toast/use-toast';

interface CurrentAccountEntry {
  id: number;
  client_name: string;
  document: string;
  description: string;
  debit: number;
  credit: number;
  date: string;
}

const { toast } = useToast();
const isCreateOpen = ref(false);
const nextId = ref(4);

const entries = ref<CurrentAccountEntry[]>([
  {
    id: 1,
    client_name: 'Cliente Exemplo, Lda.',
    document: 'FT 2026/0012',
    description: 'Fatura de serviços de consultoria',
    debit: 0,
    credit: 1250,
    date: '2026-05-10',
  },
  {
    id: 2,
    client_name: 'Inovcorp Group',
    document: 'RC 2026/0008',
    description: 'Recebimento parcial da fatura FT 2026/0012',
    debit: 500,
    credit: 0,
    date: '2026-05-11',
  },
  {
    id: 3,
    client_name: 'Cliente Exemplo, Lda.',
    document: 'NC 2026/0003',
    description: 'Nota de crédito por desconto comercial',
    debit: 0,
    credit: 75,
    date: '2026-05-12',
  },
]);

const form = reactive({
  client_name: '',
  document: '',
  description: '',
  debit: '',
  credit: '',
  date: '',
});

const errors = reactive<Record<string, string>>({});

const totalDebit = computed(() => entries.value.reduce((sum, entry) => sum + entry.debit, 0));
const totalCredit = computed(() => entries.value.reduce((sum, entry) => sum + entry.credit, 0));
const balance = computed(() => totalCredit.value - totalDebit.value);

const resetForm = () => {
  form.client_name = '';
  form.document = '';
  form.description = '';
  form.debit = '';
  form.credit = '';
  form.date = '';

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

  if (!form.client_name.trim()) errors.client_name = 'O cliente é obrigatório.';
  if (!form.document.trim()) errors.document = 'O documento é obrigatório.';
  if (!form.description.trim()) errors.description = 'A descrição é obrigatória.';
  if (!form.date.trim()) errors.date = 'A data é obrigatória.';

  const debitValue = Number(form.debit || 0);
  const creditValue = Number(form.credit || 0);

  if (Number.isNaN(debitValue) || debitValue < 0) errors.debit = 'Débito inválido.';
  if (Number.isNaN(creditValue) || creditValue < 0) errors.credit = 'Crédito inválido.';
  if (debitValue === 0 && creditValue === 0) errors.debit = 'Informe pelo menos um valor de débito ou crédito.';

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

  entries.value.unshift({
    id: nextId.value++,
    client_name: form.client_name.trim(),
    document: form.document.trim().toUpperCase(),
    description: form.description.trim(),
    debit: Number(form.debit || 0),
    credit: Number(form.credit || 0),
    date: form.date,
  });

  toast({
    title: 'Lançamento criado',
    description: 'O movimento foi adicionado à conta corrente.',
  });

  closeCreateModal();
};
</script>

<template>
  <div class="space-y-6">
    <Card>
      <CardHeader class="flex flex-row items-center justify-between gap-4">
        <div>
          <CardTitle>Conta Corrente de Clientes</CardTitle>
          <p class="mt-1 text-sm text-muted-foreground">Movimentos fictícios para demonstração e testes rápidos.</p>
        </div>
        <Button @click="openCreateModal">Novo Lançamento</Button>
      </CardHeader>

      <CardContent>
        <div class="grid gap-3 md:grid-cols-3">
          <div class="rounded-lg border p-4" style="border-color: var(--color-border);">
            <p class="text-sm text-muted-foreground">Total Débito</p>
            <p class="text-2xl font-semibold">{{ totalDebit.toFixed(2) }} €</p>
          </div>
          <div class="rounded-lg border p-4" style="border-color: var(--color-border);">
            <p class="text-sm text-muted-foreground">Total Crédito</p>
            <p class="text-2xl font-semibold">{{ totalCredit.toFixed(2) }} €</p>
          </div>
          <div class="rounded-lg border p-4" style="border-color: var(--color-border);">
            <p class="text-sm text-muted-foreground">Saldo</p>
            <p class="text-2xl font-semibold" :class="balance >= 0 ? 'text-emerald-500' : 'text-red-500'">
              {{ balance.toFixed(2) }} €
            </p>
          </div>
        </div>

        <div class="mt-6 overflow-hidden rounded-lg border" style="border-color: var(--color-border);">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-900/30 text-left text-xs uppercase tracking-wide text-muted-foreground">
              <tr>
                <th class="px-4 py-3">Data</th>
                <th class="px-4 py-3">Cliente</th>
                <th class="px-4 py-3">Documento</th>
                <th class="px-4 py-3">Descrição</th>
                <th class="px-4 py-3 text-right">Débito</th>
                <th class="px-4 py-3 text-right">Crédito</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="entry in entries" :key="entry.id" class="border-t" style="border-color: var(--color-border);">
                <td class="px-4 py-3">{{ entry.date }}</td>
                <td class="px-4 py-3 font-medium">{{ entry.client_name }}</td>
                <td class="px-4 py-3">{{ entry.document }}</td>
                <td class="px-4 py-3">{{ entry.description }}</td>
                <td class="px-4 py-3 text-right">{{ entry.debit.toFixed(2) }} €</td>
                <td class="px-4 py-3 text-right">{{ entry.credit.toFixed(2) }} €</td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardContent>
    </Card>

    <Dialog v-model:open="isCreateOpen">
      <DialogContent class="max-w-xl">
        <DialogHeader>
          <DialogTitle>Novo Lançamento</DialogTitle>
        </DialogHeader>

        <form class="space-y-4" @submit.prevent="submitCreate">
          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2 md:col-span-2">
              <Label for="client-name">Cliente</Label>
              <Input id="client-name" v-model="form.client_name" placeholder="Ex: Cliente Exemplo, Lda." />
              <p v-if="errors.client_name" class="text-sm text-destructive">{{ errors.client_name }}</p>
            </div>

            <div class="space-y-2">
              <Label for="document">Documento</Label>
              <Input id="document" v-model="form.document" placeholder="Ex: FT 2026/0013" />
              <p v-if="errors.document" class="text-sm text-destructive">{{ errors.document }}</p>
            </div>

            <div class="space-y-2">
              <Label for="date">Data</Label>
              <Input id="date" v-model="form.date" type="date" />
              <p v-if="errors.date" class="text-sm text-destructive">{{ errors.date }}</p>
            </div>

            <div class="space-y-2 md:col-span-2">
              <Label for="description">Descrição</Label>
              <Textarea id="description" v-model="form.description" rows="3" placeholder="Ex: Fatura de serviços prestados" />
              <p v-if="errors.description" class="text-sm text-destructive">{{ errors.description }}</p>
            </div>

            <div class="space-y-2">
              <Label for="debit">Débito</Label>
              <Input id="debit" v-model="form.debit" type="number" min="0" step="0.01" placeholder="0.00" />
              <p v-if="errors.debit" class="text-sm text-destructive">{{ errors.debit }}</p>
            </div>

            <div class="space-y-2">
              <Label for="credit">Crédito</Label>
              <Input id="credit" v-model="form.credit" type="number" min="0" step="0.01" placeholder="0.00" />
              <p v-if="errors.credit" class="text-sm text-destructive">{{ errors.credit }}</p>
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <Button type="button" variant="outline" @click="closeCreateModal">Cancelar</Button>
            <Button type="submit">Guardar Lançamento</Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>
