<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useApi } from '../composables/useApi';
import { useToast } from '@/components/ui/toast/use-toast';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

const { get } = useApi();
const { toast } = useToast();

const roles = ref<any[]>([]);
const isLoading = ref(false);

const fetchRoles = async () => {
  isLoading.value = true;
  try {
    const response = await get<any[]>('/roles');
    roles.value = response ?? [];
  } catch {
    toast({
      title: 'Erro ao carregar',
      description: 'Não foi possível obter os grupos de permissão.',
      variant: 'destructive',
    });
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchRoles);
</script>

<template>
  <Card>
    <CardHeader class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <CardTitle>Grupos de Permissão</CardTitle>
      <Button>Novo Grupo</Button>
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
                <span class="text-green-600">Ativo</span>
              </TableCell>
              <TableCell class="text-right">
                <Button size="sm" variant="ghost">Editar</Button>
                <Button size="sm" variant="ghost" class="text-red-600">
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
