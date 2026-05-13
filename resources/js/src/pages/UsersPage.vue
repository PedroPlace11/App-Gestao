<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useApi } from '../composables/useApi';
import { useToast } from '@/components/ui/toast/use-toast';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
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

const { get, post, put, remove } = useApi();
const { toast } = useToast();

const users = ref<any[]>([]);
const isLoading = ref(false);
const searchQuery = ref('');
const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const isSubmitting = ref(false);
const editingUserId = ref<number | null>(null);

const createForm = ref({
  name: '',
  email: '',
  mobile: '',
  roleName: 'Utilizador',
  active: 'true',
});

const editForm = ref({
  name: '',
  email: '',
  mobile: '',
  roleName: 'Utilizador',
  active: 'true',
});

const exampleUsers = [
  {
    id: 1,
    name: 'Ana Martins',
    email: 'ana.martins@inovcorp.pt',
    mobile: '+351 912 345 678',
    active: true,
    roles: [{ name: 'Administradora' }],
  },
  {
    id: 2,
    name: 'João Pereira',
    email: 'joao.pereira@inovcorp.pt',
    mobile: '+351 913 222 111',
    active: true,
    roles: [{ name: 'Gestor' }],
  },
  {
    id: 3,
    name: 'Marta Silva',
    email: 'marta.silva@inovcorp.pt',
    mobile: '+351 914 777 222',
    active: false,
    roles: [{ name: 'Utilizador' }],
  },
];

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
    const response = await get<any>('/users');
    const apiUsers = Array.isArray(response?.data) ? response.data : (Array.isArray(response) ? response : []);
    users.value = apiUsers.length ? apiUsers : exampleUsers;
  } catch {
    users.value = exampleUsers;
    toast({
      title: 'Erro ao carregar',
      description: 'Não foi possível obter os utilizadores.',
      variant: 'destructive',
    });
  } finally {
    isLoading.value = false;
  }
};

const validateForm = (name: string, email: string, currentId?: number) => {
  if (!name.trim()) {
    toast({ title: 'Nome obrigatório', description: 'Informe o nome do utilizador.', variant: 'destructive' });
    return false;
  }

  if (!email.trim() || !email.includes('@')) {
    toast({ title: 'Email inválido', description: 'Informe um email válido.', variant: 'destructive' });
    return false;
  }

  const duplicate = users.value.some(
    (u) => String(u.email ?? '').toLowerCase() === email.toLowerCase() && Number(u.id) !== Number(currentId ?? 0),
  );

  if (duplicate) {
    toast({ title: 'Email já existe', description: 'Use um email diferente.', variant: 'destructive' });
    return false;
  }

  return true;
};

const openCreate = () => {
  createForm.value = {
    name: '',
    email: '',
    mobile: '',
    roleName: 'Utilizador',
    active: 'true',
  };
  isCreateOpen.value = true;
};

const createUser = async () => {
  const name = createForm.value.name.trim();
  const email = createForm.value.email.trim();

  if (!validateForm(name, email)) return;

  isSubmitting.value = true;
  try {
    const created = await post('/users', {
      name,
      email,
      password: 'TempPass123',
      active: createForm.value.active === 'true',
    });

    const user = {
      id: created?.id ?? Date.now(),
      name: created?.name ?? name,
      email: created?.email ?? email,
      mobile: createForm.value.mobile,
      active: created?.active ?? (createForm.value.active === 'true'),
      roles: [{ name: createForm.value.roleName }],
    };

    users.value.unshift(user);
    toast({ title: 'Utilizador criado', description: 'Novo utilizador adicionado com sucesso.' });
    isCreateOpen.value = false;
  } catch {
    const user = {
      id: Date.now(),
      name,
      email,
      mobile: createForm.value.mobile,
      active: createForm.value.active === 'true',
      roles: [{ name: createForm.value.roleName }],
    };

    users.value.unshift(user);
    toast({
      title: 'Criado localmente',
      description: 'A API não respondeu. O utilizador foi adicionado na lista local.',
    });
    isCreateOpen.value = false;
  } finally {
    isSubmitting.value = false;
  }
};

const openEdit = (user: any) => {
  editingUserId.value = Number(user.id);
  editForm.value = {
    name: String(user.name ?? ''),
    email: String(user.email ?? ''),
    mobile: String(user.mobile ?? ''),
    roleName: String(user.roles?.[0]?.name ?? 'Utilizador'),
    active: user.active ? 'true' : 'false',
  };
  isEditOpen.value = true;
};

const saveEdit = async () => {
  if (editingUserId.value === null) return;

  const name = editForm.value.name.trim();
  const email = editForm.value.email.trim();

  if (!validateForm(name, email, editingUserId.value)) return;

  isSubmitting.value = true;
  try {
    const updated = await put(`/users/${editingUserId.value}`, {
      name,
      email,
      active: editForm.value.active === 'true',
    });

    const idx = users.value.findIndex((u) => Number(u.id) === editingUserId.value);
    if (idx >= 0) {
      users.value[idx] = {
        ...users.value[idx],
        ...updated,
        name: updated?.name ?? name,
        email: updated?.email ?? email,
        mobile: editForm.value.mobile,
        active: updated?.active ?? (editForm.value.active === 'true'),
        roles: [{ name: editForm.value.roleName }],
      };
    }

    toast({ title: 'Utilizador atualizado', description: 'Dados atualizados com sucesso.' });
    isEditOpen.value = false;
  } catch {
    const idx = users.value.findIndex((u) => Number(u.id) === editingUserId.value);
    if (idx >= 0) {
      users.value[idx] = {
        ...users.value[idx],
        name,
        email,
        mobile: editForm.value.mobile,
        active: editForm.value.active === 'true',
        roles: [{ name: editForm.value.roleName }],
      };
    }

    toast({
      title: 'Atualizado localmente',
      description: 'A API não respondeu. Os dados foram atualizados localmente.',
    });
    isEditOpen.value = false;
  } finally {
    isSubmitting.value = false;
  }
};

const handleDelete = async (user: any) => {
  if (!window.confirm(`Eliminar utilizador "${user.name}"?`)) return;

  try {
    await remove(`/users/${user.id}`);
    users.value = users.value.filter(u => u.id !== user.id);
    toast({ title: 'Removido', description: 'Utilizador eliminado com sucesso.' });
  } catch {
    users.value = users.value.filter(u => u.id !== user.id);
    toast({
      title: 'Eliminado localmente',
      description: 'A API não respondeu. O utilizador foi removido da lista local.',
    });
  }
};

onMounted(fetchUsers);
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
        <Button @click="openCreate">Novo Utilizador</Button>
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
                <Button size="sm" variant="ghost" @click="openEdit(user)">Editar</Button>
                <Button size="sm" variant="ghost" class="text-red-600" @click="handleDelete(user)">
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
          <DialogTitle>Novo Utilizador</DialogTitle>
        </DialogHeader>

        <form class="space-y-4" @submit.prevent="createUser">
          <div class="space-y-2">
            <Label for="create-name">Nome</Label>
            <Input id="create-name" v-model="createForm.name" placeholder="Nome completo" />
          </div>

          <div class="space-y-2">
            <Label for="create-email">Email</Label>
            <Input id="create-email" v-model="createForm.email" placeholder="email@empresa.pt" />
          </div>

          <div class="space-y-2">
            <Label for="create-mobile">Telemóvel</Label>
            <Input id="create-mobile" v-model="createForm.mobile" placeholder="+351 9XX XXX XXX" />
          </div>

          <div class="space-y-2">
            <Label>Grupo</Label>
            <Select v-model="createForm.roleName">
              <SelectTrigger>
                <SelectValue placeholder="Selecionar grupo" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="Administradora">Administradora</SelectItem>
                <SelectItem value="Gestor">Gestor</SelectItem>
                <SelectItem value="Utilizador">Utilizador</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Label>Estado</Label>
            <Select v-model="createForm.active">
              <SelectTrigger>
                <SelectValue placeholder="Selecionar estado" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="true">Ativo</SelectItem>
                <SelectItem value="false">Inativo</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="flex justify-end gap-2">
            <Button type="button" variant="outline" :disabled="isSubmitting" @click="isCreateOpen = false">
              Cancelar
            </Button>
            <Button type="submit" :disabled="isSubmitting">
              {{ isSubmitting ? 'A guardar...' : 'Guardar' }}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>

    <Dialog :open="isEditOpen" @update:open="(value) => { if (!value) isEditOpen = false; }">
      <DialogContent class="max-w-md">
        <DialogHeader>
          <DialogTitle>Editar Utilizador</DialogTitle>
        </DialogHeader>

        <form class="space-y-4" @submit.prevent="saveEdit">
          <div class="space-y-2">
            <Label for="edit-name">Nome</Label>
            <Input id="edit-name" v-model="editForm.name" placeholder="Nome completo" />
          </div>

          <div class="space-y-2">
            <Label for="edit-email">Email</Label>
            <Input id="edit-email" v-model="editForm.email" placeholder="email@empresa.pt" />
          </div>

          <div class="space-y-2">
            <Label for="edit-mobile">Telemóvel</Label>
            <Input id="edit-mobile" v-model="editForm.mobile" placeholder="+351 9XX XXX XXX" />
          </div>

          <div class="space-y-2">
            <Label>Grupo</Label>
            <Select v-model="editForm.roleName">
              <SelectTrigger>
                <SelectValue placeholder="Selecionar grupo" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="Administradora">Administradora</SelectItem>
                <SelectItem value="Gestor">Gestor</SelectItem>
                <SelectItem value="Utilizador">Utilizador</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Label>Estado</Label>
            <Select v-model="editForm.active">
              <SelectTrigger>
                <SelectValue placeholder="Selecionar estado" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="true">Ativo</SelectItem>
                <SelectItem value="false">Inativo</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="flex justify-end gap-2">
            <Button type="button" variant="outline" :disabled="isSubmitting" @click="isEditOpen = false">
              Cancelar
            </Button>
            <Button type="submit" :disabled="isSubmitting">
              {{ isSubmitting ? 'A guardar...' : 'Guardar alterações' }}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  </Card>
</template>
