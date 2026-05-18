# App-Gestao

App-Gestao é uma aplicação web em Laravel para gestão empresarial integrada, com autenticação, gestão de clientes e fornecedores, propostas, encomendas, faturas, geração de PDFs e interface responsiva em Vue.

## 📋 Funcionalidades

### 🏢 Gestão de Empresa
✅ Configuração e atualização de dados da empresa
🏷️ Gestão de nome, endereço, código postal, cidade e país
🖼️ Upload e gestão de logotipo da empresa
💰 Gestão de contas bancárias com IBAN

### 📊 Gestão de Clientes e Fornecedores
👥 Criação, consulta, edição e remoção de entidades
📞 Gestão de contactos associados a entidades
📧 Armazenamento de email e telefone
🏷️ Definição de tipo: cliente ou fornecedor
🌍 Seleção de país da entidade

### 📝 Gestão de Propostas
📄 Criação, consulta, edição e remoção de propostas
🎯 Associação de propostas a clientes
📅 Configuração de data de emissão e validade
💵 Gestão de valor total e itens
📋 Definição de estado: rascunho ou fechada
🔄 Conversão de proposta em encomenda

### 📦 Gestão de Encomendas (Clientes)
📋 Criação automática de números de encomenda (ENC-YYYY-XXX)
🎯 Associação a clientes
📅 Gestão de datas
💵 Cálculo de valor total
📊 Controlo de estado: rascunho ou fechada
🔄 Conversão automática para encomendas a fornecedores

### 🛒 Gestão de Encomendas a Fornecedores
📋 Criação automática de números (ECF-YYYY-XXX)
🏷️ Agrupamento por fornecedor
💰 Gestão de valores
📊 Estados: rascunho, fechada ou faturada
📄 Ligação a faturas de fornecedor

### 💳 Gestão de Faturas
📄 Criação automática de números de fatura (FAT-YYYY-XXX)
🏢 Associação a fornecedores
📅 Gestão de datas de emissão e vencimento
💵 Controlo de valor total
📦 Ligação a encomendas de fornecedor
✅ Marcação como paga com prova de pagamento
📧 Envio de comprovativo por email

### 📋 Gestão de Ordens de Trabalho
✏️ Criação automática de números (OT-YYYY-XXX)
👨‍💼 Atribuição a técnicos
🎯 Associação a clientes
📅 Agendamento com data prevista
📝 Descrição detalhada
📊 Estados: aberta, em curso ou concluída

### 📊 Artigos e Catálogo
📦 Gestão de artigos disponíveis
💰 Preços unitários
🏷️ Referências e descrições
📊 Ligação a taxas de IVA

### 📄 Geração de PDFs
🖨️ Geração automática de PDFs para propostas
🖨️ PDFs para encomendas com logotipo e dados da empresa
🖨️ PDFs para encomendas a fornecedores
🖨️ PDFs para faturas de fornecedor
📋 Suporte a templates personalizáveis

### 🔔 Notificações e Eventos
🔔 Notificações visuais para operações de sucesso e erro
📧 Eventos de criação de propostas, encomendas e faturas
✉️ Envio de comprovativo de pagamento por email

### 🌐 Experiência de Utilizador
📱 Interface responsiva para desktop e dispositivos móveis
🔐 Autenticação baseada em tokens (Laravel Sanctum)
🌗 Suporte a tema claro e escuro
🔎 Filtros avançados por estado, cliente, fornecedor
📊 Paginação e ordenação de resultados

## 🛠️ Tecnologias Utilizadas

- **Backend**: Laravel 13, PHP 8.4+
- **Frontend**: Vue 3, Vite 8
- **Styling**: Tailwind CSS 4
- **Base de Dados**: SQLite ou MySQL
- **Geração de PDFs**: DomPDF
- **Autenticação**: Laravel Sanctum
- **Testes**: Pest PHP
- **Notificações**: Laravel Mail

## ⚙️ Como Executar o Projeto

### ✅ Pré-requisitos

Certifique-se de ter instalado:
- PHP 8.4+
- Composer
- Node.js 18+
- npm ou yarn
- Base de dados (SQLite ou MySQL)

### 1️⃣ Clonar o repositório

```bash
git clone <url-do-repositorio>
cd App-Gestao
```

### 2️⃣ Instalação e configuração

```bash
# Instalar dependências PHP
composer install

# Copiar ficheiro .env
cp .env.example .env

# Gerar chave da aplicação
php artisan key:generate

# Executar migrations
php artisan migrate

# Instalar dependências Node.js
npm install

# Compilar assets
npm run build
```

### 3️⃣ Configuração do .env

Edite o ficheiro `.env` e configure:

```env
APP_NAME="App-Gestao"
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# ou
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=app_gestao
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=seu_usuario
MAIL_PASSWORD=sua_senha
MAIL_FROM_ADDRESS=noreply@app-gestao.local
```

### 4️⃣ Executar o ambiente de desenvolvimento

```bash
# Terminal 1: Servidor Laravel
composer run dev

# Terminal 2: Watch de assets (opcional)
npm run dev
```

### 5️⃣ Aceder à aplicação

Abra [http://localhost:8000](http://localhost:8000)

## 🧪 Testes

```bash
# Executar todos os testes
composer test

# Ou via artisan
php artisan test

# Com cobertura
php artisan test --coverage
```

## 📁 Estrutura do Projeto

```
📁 App-Gestao/
├── 📄 artisan
├── 📄 composer.json
├── 📄 composer.lock
├── 📄 package.json
├── 📄 package-lock.json
├── 📄 phpunit.xml
├── 📄 vite.config.js
├── 📄 README.md
├── 📁 app/
│   ├── 📁 Actions/
│   │   └── 📁 Fortify/
│   ├── 📁 Events/
│   │   ├── 📄 InvoicePaid.php
│   │   ├── 📄 OrderCreated.php
│   │   └── 📄 ProposalCreated.php
│   ├── 📁 Http/
│   │   ├── 📁 Controllers/
│   │   │   ├── 📄 CompanyController.php
│   │   │   ├── 📄 EntityController.php
│   │   │   ├── 📄 ContactController.php
│   │   │   ├── 📄 ArticleController.php
│   │   │   ├── 📄 ProposalController.php
│   │   │   ├── 📄 OrderController.php
│   │   │   ├── 📄 SupplierOrderController.php
│   │   │   ├── 📄 InvoiceController.php
│   │   │   └── 📄 CalendarEventController.php
│   │   └── 📁 Middleware/
│   ├── 📁 Listeners/
│   │   ├── 📄 SendPaymentProofEmail.php
│   │   ├── 📄 StoreInvoicePaidNotification.php
│   │   ├── 📄 StoreOrderCreatedNotification.php
│   │   └── 📄 StoreProposalCreatedNotification.php
│   ├── 📁 Mail/
│   │   ├── 📄 PaymentProofMail.php
│   │   └── 📄 WelcomeUserMail.php
│   ├── 📁 Models/
│   │   ├── 📄 Company.php
│   │   ├── 📄 Entity.php
│   │   ├── 📄 Contact.php
│   │   ├── 📄 Article.php
│   │   ├── 📄 Proposal.php
│   │   ├── 📄 Order.php
│   │   ├── 📄 SupplierOrder.php
│   │   ├── 📄 Invoice.php
│   │   ├── 📄 User.php
│   │   ├── 📄 Country.php
│   │   ├── 📄 TaxRate.php
│   │   └── 📄 CalendarEvent.php
│   ├── 📁 Notifications/
│   │   ├── 📄 InvoicePaidNotification.php
│   │   ├── 📄 OrderCreatedNotification.php
│   │   └── 📄 ProposalCreatedNotification.php
│   ├── 📁 Services/
│   │   ├── 📄 InvoicePdfService.php
│   │   ├── 📄 OrderPdfService.php
│   │   ├── 📄 ProposalPdfService.php
│   │   └── 📄 ArchiveDocumentPdfService.php
│   └── 📁 Providers/
│       ├── 📄 AppServiceProvider.php
│       └── 📄 FortifyServiceProvider.php
├── 📁 bootstrap/
│   ├── 📁 cache/
│   ├── 📄 app.php
│   └── 📄 providers.php
├── 📁 config/
│   ├── 📄 app.php
│   ├── 📄 auth.php
│   ├── 📄 cache.php
│   ├── 📄 database.php
│   ├── 📄 filesystems.php
│   ├── 📄 fortify.php
│   ├── 📄 logging.php
│   ├── 📄 mail.php
│   ├── 📄 permission.php
│   ├── 📄 queue.php
│   ├── 📄 sanctum.php
│   ├── 📄 services.php
│   ├── 📄 session.php
│   └── 📁 database/
├── 📁 database/
│   ├── 📁 factories/
│   ├── 📁 migrations/
│   │   ├── 📄 2024_01_01_000001_create_users_table.php
│   │   ├── 📄 2024_01_01_000002_create_entities_table.php
│   │   ├── 📄 2024_01_01_000003_create_contacts_table.php
│   │   ├── 📄 2024_01_01_000004_create_articles_table.php
│   │   ├── 📄 2024_01_01_000005_create_proposals_table.php
│   │   ├── 📄 2024_01_01_000006_create_orders_table.php
│   │   ├── 📄 2024_01_01_000007_create_supplier_orders_table.php
│   │   ├── 📄 2024_01_01_000008_create_invoices_table.php
│   │   └── 📄 2024_01_01_000009_create_calendar_events_table.php
│   └── 📁 seeders/
├── 📁 public/
│   ├── 📁 build/
│   ├── 📁 image/
│   ├── 📁 storage/
│   └── 📄 index.php
├── 📁 resources/
│   ├── 📁 css/
│   │   └── 📄 app.css
│   ├── 📁 js/
│   │   ├── 📄 app.js
│   │   ├── 📄 bootstrap.js
│   │   ├── 📁 src/
│   │   │   ├── 📁 composables/
│   │   │   ├── 📁 pages/
│   │   │   │   ├── 📄 CompanyPage.vue
│   │   │   │   ├── 📄 EntitiesPage.vue
│   │   │   │   ├── 📄 ProposalsPage.vue
│   │   │   │   ├── 📄 OrdersPage.vue
│   │   │   │   ├── 📄 SupplierOrdersPage.vue
│   │   │   │   ├── 📄 InvoicesPage.vue
│   │   │   │   ├── 📄 WorkOrdersPage.vue
│   │   │   │   └── 📄 CalendarPage.vue
│   │   │   └── 📁 components/
│   │   └── 📁 views/
│   └── 📁 views/
│       ├── 📄 welcome.blade.php
│       ├── 📄 app.blade.php
│       └── 📁 pdf/
│           └── 📄 template.blade.php
├── 📁 routes/
│   ├── 📄 api.php
│   ├── 📄 console.php
│   └── 📄 web.php
├── 📁 storage/
│   ├── 📁 app/
│   ├── 📁 framework/
│   └── 📁 logs/
├── 📁 tests/
│   ├── 📄 Pest.php
│   ├── 📄 TestCase.php
│   ├── 📁 Feature/
│   └── 📁 Unit/
└── 📁 vendor/
```

## 📝 Observações

- A interface principal da aplicação carrega a SPA Vue dentro das views Blade
- O sistema utiliza geração automática de números para encomendas, faturas, propostas e ordens de trabalho
- Os números seguem o padrão: `TIPO-AAAA-NNN` (ex: ENC-2026-001, FAT-2026-001, PROP-2026-001, OT-2026-001)
- Todas as rotas de API estão protegidas por autenticação via Laravel Sanctum
- Os PDFs incluem logotipo, dados da empresa e informações dinâmicas
- O sistema suporta múltiplas entidades (clientes e fornecedores) com gestão de contactos
- Existe suporte completo para notificações e eventos de negócio
- As migrações criam automaticamente as tabelas necessárias, incluindo dados de exemplo
- O projeto está estruturado em módulos separados para melhor manutenção e escalabilidade
