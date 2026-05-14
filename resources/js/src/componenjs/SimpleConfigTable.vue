<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useApi } from '../composables/useApi';
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
import SimpleConfigForm from './SimpleConfigForm.vue';

const props = defineProps<{
  title: string;
  endpoint: string;
  columns: { key: string; label: string }[];
  exampleItems?: any[];
  showCodeField?: boolean;
  showRateField?: boolean;
}>();

const { get, remove } = useApi();
const { toast } = useToast();

const items = ref<any[]>([]);
const isLoading = ref(false);
const searchQuery = ref('');
const selectedItem = ref<any | null>(null);
const isFormOpen = ref(false);

const filteredItems = computed(() => {
  if (!searchQuery.value) return items.value;
  const query = searchQuery.value.toLowerCase();
  return items.value.filter(item =>
    Object.values(item).some(val =>
      typeof val === 'string' && val.toLowerCase().includes(query)
    )
  );
});

const fetchItems = async () => {
  isLoading.value = true;
  try {
    const response = await get<PaginatedResponse<any>>(`/v1${props.endpoint}`, { per_page: 1000 });
    items.value = response.data ?? [];

    if (!items.value.length && Array.isArray(props.exampleItems) && props.exampleItems.length) {
      items.value = props.exampleItems;
    }
  } catch {
    if (Array.isArray(props.exampleItems) && props.exampleItems.length) {
      items.value = props.exampleItems;
    }

    toast({
      title: 'Erro ao carregar',
      description: 'Não foi possível obter os dados.',
      variant: 'destructive',
    });
  } finally {
    isLoading.value = false;
  }
};

const handleNew = () => {
  selectedItem.value = null;
  isFormOpen.value = true;
};

const handleEdit = (item: any) => {
  selectedItem.value = item;
  isFormOpen.value = true;
};

const handleDelete = async (item: any) => {
  const itemName = item.name || item.code || `Item ${item.id}`;
  if (!window.confirm(`Eliminar "${itemName}"?`)) return;

  try {
    await remove(`/v1${props.endpoint}/${item.id}`);
    items.value = items.value.filter(i => i.id !== item.id);
    toast({
      title: 'Removido',
      description: 'Registo eliminado com sucesso.',
    });
  } catch (err: any) {
    toast({
      title: 'Erro ao remover',
      description: err?.response?.data?.message ?? 'Não foi possível eliminar.',
      variant: 'destructive',
    });
  }
};

const handleFormSubmitted = (saved: any) => {
  const idx = items.value.findIndex(i => i.id === saved.id);
  if (idx >= 0) items.value[idx] = saved;
  else items.value.unshift(saved);
  isFormOpen.value = false;
};

onMounted(fetchItems);
</script>

<template>
  <Card>
    <CardHeader class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <CardTitle>{{ title }}</CardTitle>
      <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
        <Input
          class="md:w-72"
          :model-value="searchQuery"
          placeholder="Buscar..."
          @update:model-value="searchQuery = String($event)"
        />
        <Button @click="handleNew">Novo</Button>
      </div>
    </CardHeader>

    <CardContent>
      <div v-if="isLoading" class="py-8 text-center text-sm text-muted-foreground">
        A carregar...
      </div>

      <div v-else>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead v-for="col in columns" :key="col.key">{{ col.label }}</TableHead>
              <TableHead class="text-right">Ações</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="item in filteredItems" :key="item.id">
              <TableCell v-for="col in columns" :key="col.key">
                {{ item[col.key] }}
              </TableCell>
              <TableCell class="text-right">
                <Button
                  size="sm"
                  variant="ghost"
                  @click="handleEdit(item)"
                >
                  Editar
                </Button>
                <Button
                  size="sm"
                  variant="ghost"
                  class="text-red-600 hover:text-red-700"
                  @click="handleDelete(item)"
                >
                  Eliminar
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <div v-if="filteredItems.length === 0" class="py-8 text-center text-sm text-muted-foreground">
          Nenhum registo encontrado.
        </div>
      </div>
    </CardContent>
  </Card>

  <Dialog :open="isFormOpen" @update:open="isFormOpen = $event">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>{{ selectedItem ? 'Editar' : 'Novo' }} {{ title }}</DialogTitle>
      </DialogHeader>
      <SimpleConfigForm
        :item="selectedItem"
        :endpoint="endpoint"
        :show-code-field="showCodeField"
        :show-rate-field="showRateField"
        @submitted="handleFormSubmitted"
        @cancelled="isFormOpen = false"
      />
    </DialogContent>
  </Dialog>
</template>
