<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import ArticleForm from '../componenjs/ArticleForm.vue';
import { useApi } from '../composables/useApi';
import { usePaginatedTable } from '../composables/usePaginatedTable';
import { useArticleStore } from '../stores/articleStore';
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
const articleStore = useArticleStore();

const exampleArticles: Article[] = [
  { id: 1, reference: 'ART-001', name: 'Notebook Dell Inspiron', price: 599.99, active: true },
  { id: 2, reference: 'ART-002', name: 'Mouse Logitech MX Master', price: 99.99, active: true },
  { id: 3, reference: 'ART-003', name: 'Teclado Mecânico RGB', price: 149.99, active: true },
  { id: 4, reference: 'ART-004', name: 'Monitor Samsung 27" 4K', price: 299.99, active: true },
  { id: 5, reference: 'ART-005', name: 'Webcam Logitech C920', price: 79.99, active: true },
  { id: 6, reference: 'ART-006', name: 'Headphone Bose QC35', price: 349.99, active: false },
];

const articles = ref<Article[]>([]);
const isLoading = ref(false);

const { searchQuery, page, paginatedRows, totalPages, setSearch, setPage } =
  usePaginatedTable<Article>(
    () => articles.value,
    (row, query) => {
      const q = query.toLowerCase();
      return row.name.toLowerCase().includes(q) || row.reference.toLowerCase().includes(q);
    },
    10,
  );

const hasRows = computed(() => paginatedRows.value.length > 0);

const fetchArticles = async () => {
  isLoading.value = true;
  try {
    const response = await get<PaginatedResponse<Article>>('/articles', { per_page: 1000 });
    articles.value = response.data && response.data.length > 0 ? response.data : exampleArticles;
  } catch {
    articles.value = exampleArticles;
    toast({ title: 'Erro ao carregar artigos', description: 'Usando dados de exemplo.', variant: 'destructive' });
  } finally {
    isLoading.value = false;
  }
};

const handleDelete = async (article: Article) => {
  if (!window.confirm(`Eliminar artigo "${article.name}"?`)) return;
  try {
    await remove(`/articles/${article.id}`);
    articles.value = articles.value.filter((a) => a.id !== article.id);
    toast({ title: 'Artigo removido', description: 'Registo eliminado com sucesso.' });
  } catch {
    toast({ title: 'Erro ao remover', description: 'Nao foi possivel eliminar o artigo.', variant: 'destructive' });
  }
};

const handleFormSubmitted = (saved: Article) => {
  const idx = articles.value.findIndex((a) => a.id === saved.id);
  if (idx >= 0) articles.value[idx] = saved;
  else articles.value.unshift(saved);
  articleStore.closeModal();
};

const handleDialogOpenChange = (value: boolean) => {
  if (!value) articleStore.closeModal();
};

onMounted(fetchArticles);
</script>

<template>
  <Card>
    <CardHeader class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <CardTitle>Artigos</CardTitle>
      <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
        <Input
          class="md:w-72"
          :model-value="searchQuery"
          placeholder="Buscar por referencia ou nome"
          @update:model-value="setSearch(String($event))"
        />
        <Button @click="articleStore.openCreate()">Novo Artigo</Button>
      </div>
    </CardHeader>

    <CardContent>
      <div v-if="isLoading" class="py-8 text-center text-sm text-muted-foreground">A carregar artigos...</div>

      <div v-else>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Referencia</TableHead>
              <TableHead>Nome</TableHead>
              <TableHead>Preco</TableHead>
              <TableHead>Estado</TableHead>
              <TableHead class="text-right">Acoes</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="article in paginatedRows" :key="article.id">
              <TableCell class="font-mono">{{ article.reference }}</TableCell>
              <TableCell>{{ article.name }}</TableCell>
              <TableCell>{{ Number(article.price).toFixed(2) }} €</TableCell>
              <TableCell>
                <span :class="article.active ? 'text-green-600' : 'text-red-500'">
                  {{ article.active ? 'Ativo' : 'Inativo' }}
                </span>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button variant="outline" size="sm" @click="articleStore.openEdit(article)">Editar</Button>
                  <Button variant="destructive" size="sm" @click="handleDelete(article)">Deletar</Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="!hasRows">
              <TableCell colspan="5" class="text-center text-sm text-muted-foreground">
                Nenhum artigo encontrado.
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

    <Dialog :open="articleStore.isModalOpen" @update:open="handleDialogOpenChange">
      <DialogContent class="max-w-3xl">
        <DialogHeader>
          <DialogTitle>{{ articleStore.selectedArticle ? 'Editar Artigo' : 'Novo Artigo' }}</DialogTitle>
        </DialogHeader>
        <ArticleForm
          :article="articleStore.selectedArticle"
          @cancelled="articleStore.closeModal"
          @submitted="handleFormSubmitted"
        />
      </DialogContent>
    </Dialog>
  </Card>
</template>
