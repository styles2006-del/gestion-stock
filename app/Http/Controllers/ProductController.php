<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\BarcodeService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'stocks'])
            ->withCount('stocks');

        if ($search = $request->string('q')->toString()) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%"));
        }

        if ($categoryId = $request->integer('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($request->boolean('low')) {
            $query->whereColumn('min_stock', '>', 0);
        }

        if ($request->boolean('inactive')) {
            $query->where('is_active', false);
        }

        $products = $query->orderBy('name')->paginate(20)->withQueryString();

        // Quantités totales
        $products->getCollection()->transform(function ($p) {
            $p->total_quantity = $p->stocks->sum('quantity');

            return $p;
        });

        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('products.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['barcode'] = $data['barcode'] ?? $this->generateBarcode();

        $product = Product::create($data);

        return redirect()->route('products.show', $product)
            ->with('success', __('flash.product_created'));
    }

    public function show(Product $product)
    {
        $product->load(['category', 'supplier', 'stocks.warehouse']);

        $movements = $product->movements()
            ->with(['warehouse', 'user'])
            ->latest()->take(20)->get();

        $barcodeService = app(BarcodeService::class);

        $barcodeSvg = $product->barcode
            ? $barcodeService->barcodeSvg($product->barcode)
            : null;

        $qrSvg = $barcodeService->qrSvg(
            route('products.show', $product)
        );

        return view('products.show', compact('product', 'movements', 'barcodeSvg', 'qrSvg'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product);
        $product->update($data);

        return redirect()->route('products.show', $product)
            ->with('success', __('flash.product_updated'));
    }

    public function destroy(Product $product)
    {
        if ($product->total_quantity > 0) {
            return back()->with('error', __('flash.product_not_empty'));
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', __('flash.product_deleted'));
    }

    protected function validated(Request $request, ?Product $product = null): array
    {
        $uniqueSku = $product ? ',' . $product->id : '';
        $uniqueBarcode = $product ? ',' . $product->id : '';

        return $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku' . $uniqueSku,
            'barcode' => 'nullable|string|max:100|unique:products,barcode' . $uniqueBarcode,
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'required|string|max:20',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }

    protected function generateBarcode(): string
    {
        do {
            $code = 'ART' . str_pad((string) random_int(1, 999999999), 9, '0', STR_PAD_LEFT);
        } while (Product::where('barcode', $code)->exists());

        return $code;
    }
}
