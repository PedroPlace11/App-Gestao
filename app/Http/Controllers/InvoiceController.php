<?php

namespace App\Http\Controllers;

use App\Events\InvoicePaid;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentProofMail;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with(['supplier:id,name,nif,email', 'supplierOrder:id,number', 'user:id,name']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->integer('supplier_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('issue_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('issue_date', '<=', $request->input('date_to'));
        }

        $perPage = min($request->integer('per_page', 15), 100);

        return response()->json($query->latest()->paginate($perPage));
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'number'            => 'required|string|unique:invoices,number',
            'issue_date'        => 'required|date',
            'due_date'          => 'required|date|after_or_equal:issue_date',
            'supplier_id'       => 'required|exists:entities,id',
            'supplier_order_id' => 'required|exists:supplier_orders,id',
            'total_value'       => 'required|numeric|min:0',
            'document'          => 'nullable|file|mimes:pdf|max:10240',
            'status'            => 'sometimes|in:pending,paid',
        ]);

        $validated['user_id'] = $request->user()->id;

        // Handle document file upload
        if ($request->hasFile('document')) {
            $validated['document'] = $request->file('document')
                ->store('invoices', 'private');
        } else {
            unset($validated['document']);
        }

        $invoice = Invoice::create($validated);

        return response()->json($invoice->load(['supplier', 'supplierOrder', 'user']), 201);
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json($invoice->load(['supplier', 'supplierOrder', 'user']));
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'number'            => 'sometimes|string|unique:invoices,number,' . $invoice->id,
            'issue_date'        => 'sometimes|date',
            'due_date'          => 'sometimes|date',
            'supplier_id'       => 'sometimes|exists:entities,id',
            'supplier_order_id' => 'sometimes|exists:supplier_orders,id',
            'total_value'       => 'sometimes|numeric|min:0',
            'document'          => 'nullable|file|mimes:pdf|max:10240',
            'status'            => 'sometimes|in:pending,paid',
        ]);

        // Replace document file if new one uploaded
        if ($request->hasFile('document')) {
            if ($invoice->document) {
                Storage::disk('private')->delete($invoice->document);
            }
            $validated['document'] = $request->file('document')
                ->store('invoices', 'private');
        } else {
            unset($validated['document']);
        }

        $invoice->update($validated);

        return response()->json($invoice->load(['supplier', 'supplierOrder', 'user']));
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        $invoice->delete();

        return response()->json(null, 204);
    }

    /**
     * Mark an invoice as paid and dispatch InvoicePaid event (sends email).
     */
    public function markAsPaid(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->status === 'paid') {
            return response()->json(['message' => 'Esta fatura já se encontra paga.'], 422);
        }

        $request->validate([
            'payment_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')
                ->store('invoices/proofs', 'private');
        }

        $invoice->update([
            'status'        => 'paid',
            'payment_proof' => $proofPath,
        ]);

        $invoice->load('supplier', 'user');

        // Send payment proof email to supplier if email is available
        if ($invoice->supplier?->email) {
            Mail::to($invoice->supplier->email)->queue(new PaymentProofMail($invoice));
        }

        InvoicePaid::dispatch($invoice);

        return response()->json([
            'message' => 'Fatura marcada como paga. Comprovativo de pagamento enviado por email.',
            'invoice' => $invoice,
        ]);
    }
}

