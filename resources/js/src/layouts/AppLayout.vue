<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import {
    Users,
    Building2,
    Phone,
    FileText,
    Calendar,
    ShoppingCart,
    Truck,
    Wrench,
    Landmark,
    ArrowLeftRight,
    Receipt,
    Archive,
    Shield,
    Settings,
    Globe,
    Briefcase,
    CalendarDays,
    Zap,
    Package,
    Percent,
    ClipboardList,
    Building,
    ChevronDown,
    Menu,
    X,
    LogOut,
    User,
} from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();

// Auth user
const authUser = ref((window as any).__AUTH_USER__ ?? null);

const syncAuthUser = (event: Event) => {
    const detail = (event as CustomEvent).detail;

    if (detail) {
        authUser.value = detail;
    }
};

onMounted(() => {
    window.addEventListener('auth-user-updated', syncAuthUser as EventListener);
});

onBeforeUnmount(() => {
    window.removeEventListener('auth-user-updated', syncAuthUser as EventListener);
});

// User initials for avatar
const userInitials = computed(() => {
    const name: string = authUser.value?.name ?? '';
    return name.split(' ').map((n: string) => n[0]).slice(0, 2).join('').toUpperCase() || 'U';
});

// Sidebar open state (mobile toggle)
const sidebarOpen = ref(false);

// Track which submenus are expanded
const expandedMenus = ref<Record<string, boolean>>({
    encomendas: true,
    financeiro: false,
    gestaoAcessos: false,
    configuracoes: false,
});

const toggleMenu = (key: string) => {
    expandedMenus.value[key] = !expandedMenus.value[key];
};

const closeSidebar = () => {
    sidebarOpen.value = false;
};

const navigate = (path: string) => {
    router.push(path);
    closeSidebar();
};

const handleLogout = async () => {
    try {
        await fetch('/logout', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '' },
        });
        window.location.href = '/';
    } catch {
        window.location.href = '/';
    }
};
</script>

<template>
    <div class="app-shell">
        <!-- Mobile sidebar overlay -->
        <div v-if="sidebarOpen" class="sidebar-overlay" @click="closeSidebar" />

        <!-- Sidebar -->
        <aside :class="['sidebar', sidebarOpen ? 'sidebar--open' : '']">

            <!-- Logo -->
            <div class="sidebar-logo">
                <div class="logo-icon">
                    <Building class="h-4 w-4" />
                </div>
                <span class="logo-text">Inovcorp</span>
                <button class="sidebar-close-btn" @click="closeSidebar">
                    <X class="h-4 w-4" />
                </button>
            </div>

            <!-- Nav -->
            <nav class="sidebar-nav">

                <div class="nav-section-label">Principal</div>

                <RouterLink to="/" class="nav-item" active-class="nav-item--active" @click="closeSidebar">
                    <Users class="nav-icon" /> Clientes
                </RouterLink>
                <RouterLink to="/fornecedores" class="nav-item" active-class="nav-item--active" @click="closeSidebar">
                    <Building2 class="nav-icon" /> Fornecedores
                </RouterLink>
                <RouterLink to="/contactos" class="nav-item" active-class="nav-item--active" @click="closeSidebar">
                    <Phone class="nav-icon" /> Contactos
                </RouterLink>
                <RouterLink to="/propostas" class="nav-item" active-class="nav-item--active" @click="closeSidebar">
                    <FileText class="nav-icon" /> Propostas
                </RouterLink>
                <RouterLink to="/calendario" class="nav-item" active-class="nav-item--active" @click="closeSidebar">
                    <Calendar class="nav-icon" /> Calendário
                </RouterLink>

                <div class="nav-section-label">Operações</div>

                <!-- Encomendas submenu -->
                <div>
                    <button class="nav-item nav-item--parent" @click="toggleMenu('encomendas')">
                        <span class="flex items-center gap-2.5">
                            <ShoppingCart class="nav-icon" /> Encomendas
                        </span>
                        <ChevronDown :class="['nav-chevron', expandedMenus.encomendas ? 'nav-chevron--open' : '']" />
                    </button>
                    <div v-if="expandedMenus.encomendas" class="nav-submenu">
                        <RouterLink to="/encomendas/clientes" class="nav-sub-item" active-class="nav-item--active"
                            @click="closeSidebar">
                            <ShoppingCart class="nav-sub-icon" /> Clientes
                        </RouterLink>
                        <RouterLink to="/encomendas/fornecedores" class="nav-sub-item" active-class="nav-item--active"
                            @click="closeSidebar">
                            <Truck class="nav-sub-icon" /> Fornecedores
                        </RouterLink>
                    </div>
                </div>

                <RouterLink to="/ordens-trabalho" class="nav-item" active-class="nav-item--active"
                    @click="closeSidebar">
                    <Wrench class="nav-icon" /> Ordens de Trabalho
                </RouterLink>

                <!-- Financeiro submenu -->
                <div>
                    <button class="nav-item nav-item--parent" @click="toggleMenu('financeiro')">
                        <span class="flex items-center gap-2.5">
                            <Landmark class="nav-icon" /> Financeiro
                        </span>
                        <ChevronDown :class="['nav-chevron', expandedMenus.financeiro ? 'nav-chevron--open' : '']" />
                    </button>
                    <div v-if="expandedMenus.financeiro" class="nav-submenu">
                        <RouterLink to="/financeiro/contas-bancarias" class="nav-sub-item"
                            active-class="nav-item--active" @click="closeSidebar">
                            <Landmark class="nav-sub-icon" /> Contas Bancárias
                        </RouterLink>
                        <RouterLink to="/financeiro/conta-corrente" class="nav-sub-item" active-class="nav-item--active"
                            @click="closeSidebar">
                            <ArrowLeftRight class="nav-sub-icon" /> Conta Corrente
                        </RouterLink>
                        <RouterLink to="/financeiro/faturas-fornecedor" class="nav-sub-item"
                            active-class="nav-item--active" @click="closeSidebar">
                            <Receipt class="nav-sub-icon" /> Faturas Fornecedor
                        </RouterLink>
                    </div>
                </div>

                <RouterLink to="/arquivo" class="nav-item" active-class="nav-item--active" @click="closeSidebar">
                    <Archive class="nav-icon" /> Arquivo
                </RouterLink>

                <div class="nav-section-label">Administração</div>

                <!-- Gestão de Acessos submenu -->
                <div>
                    <button class="nav-item nav-item--parent" @click="toggleMenu('gestaoAcessos')">
                        <span class="flex items-center gap-2.5">
                            <Shield class="nav-icon" /> Gestão de Acessos
                        </span>
                        <ChevronDown :class="['nav-chevron', expandedMenus.gestaoAcessos ? 'nav-chevron--open' : '']" />
                    </button>
                    <div v-if="expandedMenus.gestaoAcessos" class="nav-submenu">
                        <RouterLink to="/gestao-acessos/utilizadores" class="nav-sub-item"
                            active-class="nav-item--active" @click="closeSidebar">
                            <User class="nav-sub-icon" /> Utilizadores
                        </RouterLink>
                        <RouterLink to="/gestao-acessos/permissoes" class="nav-sub-item" active-class="nav-item--active"
                            @click="closeSidebar">
                            <Shield class="nav-sub-icon" /> Permissões
                        </RouterLink>
                    </div>
                </div>

                <!-- Configurações submenu -->
                <div>
                    <button class="nav-item nav-item--parent" @click="toggleMenu('configuracoes')">
                        <span class="flex items-center gap-2.5">
                            <Settings class="nav-icon" /> Configurações
                        </span>
                        <ChevronDown :class="['nav-chevron', expandedMenus.configuracoes ? 'nav-chevron--open' : '']" />
                    </button>
                    <div v-if="expandedMenus.configuracoes" class="nav-submenu">
                        <RouterLink to="/configuracoes/paises" class="nav-sub-item" active-class="nav-item--active"
                            @click="closeSidebar">
                            <Globe class="nav-sub-icon" /> Países
                        </RouterLink>
                        <RouterLink to="/configuracoes/funcoes" class="nav-sub-item" active-class="nav-item--active"
                            @click="closeSidebar">
                            <Briefcase class="nav-sub-icon" /> Funções
                        </RouterLink>
                        <RouterLink to="/configuracoes/tipos-calendario" class="nav-sub-item"
                            active-class="nav-item--active" @click="closeSidebar">
                            <CalendarDays class="nav-sub-icon" /> Tipos Calendário
                        </RouterLink>
                        <RouterLink to="/configuracoes/acoes-calendario" class="nav-sub-item"
                            active-class="nav-item--active" @click="closeSidebar">
                            <Zap class="nav-sub-icon" /> Ações Calendário
                        </RouterLink>
                        <RouterLink to="/configuracoes/artigos" class="nav-sub-item" active-class="nav-item--active"
                            @click="closeSidebar">
                            <Package class="nav-sub-icon" /> Artigos
                        </RouterLink>
                        <RouterLink to="/configuracoes/iva" class="nav-sub-item" active-class="nav-item--active"
                            @click="closeSidebar">
                            <Percent class="nav-sub-icon" /> IVA
                        </RouterLink>
                        <RouterLink to="/configuracoes/logs" class="nav-sub-item" active-class="nav-item--active"
                            @click="closeSidebar">
                            <ClipboardList class="nav-sub-icon" /> Logs
                        </RouterLink>
                        <RouterLink to="/configuracoes/empresa" class="nav-sub-item" active-class="nav-item--active"
                            @click="closeSidebar">
                            <Building class="nav-sub-icon" /> Empresa
                        </RouterLink>
                    </div>
                </div>

            </nav>

            <!-- Sidebar Footer -->
            <div class="sidebar-footer">
                <button type="button" class="sidebar-user sidebar-user--button" @click="navigate('/perfil')">
                    <div class="user-avatar">{{ userInitials }}</div>
                    <div class="user-info">
                        <span class="user-name">{{ authUser?.name ?? 'Utilizador' }}</span>
                        <span class="user-role">Administrador</span>
                    </div>
                </button>
                <button class="logout-btn" @click="handleLogout" title="Terminar Sessão">
                    <LogOut class="h-4 w-4" />
                </button>
            </div>
        </aside>

        <!-- Main -->
        <div class="main-wrapper">

            <!-- Content -->
            <main class="main-content">
                <RouterView />
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Layout shell */
.app-shell {
    display: flex;
    height: 100vh;
    overflow: hidden;
    background: #e8edf5;
}

/* Sidebar overlay (mobile) */
.sidebar-overlay {
    position: fixed;
    inset: 0;
    z-index: 20;
    background: rgba(0, 0, 0, 0.45);
    backdrop-filter: blur(2px);
}

/* Sidebar */
.sidebar {
    position: fixed;
    inset-y: 0;
    left: 0;
    z-index: 30;
    display: flex;
    flex-direction: column;
    width: 248px;
    background: #0d1b2e;
    transform: translateX(-100%);
    transition: transform 0.28s cubic-bezier(.4, 0, .2, 1);
    box-shadow: 4px 0 24px rgba(0, 0, 0, .35);
}

@media (min-width: 1024px) {
    .sidebar {
        position: static;
        transform: none;
        flex-shrink: 0;
    }
}

.sidebar--open {
    transform: translateX(0);
}

/* Logo */
.sidebar-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    height: 60px;
    padding: 0 16px;
    border-bottom: 1px solid rgba(255, 255, 255, .06);
    flex-shrink: 0;
}

.logo-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #fff;
    flex-shrink: 0;
}

.logo-text {
    font-size: 1rem;
    font-weight: 700;
    color: #f1f5f9;
    letter-spacing: -0.01em;
    flex: 1;
}

.sidebar-close-btn {
    display: flex;
    padding: 4px;
    color: #64748b;
    background: none;
    border: none;
    cursor: pointer;
    border-radius: 4px;
}

.sidebar-close-btn:hover {
    color: #94a3b8;
}

@media (min-width: 1024px) {
    .sidebar-close-btn {
        display: none;
    }
}

/* Nav */
.sidebar-nav {
    flex: 1;
    overflow-y: auto;
    padding: 12px 10px;
    scrollbar-width: thin;
    scrollbar-color: #1e293b transparent;
}

.sidebar-nav::-webkit-scrollbar {
    width: 4px;
}

.sidebar-nav::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background: #1e293b;
    border-radius: 2px;
}

.nav-section-label {
    padding: 14px 10px 6px;
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #475569;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    border-radius: 7px;
    padding: 8px 10px;
    font-size: 0.8375rem;
    font-weight: 500;
    color: #94a3b8;
    transition: background 0.15s, color 0.15s;
    cursor: pointer;
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    text-decoration: none;
    border-left: 2px solid transparent;
    margin-bottom: 1px;
}

.nav-item:hover {
    background: rgba(255, 255, 255, .05);
    color: #e2e8f0;
}

.nav-item--parent {
    justify-content: space-between;
}

.nav-item--active {
    background: rgba(59, 130, 246, .15) !important;
    color: #60a5fa !important;
    border-left-color: #3b82f6 !important;
    font-weight: 600;
}

.nav-chevron {
    width: 14px;
    height: 14px;
    color: #475569;
    transition: transform 0.2s;
    flex-shrink: 0;
}

.nav-chevron--open {
    transform: rotate(180deg);
}

.nav-icon {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
}

.nav-submenu {
    margin: 2px 0 2px 14px;
    padding-left: 10px;
    border-left: 1px solid rgba(255, 255, 255, .07);
}

.nav-sub-item {
    display: flex;
    align-items: center;
    gap: 8px;
    border-radius: 6px;
    padding: 6px 10px;
    font-size: 0.8rem;
    color: #64748b;
    transition: background 0.15s, color 0.15s;
    text-decoration: none;
    margin-bottom: 1px;
    border-left: 2px solid transparent;
}

.nav-sub-item:hover {
    background: rgba(255, 255, 255, .05);
    color: #cbd5e1;
}

.nav-sub-icon {
    width: 13px;
    height: 13px;
    flex-shrink: 0;
}

/* Sidebar footer */
.sidebar-footer {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    border-top: 1px solid rgba(255, 255, 255, .06);
    flex-shrink: 0;
}

.sidebar-user {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
}

.sidebar-user--button {
    padding: 0;
    border: none;
    background: none;
    color: inherit;
    cursor: pointer;
    text-align: left;
}

.user-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    color: #fff;
    font-size: 0.75rem;
    font-weight: 700;
    flex-shrink: 0;
}

.user-avatar--sm {
    width: 30px;
    height: 30px;
    font-size: 0.7rem;
}

.user-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.user-name {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #e2e8f0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-role {
    font-size: 0.7rem;
    color: #475569;
}

.logout-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 7px;
    border: none;
    background: none;
    color: #475569;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
    flex-shrink: 0;
}

.logout-btn:hover {
    background: rgba(239, 68, 68, .15);
    color: #f87171;
}

/* Topbar */
.main-wrapper {
    display: flex;
    flex: 1;
    flex-direction: column;
    overflow: hidden;
}

.topbar {
    display: flex;
    align-items: center;
    gap: 12px;
    height: 60px;
    padding: 0 20px;
    background: #fff;
    border-bottom: 1px solid #e9eef5;
    flex-shrink: 0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
}

.mobile-menu-btn {
    display: flex;
    padding: 6px;
    border-radius: 7px;
    background: none;
    border: none;
    color: #64748b;
    cursor: pointer;
    transition: background 0.15s;
}

.mobile-menu-btn:hover {
    background: #f1f5f9;
}

.topbar-brand {
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    font-weight: 700;
    color: #1e293b;
    letter-spacing: -0.01em;
}

.topbar-user {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 4px 10px 4px 4px;
    border-radius: 50px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    transition: background 0.15s, border-color 0.15s;
    cursor: default;
}

.topbar-user:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.topbar-username {
    font-size: 0.8375rem;
    font-weight: 500;
    color: #374151;
}

/* Main content */
.main-content {
    flex: 1;
    overflow-y: auto;
    background: #e8edf5;
    padding: 24px;
}

@media (min-width: 1024px) {
    .main-content {
        padding: 28px 32px;
    }
}

/* Dark mode overrides */
:global(.dark) .app-shell {
    background: #0f172a;
}

:global(.dark) .topbar {
    background: #1e293b;
    border-bottom-color: #334155;
    box-shadow: 0 1px 3px rgba(0, 0, 0, .3);
}

:global(.dark) .mobile-menu-btn {
    color: #94a3b8;
}

:global(.dark) .mobile-menu-btn:hover {
    background: #334155;
}

:global(.dark) .topbar-brand {
    color: #f1f5f9;
}

:global(.dark) .topbar-user {
    background: #0f172a;
    border-color: #334155;
}

:global(.dark) .topbar-user:hover {
    background: #1e293b;
    border-color: #475569;
}

:global(.dark) .topbar-username {
    color: #cbd5e1;
}

:global(.dark) .main-content {
    background: #0f172a;
}
</style>
