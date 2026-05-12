<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useApi } from '../composables/useApi';
import { useToast } from '@/components/ui/toast/use-toast';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form } from '@/components/ui/form';

const { get, put } = useApi();
const { toast } = useToast();
const isLoading = ref(false);
const form = reactive({
  name: '',
  logo: '',
  address: '',
  postal_code: '',
  city: '',
  tax_id: '',
});

const fetchCompany = async () => {
  isLoading.value = true;
  try {
    const company = await get('/company');
    Object.assign(form, company);
  } catch {
    toast({
      title: 'Erro',
      description: 'Não foi possível carregar dados da empresa.',
      variant: 'destructive',
    });
  } finally {
    isLoading.value = false;
  }
};

const submit = async () => {
  isLoading.value = true;
  try {
    await put('/company', form);
    toast({ title: 'Sucesso', description: 'Dados da empresa atualizados com sucesso.' });
  } catch {
    toast({
      title: 'Erro',
      description: 'Não foi possível guardar os dados.',
      variant: 'destructive',
    });
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchCompany);
</script>

<template>
  <Card class="max-w-2xl">
    <CardHeader>
      <CardTitle>Configurações da Empresa</CardTitle>
    </CardHeader>

    <CardContent>
      <Form @submit="submit">
        <div class="space-y-4">
          <div class="grid gap-2">
            <Label>Logotipo</Label>
            <Input type="file" accept="image/*" />
          </div>

          <div class="grid gap-2">
            <Label for="name">Nome</Label>
            <Input id="name" v-model="form.name" placeholder="Nome da empresa" />
          </div>

          <div class="grid gap-2">
            <Label for="tax_id">Número Contribuinte</Label>
            <Input id="tax_id" v-model="form.tax_id" placeholder="PT123456789" />
          </div>

          <div class="grid gap-2">
            <Label for="address">Morada</Label>
            <Input id="address" v-model="form.address" placeholder="Rua..." />
          </div>

          <div class="grid gap-2">
            <Label for="postal_code">Código Postal</Label>
            <Input id="postal_code" v-model="form.postal_code" placeholder="1000-001" />
          </div>

          <div class="grid gap-2">
            <Label for="city">Localidade</Label>
            <Input id="city" v-model="form.city" placeholder="Lisboa" />
          </div>

          <div class="flex justify-end pt-4">
            <Button type="submit" :disabled="isLoading">
              {{ isLoading ? 'Guardando...' : 'Guardar' }}
            </Button>
          </div>
        </div>
      </Form>
    </CardContent>
  </Card>
</template>
