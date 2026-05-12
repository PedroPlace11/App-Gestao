<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useApi } from '../composables/useApi';
import { useToast } from '@/components/ui/toast/use-toast';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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

const users = ref<any[]>([]);
const isLoading = ref(false);
const searchQuery = ref('');

const filteredUsers = computed(() => {
  if (!searchQuery.value) return users.value;
  const q = searchQuery.value.toLowerCase();
  return users.value.filter(u =>
    u.name?.toLowerCase().includes(q) ||
    u.email?.toLowerCase().includes(q)
  );
});

const fetchUsers = async () => {
  isLoading.value = true;
  try {
    const response = await get<any[]>('/users');
    users.value = response ?? [];
  } catch {
    toast({
      title: 'Erro ao carregar',
      description: 'Não foi possível obter os utilizadores.',
      variant: 'destructive',
    });
  } finally {
    isLoading.value = false;
  }
};

const handleDelete = async (user: any) => {
  if (!window.confirm(`Eliminar utilizador "${user.name}"?`)) return;
  try {
    await remove(`/users/${user.id}`);
    users.value = users.value.filter(u => u.id !== user.id);
    toast({ title: 'Removido', description: 'Utilizador eliminado com sucesso.' });
  } catch {
    toast({
      title: 'Erro ao remover',
      description: 'Não foi possível eliminar o utilizador.',
      variant: 'destructive',
    });
  }
};

onMounted(fetchUsers);

import { computed } from 'vue';
</script>

<template>
  <Card>
    <CardHeader class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <CardTitle>Utilizadores</CardTitle>
      <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
        <Input
          class="md:w-72"
          :model-value="searchQuery"
          placeholder="Buscar por nome ou email"
          @update:model-value="searchQuery = String($event)"
        />
        <Button>Novo Utilizador</Button>
      </div>
    </CardHeader>

    <CardContent>
      <div v-if="isLoading" class="py-8 text-center text-sm text-muted-foreground">
        A carregar utilizadores...
      </div>

      <div v-else>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Nome</TableHead>
              <TableHead>Email</TableHead>
              <TableHead>Telemóvel</TableHead>
              <TableHead>Grupo</TableHead>
              <TableHead>Estado</TableHead>
              <TableHead class="text-right">Ações</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="user in filteredUsers" :key="user.id">
              <TableCell>{{ user.name }}</TableCell>
              <TableCell>{{ user.email }}</TableCell>
              <TableCell>{{ user.mobile || '-' }}</TableCell>
              <TableCell>{{ user.roles?.[0]?.name || '-' }}</TableCell>
              <TableCell>
                <span :class="user.active ? 'text-green-600' : 'text-red-600'">
                  {{ user.active ? 'Ativo' : 'Inativo' }}
                </span>
              </TableCell>
              <TableCell class="text-right">
                <Button size="sm" variant="ghost">Editar</Button>
                <Button size="sm" variant="ghost" class="text-red-600" @click="handleDelete(user)">
                  Eliminar
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </CardContent>
  </Card>
</template>
