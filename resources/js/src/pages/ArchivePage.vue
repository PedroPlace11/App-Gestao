<script setup lang="ts">
import { computed, ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useToast } from '@/components/ui/toast/use-toast';

interface ArchiveDocument {
  id: number;
  name: string;
  type: 'Fatura' | 'Contrato' | 'Proposta' | 'Comprovativo';
  entity: string;
  date: string;
  sizeKb: number;
  status: 'Ativo' | 'Arquivado';
}

const { toast } = useToast();
const search = ref('');

const documents = ref<ArchiveDocument[]>([
  { id: 1, name: 'FF-2026-001.pdf', type: 'Fatura', entity: 'Fornecedor Exemplo, Lda.', date: '2026-05-05', sizeKb: 312, status: 'Ativo' },
  { id: 2, name: 'Contrato-Manutencao-Cliente-Exemplo.pdf', type: 'Contrato', entity: 'Cliente Exemplo, Lda.', date: '2026-04-28', sizeKb: 844, status: 'Ativo' },
  { id: 3, name: 'PROP-2026-002.pdf', type: 'Proposta', entity: 'Inovcorp Group', date: '2026-05-09', sizeKb: 276, status: 'Ativo' },
  { id: 4, name: 'Comprovativo-Pagamento-FF-2026-002.pdf', type: 'Comprovativo', entity: 'Suprimentos Atlântico, S.A.', date: '2026-05-11', sizeKb: 198, status: 'Arquivado' },
]);

const filteredDocuments = computed(() => {
  const q = search.value.trim().toLowerCase();
  if (!q) return documents.value;

  return documents.value.filter((doc) =>
    doc.name.toLowerCase().includes(q)
    || doc.type.toLowerCase().includes(q)
    || doc.entity.toLowerCase().includes(q),
  );
});

const viewDocument = (doc: ArchiveDocument) => {
  toast({
    title: 'Visualizar documento',
    description: `Abertura simulada: ${doc.name}`,
  });
};

const downloadDocument = (doc: ArchiveDocument) => {
  const params = new URLSearchParams({
    name: doc.name,
    type: doc.type,
    entity: doc.entity,
    date: doc.date,
    status: doc.status,
  });

  window.open(`/api/v1/archive-documents-pdf?${params.toString()}`, '_blank');
};
</script>

<template>
  <Card>
    <CardHeader class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <CardTitle>Arquivo Digital</CardTitle>
      <Input v-model="search" class="md:w-80" placeholder="Buscar por nome, tipo ou entidade" />
    </CardHeader>

    <CardContent>
      <div v-if="filteredDocuments.length === 0" class="py-10 text-center text-sm text-muted-foreground">
        Nenhum documento encontrado.
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="doc in filteredDocuments"
          :key="doc.id"
          class="rounded-lg border p-4"
          style="border-color: var(--color-border);"
        >
          <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <div>
              <p class="font-medium">{{ doc.name }}</p>
              <p class="text-sm text-muted-foreground">{{ doc.type }} · {{ doc.entity }}</p>
              <p class="text-xs text-muted-foreground">{{ doc.date }} · {{ doc.sizeKb }} KB</p>
            </div>

            <div class="flex items-center gap-2">
              <span
                class="rounded px-2 py-1 text-xs font-medium"
                :class="doc.status === 'Ativo' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-700'"
              >
                {{ doc.status }}
              </span>
              <Button size="sm" variant="outline" @click="viewDocument(doc)">Ver</Button>
              <Button size="sm" @click="downloadDocument(doc)">Download</Button>
            </div>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
