import { createRouter, createWebHistory } from 'vue-router';

// Existing pages
import DashboardPage from '../pages/DashboardPage.vue';
import ClientsPage from '../pages/ClientsPage.vue';
import SuppliersPage from '../pages/SuppliersPage.vue';
import ContactsPage from '../pages/ContactsPage.vue';
import ProposalsPage from '../pages/ProposalsPage.vue';
import CalendarPage from '../pages/CalendarPage.vue';
import OrdersPage from '../pages/OrdersPage.vue';
import InvoicesPage from '../pages/InvoicesPage.vue';
import ArticlesPage from '../pages/ArticlesPage.vue';

// Stub pages
import SupplierOrdersPage from '../pages/SupplierOrdersPage.vue';
import WorkOrdersPage from '../pages/WorkOrdersPage.vue';
import BankAccountsPage from '../pages/BankAccountsPage.vue';
import CurrentAccountPage from '../pages/CurrentAccountPage.vue';
import ArchivePage from '../pages/ArchivePage.vue';
import UsersPage from '../pages/UsersPage.vue';
import PermissionsPage from '../pages/PermissionsPage.vue';
import CountriesConfigPage from '../pages/CountriesConfigPage.vue';
import ContactFunctionsPage from '../pages/ContactFunctionsPage.vue';
import CalendarTypesPage from '../pages/CalendarTypesPage.vue';
import CalendarActionsPage from '../pages/CalendarActionsPage.vue';
import TaxRatesPage from '../pages/TaxRatesPage.vue';
import LogsPage from '../pages/LogsPage.vue';
import CompanyPage from '../pages/CompanyPage.vue';
import ProfilePage from '../pages/ProfilePage.vue';
import TenantManagement from '../pages/TenantManagement.vue';

const routes = [
  { path: '/', component: DashboardPage, meta: { title: 'Dashboard' } },
  { path: '/dashboard', component: DashboardPage, meta: { title: 'Dashboard' } },
  { path: '/perfil', component: ProfilePage, meta: { title: 'Perfil' } },
  { path: '/tenants', component: TenantManagement, meta: { title: 'Tenants' } },

  // CRM
  { path: '/clientes', component: ClientsPage, meta: { title: 'Clientes' } },
  { path: '/fornecedores', component: SuppliersPage, meta: { title: 'Fornecedores' } },
  { path: '/contactos', component: ContactsPage, meta: { title: 'Contactos' } },

  // Propostas
  { path: '/propostas', component: ProposalsPage, meta: { title: 'Propostas' } },

  // Calendário
  { path: '/calendario', component: CalendarPage, meta: { title: 'Calendário' } },

  // Encomendas
  { path: '/encomendas/clientes', component: OrdersPage, meta: { title: 'Encomendas Clientes' } },
  { path: '/encomendas/fornecedores', component: SupplierOrdersPage, meta: { title: 'Encomendas Fornecedores' } },

  // Ordens de Trabalho
  { path: '/ordens-trabalho', component: WorkOrdersPage, meta: { title: 'Ordens de Trabalho' } },

  // Financeiro
  { path: '/financeiro/contas-bancarias', component: BankAccountsPage, meta: { title: 'Contas Bancárias' } },
  { path: '/financeiro/conta-corrente', component: CurrentAccountPage, meta: { title: 'Conta Corrente' } },
  { path: '/financeiro/faturas-fornecedor', component: InvoicesPage, meta: { title: 'Faturas Fornecedor' } },

  // Arquivo
  { path: '/arquivo', component: ArchivePage, meta: { title: 'Arquivo' } },

  // Gestão de Acessos
  { path: '/gestao-acessos/utilizadores', component: UsersPage, meta: { title: 'Utilizadores' } },
  { path: '/gestao-acessos/permissoes', component: PermissionsPage, meta: { title: 'Permissões' } },

  // Configurações
  { path: '/configuracoes/paises', component: CountriesConfigPage, meta: { title: 'Países' } },
  { path: '/configuracoes/funcoes', component: ContactFunctionsPage, meta: { title: 'Funções de Contacto' } },
  { path: '/configuracoes/tipos-calendario', component: CalendarTypesPage, meta: { title: 'Tipos de Calendário' } },
  { path: '/configuracoes/acoes-calendario', component: CalendarActionsPage, meta: { title: 'Ações de Calendário' } },
  { path: '/configuracoes/artigos', component: ArticlesPage, meta: { title: 'Artigos' } },
  { path: '/configuracoes/iva', component: TaxRatesPage, meta: { title: 'Taxas de IVA' } },
  { path: '/configuracoes/logs', component: LogsPage, meta: { title: 'Logs' } },
  { path: '/configuracoes/empresa', component: CompanyPage, meta: { title: 'Empresa' } },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Update document title on navigation
router.afterEach((to) => {
  if (to.path === '/login' || to.path === '/register' || to.path === '/two-factor-challenge') {
    return;
  }

  const title = to.meta?.title;
  document.title = title && title !== 'Dashboard' ? `${title} — Inovcorp` : 'Inovcorp';
});

export default router;
