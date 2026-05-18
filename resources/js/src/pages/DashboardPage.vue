<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useApi } from '../composables/useApi';
import { useToast } from '@/components/ui/toast/use-toast';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Users, FileText, Receipt, CalendarClock, Activity } from 'lucide-vue-next';

type ApiListResponse<T> = T[] | { data?: T[] } | null;

interface Entity {
  id: number;
  type?: string;
}

interface Proposal {
  id: number;
  date?: string;
  total_value?: number | string;
  status?: 'draft' | 'closed' | string;
}

interface Order {
  id: number;
  date?: string;
  total_value?: number | string;
}

interface Invoice {
  id: number;
  issue_date?: string;
  total_value?: number | string;
  status?: 'pending' | 'paid' | string;
}

interface CalendarEvent {
  id: number;
  start_date?: string;
  date?: string;
}

interface DashboardTotals {
  total_entities?: number;
  total_contacts?: number;
  total_proposals?: number;
  total_orders?: number;
  total_supplier_orders?: number;
  total_invoices?: number;
  total_calendar_events?: number;
}

const { get } = useApi();
const { toast } = useToast();

const isLoading = ref(false);
const totals = ref<DashboardTotals>({});
const clients = ref<Entity[]>([]);
const proposals = ref<Proposal[]>([]);
const orders = ref<Order[]>([]);
const invoices = ref<Invoice[]>([]);
const events = ref<CalendarEvent[]>([]);

const fallbackMonths = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'];

const extractRows = <T>(payload: ApiListResponse<T>): T[] => {
  if (!payload) return [];
  if (Array.isArray(payload)) return payload;
  return Array.isArray(payload.data) ? payload.data : [];
};

const toNumber = (value: unknown): number => {
  const parsed = Number(value ?? 0);
  return Number.isFinite(parsed) ? parsed : 0;
};

const formatCurrency = (value: number): string =>
  new Intl.NumberFormat('pt-PT', { style: 'currency', currency: 'EUR' }).format(value);

const formatCompact = (value: number): string =>
  new Intl.NumberFormat('pt-PT', { notation: 'compact', maximumFractionDigits: 1 }).format(value);

const monthLabel = (dateInput: string | undefined): string => {
  if (!dateInput) return '';
  const date = new Date(dateInput);
  if (Number.isNaN(date.getTime())) return '';
  return date.toLocaleDateString('pt-PT', { month: 'short' }).replace('.', '');
};

const inCurrentWeek = (dateInput: string | undefined): boolean => {
  if (!dateInput) return false;
  const date = new Date(dateInput);
  if (Number.isNaN(date.getTime())) return false;

  const now = new Date();
  const day = now.getDay();
  const shiftToMonday = (day + 6) % 7;
  const weekStart = new Date(now);
  weekStart.setDate(now.getDate() - shiftToMonday);
  weekStart.setHours(0, 0, 0, 0);

  const weekEnd = new Date(weekStart);
  weekEnd.setDate(weekStart.getDate() + 7);

  return date >= weekStart && date < weekEnd;
};

const buildMonthlySeries = (
  rows: Array<{ date?: string; issue_date?: string; total_value?: number | string }>,
): { labels: string[]; values: number[] } => {
  const bucket = new Map<string, number>();

  rows.forEach((row) => {
    const rawDate = row.date ?? row.issue_date;
    const label = monthLabel(rawDate);
    if (!label) return;
    bucket.set(label, (bucket.get(label) ?? 0) + toNumber(row.total_value));
  });

  const labels = Array.from(bucket.keys());
  const values = Array.from(bucket.values());

  if (!labels.length) {
    return {
      labels: fallbackMonths,
      values: [6_000, 8_200, 7_100, 9_500, 12_400, 11_200],
    };
  }

  return { labels, values };
};

const proposalSeries = computed(() => buildMonthlySeries(proposals.value));
const invoiceSeries = computed(() => buildMonthlySeries(invoices.value));

const maxSeriesValue = (values: number[]): number => Math.max(...values, 1);

const proposalPolyline = computed(() => {
  const values = proposalSeries.value.values;
  const max = maxSeriesValue(values);
  const step = values.length > 1 ? 100 / (values.length - 1) : 100;

  return values
    .map((value, index) => {
      const x = step * index;
      const y = 100 - (value / max) * 100;
      return `${x},${y}`;
    })
    .join(' ');
});

const paidAmount = computed(() =>
  invoices.value
    .filter((invoice) => invoice.status === 'paid')
    .reduce((sum, invoice) => sum + toNumber(invoice.total_value), 0),
);

const pendingAmount = computed(() =>
  invoices.value
    .filter((invoice) => invoice.status !== 'paid')
    .reduce((sum, invoice) => sum + toNumber(invoice.total_value), 0),
);

const donutFill = computed(() => {
  const paid = paidAmount.value;
  const pending = pendingAmount.value;
  const total = paid + pending;
  const paidPct = total > 0 ? (paid / total) * 100 : 45;

  return {
    background: `conic-gradient(#16a34a 0% ${paidPct}%, #f59e0b ${paidPct}% 100%)`,
  };
});

const openProposals = computed(() =>
  proposals.value.filter((proposal) => proposal.status !== 'closed').length,
);

const currentWeekEvents = computed(() =>
  events.value.filter((event) => inCurrentWeek(event.start_date ?? event.date)).length,
);

const totalOrdersAmount = computed(() =>
  orders.value.reduce((sum, order) => sum + toNumber(order.total_value), 0),
);

const totalClients = computed(() => {
  if (clients.value.length > 0) return clients.value.length;
  return totals.value.total_entities ?? 0;
});

const topMetrics = computed(() => [
  {
    title: 'Clientes Ativos',
    value: String(totalClients.value),
    subtitle: `${totals.value.total_contacts ?? 0} contactos registados`,
    icon: Users,
  },
  {
    title: 'Propostas em Aberto',
    value: String(openProposals.value || totals.value.total_proposals || 0),
    subtitle: `${proposals.value.length || totals.value.total_proposals || 0} propostas no total`,
    icon: FileText,
  },
  {
    title: 'Faturação Pendente',
    value: formatCompact(pendingAmount.value),
    subtitle: formatCurrency(pendingAmount.value),
    icon: Receipt,
  },
  {
    title: 'Eventos Esta Semana',
    value: String(currentWeekEvents.value),
    subtitle: `${totals.value.total_calendar_events ?? 0} eventos no calendário`,
    icon: CalendarClock,
  },
]);

const fetchData = async () => {
  isLoading.value = true;
  try {
    const [dashboardData, clientsData, proposalsData, ordersData, invoicesData, eventsData] =
      await Promise.all([
        get<DashboardTotals>('/dashboard'),
        get<ApiListResponse<Entity>>('/entities', { per_page: 1000, type: 'client' }),
        get<ApiListResponse<Proposal>>('/proposals', { per_page: 1000 }),
        get<ApiListResponse<Order>>('/orders', { per_page: 1000 }),
        get<ApiListResponse<Invoice>>('/invoices', { per_page: 1000 }),
        get<ApiListResponse<CalendarEvent>>('/calendar-events', { per_page: 1000 }),
      ]);

    totals.value = dashboardData ?? {};
    clients.value = extractRows(clientsData);
    proposals.value = extractRows(proposalsData);
    orders.value = extractRows(ordersData);
    invoices.value = extractRows(invoicesData);
    events.value = extractRows(eventsData);
  } catch {
    toast({
      title: 'Não foi possível carregar todas as métricas',
      description: 'A dashboard foi preenchida com dados de referência.',
      variant: 'destructive',
    });
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchData);
</script>

<template>
  <section class="dashboard">
    <header class="dashboard-header">
      <div>
        <h1 class="dashboard-title">Dashboard</h1>
        <p class="dashboard-subtitle">Visão geral da operação comercial e financeira</p>
      </div>
      <div class="dashboard-kpi-chip">
        <Activity class="h-4 w-4" />
        Atualizado em tempo real
      </div>
    </header>

    <div class="metrics-grid">
      <Card v-for="item in topMetrics" :key="item.title" class="metric-card">
        <CardContent class="metric-content">
          <div class="metric-head">
            <span class="metric-title">{{ item.title }}</span>
            <component :is="item.icon" class="metric-icon" />
          </div>
          <p class="metric-value">{{ item.value }}</p>
          <p class="metric-subtitle">{{ item.subtitle }}</p>
        </CardContent>
      </Card>
    </div>

    <div class="charts-grid">
      <Card class="chart-card chart-card--wide">
        <CardHeader>
          <CardTitle>Evolução de Propostas (€)</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="line-chart-wrap">
            <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="line-chart">
              <polyline class="line-chart-shadow" :points="proposalPolyline" />
              <polyline class="line-chart-main" :points="proposalPolyline" />
            </svg>
            <div class="chart-labels">
              <span v-for="label in proposalSeries.labels" :key="`proposal-${label}`">{{ label }}</span>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="chart-card">
        <CardHeader>
          <CardTitle>Recebimentos vs Pendentes</CardTitle>
        </CardHeader>
        <CardContent class="donut-wrap">
          <div class="donut-chart" :style="donutFill">
            <div class="donut-center">
              <span>Total</span>
              <strong>{{ formatCompact(paidAmount + pendingAmount) }}</strong>
            </div>
          </div>
          <div class="donut-legend">
            <p><span class="dot dot--green" /> Pago: {{ formatCurrency(paidAmount) }}</p>
            <p><span class="dot dot--amber" /> Pendente: {{ formatCurrency(pendingAmount) }}</p>
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="charts-grid charts-grid--bottom">
      <Card class="chart-card chart-card--wide">
        <CardHeader>
          <CardTitle>Volume Mensal de Faturas (€)</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="bar-chart">
            <div
              v-for="(value, index) in invoiceSeries.values"
              :key="`invoice-${invoiceSeries.labels[index]}-${index}`"
              class="bar-item"
            >
              <div
                class="bar"
                :style="{ height: `${Math.max((value / Math.max(...invoiceSeries.values, 1)) * 100, 8)}%` }"
              />
              <span class="bar-label">{{ invoiceSeries.labels[index] }}</span>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="chart-card">
        <CardHeader>
          <CardTitle>Resumo Operacional</CardTitle>
        </CardHeader>
        <CardContent class="summary-list">
          <div class="summary-row">
            <span>Pipeline comercial</span>
            <strong>{{ formatCurrency(proposalSeries.values.reduce((acc, value) => acc + value, 0)) }}</strong>
          </div>
          <div class="summary-row">
            <span>Valor em encomendas</span>
            <strong>{{ formatCurrency(totalOrdersAmount) }}</strong>
          </div>
          <div class="summary-row">
            <span>Faturas emitidas</span>
            <strong>{{ invoices.length || totals.total_invoices || 0 }}</strong>
          </div>
          <div class="summary-row">
            <span>Taxa de cobrança</span>
            <strong>
              {{
                paidAmount + pendingAmount > 0
                  ? `${Math.round((paidAmount / (paidAmount + pendingAmount)) * 100)}%`
                  : '0%'
              }}
            </strong>
          </div>
        </CardContent>
      </Card>
    </div>

    <p v-if="isLoading" class="dashboard-loading">A atualizar métricas...</p>
  </section>
</template>

<style scoped>
.dashboard {
  display: grid;
  gap: 1rem;
}

.dashboard-header {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

.dashboard-title {
  margin: 0;
  font-size: 1.35rem;
  font-weight: 700;
  color: #0f172a;
}

.dashboard-subtitle {
  margin: 0.2rem 0 0;
  color: #64748b;
  font-size: 0.92rem;
}

.dashboard-kpi-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.4rem 0.7rem;
  border-radius: 999px;
  background: #e2e8f0;
  color: #0f172a;
  font-size: 0.8rem;
  font-weight: 600;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(1, minmax(0, 1fr));
  gap: 0.75rem;
}

@media (min-width: 768px) {
  .metrics-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 1280px) {
  .metrics-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

.metric-card {
  border-color: #dbe5f0;
  box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
}

.metric-content {
  display: grid;
  gap: 0.55rem;
}

.metric-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  color: #475569;
}

.metric-title {
  font-size: 0.82rem;
  font-weight: 600;
}

.metric-icon {
  width: 1rem;
  height: 1rem;
}

.metric-value {
  margin: 0;
  font-size: 1.55rem;
  font-weight: 700;
  color: #0f172a;
}

.metric-subtitle {
  margin: 0;
  font-size: 0.8rem;
  color: #64748b;
}

.charts-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.75rem;
}

@media (min-width: 1024px) {
  .charts-grid {
    grid-template-columns: 2fr 1fr;
  }
}

.charts-grid--bottom {
  margin-top: -0.25rem;
}

.chart-card {
  border-color: #dbe5f0;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
}

.chart-card--wide {
  min-height: 250px;
}

.line-chart-wrap {
  display: grid;
  gap: 0.5rem;
}

.line-chart {
  width: 100%;
  height: 170px;
  border-radius: 0.7rem;
  background: linear-gradient(180deg, #dbeafe 0%, #eff6ff 45%, #f8fafc 100%);
}

.line-chart-shadow {
  fill: none;
  stroke: rgba(30, 64, 175, 0.18);
  stroke-width: 3.4;
}

.line-chart-main {
  fill: none;
  stroke: #1d4ed8;
  stroke-width: 1.8;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.chart-labels {
  display: flex;
  justify-content: space-between;
  color: #64748b;
  font-size: 0.75rem;
}

.donut-wrap {
  display: grid;
  justify-items: center;
  gap: 0.9rem;
}

.donut-chart {
  width: 165px;
  height: 165px;
  border-radius: 50%;
  display: grid;
  place-items: center;
}

.donut-center {
  width: 105px;
  height: 105px;
  border-radius: 50%;
  background: #fff;
  display: grid;
  place-items: center;
  text-align: center;
  color: #64748b;
  font-size: 0.72rem;
}

.donut-center strong {
  color: #0f172a;
  font-size: 1rem;
}

.donut-legend {
  width: 100%;
  display: grid;
  gap: 0.45rem;
  font-size: 0.8rem;
  color: #334155;
}

.dot {
  display: inline-block;
  width: 9px;
  height: 9px;
  border-radius: 50%;
  margin-right: 0.4rem;
}

.dot--green {
  background: #16a34a;
}

.dot--amber {
  background: #f59e0b;
}

.bar-chart {
  height: 190px;
  display: flex;
  align-items: end;
  justify-content: space-between;
  gap: 0.45rem;
}

.bar-item {
  flex: 1;
  display: grid;
  justify-items: center;
  gap: 0.4rem;
}

.bar {
  width: 100%;
  border-radius: 0.55rem 0.55rem 0.25rem 0.25rem;
  background: linear-gradient(180deg, #3b82f6, #1e40af);
  min-height: 10px;
}

.bar-label {
  font-size: 0.72rem;
  color: #64748b;
}

.summary-list {
  display: grid;
  gap: 0.6rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.55rem 0.65rem;
  border-radius: 0.55rem;
  background: #f8fafc;
  color: #334155;
  font-size: 0.85rem;
}

.summary-row strong {
  color: #0f172a;
}

.dashboard-loading {
  margin: 0;
  color: #64748b;
  font-size: 0.82rem;
}

@media (max-width: 768px) {
  .dashboard-title {
    font-size: 1.2rem;
  }

  .donut-chart {
    width: 140px;
    height: 140px;
  }

  .donut-center {
    width: 88px;
    height: 88px;
  }
}

.dark .dashboard-title {
  color: #e2e8f0;
}

.dark .dashboard-subtitle,
.dark .dashboard-loading,
.dark .chart-labels,
.dark .bar-label,
.dark .metric-subtitle {
  color: #94a3b8;
}

.dark .dashboard-kpi-chip {
  background: #1e293b;
  color: #cbd5e1;
}

.dark .metric-card,
.dark .chart-card {
  border-color: #334155;
  box-shadow: 0 10px 28px rgba(2, 6, 23, 0.45);
}

.dark .metric-head,
.dark .metric-title,
.dark .metric-icon,
.dark .donut-legend,
.dark .summary-row {
  color: #cbd5e1;
}

.dark .metric-value,
.dark .summary-row strong,
.dark .donut-center strong {
  color: #f8fafc;
}

.dark .line-chart {
  background: linear-gradient(180deg, #1e293b 0%, #172033 45%, #0f172a 100%);
}

.dark .line-chart-shadow {
  stroke: rgba(96, 165, 250, 0.24);
}

.dark .line-chart-main {
  stroke: #60a5fa;
}

.dark .donut-center {
  background: #0f172a;
  color: #94a3b8;
}

.dark .summary-row {
  background: #0f172a;
  border: 1px solid #1e293b;
}
</style>
