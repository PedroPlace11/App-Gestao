<?php

use App\Http\Controllers\EntityController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\TenantController;
use App\Models\Country;
use App\Models\SupplierOrder;
use App\Models\TaxRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {

    // Tenants
    Route::prefix('v1/tenants')->group(function () {
        Route::get('/', [TenantController::class, 'index']);
        Route::post('/', [TenantController::class, 'store']);
        Route::get('/current', [TenantController::class, 'current']);
        Route::post('/switch', [TenantController::class, 'switch']);
        // Onboarding self-service
        Route::post('/onboarding', [\App\Http\Controllers\TenantOnboardingController::class, 'create']);
        Route::get('/{company}/onboarding-checklist', [\App\Http\Controllers\TenantOnboardingController::class, 'onboardingChecklist']);
        // Upgrade/downgrade de plano
        Route::post('/{company}/change-plan', [\App\Http\Controllers\TenantOnboardingController::class, 'changePlan']);
        // Logs de alterações de plano
        Route::get('/{company}/plan-logs', [\App\Http\Controllers\TenantOnboardingController::class, 'planLogs']);
        Route::put('/preferences', [TenantController::class, 'updatePreferences']);
        Route::put('/onboarding-checklist', [TenantController::class, 'updateOnboardingChecklist']);
    });

    // Billing
    Route::prefix('v1/billing')->group(function () {
        Route::get('/plans', [BillingController::class, 'plans']);
        Route::get('/subscription', [BillingController::class, 'subscription']);
        Route::post('/change-plan', [BillingController::class, 'changePlan']);
        Route::post('/cancel', [BillingController::class, 'cancel']);
        Route::get('/usage', [BillingController::class, 'usage']);
        Route::get('/logs', [BillingController::class, 'auditLogs']);
    });

    // Dashboard Statistics
    Route::get('/v1/dashboard', function (Request $request) {
        $tenantId = (int) ($request->attributes->get('tenant_id') ?? 0);

        if ($tenantId <= 0) {
            return response()->json([
                'total_entities' => 0,
                'total_contacts' => 0,
                'total_proposals' => 0,
                'total_orders' => 0,
                'total_supplier_orders' => 0,
                'total_invoices' => 0,
                'total_calendar_events' => 0,
            ]);
        }

        return response()->json([
            'total_entities' => DB::table('entities')->where('company_id', $tenantId)->count(),
            'total_contacts' => DB::table('contacts')->where('company_id', $tenantId)->count(),
            'total_proposals' => DB::table('proposals')->where('company_id', $tenantId)->count(),
            'total_orders' => DB::table('orders')->where('company_id', $tenantId)->count(),
            'total_supplier_orders' => DB::table('supplier_orders')->where('company_id', $tenantId)->count(),
            'total_invoices' => DB::table('invoices')->where('company_id', $tenantId)->count(),
            'total_calendar_events' => DB::table('calendar_events')->where('company_id', $tenantId)->count(),
        ]);
    });

    // Configuration
    Route::prefix('v1/configuration')->group(function () {
        Route::get('/company', function (Request $request) {
            $company = $request->attributes->get('tenant');

            if (!$company) {
                return response()->json(['message' => 'Company not found'], 404);
            }

            return response()->json($company);
        });

        Route::put('/company', function (Request $request) {
            // Validate request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'logo' => 'nullable|file|image|max:4096',
                'address' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:20',
                'city' => 'nullable|string|max:100',
                'tax_id' => 'nullable|string|max:20',
            ]);

            $company = $request->attributes->get('tenant');

            if (!$company) {
                return response()->json(['message' => 'Company not found'], 404);
            }

            if ($request->hasFile('logo')) {
                if (!empty($company->logo) && str_starts_with($company->logo, '/storage/')) {
                    $previousPath = substr($company->logo, strlen('/storage/'));
                    Storage::disk('public')->delete($previousPath);
                }

                $logoPath = $request->file('logo')->store('company-logos', 'public');
                $validated['logo'] = '/storage/' . $logoPath;
            } else {
                unset($validated['logo']);
            }

            $company->fill($validated);
            $company->save();

            return response()->json(['message' => 'Company configuration updated successfully', 'company' => $company]);
        });
    });

    // VIES Integration (european VAT validation)
    Route::post('/v1/vies/{nif}', [EntityController::class, 'viesValidate']);

    // RESTful API v1
    Route::prefix('v1')->group(function () {

        // Entities
        Route::get('/entities', [EntityController::class, 'index']);
        Route::post('/entities', [EntityController::class, 'store']);
        Route::get('/entities/{id}', [EntityController::class, 'show']);
        Route::put('/entities/{id}', [EntityController::class, 'update']);
        Route::delete('/entities/{id}', [EntityController::class, 'destroy']);

        // Contacts
        Route::get('/contacts', [ContactController::class, 'index']);
        Route::post('/contacts', [ContactController::class, 'store']);
        Route::get('/contacts/{contact}', [ContactController::class, 'show']);
        Route::put('/contacts/{contact}', [ContactController::class, 'update']);
        Route::delete('/contacts/{contact}', [ContactController::class, 'destroy']);

        // Users
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::put('/users/{user}/password', [UserController::class, 'updatePassword']);
        Route::patch('/users/{user}/toggle', [UserController::class, 'toggle']);

        // Contact Functions (lookup)
        Route::get('/contact-functions', function () {
            return DB::table('contact_functions')
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'active']);
        });

        // Calendar Lookups
        Route::get('/calendar-types', function () {
            return DB::table('calendar_types')->orderBy('name')->get(['id', 'name']);
        });
        Route::get('/calendar-actions', function () {
            return DB::table('calendar_actions')->orderBy('name')->get(['id', 'name']);
        });

        // Articles
        Route::get('/articles', [ArticleController::class, 'index']);
        Route::post('/articles', [ArticleController::class, 'store']);
        Route::get('/articles/{article}', [ArticleController::class, 'show']);
        Route::put('/articles/{article}', [ArticleController::class, 'update']);
        Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);

        // Proposals
        Route::get('/proposals', [ProposalController::class, 'index']);
        Route::post('/proposals', [ProposalController::class, 'store']);
        Route::get('/proposals/{proposal}', [ProposalController::class, 'show']);
        Route::put('/proposals/{proposal}', [ProposalController::class, 'update']);
        Route::delete('/proposals/{proposal}', [ProposalController::class, 'destroy']);
        Route::get('/proposals/{proposal}/pdf', [ProposalController::class, 'pdf']);
        Route::get('/proposals/{proposal}/download-pdf', [ProposalController::class, 'downloadPdf']);
        Route::post('/proposals/{proposal}/convert-to-order', [ProposalController::class, 'convertToOrder']);

        // Orders
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::put('/orders/{order}', [OrderController::class, 'update']);
        Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
        Route::get('/orders/{order}/pdf', [OrderController::class, 'pdf']);
        Route::get('/orders/{order}/download-pdf', [OrderController::class, 'downloadPdf']);
        Route::post('/orders/{order}/convert-to-supplier-orders', [OrderController::class, 'convertToSupplierOrders']);

        // Supplier Orders
        Route::get('/supplier-orders', function () {
            return SupplierOrder::query()->paginate();
        });
        Route::post('/supplier-orders', function (Request $request) {
            $supplierOrder = SupplierOrder::create($request->all());
            return response()->json($supplierOrder, 201);
        });
        Route::get('/supplier-orders/{id}', function ($id) {
            return SupplierOrder::findOrFail($id);
        });
        Route::put('/supplier-orders/{id}', function (Request $request, $id) {
            $supplierOrder = SupplierOrder::findOrFail($id);
            $supplierOrder->update($request->all());
            return response()->json($supplierOrder);
        });
        Route::delete('/supplier-orders/{id}', function ($id) {
            SupplierOrder::findOrFail($id)->delete();
            return response()->json(null, 204);
        });

        // Invoices
        Route::get('/invoices', [InvoiceController::class, 'index']);
        Route::post('/invoices', [InvoiceController::class, 'store']);
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
        Route::put('/invoices/{invoice}', [InvoiceController::class, 'update']);
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy']);
        Route::post('/invoices/{invoice}/mark-as-paid', [InvoiceController::class, 'markAsPaid']);
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf']);
        Route::get('/invoices/{invoice}/download-pdf', [InvoiceController::class, 'downloadPdf']);
        Route::get('/invoices-template-pdf', [InvoiceController::class, 'templatePdf']);

        // Archive Documents
        Route::get('/archive-documents-pdf', [InvoiceController::class, 'archiveDocumentPdf']);

        // Calendar Events
        Route::get('/calendar-events', [CalendarController::class, 'index']);
        Route::post('/calendar-events', [CalendarController::class, 'store']);
        Route::get('/calendar-events/{calendarEvent}', [CalendarController::class, 'show']);
        Route::put('/calendar-events/{calendarEvent}', [CalendarController::class, 'update']);
        Route::delete('/calendar-events/{calendarEvent}', [CalendarController::class, 'destroy']);

        // Countries
        Route::get('/countries', function () {
            return ['data' => DB::table('countries')->orderBy('name')->get()];
        });
        Route::post('/countries', function (Request $request) {
            $country = Country::create($request->all());
            return response()->json($country, 201);
        });
        Route::get('/countries/{id}', function ($id) {
            return Country::findOrFail($id);
        });
        Route::put('/countries/{id}', function (Request $request, $id) {
            $country = Country::findOrFail($id);
            $country->update($request->all());
            return response()->json($country);
        });
        Route::delete('/countries/{id}', function ($id) {
            Country::findOrFail($id)->delete();
            return response()->json(null, 204);
        });

        // Tax Rates
        Route::get('/tax-rates', function () {
            return DB::table('tax_rates')->orderBy('name')->paginate();
        });
        Route::post('/tax-rates', function (Request $request) {
            $taxRate = TaxRate::create($request->all());
            return response()->json($taxRate, 201);
        });
        Route::get('/tax-rates/{id}', function ($id) {
            return TaxRate::findOrFail($id);
        });
        Route::put('/tax-rates/{id}', function (Request $request, $id) {
            $taxRate = TaxRate::findOrFail($id);
            $taxRate->update($request->all());
            return response()->json($taxRate);
        });
        Route::delete('/tax-rates/{id}', function ($id) {
            TaxRate::findOrFail($id)->delete();
            return response()->json(null, 204);
        });

        // Orders
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::put('/orders/{order}', [OrderController::class, 'update']);
        Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
        Route::get('/orders/{order}/pdf', [OrderController::class, 'pdf']);
        Route::get('/orders/{order}/download-pdf', [OrderController::class, 'downloadPdf']);
        Route::get('/orders-template-pdf', [OrderController::class, 'templatePdf']);
        Route::post('/orders/{order}/convert-to-supplier-orders', [OrderController::class, 'convertToSupplierOrders']);
        Route::get('/activity-logs', [ActivityLogController::class, 'index']);
        Route::get('/activity-logs/{activity}', [ActivityLogController::class, 'show']);
    });
});
