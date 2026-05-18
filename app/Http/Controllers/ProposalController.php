<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Events\ProposalCreated;
use App\Models\Order;
use App\Models\Proposal;
use App\Services\ProposalPdfService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProposalController extends Controller
{
    public function __construct(private ProposalPdfService $pdfService)
    {
    }

    /**
     * Display a paginated listing of proposals.
     * Filters: status, client_id, search (number)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Proposal::with(['entity:id,name,nif', 'user:id,name']);

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
     * Store a newly created proposal with optional line items.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'number'        => 'nullable|string|unique:proposals,number',
            'date'          => 'required|date',
            'client_id'     => 'required|exists:entities,id',
            'validity'      => 'required|date|after_or_equal:date',
            'total_value'   => 'required|numeric|min:0',
            'status'        => 'sometimes|in:draft,closed',
            'notes'         => 'nullable|string',
            'items'         => 'nullable|array',
            'items.*.article_id'  => 'nullable|exists:articles,id',
            'items.*.description' => 'required_without:items.*.article_id|string|max:255',
            'items.*.quantity'    => 'required|numeric|min:0.001',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.tax_rate'    => 'required|numeric|min:0|max:100',
        ]);

        // If no number is provided, generate one using PROP-YYYY-XXX format.
        if (empty($validated['number'])) {
            $validated['number'] = $this->generateNextProposalNumber();
        }

        $validated['user_id'] = $request->user()->id;

        $proposal = Proposal::create($validated);

        ProposalCreated::dispatch($proposal);

        return response()->json($proposal->load(['entity:id,name,nif', 'user:id,name']), 201);
    }

    /**
     * Display the specified proposal.
     */
    public function show(Proposal $proposal): JsonResponse
    {
        return response()->json($proposal->load(['entity:id,name,nif', 'user:id,name', 'orders']));
    }

    /**
     * Update the specified proposal.
     */
    public function update(Request $request, Proposal $proposal): JsonResponse
    {
        $validated = $request->validate([
            'number'        => 'sometimes|string|unique:proposals,number,' . $proposal->id,
            'date'          => 'sometimes|date',
            'client_id'     => 'sometimes|exists:entities,id',
            'validity'      => 'sometimes|date',
            'total_value'   => 'sometimes|numeric|min:0',
            'status'        => 'sometimes|in:draft,closed',
            'notes'         => 'nullable|string',
            'items'         => 'nullable|array',
            'items.*.article_id'  => 'nullable|exists:articles,id',
            'items.*.description' => 'required_without:items.*.article_id|string|max:255',
            'items.*.quantity'    => 'required|numeric|min:0.001',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.tax_rate'    => 'required|numeric|min:0|max:100',
        ]);

        $proposal->update($validated);

        return response()->json($proposal->load(['entity:id,name,nif', 'user:id,name']));
    }

    /**
     * Remove the specified proposal.
     */
    public function destroy(Proposal $proposal): JsonResponse
    {
        $proposal->delete();

        return response()->json(null, 204);
    }

    /**
     * Convert a proposal to an order.
     * Creates an Order from the Proposal data, marks proposal as closed.
     */
    public function convertToOrder(Request $request, Proposal $proposal): JsonResponse
    {
        $request->validate([
            'order_number' => 'required|string|unique:orders,number',
            'date'         => 'required|date',
        ]);

        $order = Order::create([
            'number'      => $request->input('order_number'),
            'date'        => $request->input('date'),
            'client_id'   => $proposal->client_id,
            'total_value' => $proposal->total_value,
            'status'      => 'draft',
            'proposal_id' => $proposal->id,
            'items'       => $proposal->items,
            'notes'       => $proposal->notes ?? null,
            'user_id'     => $request->user()->id,
        ]);

        $proposal->update(['status' => 'closed']);

        OrderCreated::dispatch($order);

        return response()->json([
            'order'    => $order->load(['entity:id,name,nif', 'user:id,name']),
            'proposal' => $proposal->load(['entity:id,name,nif', 'user:id,name']),
        ], 201);
    }

    /**
     * Generate and download PDF for the proposal.
     */
    public function pdf(Proposal $proposal)
    {
        $pdf = $this->pdfService->generate($proposal);

        $filename = "proposta_{$proposal->number}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Download PDF using the Pdf facade directly (alias for pdf()).
     */
    public function downloadPdf(Proposal $proposal): \Illuminate\Http\Response
    {
        return $this->pdfService->generate($proposal)->download("proposta-{$proposal->number}.pdf");
    }

    private function generateNextProposalNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "PROP-{$year}-";

        $lastProposal = Proposal::query()
            ->where('number', 'like', $prefix . '%')
            ->orderByDesc('number')
            ->first();

        $nextSequence = 1;

        if ($lastProposal && preg_match('/^PROP-' . preg_quote($year, '/') . '-(\d+)$/', $lastProposal->number, $matches)) {
            $nextSequence = ((int) $matches[1]) + 1;
        }

        do {
            $number = sprintf('%s%03d', $prefix, $nextSequence);
            $exists = Proposal::query()->where('number', $number)->exists();
            $nextSequence++;
        } while ($exists);

        return $number;
    }
}

