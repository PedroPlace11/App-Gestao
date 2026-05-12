<script setup lang="ts">
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue';
import { useApi } from '../composables/useApi';
import { useToast } from '@/components/ui/toast/use-toast';
import { Button } from '@/components/ui/button';
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
import { useEntityStore } from '../stores/entityStore';
import { useVies } from '../composables/useVies';
import type { CountryOption, Entity, EntityType, PaginatedResponse } from '../types/index';

interface EntityFormValues {
  type: EntityType;
  nif: string;
  name: string;
  address: string;
  postal_code: string;
  city: string;
  country_id: number | null;
  phone: string;
  mobile: string;
  website: string;
  email: string;
  rgpd_consent: boolean;
  observations: string;
  state: 'active' | 'inactive';
}

const props = defineProps<{
  entity?: Entity | null;
}>();

const emit = defineEmits<{
  (e: 'submitted', payload: Entity): void;
  (e: 'cancelled'): void;
}>();

const { get, post, put } = useApi();
const { toast } = useToast();
const entityStore = useEntityStore();
const { loading: viesLoading, validate: viesValidate } = useVies();

const countries = ref<CountryOption[]>([]);
const loadingCountries = ref(false);

const countrySearch = ref('');
const countryOpen = ref(false);

const filteredCountries = computed(() => {
  const q = countrySearch.value.toLowerCase().trim();
  return q
    ? countries.value.filter(c => c.name.toLowerCase().includes(q))
    : countries.value;
});

const selectCountry = (country: CountryOption) => {
  form.country_id = country.id;
  countrySearch.value = country.name;
  countryOpen.value = false;
};

const normalizeCountryText = (value: string) => value
  .trim()
  .toLowerCase()
  .normalize('NFD')
  .replace(/[\u0300-\u036f]/g, '')
  .replace(/\s+/g, ' ');

const findCountryBySearch = (value: string): CountryOption | undefined => {
  const normalized = normalizeCountryText(value);
  if (!normalized) return undefined;

  const exactMatch = countries.value.find((country) => {
    return normalizeCountryText(country.name) === normalized || normalizeCountryText(country.code) === normalized;
  });

  if (exactMatch) {
    return exactMatch;
  }

  const startsWithMatches = countries.value.filter((country) => {
    const name = normalizeCountryText(country.name);
    const code = normalizeCountryText(country.code);
    return name.startsWith(normalized) || code.startsWith(normalized);
  });

  if (startsWithMatches.length === 1) {
    return startsWithMatches[0];
  }

  const includesMatches = countries.value.filter((country) => {
    const name = normalizeCountryText(country.name);
    const code = normalizeCountryText(country.code);
    return name.includes(normalized) || code.includes(normalized);
  });

  if (includesMatches.length === 1) {
    return includesMatches[0];
  }

  return undefined;
};

const resolveCountryFromSearch = () => {
  const matchedCountry = findCountryBySearch(countrySearch.value);

  if (matchedCountry) {
    form.country_id = matchedCountry.id;
    countrySearch.value = matchedCountry.name;

    if (formErrors.country_id) {
      delete formErrors.country_id;
    }

    return;
  }

  if (countrySearch.value.trim() !== '' && filteredCountries.value.length === 1) {
    selectCountry(filteredCountries.value[0]);
    return;
  }

  if (countrySearch.value.trim() === '') {
    form.country_id = null;
  }
};

const handleCountryInput = () => {
  countryOpen.value = true;
  resolveCountryFromSearch();
};

const handleCountryEnter = () => {
  resolveCountryFromSearch();

  if (!form.country_id && filteredCountries.value.length === 1) {
    selectCountry(filteredCountries.value[0]);
  }
};

const handleCountryBlur = () => {
  resolveCountryFromSearch();
  closeCountryDropdown();
};

const closeCountryDropdown = () => {
  setTimeout(() => { countryOpen.value = false; }, 150);
};

// Sync input when countries load (edit mode)
watch(countries, (list) => {
  if (form.country_id) {
    const found = list.find(c => c.id === form.country_id);
    if (found) countrySearch.value = found.name;
    return;
  }

  // If user already typed a country while list was still loading, resolve it now.
  if (countrySearch.value.trim()) {
    resolveCountryFromSearch();
  }
});

// Sync input when country_id changes programmatically
watch(() => form.country_id, (id) => {
  if (!id) { countrySearch.value = ''; return; }
  const found = countries.value.find(c => c.id === id);
  if (found) countrySearch.value = found.name;
});

watch(countrySearch, (value) => {
  const normalized = normalizeCountryText(value);

  if (!normalized) {
    form.country_id = null;
    return;
  }

  const matchedCountry = findCountryBySearch(value);
  if (matchedCountry) {
    form.country_id = matchedCountry.id;
    return;
  }

  if (form.country_id) {
    const selectedCountry = countries.value.find((country) => country.id === form.country_id);
    if (selectedCountry && normalizeCountryText(selectedCountry.name) !== normalized) {
      form.country_id = null;
    }
  }
});

const nifRegex = /^\d{9}$/;

const validateVies = async () => {
  if (!form.nif.trim()) {
    toast({ title: 'NIF em falta', description: 'Insira o NIF antes de validar na VIES.', variant: 'destructive' });
    return;
  }

  const countryCode = countries.value.find((c) => c.id === form.country_id)?.code ?? 'PT';
  const fullNif = `${countryCode}${form.nif.replace(/\s+/g, '')}`;

  const result = await viesValidate(fullNif);

  if (!result?.valid) {
    toast({
      title: 'Não encontrado na VIES',
      description: result?.message ?? result?.error ?? 'NIF não registado no VIES.',
      variant: 'destructive',
    });
    return;
  }

  // VIES para alguns países (ex: PT) devolve "---" quando não partilha dados
  const isUsable = (v: string | null | undefined) => v && v.trim() !== '' && v.trim() !== '---';

  let filled = 0;
  if (isUsable(result.name))    { form.name    = result.name!;    filled++; }
  if (isUsable(result.address)) { form.address = result.address!; filled++; }

  if (filled > 0) {
    toast({
      title: 'VIES: NIF válido',
      description: `Dados preenchidos automaticamente: ${result.name}`,
    });
  } else {
    toast({
      title: 'VIES: NIF válido',
      description: 'NIF validado com sucesso. Este país não partilha nome/morada via VIES — preencha manualmente.',
    });
  }
};

const form = reactive<EntityFormValues>({
  type: 'client',
  nif: '',
  name: '',
  address: '',
  postal_code: '',
  city: '',
  country_id: null,
  phone: '',
  mobile: '',
  website: '',
  email: '',
  rgpd_consent: false,
  observations: '',
  state: 'active',
});

const formErrors = reactive<Record<string, string>>({});

const isEditing = computed(() => Boolean(props.entity?.id));

const resetErrors = () => {
  Object.keys(formErrors).forEach((key) => {
    delete formErrors[key];
  });
};

const mapFromEntity = (entity?: Entity | null) => {
  form.type = entity?.type ?? 'client';
  form.nif = entity?.nif ?? '';
  form.name = entity?.name ?? '';
  form.address = entity?.address ?? '';
  form.postal_code = entity?.postal_code ?? '';
  form.city = entity?.city ?? '';
  form.country_id = entity?.country_id ?? null;
  form.phone = entity?.phone ?? '';
  form.mobile = entity?.mobile ?? '';
  form.website = entity?.website ?? '';
  form.email = entity?.email ?? '';
  form.rgpd_consent = Boolean(entity?.rgpd_consent);
  form.observations = entity?.observations ?? '';
  form.state = entity?.active === false ? 'inactive' : 'active';
};

const validate = async (): Promise<boolean> => {
  resetErrors();

  console.log('[EntityForm] Starting validation with form values:', {
    type: form.type,
    nif: form.nif,
    name: form.name,
    address: form.address,
    postal_code: form.postal_code,
    city: form.city,
    country_id: form.country_id,
    countrySearch: countrySearch.value,
  });

  // Final attempt to map typed country text into country_id before validating.
  await ensureCountryIdFromSearch();

  const normalizedNif = form.nif.replace(/\D/g, '');
  form.nif = normalizedNif;

  if (!form.type) formErrors.type = 'Selecione o tipo de entidade.';
  if (!nifRegex.test(normalizedNif)) formErrors.nif = 'NIF invalido. Use 9 digitos.';
  if (!form.name.trim()) formErrors.name = 'Nome e obrigatorio.';
  if (!form.address.trim()) formErrors.address = 'Morada e obrigatoria.';
  if (!form.postal_code.trim()) formErrors.postal_code = 'Codigo postal e obrigatorio.';
  if (!form.city.trim()) formErrors.city = 'Cidade e obrigatoria.';
  if (!form.country_id) formErrors.country_id = 'Selecione um pais da lista.';

  const websiteValue = form.website.trim();
  if (websiteValue === 'https://' || websiteValue === 'http://') {
    form.website = '';
  } else if (websiteValue) {
    try {
      const parsedUrl = new URL(websiteValue);
      if (!parsedUrl.hostname) {
        formErrors.website = 'Website invalido. Use formato completo, ex: https://exemplo.com';
      }
    } catch {
      formErrors.website = 'Website invalido. Use formato completo, ex: https://exemplo.com';
    }
  }

  const emailValue = form.email.trim();
  if (emailValue && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue)) {
    formErrors.email = 'Email invalido.';
  }

  if (Object.keys(formErrors).length > 0) {
    console.log('[EntityForm] Validation failed with errors:', formErrors);
    const firstErrorMessage = Object.values(formErrors)[0];
    toast({
      title: 'Verifique os campos obrigatorios',
      description: firstErrorMessage,
      variant: 'destructive',
    });

    await nextTick();
    const firstErrorElement = document.querySelector('.field-error');
    firstErrorElement?.scrollIntoView({ behavior: 'smooth', block: 'center' });
  } else {
    console.log('[EntityForm] ✅ Validation passed');
  }

  return Object.keys(formErrors).length === 0;
};

const fetchCountries = async () => {
  loadingCountries.value = true;
  try {
    const response = await get<PaginatedResponse<CountryOption>>('/countries');
    countries.value = response.data ?? [];

    if (countries.value.length === 0) {
      console.warn('No countries returned from API');
      toast({
        title: 'Aviso',
        description: 'A lista de países está vazia. Contate o administrador.',
        variant: 'destructive',
      });
    }
  } catch (error) {
    console.error('Error fetching countries:', error);
    toast({
      title: 'Erro ao carregar paises',
      description: 'Nao foi possivel carregar a lista de paises. Tente novamente mais tarde.',
      variant: 'destructive',
    });
  } finally {
    loadingCountries.value = false;
  }
};

const ensureCountryIdFromSearch = async () => {
  if (form.country_id || !countrySearch.value.trim()) {
    return;
  }

  resolveCountryFromSearch();

  if (form.country_id) {
    return;
  }

  if (!countries.value.length && !loadingCountries.value) {
    await fetchCountries();
    resolveCountryFromSearch();
  }
};

const submit = async () => {
  console.log('[SUBMIT CALLED] Time:', new Date().toISOString());

  if (entityStore.isSubmitting) {
    console.log('[SUBMIT] Already submitting, ignoring duplicate');
    return;
  }

  console.log('[SUBMIT] Starting validation');
  if (!(await validate())) {
    console.log('[SUBMIT] Validation failed, stopping');
    return;
  }

  console.log('[SUBMIT] Validation passed, preparing payload');
  entityStore.isSubmitting = true;

  const payload = {
    type: form.type,
    nif: form.nif,
    name: form.name,
    address: form.address,
    postal_code: form.postal_code,
    city: form.city,
    country_id: form.country_id,
    country_name: countrySearch.value.trim() || null,
    phone: form.phone || null,
    mobile: form.mobile || null,
    website: form.website.trim() || null,
    email: form.email.trim() || null,
    rgpd_consent: form.rgpd_consent,
    observations: form.observations || null,
    active: form.state === 'active',
  };

  console.log('[SUBMIT] Payload ready:', JSON.stringify(payload, null, 2));
  console.log('[SUBMIT] Is editing?', isEditing.value);

  try {
    console.log('[SUBMIT] About to call API...');
    const savedEntity = isEditing.value
      ? await put<Entity>(`/entities/${props.entity?.id}`, payload)
      : await post<Entity>('/entities', payload);

    console.log('[SUBMIT] Entity saved successfully:', savedEntity);

    toast({
      title: isEditing.value ? 'Entidade atualizada' : 'Entidade criada',
      description: 'Os dados da entidade foram guardados com sucesso.',
    });

    console.log('[SUBMIT] Emitting submitted event');
    emit('submitted', savedEntity);
    // Ensure modal closes even if parent update handler fails unexpectedly.
    entityStore.closeModal();
    console.log('[SUBMIT] Success - submitted event emitted and modal closed');
  } catch (error) {
    console.error('[SUBMIT] Error caught:', error);
    console.error('Error type:', error instanceof Error ? error.constructor.name : typeof error);
    console.error('Error message:', error instanceof Error ? error.message : String(error));
    console.error('Error status:', (error as any)?.status);
    console.error('Error payload:', (error as any)?.payload);
    console.error('Full error object:', error);

    const message = error instanceof Error ? error.message : 'Verifique os dados e tente novamente.';
    const errorPayload = (error as any)?.payload;

    if (errorPayload?.errors && typeof errorPayload.errors === 'object') {
      console.log('Form validation errors from server:', errorPayload.errors);
      Object.entries(errorPayload.errors).forEach(([field, messages]) => {
        if (Array.isArray(messages) && messages.length > 0) {
          formErrors[field] = String(messages[0]);
          console.log(`Field error [${field}]:`, messages[0]);
        }
      });
    }

    toast({
      title: 'Erro ao guardar entidade',
      description: message,
      variant: 'destructive',
    });

    await nextTick();
    const firstErrorElement = document.querySelector('.field-error');
    firstErrorElement?.scrollIntoView({ behavior: 'smooth', block: 'center' });
  } finally {
    console.log('[SUBMIT] Finally block - resetting isSubmitting');
    entityStore.isSubmitting = false;
    console.log('[SUBMIT] Complete');
  }
};

watch(
  () => props.entity,
  (entity) => {
    mapFromEntity(entity);
    resetErrors();
  },
  { immediate: true },
);

onMounted(fetchCountries);
</script>

<template>
  <Form>
    <form class="space-y-6" novalidate @submit.prevent="submit">

      <!-- Secção: Identificação -->
      <div class="form-section">
        <h3 class="form-section-title">Identificação</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

          <div class="field-wrap">
            <Label for="entity-type">Tipo <span class="field-required">*</span></Label>
            <Select id="entity-type" v-model="form.type">
              <SelectTrigger>
                <SelectValue placeholder="Selecione o tipo" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="client">Cliente</SelectItem>
                <SelectItem value="supplier">Fornecedor</SelectItem>
                <SelectItem value="both">Ambos</SelectItem>
              </SelectContent>
            </Select>
            <p v-if="formErrors.type" class="field-error">{{ formErrors.type }}</p>
          </div>

          <div class="field-wrap">
            <Label for="entity-nif">NIF <span class="field-required">*</span></Label>
            <div class="flex gap-2">
              <Input id="entity-nif" v-model="form.nif" maxlength="20" placeholder="123456789" class="flex-1" />
              <Button type="button" variant="outline" size="sm" :disabled="viesLoading" @click="validateVies">
                {{ viesLoading ? 'A validar...' : 'Validar VIES' }}
              </Button>
            </div>
            <p v-if="formErrors.nif" class="field-error">{{ formErrors.nif }}</p>
          </div>

          <div class="field-wrap md:col-span-2">
            <Label for="entity-name">Nome <span class="field-required">*</span></Label>
            <Input id="entity-name" v-model="form.name" placeholder="Nome da entidade" />
            <p v-if="formErrors.name" class="field-error">{{ formErrors.name }}</p>
          </div>

          <div class="field-wrap">
            <Label for="entity-state">Estado</Label>
            <Select id="entity-state" v-model="form.state">
              <SelectTrigger>
                <SelectValue placeholder="Estado" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="active">Ativo</SelectItem>
                <SelectItem value="inactive">Inativo</SelectItem>
              </SelectContent>
            </Select>
          </div>

        </div>
      </div>

      <!-- Divider -->
      <div class="form-divider" />

      <!-- Secção: Localização -->
      <div class="form-section">
        <h3 class="form-section-title">Localização</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

          <div class="field-wrap md:col-span-2">
            <Label for="entity-address">Morada <span class="field-required">*</span></Label>
            <Input id="entity-address" v-model="form.address" placeholder="Rua, número, andar..." />
            <p v-if="formErrors.address" class="field-error">{{ formErrors.address }}</p>
          </div>

          <div class="field-wrap">
            <Label for="entity-postal">Código Postal <span class="field-required">*</span></Label>
            <Input id="entity-postal" v-model="form.postal_code" placeholder="0000-000" />
            <p v-if="formErrors.postal_code" class="field-error">{{ formErrors.postal_code }}</p>
          </div>

          <div class="field-wrap">
            <Label for="entity-city">Cidade <span class="field-required">*</span></Label>
            <Input id="entity-city" v-model="form.city" placeholder="Cidade" />
            <p v-if="formErrors.city" class="field-error">{{ formErrors.city }}</p>
          </div>

          <div class="field-wrap md:col-span-2">
            <Label for="entity-country">País <span class="field-required">*</span></Label>
            <div class="country-combobox">
              <input
                id="entity-country"
                class="country-input"
                type="text"
                v-model="countrySearch"
                :disabled="loadingCountries"
                :placeholder="loadingCountries ? 'A carregar países...' : 'Pesquisar país...'"
                autocomplete="off"
                @focus="countryOpen = true"
                @input="handleCountryInput"
                @keydown.enter.prevent="handleCountryEnter"
                @blur="handleCountryBlur"
              />
              <ul v-if="countryOpen && filteredCountries.length" class="country-dropdown">
                <li
                  v-for="country in filteredCountries"
                  :key="country.id"
                  class="country-option"
                  :class="{ 'country-option--selected': form.country_id === country.id }"
                  @mousedown.prevent="selectCountry(country)"
                >
                  {{ country.name }}
                </li>
              </ul>
            </div>
            <p v-if="formErrors.country_id" class="field-error">{{ formErrors.country_id }}</p>
          </div>

        </div>
      </div>

      <!-- Divider -->
      <div class="form-divider" />

      <!-- Secção: Contacto -->
      <div class="form-section">
        <h3 class="form-section-title">Contacto</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

          <div class="field-wrap">
            <Label for="entity-phone">Telefone</Label>
            <Input id="entity-phone" v-model="form.phone" placeholder="+351 000 000 000" />
          </div>

          <div class="field-wrap">
            <Label for="entity-mobile">Telemóvel</Label>
            <Input id="entity-mobile" v-model="form.mobile" placeholder="+351 900 000 000" />
          </div>

          <div class="field-wrap">
            <Label for="entity-website">Website</Label>
            <Input id="entity-website" v-model="form.website" placeholder="https://" />
            <p v-if="formErrors.website" class="field-error">{{ formErrors.website }}</p>
          </div>

          <div class="field-wrap">
            <Label for="entity-email">Email</Label>
            <Input id="entity-email" v-model="form.email" type="email" placeholder="email@dominio.pt" />
            <p v-if="formErrors.email" class="field-error">{{ formErrors.email }}</p>
          </div>

        </div>
      </div>

      <!-- Divider -->
      <div class="form-divider" />

      <!-- Secção: Extra -->
      <div class="form-section">
        <h3 class="form-section-title">Informação Adicional</h3>
        <div class="space-y-4">

          <div class="flex items-center gap-3 rounded-lg border p-3" style="border-color: var(--color-border);">
            <Checkbox id="entity-rgpd" v-model:checked="form.rgpd_consent" />
            <div>
              <Label for="entity-rgpd" class="cursor-pointer font-medium">Consentimento RGPD</Label>
              <p class="text-xs" style="color: var(--color-text-sub, var(--color-muted-foreground));">O titular dos dados consentiu o tratamento de dados pessoais.</p>
            </div>
          </div>

          <div class="field-wrap">
            <Label for="entity-observations">Observações</Label>
            <Textarea id="entity-observations" v-model="form.observations" rows="3" placeholder="Notas adicionais sobre esta entidade..." />
          </div>

        </div>
      </div>

      <!-- Acções -->
      <div class="flex justify-end gap-2 border-t pt-4" style="border-color: var(--color-border);">
        <Button type="button" variant="outline" @click="emit('cancelled')">Cancelar</Button>
        <Button type="submit" :disabled="entityStore.isSubmitting">
          {{ entityStore.isSubmitting ? 'A guardar...' : isEditing ? 'Guardar alterações' : 'Criar entidade' }}
        </Button>
      </div>

    </form>
  </Form>
</template>

<style scoped>
.form-section { }
.form-section-title {
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--color-text-sub, var(--color-muted-foreground));
  margin-bottom: 0.75rem;
}
.form-divider {
  height: 1px;
  background-color: var(--color-border);
}
.field-wrap {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}
.field-required {
  color: var(--color-destructive);
}
.field-error {
  font-size: 0.75rem;
  color: var(--color-destructive);
}

/* Country Combobox */
.country-combobox {
  position: relative;
}

.country-input {
  width: 100%;
  border-radius: calc(var(--radius) - 2px);
  border: 1px solid var(--color-input);
  background: var(--color-background);
  color: var(--color-foreground);
  font-size: 0.875rem;
  padding: 0.5rem 0.75rem;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  line-height: 1.5;
}

.country-input:focus {
  border-color: var(--color-ring);
  box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-ring) 30%, transparent);
}

.country-input:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.country-dropdown {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  max-height: 220px;
  overflow-y: auto;
  background: var(--color-popover);
  border: 1px solid var(--color-border);
  border-radius: calc(var(--radius) - 2px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.25);
  z-index: 9999;
  list-style: none;
  margin: 0;
  padding: 4px;
}

.country-option {
  padding: 0.45rem 0.75rem;
  font-size: 0.875rem;
  color: var(--color-popover-foreground);
  border-radius: calc(var(--radius) - 4px);
  cursor: pointer;
  transition: background 0.15s;
}

.country-option:hover,
.country-option--selected {
  background: var(--color-accent);
  color: var(--color-accent-foreground);
}
</style>
