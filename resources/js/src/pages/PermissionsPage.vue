<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useApi } from '../composables/useApi';
import { useToast } from '@/components/ui/toast/use-toast';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

const { get, post, put, remove } = useApi();
const { toast } = useToast();

const roles = ref<any[]>([]);
const isLoading = ref(false);
const isCreateOpen = ref(false);
const isCreating = ref(false);
const newRoleName = ref('');
const isEditOpen = ref(false);
const isEditing = ref(false);
const editRoleId = ref<number | null>(null);
const editRoleName = ref('');

const exampleRoles = [
  { id: 1, name: 'Administradores', users_count: 2, active: true },
  { id: 2, name: 'Gestores', users_count: 4, active: true },
  { id: 3, name: 'Utilizadores', users_count: 7, active: true },
];

const fetchRoles = async () => {
  isLoading.value = true;
  try {
    const response = await get<any[]>('/roles');
    roles.value = Array.isArray(response?.data)
      ? response.data
      : (Array.isArray(response) ? response : []);

    if (!roles.value.length) {
      roles.value = exampleRoles;
    }
  } catch {
    roles.value = exampleRoles;
    toast({
      title: 'Erro ao carregar',
      description: 'Não foi possível obter os grupos de permissão.',
      variant: 'destructive',
    });
  } finally {
    isLoading.value = false;
  }
};

const openCreate = () => {
  newRoleName.value = '';
  isCreateOpen.value = true;
};

const createRole = async () => {
  const name = newRoleName.value.trim();

  if (!name) {
    toast({ title: 'Nome obrigatório', description: 'Informe o nome do grupo.', variant: 'destructive' });
    return;
  }

  const exists = roles.value.some((role) => String(role.name ?? '').toLowerCase() === name.toLowerCase());
  if (exists) {
    toast({ title: 'Grupo já existe', description: 'Escolha outro nome para o grupo.', variant: 'destructive' });
    return;
  }

  isCreating.value = true;
  try {
    const created = await post('/roles', { name, active: true });
    const role = {
      id: created?.id ?? Date.now(),
      name: created?.name ?? name,
      users_count: created?.users_count ?? 0,
      active: created?.active ?? true,
    };
    roles.value.unshift(role);
    toast({ title: 'Grupo criado', description: 'Grupo de permissão criado com sucesso.' });
    isCreateOpen.value = false;
  } catch {
    const role = { id: Date.now(), name, users_count: 0, active: true };
    roles.value.unshift(role);
    toast({
      title: 'Grupo criado localmente',
      description: 'A API de criação não respondeu. O grupo foi adicionado como exemplo na lista.',
    });
    isCreateOpen.value = false;
  } finally {
    isCreating.value = false;
  }
};

const openEdit = (role: any) => {
  editRoleId.value = Number(role.id);
  editRoleName.value = String(role.name ?? '');
  isEditOpen.value = true;
};

const saveEdit = async () => {
  const name = editRoleName.value.trim();

  if (!name || editRoleId.value === null) {
    toast({ title: 'Nome obrigatório', description: 'Informe o nome do grupo.', variant: 'destructive' });
    return;
  }

  const exists = roles.value.some(
    (role) => Number(role.id) !== editRoleId.value && String(role.name ?? '').toLowerCase() === name.toLowerCase(),
  );
  if (exists) {
    toast({ title: 'Grupo já existe', description: 'Escolha outro nome para o grupo.', variant: 'destructive' });
    return;
  }

  isEditing.value = true;
  try {
    const updated = await put(`/roles/${editRoleId.value}`, { name });
    const idx = roles.value.findIndex((role) => Number(role.id) === editRoleId.value);
    if (idx >= 0) {
      roles.value[idx] = {
        ...roles.value[idx],
        ...updated,
        name: updated?.name ?? name,
      };
    }
    toast({ title: 'Grupo atualizado', description: 'Grupo de permissão atualizado com sucesso.' });
    isEditOpen.value = false;
  } catch {
    const idx = roles.value.findIndex((role) => Number(role.id) === editRoleId.value);
    if (idx >= 0) {
      roles.value[idx] = {
        ...roles.value[idx],
        name,
      };
    }
    toast({
      title: 'Atualizado localmente',
      description: 'A API de edição não respondeu. O nome foi atualizado localmente.',
    });
    isEditOpen.value = false;
  } finally {
    isEditing.value = false;
  }
};

const handleDelete = async (role: any) => {
  if (!window.confirm(`Eliminar grupo "${role.name}"?`)) return;

  const roleId = Number(role.id);

  try {
    await remove(`/roles/${roleId}`);
    roles.value = roles.value.filter((item) => Number(item.id) !== roleId);
    toast({ title: 'Grupo eliminado', description: 'Grupo removido com sucesso.' });
  } catch {
    roles.value = roles.value.filter((item) => Number(item.id) !== roleId);
    toast({
      title: 'Eliminado localmente',
      description: 'A API de remoção não respondeu. O grupo foi removido da lista local.',
    });
  }
};

onMounted(fetchRoles);
</script>

<template>
  <Card>
    <CardHeader class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <CardTitle>Grupos de Permissão</CardTitle>
      <Button @click="openCreate">Novo Grupo</Button>
    </CardHeader>

    <CardContent>
      <div v-if="isLoading" class="py-8 text-center text-sm text-muted-foreground">
        A carregar grupos...
      </div>

      <div v-else>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Nome do Grupo</TableHead>
              <TableHead>Utilizadores Relacionados</TableHead>
              <TableHead>Estado</TableHead>
              <TableHead class="text-right">Ações</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="role in roles" :key="role.id">
              <TableCell>{{ role.name }}</TableCell>
              <TableCell>{{ role.users_count ?? 0 }}</TableCell>
              <TableCell>
                <span :class="role.active === false ? 'text-red-600' : 'text-green-600'">
                  {{ role.active === false ? 'Inativo' : 'Ativo' }}
                </span>
              </TableCell>
              <TableCell class="text-right">
                <Button size="sm" variant="ghost" @click="openEdit(role)">Editar</Button>
                <Button size="sm" variant="ghost" class="text-red-600" @click="handleDelete(role)">
                  Eliminar
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </CardContent>

    <Dialog :open="isCreateOpen" @update:open="(value) => { if (!value) isCreateOpen = false; }">
      <DialogContent class="max-w-md">
        <DialogHeader>
          <DialogTitle>Novo Grupo de Permissão</DialogTitle>
        </DialogHeader>

        <form class="space-y-4" @submit.prevent="createRole">
          <div class="space-y-2">
            <Label for="role-name">Nome do Grupo</Label>
            <Input
              id="role-name"
              v-model="newRoleName"
              placeholder="Ex.: Financeiro"
            />
          </div>

          <div class="flex justify-end gap-2">
            <Button type="button" variant="outline" :disabled="isCreating" @click="isCreateOpen = false">
              Cancelar
            </Button>
            <Button type="submit" :disabled="isCreating">
              {{ isCreating ? 'A guardar...' : 'Guardar' }}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>

    <Dialog :open="isEditOpen" @update:open="(value) => { if (!value) isEditOpen = false; }">
      <DialogContent class="max-w-md">
        <DialogHeader>
          <DialogTitle>Editar Grupo de Permissão</DialogTitle>
        </DialogHeader>

        <form class="space-y-4" @submit.prevent="saveEdit">
          <div class="space-y-2">
            <Label for="edit-role-name">Nome do Grupo</Label>
            <Input
              id="edit-role-name"
              v-model="editRoleName"
              placeholder="Ex.: Financeiro"
            />
          </div>

          <div class="flex justify-end gap-2">
            <Button type="button" variant="outline" :disabled="isEditing" @click="isEditOpen = false">
              Cancelar
            </Button>
            <Button type="submit" :disabled="isEditing">
              {{ isEditing ? 'A guardar...' : 'Guardar alterações' }}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  </Card>
</template>
