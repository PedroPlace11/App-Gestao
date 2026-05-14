<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useApi } from '../composables/useApi';
import { useToast } from '@/components/ui/toast/use-toast';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form } from '@/components/ui/form';

interface SimpleConfigFormData {
  name: string;
  code: string;
  rate: number | '';
}

const props = defineProps<{
  item?: any | null;
  endpoint: string;
  showCodeField?: boolean;
  showRateField?: boolean;
}>();

const emit = defineEmits<{
  (e: 'submitted', payload: any): void;
  (e: 'cancelled'): void;
}>();

const { post, put } = useApi();
const { toast } = useToast();
const loading = ref(false);

const form = reactive<SimpleConfigFormData>({
  name: props.item?.name ?? '',
  code: props.showCodeField ? String(props.item?.code ?? '') : '',
  rate: props.showRateField ? Number(props.item?.rate ?? 0) : '',
});

const submit = async () => {
  loading.value = true;
  try {
    const payload = {
      name: form.name,
      ...(props.showCodeField ? { code: form.code.trim().toUpperCase() } : {}),
      ...(props.showRateField ? { rate: Number(form.rate) } : {}),
    };

    const result = props.item
      ? await put<any>(`/v1${props.endpoint}/${props.item.id}`, payload)
      : await post<any>(`/v1${props.endpoint}`, payload);

    toast({
      title: 'Sucesso',
      description: `Registo ${props.item ? 'atualizado' : 'criado'} com sucesso.`,
    });

    emit('submitted', result);
  } catch (err: any) {
    toast({
      title: 'Erro',
      description: err?.response?.data?.message ?? 'Erro ao guardar.',
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
          placeholder="Digite o nome"
          required
        />
      </div>

      <div v-if="props.showCodeField" class="grid gap-2">
        <Label for="code">Código do país</Label>
        <Input
          id="code"
          v-model="form.code"
          placeholder="Digite o código"
          maxlength="2"
          class="uppercase"
          required
        />
      </div>

      <div v-if="props.showRateField" class="grid gap-2">
        <Label for="rate">Taxa (%)</Label>
        <Input
          id="rate"
          v-model="form.rate"
          type="number"
          min="0"
          max="100"
          step="0.01"
          placeholder="Digite a taxa"
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
