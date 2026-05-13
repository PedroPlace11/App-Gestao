<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\SupplierOrder;
use App\Services\OrderPdfService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(private OrderPdfService $pdfService)
    {
    }

    /**
     * Display a paginated listing of orders.
     * Filters: status, client_id, search (number)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['entity:id,name,nif', 'user:id,name']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->lower();
            $query->whereRaw('LOWER(number) LIKE ?', ["%{$search}%"]);
        }

        $perPage = min($request->integer('per_page', 15), 100);

        return response()->json($query->latest()->paginate($perPage));
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'number'      => 'required|string|unique:orders,number',
            'date'        => 'required|date',
            'client_id'   => 'required|exists:entities,id',
            'total_value' => 'required|numeric|min:0',
            'status'      => 'sometimes|in:draft,closed',
            'proposal_id' => 'nullable|exists:proposals,id',
            'items'       => 'nullable|array',
            'notes'       => 'nullable|string',
        ]);

        $validated['user_id'] = $request->user()->id;

        $order = Order::create($validated);

        OrderCreated::dispatch($order);

        return response()->json($order->load(['entity:id,name,nif', 'user:id,name']), 201);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): JsonResponse
    {
        return response()->json($order->load(['entity:id,name,nif', 'user:id,name', 'supplierOrders', 'invoices']));
    }

    /**
     * Update the specified order.
     */
    public function update(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'number'      => 'sometimes|string|unique:orders,number,' . $order->id,
            'date'        => 'sometimes|date',
            'client_id'   => 'sometimes|exists:entities,id',
            'total_value' => 'sometimes|numeric|min:0',
            'status'      => 'sometimes|in:draft,closed',
            'proposal_id' => 'nullable|exists:proposals,id',
            'items'       => 'nullable|array',
            'notes'       => 'nullable|string',
        ]);

        $order->update($validated);

        return response()->json($order->load(['entity:id,name,nif', 'user:id,name']));
    }

    /**
     * Remove the specified order.
     */
    public function destroy(Order $order): JsonResponse
    {
        $order->delete();

        return response()->json(null, 204);
    }

    /**
     * Convert order line items into SupplierOrders grouped by supplier.
     *
     * Each item in $order->items should have a 'supplier_id' field.
     * Items without a supplier_id are skipped.
     * One SupplierOrder is created per distinct supplier_id.
     */
    public function convertToSupplierOrders(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'date'   => 'required|date',
            'prefix' => 'nullable|string|max:20',
        ]);

        $items = is_array($order->items) ? $order->items : [];

        // Group items by supplier_id
        $grouped = collect($items)
            ->filter(fn ($item) => !empty($item['supplier_id']))
            ->groupBy('supplier_id');

        if ($grouped->isEmpty()) {
            return response()->json(['message' => 'Nenhum artigo com fornecedor definido.'], 422);
        }

        $created = [];
        $prefix  = $request->input('prefix', 'EF');
        $date    = $request->input('date');
        $userId  = $request->user()->id;
        $seq     = SupplierOrder::count();

        DB::transaction(function () use ($grouped, $order, $prefix, $date, $userId, &$created, &$seq) {
            foreach ($grouped as $supplierId => $supplierItems) {
                $seq++;
                $number     = sprintf('%s-%05d', $prefix, $seq);
                $totalValue = $supplierItems->sum(fn ($i) => ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0));

                $supplierOrder = SupplierOrder::create([
                    'number'      => $number,
                    'date'        => $date,
                    'supplier_id' => $supplierId,
                    'order_id'    => $order->id,
                    'status'      => 'draft',
                    'total_value' => $totalValue,
                    'items'       => $supplierItems->values()->all(),
                    'user_id'     => $userId,
                ]);

                $created[] = $supplierOrder->load(['supplier:id,name,nif']);
            }
        });

        return response()->json(['supplier_orders' => $created], 201);
    }

    /**
     * Generate and download PDF for the order.
     */
    public function pdf(Order $order)
    {
        $pdf = $this->pdfService->generate($order);

        $filename = "encomenda_{$order->number}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Download PDF using the Pdf facade directly (alias for pdf()).
     */
    public function downloadPdf(Order $order): \Illuminate\Http\Response
    {
        return $this->pdfService->generate($order)->download("encomenda-{$order->number}.pdf");
    }

    /**
     * Generate a PDF for order examples from query payload.
     */
    public function templatePdf(Request $request): \Illuminate\Http\Response
    {
        $validated = $request->validate([
            'number' => 'required|string|max:50',
            'date' => 'required|date',
            'client_id' => 'nullable|integer',
            'client_name' => 'nullable|string|max:255',
            'status' => 'required|string|in:draft,closed',
            'total_value' => 'required|numeric|min:0',
        ]);

        $order = new Order($validated);
        $order->id = 1; // Dummy ID for example orders
        $order->entity = (object)['name' => $validated['client_name'] ?? 'Cliente Exemplo'];

        $pdf = $this->pdfService->generate($order);

        return $pdf->download("encomenda-{$validated['number']}.pdf");
    }
}

