<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import EntityForm from './EntityForm.vue';
import { useApi } from '../composables/useApi';
import { usePaginatedTable } from '../composables/usePaginatedTable';
import { useEntityStore } from '../stores/entityStore';
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
import { Search, Plus, Pencil, Trash2, Users, ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps<{
    filterType?: EntityType;
    title?: string;
    hideWebsite?: boolean;
}>();

const { get, remove } = useApi();
const { toast } = useToast();
const entityStore = useEntityStore();

const entities = ref<Entity[]>([]);
const countries = ref<Array<{ id: number; name: string; code?: string }>>([]);
const isLoading = ref(false);
const isDetailOpen = ref(false);
const selectedDetailEntity = ref<Entity | null>(null);

const {
    searchQuery,
    page,
    paginatedRows,
    totalPages,
    setSearch,
    setPage,
} = usePaginatedTable<Entity>(
    () => entities.value,
    (row, query) => {
        const normalized = query.toLowerCase();
        return row.name.toLowerCase().includes(normalized) || row.nif.toLowerCase().includes(normalized);
    },
    10,
);

const hasRows = computed(() => paginatedRows.value.length > 0);
const isClientView = computed(() => props.filterType === 'client');
const isSupplierView = computed(() => props.filterType === 'supplier');

const createButtonLabel = computed(() => {
    if (isClientView.value) return 'Nova Pessoa';
    if (isSupplierView.value) return 'Nova Empresa';
    return 'Nova Entidade';
});

const nameColumnLabel = computed(() => {
    if (isClientView.value) return 'Nome completo';
    if (isSupplierView.value) return 'Empresa';
    return 'Nome';
});

const searchPlaceholder = computed(() => {
    if (isClientView.value) return 'Buscar por nome ou NIF da pessoa...';
    if (isSupplierView.value) return 'Buscar por empresa ou NIF...';
    return 'Buscar por nome ou NIF...';
});

const showWebsiteColumn = computed(() => !props.hideWebsite && !isClientView.value);
const emptyColspan = computed(() => (showWebsiteColumn.value ? 7 : 6));

const modalTitle = computed(() => {
    if (entityStore.selectedEntity) {
        if (isClientView.value) return 'Editar Pessoa';
        if (isSupplierView.value) return 'Editar Empresa';
        return 'Editar Entidade';
    }

    if (isClientView.value) return 'Nova Pessoa';
    if (isSupplierView.value) return 'Nova Empresa';
    return 'Nova Entidade';
});

const modalOpen = computed({
    get: () => entityStore.isModalOpen,
    set: (value: boolean) => {
        if (!value) {
            entityStore.closeModal();
        }
    },
});

const countryNameById = computed(() => {
    const map = new Map<number, string>();
    for (const country of countries.value) {
        map.set(country.id, country.name);
    }
    return map;
});

const getCountryLabel = (entity: Entity) => {
    if (entity.country_name?.trim()) return entity.country_name;

    const nestedName = (entity as any).country?.name;
    if (typeof nestedName === 'string' && nestedName.trim()) return nestedName;

    const countryId = Number((entity as any).country_id ?? 0);
    if (countryId && countryNameById.value.has(countryId)) {
        return countryNameById.value.get(countryId) ?? '-';
    }

    return '-';
};

const fetchEntities = async () => {
    isLoading.value = true;
    try {
        const query: Record<string, string | number> = { per_page: 1000 };
        if (props.filterType) query.type = props.filterType;

        const [entitiesResponse, countriesResponse] = await Promise.all([
            get<PaginatedResponse<Entity>>('/entities', query),
            get<PaginatedResponse<{ id: number; name: string; code?: string }>>('/countries', { per_page: 1000 }),
        ]);

        entities.value = entitiesResponse.data ?? [];
        countries.value = countriesResponse.data ?? [];
    } catch (error) {
        countries.value = [];
        toast({
            title: 'Erro ao carregar entidades',
            description: error instanceof Error ? error.message : 'Nao foi possivel obter os dados da tabela.',
            variant: 'destructive',
        });
    } finally {
        isLoading.value = false;
    }
};

const handleCreate = () => entityStore.openCreate();

const handleEdit = (entity: Entity) => entityStore.openEdit(entity);

const handleViewDetails = (entity: Entity) => {
    selectedDetailEntity.value = entity;
    isDetailOpen.value = true;
};

const handleDelete = async (entity: Entity) => {
    const confirmed = window.confirm(`Deseja realmente eliminar ${entity.name}?`);
    if (!confirmed) {
        return;
    }

    try {
        await remove(`/entities/${entity.id}`);
        entities.value = entities.value.filter((item: Entity) => item.id !== entity.id);

        toast({
            title: 'Entidade removida',
            description: 'O registo foi eliminado com sucesso.',
        });
    } catch {
        toast({
            title: 'Erro ao remover',
            description: 'Nao foi possivel eliminar a entidade.',
            variant: 'destructive',
        });
    }
};

const handleFormSubmitted = (savedEntity: Entity) => {
    console.log('Entity form submitted with:', savedEntity);

    const index = entities.value.findIndex((entity: Entity) => entity.id === savedEntity.id);

    if (index >= 0) {
        entities.value[index] = savedEntity;
    } else {
        entities.value.unshift(savedEntity);
    }

    // Close modal immediately after handling the submission
    entityStore.closeModal();

    console.log('Modal closed after submission');
};

onMounted(fetchEntities);
</script>

<template>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="page-icon-wrap">
                    <Users class="h-5 w-5" style="color: var(--color-primary);" />
                </div>
                <div>
                    <h1 class="page-title">{{ props.title ?? 'Entidades' }}</h1>
                    <p class="page-subtitle">
                        {{ entities.length }} {{ entities.length === 1 ? 'registo' : 'registos' }} no total
                    </p>
                </div>
            </div>

            <Button class="gap-2 self-start sm:self-auto" @click="handleCreate">
                <Plus class="h-4 w-4" />
                {{ createButtonLabel }}
            </Button>
        </div>

        <!-- Search + Table Card -->
        <Card class="shadow-sm">
            <CardHeader class="border-b pb-4">
                <div class="relative w-full sm:max-w-xs">
                    <Search class="search-icon" />
                    <Input class="pl-9" :model-value="searchQuery" :placeholder="searchPlaceholder"
                        @update:model-value="setSearch(String($event))" />
                </div>
            </CardHeader>

            <CardContent class="p-0">
                <!-- Loading skeleton -->
                <div v-if="isLoading" class="divide-y">
                    <div v-for="i in 5" :key="i" class="flex items-center gap-4 px-6 py-4">
                        <div class="skeleton h-4 w-24 rounded" />
                        <div class="skeleton h-4 w-40 rounded" />
                        <div class="skeleton h-4 w-32 rounded" />
                        <div class="skeleton ml-auto h-4 w-16 rounded" />
                    </div>
                </div>

                <div v-else>
                    <Table>
                        <TableHeader>
                            <TableRow class="table-header-row">
                                <TableHead class="th-cell">NIF</TableHead>
                                <TableHead class="th-cell">{{ nameColumnLabel }}</TableHead>
                                <TableHead class="th-cell">Telefone</TableHead>
                                <TableHead class="th-cell">Telemovel</TableHead>
                                <TableHead v-if="showWebsiteColumn" class="th-cell">Website</TableHead>
                                <TableHead class="th-cell">Email</TableHead>
                                <TableHead class="th-cell text-right">Ações</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow v-for="entity in paginatedRows" :key="entity.id"
                                class="entity-row group transition-colors">
                                <TableCell class="td-sub font-mono text-sm font-medium">{{ entity.nif }}</TableCell>
                                <TableCell class="td-main font-medium cursor-pointer hover:underline"
                                    @click="handleViewDetails(entity)">{{ entity.name }}</TableCell>
                                <TableCell class="td-sub text-sm">{{ entity.phone || '—' }}</TableCell>
                                <TableCell class="td-sub text-sm">{{ entity.mobile || '—' }}</TableCell>
                                <TableCell v-if="showWebsiteColumn" class="text-sm">
                                    <a v-if="entity.website" :href="entity.website" target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-primary underline-offset-4 hover:underline">{{ entity.website }}</a>
                                    <span v-else class="td-sub">—</span>
                                </TableCell>
                                <TableCell class="td-sub text-sm">{{ entity.email || '—' }}</TableCell>
                                <TableCell class="text-right">
                                    <div
                                        class="flex justify-end gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                        <Button variant="ghost" size="icon" class="h-8 w-8" title="Editar"
                                            @click="handleEdit(entity)">
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 hover:text-destructive"
                                            title="Eliminar" @click="handleDelete(entity)">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>

                            <TableRow v-if="!hasRows">
                                <TableCell :colspan="emptyColspan" class="py-16 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <Users class="h-10 w-10"
                                            style="color: var(--color-muted-foreground); opacity: 0.4;" />
                                        <p class="empty-title">Nenhum resultado encontrado.</p>
                                        <p class="empty-sub">Tente ajustar os filtros ou adicione uma nova entidade.</p>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div class="flex items-center justify-between border-t px-6 py-4">
                        <p class="page-subtitle">
                            Página <span class="td-main font-medium">{{ page }}</span> de
                            <span class="td-main font-medium">{{ totalPages }}</span>
                        </p>

                        <div class="flex items-center gap-1">
                            <Button variant="outline" size="icon" class="h-8 w-8" :disabled="page <= 1"
                                @click="setPage(page - 1)">
                                <ChevronLeft class="h-4 w-4" />
                            </Button>
                            <Button variant="outline" size="icon" class="h-8 w-8" :disabled="page >= totalPages"
                                @click="setPage(page + 1)">
                                <ChevronRight class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>

    <Dialog v-model:open="modalOpen">
        <DialogContent class="flex max-h-[90vh] max-w-4xl flex-col overflow-hidden">
            <DialogHeader class="flex-shrink-0">
                <DialogTitle>{{ modalTitle }}</DialogTitle>
            </DialogHeader>
            <div class="flex-1 overflow-y-auto pr-1">
                <div v-if="entityStore.selectedEntity?.description" class="mb-4 p-3 rounded bg-muted text-sm">
                    <strong>Descrição:</strong>
                    <span>{{ entityStore.selectedEntity.description }}</span>
                </div>
                                <EntityForm
                                    :key="entityStore.selectedEntity ? entityStore.selectedEntity.id : 'new'"
                                    :entity="entityStore.selectedEntity"
                                    @cancelled="entityStore.closeModal"
                                    :forced-type="props.filterType"
                                    @submitted="handleFormSubmitted"
                                />
            </div>
        </DialogContent>
    </Dialog>

    <Dialog :open="isDetailOpen" @update:open="(open) => { if (!open) isDetailOpen = false; }">
        <DialogContent class="max-w-2xl">
            <DialogHeader>
                <DialogTitle>{{ selectedDetailEntity?.name ?? 'Detalhe da Entidade' }}</DialogTitle>
            </DialogHeader>

            <div v-if="selectedDetailEntity" class="grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                <div><span class="font-medium">Tipo:</span> {{ selectedDetailEntity.type === 'client' ? 'Cliente' : selectedDetailEntity.type === 'supplier' ? 'Fornecedor' : '-' }}</div>
                <div><span class="font-medium">NIF:</span> {{ selectedDetailEntity.nif ?? '-' }}</div>
                <div><span class="font-medium">Email:</span> {{ selectedDetailEntity.email ?? '-' }}</div>
                <div><span class="font-medium">Telefone:</span> {{ selectedDetailEntity.phone ?? '-' }}</div>
                <div><span class="font-medium">Telemóvel:</span> {{ selectedDetailEntity.mobile ?? '-' }}</div>
                <div v-if="showWebsiteColumn"><span class="font-medium">Website:</span> {{ selectedDetailEntity.website ?? '-' }}</div>
                <div><span class="font-medium">País:</span> {{ getCountryLabel(selectedDetailEntity) }}</div>
                <div class="md:col-span-2"><span class="font-medium">Morada:</span> {{ selectedDetailEntity.address ?? '-' }}</div>
                <div><span class="font-medium">Código Postal:</span> {{ selectedDetailEntity.postal_code ?? '-' }}</div>
                <div><span class="font-medium">Cidade:</span> {{ selectedDetailEntity.city ?? '-' }}</div>
                <div class="md:col-span-2"><span class="font-medium">Descrição:</span> {{ selectedDetailEntity.description ?? '-' }}</div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <Button variant="outline" @click="isDetailOpen = false">Fechar</Button>
                <Button @click="() => { if (selectedDetailEntity) { isDetailOpen = false; handleEdit(selectedDetailEntity); } }">Editar</Button>
            </div>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
/* Text colours — use CSS vars so they respond to both light-theme and dark-theme */
.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    letter-spacing: -0.01em;
    color: var(--color-text-main, var(--color-foreground));
}

.page-subtitle {
    font-size: 0.875rem;
    color: var(--color-text-sub, var(--color-muted-foreground));
}

.td-main {
    color: var(--color-text-main, var(--color-foreground));
}

.td-sub {
    color: var(--color-text-sub, var(--color-muted-foreground));
}

.empty-title {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--color-text-main, var(--color-foreground));
}

.empty-sub {
    font-size: 0.75rem;
    color: var(--color-text-sub, var(--color-muted-foreground));
}

/* Table header row */
.table-header-row {
    background-color: var(--color-table-header, #f1f5f9);
}

.table-header-row:hover {
    background-color: var(--color-table-header, #f1f5f9);
}

.th-cell {
    font-weight: 600;
    color: var(--color-text-main, var(--color-foreground));
}

/* Data rows */
.entity-row:hover {
    background-color: var(--color-row-hover, #f1f5f9);
}

/* Page icon */
.page-icon-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.5rem;
    background-color: color-mix(in srgb, var(--color-primary) 15%, transparent);
}

/* Search icon positioning */
.search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    width: 1rem;
    height: 1rem;
    color: var(--color-muted-foreground);
}

/* Skeleton */
.skeleton {
    background-color: var(--color-border);
    animation: pulse 1.5s cubic-bezier(.4, 0, .6, 1) infinite;
}

@keyframes pulse {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.4;
    }
}
</style>
