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
            'number'      => 'required|string|unique:supplier_orders,number',
            'date'        => 'required|date',
            'supplier_id' => 'required|exists:entities,id',
            'order_id'    => 'nullable|exists:orders,id',
            'status'      => 'sometimes|in:draft,closed,invoiced',
            'total_value' => 'required|numeric|min:0',
            'items'       => 'nullable|array',
            'notes'       => 'nullable|string',
        ]);

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
}
