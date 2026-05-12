<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useApi } from '../composables/useApi';
import { useToast } from '@/components/ui/toast/use-toast';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form } from '@/components/ui/form';

interface CountryFormData {
  name: string;
  code: string;
}

const props = defineProps<{
  country?: Country | null;
}>();

const emit = defineEmits<{
  (e: 'submitted', payload: Country): void;
  (e: 'cancelled'): void;
}>();

const { post, put } = useApi();
const { toast } = useToast();
const loading = ref(false);

const form = reactive<CountryFormData>({
  name: props.country?.name ?? '',
  code: props.country?.code ?? '',
});

const submit = async () => {
  loading.value = true;
  try {
    const payload = { ...form };
    
    const result = props.country
      ? await put<Country>(`/v1/countries/${props.country.id}`, payload)
      : await post<Country>('/v1/countries', payload);

    toast({
      title: 'Sucesso',
      description: `País ${props.country ? 'atualizado' : 'criado'} com sucesso.`,
    });
    
    emit('submitted', result);
  } catch (err: any) {
    toast({
      title: 'Erro',
      description: err?.response?.data?.message ?? 'Erro ao guardar país.',
      variant: 'destructive',
    });
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <Form @submit="submit">
    <div class="space-y-4">
      <div class="grid gap-2">
        <Label for="name">Nome</Label>
        <Input
          id="name"
          v-model="form.name"
          placeholder="Ex: Portugal"
          required
        />
      </div>

      <div class="grid gap-2">
        <Label for="code">Código</Label>
        <Input
          id="code"
          v-model="form.code"
          placeholder="Ex: PT"
          maxlength="3"
          required
        />
      </div>

      <div class="flex justify-end gap-2 pt-4">
        <Button type="button" variant="outline" @click="emit('cancelled')">Cancelar</Button>
        <Button type="submit" :disabled="loading">{{ loading ? 'Guardando...' : 'Guardar' }}</Button>
      </div>
    </div>
  </Form>
</template>
