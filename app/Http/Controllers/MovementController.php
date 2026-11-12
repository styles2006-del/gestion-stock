<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\Request;

class MovementController extends Controller
{
    public function __construct(protected StockService $stockService) {}

    public function index(Request $request)
    {
        $query = StockMovement::with(['product', 'warehouse', 'user']);

        if ($search = $request->string('q')->toString()) {
            $query->where(fn ($q) => $q->where('reference', 'like', "%{$search}%")
                ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")));
        }

        if ($type = $request->string('type')->toString()) {
            $query->where('type', $type);
        }

        if ($warehouseId = $request->integer('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($from = $request->date('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->date('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $movements = $query->latest()->paginate(25)->withQueryString();

        $warehouses = Warehouse::orderBy('name')->get();

        return view('movements.index', compact('movements', 'warehouses'));
    }

    public function create(Request $request)
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        $selectedProduct = $request->integer('product_id') ?: null;
        $selectedType = $request->string('type')->toString() ?: StockMovement::TYPE_IN;

        return view('movements.create', compact('products', 'warehouses', 'selectedProduct', 'selectedType'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        $data['user_id'] = $request->user()->id;

        try {
            $this->stockService->move($data);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('movements.index')
            ->with('success', __('flash.movement_created'));
    }

    public function transferForm()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('movements.transfer', compact('products', 'warehouses'));
    }

    public function transfer(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        try {
            $this->stockService->transfer(
                $data['product_id'],
                $data['from_warehouse_id'],
                $data['to_warehouse_id'],
                $data['quantity'],
                $request->user()->id,
                $data['note'] ?? null,
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('movements.index')
            ->with('success', __('flash.transfer_created'));
    }
}
