<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useToast } from '@/components/ui/toast/use-toast';

interface BankAccountItem {
  id: number;
  bank_name: string;
  iban: string;
  account_number: string;
  active: boolean;
}

const { toast } = useToast();
const isCreateOpen = ref(false);
const nextId = ref(4);
const accounts = ref<BankAccountItem[]>([
  {
    id: 1,
    bank_name: 'Banco Exemplo, S.A.',
    iban: 'PT50 0002 0123 12345678901 54',
    account_number: '12345678901',
    active: true,
  },
  {
    id: 2,
    bank_name: 'Caixa Geral de Depósitos',
    iban: 'PT50 0035 0123 98765432109 11',
    account_number: '98765432109',
    active: true,
  },
  {
    id: 3,
    bank_name: 'Banco Comercial Português',
    iban: 'PT50 0010 0456 12312312312 34',
    account_number: '12312312312',
    active: false,
  },
]);

const form = reactive({
  bank_name: '',
  iban: '',
  account_number: '',
});

const errors = reactive<Record<string, string>>({});

const hasAccounts = computed(() => accounts.value.length > 0);

const resetForm = () => {
  form.bank_name = '';
  form.iban = '';
  form.account_number = '';
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

  if (!form.bank_name.trim()) {
    errors.bank_name = 'O nome do banco é obrigatório.';
  }

  if (!form.iban.trim()) {
    errors.iban = 'O IBAN é obrigatório.';
  }

  if (!form.account_number.trim()) {
    errors.account_number = 'O número da conta é obrigatório.';
  }

  const normalizedIban = form.iban.replace(/\s+/g, '').toUpperCase();
  if (normalizedIban && !/^[A-Z]{2}[0-9A-Z]{13,32}$/.test(normalizedIban)) {
    errors.iban = 'IBAN inválido.';
  }

  const duplicated = accounts.value.some((item) => {
    return item.iban.replace(/\s+/g, '').toUpperCase() === normalizedIban;
  });
  if (duplicated) {
    errors.iban = 'Este IBAN já existe na lista.';
  }

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

  accounts.value.unshift({
    id: nextId.value++,
    bank_name: form.bank_name.trim(),
    iban: form.iban.trim().toUpperCase(),
    account_number: form.account_number.trim(),
    active: true,
  });

  toast({
    title: 'Conta criada',
    description: 'A conta bancária foi adicionada com sucesso.',
  });

  closeCreateModal();
};
</script>

<template>
  <Card>
    <CardHeader class="flex flex-row items-center justify-between">
      <CardTitle>Contas Bancárias</CardTitle>
      <Button @click="openCreateModal">Nova Conta</Button>
    </CardHeader>

    <CardContent>
      <div v-if="!hasAccounts" class="py-12 text-center text-muted-foreground">
        <p class="mb-4">Ainda não existem contas bancárias registadas.</p>
        <p class="text-sm">Clique em "Nova Conta" para adicionar a primeira conta.</p>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="account in accounts"
          :key="account.id"
          class="rounded-lg border p-4"
          style="border-color: var(--color-border);"
        >
          <p class="font-medium">{{ account.bank_name }}</p>
          <p class="text-sm text-muted-foreground">IBAN: {{ account.iban }}</p>
          <p class="text-sm text-muted-foreground">Conta: {{ account.account_number }}</p>
        </div>
      </div>
    </CardContent>
  </Card>

  <Dialog v-model:open="isCreateOpen">
    <DialogContent class="max-w-lg">
      <DialogHeader>
        <DialogTitle>Nova Conta Bancária</DialogTitle>
      </DialogHeader>

      <form class="space-y-4" @submit.prevent="submitCreate">
        <div class="space-y-2">
          <Label for="bank-name">Banco</Label>
          <Input id="bank-name" v-model="form.bank_name" placeholder="Ex: Banco Santander" />
          <p v-if="errors.bank_name" class="text-sm text-destructive">{{ errors.bank_name }}</p>
        </div>

        <div class="space-y-2">
          <Label for="iban">IBAN</Label>
          <Input id="iban" v-model="form.iban" placeholder="Ex: PT50 0002 0123 12345678901 54" />
          <p v-if="errors.iban" class="text-sm text-destructive">{{ errors.iban }}</p>
        </div>

        <div class="space-y-2">
          <Label for="account-number">Número da Conta</Label>
          <Input id="account-number" v-model="form.account_number" placeholder="Ex: 12345678901" />
          <p v-if="errors.account_number" class="text-sm text-destructive">{{ errors.account_number }}</p>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <Button type="button" variant="outline" @click="closeCreateModal">Cancelar</Button>
          <Button type="submit">Guardar Conta</Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>
</template>
