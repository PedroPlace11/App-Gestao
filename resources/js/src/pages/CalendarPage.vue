<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import type { CalendarOptions, DateSelectArg, EventClickArg, EventInput } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import ptLocale from '@fullcalendar/core/locales/pt';
import { useApi } from '../composables/useApi';

const { get, post, put } = useApi();

// ── State ──────────────────────────────────────────────────────────────────────
const rawEvents    = ref<CalendarEvent[]>([]);
const entities     = ref<Entity[]>([]);
const calendarTypes   = ref<{ id: number; name: string }[]>([]);
const calendarActions = ref<{ id: number; name: string }[]>([]);
const isLoading    = ref(false);

// Filters
const filterUserId   = ref<number | null>(null);
const filterEntityId = ref<number | null>(null);

// Modal state
const showModal   = ref(false);
const isEditing   = ref(false);
const isSaving    = ref(false);
const currentId   = ref<number | null>(null);

const form = ref({
  date:        '',
  time:        '09:00',
  duration:    60,
  entity_id:   null as number | null,
  type_id:     null as number | null,
  action_id:   null as number | null,
  description: '',
  status:      'active' as 'active' | 'closed',
});

const formError = ref('');

// ── Computed ──────────────────────────────────────────────────────────────────
const filteredEvents = computed<CalendarEvent[]>(() => {
  return rawEvents.value.filter((e) => {
    if (filterUserId.value   && e.user_id   !== filterUserId.value)   return false;
    if (filterEntityId.value && e.entity_id !== filterEntityId.value) return false;
    return true;
  });
});

const calendarEvents = computed<EventInput[]>(() =>
  filteredEvents.value.map((e) => {
    const startDt = `${e.date}T${e.time}`;
    const endMs   = new Date(startDt).getTime() + (e.duration ?? 60) * 60 * 1000;
    return {
      id:            String(e.id),
      title:         e.description || e.calendar_action?.name || 'Evento',
      start:         startDt,
      end:           new Date(endMs).toISOString(),
      backgroundColor: e.status === 'closed' ? '#9ca3af' : '#2563eb',
      borderColor:     e.status === 'closed' ? '#6b7280' : '#1d4ed8',
      extendedProps:   e,
    };
  }),
);

const calendarOptions = computed<CalendarOptions>(() => ({
  plugins:     [dayGridPlugin, timeGridPlugin, interactionPlugin],
  locale:      ptLocale,
  initialView: 'dayGridMonth',
  headerToolbar: {
    left:   'prev,next today',
    center: 'title',
    right:  'dayGridMonth,timeGridWeek,timeGridDay',
  },
  events:      calendarEvents.value,
  selectable:  true,
  editable:    false,
  dayMaxEvents: 3,
  select(info: DateSelectArg) {
    openCreateModal(info.startStr.slice(0, 10));
  },
  eventClick(info: EventClickArg) {
    const ev = info.event.extendedProps as CalendarEvent;
    openEditModal(ev);
  },
}));

// ── Methods ───────────────────────────────────────────────────────────────────
const fetchEvents = async () => {
  isLoading.value = true;
  try {
    const data = await get<CalendarEvent[]>('/calendar-events', { all: true });
    rawEvents.value = Array.isArray(data) ? data : (data as any).data ?? [];
  } catch {
    // silently fail – already handled by useApi
  } finally {
    isLoading.value = false;
  }
};

const fetchLookups = async () => {
  try {
    const [entRes, typRes, actRes] = await Promise.all([
      get<PaginatedResponse<Entity>>('/entities', { per_page: 500 }),
      get<{ id: number; name: string }[]>('/api/v1/calendar-types'),
      get<{ id: number; name: string }[]>('/api/v1/calendar-actions'),
    ]);
    entities.value     = (entRes as any).data ?? [];
    calendarTypes.value   = Array.isArray(typRes) ? typRes : (typRes as any).data ?? [];
    calendarActions.value = Array.isArray(actRes) ? actRes : (actRes as any).data ?? [];
  } catch {
    // non-critical lookups
  }
};

const openCreateModal = (date = '') => {
  isEditing.value  = false;
  currentId.value  = null;
  formError.value  = '';
  form.value = {
    date:        date,
    time:        '09:00',
    duration:    60,
    entity_id:   null,
    type_id:     null,
    action_id:   null,
    description: '',
    status:      'active',
  };
  showModal.value = true;
};

const openEditModal = (ev: CalendarEvent) => {
  isEditing.value  = true;
  currentId.value  = ev.id;
  formError.value  = '';
  form.value = {
    date:        typeof ev.date === 'string' ? ev.date : (ev.date as any).toString().slice(0, 10),
    time:        ev.time?.slice(0, 5) ?? '09:00',
    duration:    ev.duration ?? 60,
    entity_id:   ev.entity_id ?? null,
    type_id:     ev.type_id,
    action_id:   ev.action_id,
    description: ev.description ?? '',
    status:      ev.status,
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const saveEvent = async () => {
  if (!form.value.date || !form.value.type_id || !form.value.action_id) {
    formError.value = 'Data, Tipo e Ação são obrigatórios.';
    return;
  }

  isSaving.value = true;
  formError.value = '';

  try {
    if (isEditing.value && currentId.value) {
      await put(`/calendar-events/${currentId.value}`, form.value);
    } else {
      await post('/calendar-events', form.value);
    }
    await fetchEvents();
    closeModal();
  } catch (e: any) {
    formError.value = e?.message ?? 'Erro ao guardar evento.';
  } finally {
    isSaving.value = false;
  }
};

// ── Lifecycle ──────────────────────────────────────────────────────────────────
onMounted(() => {
  fetchEvents();
  fetchLookups();
});
</script>

<template>
  <div class="space-y-4">
    <!-- Toolbar -->
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border bg-card p-4">
      <h1 class="text-xl font-semibold">Calendário</h1>

      <div class="flex flex-wrap items-center gap-2">
        <!-- Filter by entity -->
        <select
          v-model="filterEntityId"
          class="h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
        >
          <option :value="null">Todas as entidades</option>
          <option v-for="e in entities" :key="e.id" :value="e.id">{{ e.name }}</option>
        </select>

        <button
          type="button"
          class="inline-flex h-9 items-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90"
          @click="openCreateModal()"
        >
          + Novo Evento
        </button>
      </div>
    </div>

    <!-- Loading overlay -->
    <div v-if="isLoading" class="py-12 text-center text-sm text-muted-foreground">
      A carregar eventos...
    </div>

    <!-- FullCalendar -->
    <div v-else class="rounded-lg border bg-card p-4">
      <FullCalendar :options="calendarOptions" />
    </div>

    <!-- Modal Overlay -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
      @click.self="closeModal"
    >
      <div class="w-full max-w-lg rounded-lg bg-background shadow-xl">
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b px-6 py-4">
          <h2 class="text-lg font-semibold">
            {{ isEditing ? 'Editar Evento' : 'Novo Evento' }}
          </h2>
          <button
            type="button"
            class="rounded p-1 text-muted-foreground hover:text-foreground"
            @click="closeModal"
          >
            ✕
          </button>
        </div>

        <!-- Modal Body -->
        <form class="space-y-4 px-6 py-5" @submit.prevent="saveEvent">
          <div v-if="formError" class="rounded-md bg-destructive/10 px-3 py-2 text-sm text-destructive">
            {{ formError }}
          </div>

          <div class="grid grid-cols-2 gap-4">
            <!-- Date -->
            <div class="space-y-1">
              <label class="text-sm font-medium">Data *</label>
              <input
                v-model="form.date"
                type="date"
                required
                class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
              />
            </div>

            <!-- Time -->
            <div class="space-y-1">
              <label class="text-sm font-medium">Hora *</label>
              <input
                v-model="form.time"
                type="time"
                required
                class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
              />
            </div>
          </div>

          <!-- Duration -->
          <div class="space-y-1">
            <label class="text-sm font-medium">Duração (minutos) *</label>
            <input
              v-model.number="form.duration"
              type="number"
              min="1"
              required
              class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
            />
          </div>

          <!-- Entity -->
          <div class="space-y-1">
            <label class="text-sm font-medium">Entidade</label>
            <select
              v-model="form.entity_id"
              class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
            >
              <option :value="null">— Sem entidade —</option>
              <option v-for="e in entities" :key="e.id" :value="e.id">{{ e.name }}</option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <!-- Type -->
            <div class="space-y-1">
              <label class="text-sm font-medium">Tipo *</label>
              <select
                v-model="form.type_id"
                required
                class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
              >
                <option :value="null">— Selecione —</option>
                <option v-for="t in calendarTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
              </select>
            </div>

            <!-- Action -->
            <div class="space-y-1">
              <label class="text-sm font-medium">Ação *</label>
              <select
                v-model="form.action_id"
                required
                class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
              >
                <option :value="null">— Selecione —</option>
                <option v-for="a in calendarActions" :key="a.id" :value="a.id">{{ a.name }}</option>
              </select>
            </div>
          </div>

          <!-- Description -->
          <div class="space-y-1">
            <label class="text-sm font-medium">Descrição</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
              placeholder="Notas ou descrição do evento..."
            />
          </div>

          <!-- Status -->
          <div class="space-y-1">
            <label class="text-sm font-medium">Estado</label>
            <select
              v-model="form.status"
              class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
            >
              <option value="active">Ativo</option>
              <option value="closed">Fechado</option>
            </select>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-2 border-t pt-4">
            <button
              type="button"
              class="inline-flex h-9 items-center rounded-md border px-4 text-sm font-medium hover:bg-accent"
              @click="closeModal"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="isSaving"
              class="inline-flex h-9 items-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90 disabled:opacity-50"
            >
              {{ isSaving ? 'A guardar...' : (isEditing ? 'Guardar' : 'Criar Evento') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>