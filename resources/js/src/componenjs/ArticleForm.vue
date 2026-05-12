<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useApi } from '../composables/useApi';
import { useToast } from '@/components/ui/toast/use-toast';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Form } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useArticleStore } from '../stores/articleStore';

interface ArticleFormValues {
    reference: string;
    name: string;
    description: string;
    price: string;
    tax_rate_id: number | null;
    observations: string;
    state: 'active' | 'inactive';
}

const props = defineProps<{
    article?: Article | null;
}>();

const emit = defineEmits<{
    (e: 'submitted', payload: Article): void;
    (e: 'cancelled'): void;
}>();

const { get, post, put } = useApi();
const { toast } = useToast();
const articleStore = useArticleStore();

const taxRates = ref<TaxRateOption[]>([]);
const loadingTaxRates = ref(false);
const selectedPhoto = ref<File | null>(null);
const photoPreview = ref<string | null>(null);

const form = reactive<ArticleFormValues>({
    reference: '',
    name: '',
    description: '',
    price: '',
    tax_rate_id: null,
    observations: '',
    state: 'active',
});

const formErrors = reactive<Record<string, string>>({});

const isEditing = computed(() => Boolean(props.article?.id));

const resetErrors = () => {
    Object.keys(formErrors).forEach((key) => {
        delete formErrors[key];
    });
};

const mapFromArticle = (article?: Article | null) => {
    form.reference = article?.reference ?? '';
    form.name = article?.name ?? '';
    form.description = article?.description ?? '';
    form.price = article?.price ? String(article.price) : '';
    form.tax_rate_id = article?.tax_rate_id ?? null;
    form.observations = article?.observations ?? '';
    form.state = article?.active === false ? 'inactive' : 'active';
    photoPreview.value = article?.photo ?? null;
    selectedPhoto.value = null;
};

const validate = () => {
    resetErrors();

    if (!form.reference.trim()) formErrors.reference = 'Referencia e obrigatoria.';
    if (!form.name.trim()) formErrors.name = 'Nome e obrigatorio.';
    if (!form.price || Number(form.price) <= 0) formErrors.price = 'Preco deve ser maior que zero.';
    if (!form.tax_rate_id) formErrors.tax_rate_id = 'Selecione a taxa de IVA.';

    return Object.keys(formErrors).length === 0;
};

const fetchTaxRates = async () => {
    loadingTaxRates.value = true;

    try {
        const response = await get<PaginatedResponse<TaxRateOption>>('/tax-rates', { per_page: 1000 });
        taxRates.value = response.data ?? [];
    } catch {
        toast({
            title: 'Erro ao carregar taxas de IVA',
            description: 'Nao foi possivel carregar a lista de taxas.',
            variant: 'destructive',
        });
    } finally {
        loadingTaxRates.value = false;
    }
};

const onPhotoChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    selectedPhoto.value = file;

    if (!file) {
        return;
    }

    const reader = new FileReader();
    reader.onload = () => {
        photoPreview.value = typeof reader.result === 'string' ? reader.result : null;
    };
    reader.readAsDataURL(file);
};

const submit = async () => {
    if (!validate()) {
        return;
    }

    articleStore.isSubmitting = true;

    const price = Number(form.price);

    try {
        let saved: Article;

        if (selectedPhoto.value) {
            const multipart = new FormData();
            multipart.append('reference', form.reference);
            multipart.append('name', form.name);
            multipart.append('description', form.description);
            multipart.append('price', String(price));
            multipart.append('tax_rate_id', String(form.tax_rate_id));
            multipart.append('observations', form.observations);
            multipart.append('active', form.state === 'active' ? '1' : '0');
            multipart.append('photo', selectedPhoto.value);

            saved = isEditing.value
                ? await put<Article>(`/articles/${props.article?.id}`, multipart)
                : await post<Article>('/articles', multipart);
        } else {
            const payload = {
                reference: form.reference,
                name: form.name,
                description: form.description || null,
                price,
                tax_rate_id: form.tax_rate_id,
                observations: form.observations || null,
                active: form.state === 'active',
            };

            saved = isEditing.value
                ? await put<Article>(`/articles/${props.article?.id}`, payload)
                : await post<Article>('/articles', payload);
        }

        toast({
            title: isEditing.value ? 'Artigo atualizado' : 'Artigo criado',
            description: 'Dados guardados com sucesso.',
        });

        emit('submitted', saved);
    } catch {
        toast({
            title: 'Erro ao guardar artigo',
            description: 'Nao foi possivel guardar os dados.',
            variant: 'destructive',
        });
    } finally {
        articleStore.isSubmitting = false;
    }
};

watch(
    () => props.article,
    (value) => {
        mapFromArticle(value);
        resetErrors();
    },
    { immediate: true },
);

onMounted(fetchTaxRates);
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>{{ isEditing ? 'Editar Artigo' : 'Novo Artigo' }}</CardTitle>
        </CardHeader>

        <CardContent>
            <Form>
                <form class="grid grid-cols-1 gap-4 md:grid-cols-2" @submit.prevent="submit">
                    <div>
                        <Label for="article-reference">Referencia</Label>
                        <Input id="article-reference" v-model="form.reference" placeholder="REF-001" />
                        <p v-if="formErrors.reference" class="text-sm text-red-500">{{ formErrors.reference }}</p>
                    </div>

                    <div>
                        <Label for="article-state">Estado</Label>
                        <Select id="article-state" v-model="form.state">
                            <SelectTrigger>
                                <SelectValue placeholder="Estado" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">Ativo</SelectItem>
                                <SelectItem value="inactive">Inativo</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="md:col-span-2">
                        <Label for="article-name">Nome</Label>
                        <Input id="article-name" v-model="form.name" placeholder="Nome do artigo" />
                        <p v-if="formErrors.name" class="text-sm text-red-500">{{ formErrors.name }}</p>
                    </div>

                    <div>
                        <Label for="article-price">Preco</Label>
                        <Input id="article-price" v-model="form.price" type="number" min="0" step="0.01" />
                        <p v-if="formErrors.price" class="text-sm text-red-500">{{ formErrors.price }}</p>
                    </div>

                    <div>
                        <Label for="article-tax-rate">Taxa IVA</Label>
                        <Select id="article-tax-rate" :disabled="loadingTaxRates" v-model="form.tax_rate_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Selecione a taxa" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="tax in taxRates" :key="tax.id" :value="tax.id">
                                    {{ tax.name }} ({{ tax.rate }}%)
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="formErrors.tax_rate_id" class="text-sm text-red-500">{{ formErrors.tax_rate_id }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <Label for="article-description">Descricao</Label>
                        <Textarea id="article-description" v-model="form.description" rows="4" />
                    </div>

                    <div class="md:col-span-2">
                        <Label for="article-photo">Foto</Label>
                        <Input id="article-photo" type="file" accept="image/*" @change="onPhotoChange" />
                    </div>

                    <div v-if="photoPreview" class="md:col-span-2">
                        <p class="mb-2 text-sm text-muted-foreground">Preview da imagem:</p>
                        <img :src="photoPreview" alt="Preview artigo"
                            class="h-48 w-48 rounded-md border object-cover" />
                    </div>

                    <div class="md:col-span-2">
                        <Label for="article-observations">Observacoes</Label>
                        <Textarea id="article-observations" v-model="form.observations" rows="4" />
                    </div>

                    <div class="md:col-span-2 flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="emit('cancelled')">Cancelar</Button>
                        <Button type="submit" :disabled="articleStore.isSubmitting">
                            {{ articleStore.isSubmitting ? 'A guardar...' : 'Guardar' }}
                        </Button>
                    </div>
                </form>
            </Form>
        </CardContent>
    </Card>
</template>
