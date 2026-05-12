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
use App\Models\Country;
use App\Models\SupplierOrder;
use App\Models\TaxRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

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

    // Dashboard Statistics
    Route::get('/v1/dashboard', function () {
        return response()->json([
            'total_entities' => DB::table('entities')->count(),
            'total_contacts' => DB::table('contacts')->count(),
            'total_proposals' => DB::table('proposals')->count(),
            'total_orders' => DB::table('orders')->count(),
            'total_supplier_orders' => DB::table('supplier_orders')->count(),
            'total_invoices' => DB::table('invoices')->count(),
            'total_calendar_events' => DB::table('calendar_events')->count(),
        ]);
    });

    // Configuration
    Route::prefix('v1/configuration')->group(function () {
        Route::get('/company', function () {
            // Return company configuration
            return response()->json([
                'message' => 'Company configuration endpoint',
            ]);
        });

        Route::put('/company', function (Request $request) {
            // Update company configuration
            return response()->json([
                'message' => 'Company configuration updated',
            ]);
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
            return SupplierOrder::paginate();
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

        // Users
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::put('/users/{user}/password', [UserController::class, 'updatePassword']);
        Route::post('/users/{user}/toggle', [UserController::class, 'toggle']);

        // Activity Logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index']);
        Route::get('/activity-logs/{activity}', [ActivityLogController::class, 'show']);
    });
});
