<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useApi } from '../composables/useApi';
import { useToast } from '@/components/ui/toast/use-toast';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
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
import { useContactStore } from '../stores/contactStore';

interface ContactFormValues {
    entity_id: number | null;
    first_name: string;
    last_name: string;
    function_id: number | null;
    phone: string;
    mobile: string;
    email: string;
    rgpd_consent: boolean;
    observations: string;
    state: 'active' | 'inactive';
}

const props = defineProps<{
    contact?: Contact | null;
}>();

const emit = defineEmits<{
    (e: 'submitted', payload: Contact): void;
    (e: 'cancelled'): void;
}>();

const { get, post, put } = useApi();
const { toast } = useToast();
const contactStore = useContactStore();

const entities = ref<Entity[]>([]);
const functions = ref<ContactFunctionOption[]>([]);
const loadingReferences = ref(false);

const form = reactive<ContactFormValues>({
    entity_id: null,
    first_name: '',
    last_name: '',
    function_id: null,
    phone: '',
    mobile: '',
    email: '',
    rgpd_consent: false,
    observations: '',
    state: 'active',
});

const formErrors = reactive<Record<string, string>>({});

const isEditing = computed(() => Boolean(props.contact?.id));

const resetErrors = () => {
    Object.keys(formErrors).forEach((key) => {
        delete formErrors[key];
    });
};

const mapFromContact = (contact?: Contact | null) => {
    form.entity_id = contact?.entity_id ?? null;
    form.first_name = contact?.first_name ?? '';
    form.last_name = contact?.last_name ?? '';
    form.function_id = contact?.function_id ?? null;
    form.phone = contact?.phone ?? '';
    form.mobile = contact?.mobile ?? '';
    form.email = contact?.email ?? '';
    form.rgpd_consent = Boolean(contact?.rgpd_consent);
    form.observations = contact?.observations ?? '';
    form.state = contact?.active === false ? 'inactive' : 'active';
};

const validate = () => {
    resetErrors();

    if (!form.entity_id) formErrors.entity_id = 'Selecione a entidade.';
    if (!form.first_name.trim()) formErrors.first_name = 'Primeiro nome e obrigatorio.';
    if (!form.last_name.trim()) formErrors.last_name = 'Apelido e obrigatorio.';

    return Object.keys(formErrors).length === 0;
};

const fetchReferences = async () => {
    loadingReferences.value = true;

    try {
        const [entitiesResponse, functionsResponse] = await Promise.allSettled([
            get<PaginatedResponse<Entity>>('/entities', { per_page: 1000 }),
            get<PaginatedResponse<ContactFunctionOption>>('/contact-functions', { per_page: 1000 }),
        ]);

        if (entitiesResponse.status === 'fulfilled') {
            entities.value = entitiesResponse.value.data ?? [];
        }

        if (functionsResponse.status === 'fulfilled') {
            functions.value = functionsResponse.value.data ?? [];
        }

        if (entitiesResponse.status === 'rejected') {
            throw new Error('entities-load-failed');
        }

        if (functionsResponse.status === 'rejected') {
            toast({
                title: 'Funcoes nao carregadas',
                description: 'O formulario continua disponivel sem lista de funcoes.',
                variant: 'destructive',
            });
        }
    } catch {
        toast({
            title: 'Erro ao carregar referencias',
            description: 'Nao foi possivel carregar a lista de entidades.',
            variant: 'destructive',
        });
    } finally {
        loadingReferences.value = false;
    }
};

const submit = async () => {
    if (!validate()) {
        return;
    }

    contactStore.isSubmitting = true;

    const payload = {
        entity_id: form.entity_id,
        first_name: form.first_name,
        last_name: form.last_name,
        function_id: form.function_id,
        phone: form.phone || null,
        mobile: form.mobile || null,
        email: form.email || null,
        rgpd_consent: form.rgpd_consent,
        observations: form.observations || null,
        active: form.state === 'active',
    };

    try {
        const saved = isEditing.value
            ? await put<Contact>(`/contacts/${props.contact?.id}`, payload)
            : await post<Contact>('/contacts', payload);

        toast({
            title: isEditing.value ? 'Contacto atualizado' : 'Contacto criado',
            description: 'Dados guardados com sucesso.',
        });

        emit('submitted', saved);
    } catch {
        toast({
            title: 'Erro ao guardar contacto',
            description: 'Verifique os dados e tente novamente.',
            variant: 'destructive',
        });
    } finally {
        contactStore.isSubmitting = false;
    }
};

watch(
    () => props.contact,
    (value) => {
        mapFromContact(value);
        resetErrors();
    },
    { immediate: true },
);

onMounted(fetchReferences);
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>{{ isEditing ? 'Editar Contacto' : 'Novo Contacto' }}</CardTitle>
        </CardHeader>

        <CardContent>
            <Form>
                <form class="grid grid-cols-1 gap-4 md:grid-cols-2" @submit.prevent="submit">
                    <div>
                        <Label for="contact-entity">Entidade</Label>
                        <Select id="contact-entity" :disabled="loadingReferences" v-model="form.entity_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Selecione a entidade" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="entity in entities" :key="entity.id" :value="entity.id">
                                    {{ entity.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="formErrors.entity_id" class="text-sm text-red-500">{{ formErrors.entity_id }}</p>
                    </div>

                    <div>
                        <Label for="contact-function">Funcao</Label>
                        <Select id="contact-function" :disabled="loadingReferences" v-model="form.function_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Selecione a funcao" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="null">Sem funcao</SelectItem>
                                <SelectItem v-for="fn in functions" :key="fn.id" :value="fn.id">
                                    {{ fn.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div>
                        <Label for="contact-first-name">Nome</Label>
                        <Input id="contact-first-name" v-model="form.first_name" placeholder="Primeiro nome" />
                        <p v-if="formErrors.first_name" class="text-sm text-red-500">{{ formErrors.first_name }}</p>
                    </div>

                    <div>
                        <Label for="contact-last-name">Apelido</Label>
                        <Input id="contact-last-name" v-model="form.last_name" placeholder="Apelido" />
                        <p v-if="formErrors.last_name" class="text-sm text-red-500">{{ formErrors.last_name }}</p>
                    </div>

                    <div>
                        <Label for="contact-phone">Telefone</Label>
                        <Input id="contact-phone" v-model="form.phone" placeholder="Telefone" />
                    </div>

                    <div>
                        <Label for="contact-mobile">Telemovel</Label>
                        <Input id="contact-mobile" v-model="form.mobile" placeholder="Telemovel" />
                    </div>

                    <div>
                        <Label for="contact-email">Email</Label>
                        <Input id="contact-email" type="email" v-model="form.email" placeholder="email@dominio.pt" />
                    </div>

                    <div>
                        <Label for="contact-state">Estado</Label>
                        <Select id="contact-state" v-model="form.state">
                            <SelectTrigger>
                                <SelectValue placeholder="Estado" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">Ativo</SelectItem>
                                <SelectItem value="inactive">Inativo</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="md:col-span-2 flex items-center gap-2">
                        <Checkbox id="contact-rgpd" v-model:checked="form.rgpd_consent" />
                        <Label for="contact-rgpd">Consentimento RGPD</Label>
                    </div>

                    <div class="md:col-span-2">
                        <Label for="contact-observations">Observacoes</Label>
                        <Textarea id="contact-observations" v-model="form.observations" rows="4" />
                    </div>

                    <div class="md:col-span-2 flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="emit('cancelled')">Cancelar</Button>
                        <Button type="submit" :disabled="contactStore.isSubmitting">
                            {{ contactStore.isSubmitting ? 'A guardar...' : 'Guardar' }}
                        </Button>
                    </div>
                </form>
            </Form>
        </CardContent>
    </Card>
</template>
