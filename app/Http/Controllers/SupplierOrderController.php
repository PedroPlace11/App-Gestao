<?php

namespace App\Http\Controllers;

use App\Models\SupplierOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierOrderController extends Controller
{
    /**
     * Display a paginated listing of supplier orders.
     * Filters: status, supplier_id, search (number)
     */
    public function index(Request $request): JsonResponse
    {
        $query = SupplierOrder::with(['supplier:id,name,nif', 'user:id,name', 'order:id,number']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->integer('supplier_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->lower();
            $query->whereRaw('LOWER(number) LIKE ?', ["%{$search}%"]);
        }

        $perPage = min($request->integer('per_page', 15), 100);

        return response()->json($query->latest()->paginate($perPage));
    }

    /**
     * Store a newly created supplier order.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'number'      => 'nullable|string|unique:supplier_orders,number',
            'date'        => 'required|date',
            'supplier_id' => 'required|exists:entities,id',
            'order_id'    => 'nullable|exists:orders,id',
            'status'      => 'sometimes|in:draft,closed,invoiced',
            'total_value' => 'required|numeric|min:0',
            'items'       => 'nullable|array',
            'notes'       => 'nullable|string',
        ]);

        // If no number is provided, generate one using ECF-YYYY-XXX format.
        if (empty($validated['number'])) {
            $validated['number'] = $this->generateNextSupplierOrderNumber();
        }

        $validated['user_id'] = $request->user()->id;

        $supplierOrder = SupplierOrder::create($validated);

        return response()->json($supplierOrder->load(['supplier:id,name,nif', 'user:id,name', 'order:id,number']), 201);
    }

    /**
     * Display the specified supplier order.
     */
    public function show(SupplierOrder $supplierOrder): JsonResponse
    {
        return response()->json($supplierOrder->load(['supplier:id,name,nif', 'user:id,name', 'order:id,number', 'invoices']));
    }

    /**
     * Update the specified supplier order.
     */
    public function update(Request $request, SupplierOrder $supplierOrder): JsonResponse
    {
        $validated = $request->validate([
            'number'      => 'sometimes|string|unique:supplier_orders,number,' . $supplierOrder->id,
            'date'        => 'sometimes|date',
            'supplier_id' => 'sometimes|exists:entities,id',
            'order_id'    => 'nullable|exists:orders,id',
            'status'      => 'sometimes|in:draft,closed,invoiced',
            'total_value' => 'sometimes|numeric|min:0',
            'items'       => 'nullable|array',
            'notes'       => 'nullable|string',
        ]);

        $supplierOrder->update($validated);

        return response()->json($supplierOrder->load(['supplier:id,name,nif', 'user:id,name', 'order:id,number']));
    }

    /**
     * Remove the specified supplier order.
     */
    public function destroy(SupplierOrder $supplierOrder): JsonResponse
    {
        // Check if supplier order has invoices
        if ($supplierOrder->invoices()->exists()) {
            return response()->json(['message' => 'Não é possível eliminar uma encomenda de fornecedor que tem faturas associadas.'], 422);
        }

        $supplierOrder->delete();

        return response()->json(null, 204);
    }

    private function generateNextSupplierOrderNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "ECF-{$year}-";

        $lastSupplierOrder = SupplierOrder::query()
            ->where('number', 'like', $prefix . '%')
            ->orderByDesc('number')
            ->first();

        $nextSequence = 1;

        if ($lastSupplierOrder && preg_match('/^ECF-' . preg_quote($year, '/') . '-(\d+)$/', $lastSupplierOrder->number, $matches)) {
            $nextSequence = ((int) $matches[1]) + 1;
        }

        do {
            $number = sprintf('%s%03d', $prefix, $nextSequence);
            $exists = SupplierOrder::query()->where('number', $number)->exists();
            $nextSequence++;
        } while ($exists);

        return $number;
    }
}
