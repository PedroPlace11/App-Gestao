<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import ContactForm from './ContactForm.vue';
import { useApi } from '../composables/useApi';
import { usePaginatedTable } from '../composables/usePaginatedTable';
import { useContactStore } from '../stores/contactStore';
import { useToast } from '@/components/ui/toast/use-toast';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

const { get, remove } = useApi();
const { toast } = useToast();
const contactStore = useContactStore();

const contacts = ref<Contact[]>([]);
const isLoading = ref(false);

const {
  searchQuery,
  page,
  paginatedRows,
  totalPages,
  setSearch,
  setPage,
} = usePaginatedTable<Contact>(
  () => contacts.value,
  (row, query) => {
    const normalized = query.toLowerCase();
    return (
      row.first_name.toLowerCase().includes(normalized) ||
      row.last_name.toLowerCase().includes(normalized) ||
      (row.email || '').toLowerCase().includes(normalized)
    );
  },
  10,
);

const hasRows = computed(() => paginatedRows.value.length > 0);

const fullName = (contact: Contact) => `${contact.first_name} ${contact.last_name}`.trim();

const fetchContacts = async () => {
  isLoading.value = true;
  try {
    const response = await get<PaginatedResponse<Contact>>('/contacts', { per_page: 1000 });
    contacts.value = response.data ?? [];
  } catch {
    toast({
      title: 'Erro ao carregar contactos',
      description: 'Nao foi possivel carregar os registos.',
      variant: 'destructive',
    });
  } finally {
    isLoading.value = false;
  }
};

const handleCreate = () => contactStore.openCreate();

const handleEdit = (contact: Contact) => contactStore.openEdit(contact);

const handleDelete = async (contact: Contact) => {
  const confirmed = window.confirm(`Deseja realmente eliminar o contacto ${fullName(contact)}?`);
  if (!confirmed) {
    return;
  }

  try {
    await remove(`/contacts/${contact.id}`);
    contacts.value = contacts.value.filter((item: Contact) => item.id !== contact.id);

    toast({
      title: 'Contacto removido',
      description: 'O registo foi eliminado com sucesso.',
    });
  } catch {
    toast({
      title: 'Erro ao remover contacto',
      description: 'Nao foi possivel eliminar o registo.',
      variant: 'destructive',
    });
  }
};

const handleFormSubmitted = (savedContact: Contact) => {
  const index = contacts.value.findIndex((item: Contact) => item.id === savedContact.id);

  if (index >= 0) {
    contacts.value[index] = savedContact;
  } else {
    contacts.value.unshift(savedContact);
  }

  contactStore.closeModal();
};

const handleDialogOpenChange = (value: boolean) => {
  if (!value) {
    contactStore.closeModal();
  }
};

onMounted(fetchContacts);
</script>

<template>
  <Card>
    <CardHeader class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <CardTitle>Contactos</CardTitle>
      <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
        <Input
          class="md:w-72"
          :model-value="searchQuery"
          placeholder="Buscar por nome/apelido/email"
          @update:model-value="setSearch(String($event))"
        />
        <Button @click="handleCreate">Novo Contacto</Button>
      </div>
    </CardHeader>

    <CardContent>
      <div v-if="isLoading" class="py-8 text-center text-sm text-muted-foreground">A carregar contactos...</div>

      <div v-else>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Nome</TableHead>
              <TableHead>Apelido</TableHead>
              <TableHead>Funcao</TableHead>
              <TableHead>Entidade</TableHead>
              <TableHead>Telefone</TableHead>
              <TableHead>Telemovel</TableHead>
              <TableHead>Email</TableHead>
              <TableHead class="text-right">Acoes</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-for="contact in paginatedRows" :key="contact.id">
              <TableCell>{{ contact.first_name }}</TableCell>
              <TableCell>{{ contact.last_name }}</TableCell>
              <TableCell>{{ contact.contact_function?.name || '-' }}</TableCell>
              <TableCell>{{ contact.entity?.name || '-' }}</TableCell>
              <TableCell>{{ contact.phone || '-' }}</TableCell>
              <TableCell>{{ contact.mobile || '-' }}</TableCell>
              <TableCell>{{ contact.email || '-' }}</TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button variant="outline" size="sm" @click="handleEdit(contact)">Editar</Button>
                  <Button variant="destructive" size="sm" @click="handleDelete(contact)">Deletar</Button>
                </div>
              </TableCell>
            </TableRow>

            <TableRow v-if="!hasRows">
              <TableCell colspan="8" class="text-center text-sm text-muted-foreground">
                Nenhum resultado encontrado.
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <div class="mt-4 flex items-center justify-between">
          <p class="text-sm text-muted-foreground">
            Pagina {{ page }} de {{ totalPages }}
          </p>
          <div class="flex items-center gap-2">
            <Button variant="outline" size="sm" :disabled="page <= 1" @click="setPage(page - 1)">
              Anterior
            </Button>
            <Button
              variant="outline"
              size="sm"
              :disabled="page >= totalPages"
              @click="setPage(page + 1)"
            >
              Proxima
            </Button>
          </div>
        </div>
      </div>
    </CardContent>

    <Dialog :open="contactStore.isModalOpen" @update:open="handleDialogOpenChange">
      <DialogContent class="max-w-4xl">
        <DialogHeader>
          <DialogTitle>{{ contactStore.selectedContact ? 'Editar Contacto' : 'Novo Contacto' }}</DialogTitle>
        </DialogHeader>

        <ContactForm
          :contact="contactStore.selectedContact"
          @cancelled="contactStore.closeModal"
          @submitted="handleFormSubmitted"
        />
      </DialogContent>
    </Dialog>
  </Card>
</template>
