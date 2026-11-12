<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentLine;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\StockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    public function __construct(protected StockService $stockService) {}

    public function index(Request $request)
    {
        $query = Document::with(['supplier', 'warehouse', 'user']);

        if ($type = $request->string('type')->toString()) {
            $query->where('type', $type);
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($search = $request->string('q')->toString()) {
            $query->where(fn ($q) => $q->where('number', 'like', "%{$search}%")
                ->orWhere('client_name', 'like', "%{$search}%")
                ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%")));
        }

        $documents = $query->latest('document_date')->latest('id')->paginate(20)->withQueryString();

        return view('documents.index', compact('documents'));
    }

    public function create(Request $request)
    {
        $type = $request->string('type')->toString() ?: Document::TYPE_PURCHASE_ORDER;

        if (! in_array($type, Document::TYPES, true)) {
            $type = Document::TYPE_PURCHASE_ORDER;
        }

        $document = new Document([
            'type' => $type,
            'status' => Document::STATUS_DRAFT,
            'document_date' => now()->toDateString(),
            'tax_rate' => (float) $this->orgInfo()['tax_rate'],
            'currency' => $this->orgInfo()['currency'],
            'number' => Document::nextNumber($type),
        ]);

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('documents.create', compact('document', 'suppliers', 'warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:' . implode(',', Document::TYPES),
            'status' => 'required|in:' . implode(',', Document::STATUSES),
            'supplier_id' => 'nullable|exists:suppliers,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'client_name' => 'nullable|string|max:255',
            'client_address' => 'nullable|string|max:255',
            'document_date' => 'required|date',
            'due_date' => 'nullable|date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'currency' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|integer|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
        ]);

        $document = DB::transaction(function () use ($data, $request) {
            $document = Document::create([
                'number' => Document::nextNumber($data['type']),
                'type' => $data['type'],
                'status' => $data['status'],
                'supplier_id' => $data['supplier_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'client_name' => $data['client_name'] ?? null,
                'client_address' => $data['client_address'] ?? null,
                'document_date' => $data['document_date'],
                'due_date' => $data['due_date'] ?? null,
                'tax_rate' => $data['tax_rate'] ?? 0,
                'currency' => $data['currency'] ?? 'EUR',
                'notes' => $data['notes'] ?? null,
                'user_id' => $request->user()->id,
            ]);

            foreach ($data['lines'] as $line) {
                $document->lines()->create([
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'total' => $line['quantity'] * $line['unit_price'],
                ]);
            }

            $document->recalculateTotals();

            return $document;
        });

        return redirect()->route('documents.show', $document)
            ->with('success', __('flash.document_created'));
    }

    public function show(Document $document)
    {
        $document->load(['lines.product', 'supplier', 'warehouse', 'user']);

        return view('documents.show', [
            'document' => $document,
            'org' => $this->orgInfo(),
        ]);
    }

    public function edit(Document $document)
    {
        $document->load('lines');
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('documents.edit', compact('document', 'suppliers', 'warehouses', 'products'));
    }

    public function update(Request $request, Document $document)
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', Document::STATUSES),
            'supplier_id' => 'nullable|exists:suppliers,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'client_name' => 'nullable|string|max:255',
            'client_address' => 'nullable|string|max:255',
            'document_date' => 'required|date',
            'due_date' => 'nullable|date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|integer|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data, $document) {
            $document->update([
                'status' => $data['status'],
                'supplier_id' => $data['supplier_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'client_name' => $data['client_name'] ?? null,
                'client_address' => $data['client_address'] ?? null,
                'document_date' => $data['document_date'],
                'due_date' => $data['due_date'] ?? null,
                'tax_rate' => $data['tax_rate'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $document->lines()->delete();

            foreach ($data['lines'] as $line) {
                $document->lines()->create([
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'total' => $line['quantity'] * $line['unit_price'],
                ]);
            }

            $document->recalculateTotals();
        });

        return redirect()->route('documents.show', $document)
            ->with('success', __('flash.document_updated'));
    }

    public function destroy(Document $document)
    {
        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', __('flash.document_deleted'));
    }

    /**
     * Applique les mouvements de stock liés au document.
     */
    public function applyStock(Document $document)
    {
        try {
            $this->stockService->applyDocumentStock($document);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('flash.stock_applied'));
    }

    public function pdf(Document $document)
    {
        $document->load(['lines.product', 'supplier', 'warehouse', 'user']);

        $pdf = Pdf::loadView('documents.pdf', [
            'document' => $document,
            'org' => $this->orgInfo(),
        ]);

        return $pdf->download($document->number . '.pdf');
    }

    public function print(Document $document)
    {
        $document->load(['lines.product', 'supplier', 'warehouse', 'user']);

        return view('documents.print', [
            'document' => $document,
            'org' => $this->orgInfo(),
        ]);
    }
}
