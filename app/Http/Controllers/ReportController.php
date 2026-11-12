<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\StockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->date('from') ?? Carbon::now()->startOfMonth();
        $to = $request->date('to') ?? Carbon::now();

        $summary = $this->buildSummary($from, $to);

        return view('reports.index', compact('from', 'to', 'summary'));
    }

    public function stock(Request $request)
    {
        $warehouseId = $request->integer('warehouse_id') ?: null;
        $rows = $this->stockRows($warehouseId);
        $warehouses = Warehouse::orderBy('name')->get();
        $totalValue = $rows->sum(fn ($r) => $r['value']);

        return view('reports.stock', compact('rows', 'warehouses', 'warehouseId', 'totalValue'));
    }

    public function movements(Request $request)
    {
        [$from, $to] = $this->range($request);

        $movements = StockMovement::with(['product.category', 'warehouse', 'user'])
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->latest()
            ->get();

        return view('reports.movements', compact('movements', 'from', 'to'));
    }

    public function exportStockCsv(Request $request)
    {
        $warehouseId = $request->integer('warehouse_id') ?: null;
        $rows = $this->stockRows($warehouseId);

        $filename = 'etat-stock-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8 pour Excel
            fputcsv($out, ['SKU', 'Produit', 'Catégorie', 'Dépôt', 'Unité', 'Quantité', 'Seuil', 'Prix achat', 'Valeur'], ';');

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row['sku'], $row['name'], $row['category'], $row['warehouse'],
                    $row['unit'], $row['quantity'], $row['min_stock'],
                    number_format($row['purchase_price'], 2, ',', ''),
                    number_format($row['value'], 2, ',', ''),
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportMovementsCsv(Request $request)
    {
        [$from, $to] = $this->range($request);

        $movements = StockMovement::with(['product', 'warehouse', 'user'])
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->latest()->get();

        $filename = 'mouvements-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($movements) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Date', 'Type', 'SKU', 'Produit', 'Dépôt', 'Quantité', 'Avant', 'Après', 'Référence', 'Utilisateur'], ';');

            foreach ($movements as $m) {
                fputcsv($out, [
                    $m->created_at->format('d/m/Y H:i'),
                    __('movements.types.' . $m->type),
                    $m->product?->sku,
                    $m->product?->name,
                    $m->warehouse?->name,
                    $m->quantity,
                    $m->quantity_before,
                    $m->quantity_after,
                    $m->reference,
                    $m->user?->name,
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function stockPdf(Request $request)
    {
        $warehouseId = $request->integer('warehouse_id') ?: null;
        $rows = $this->stockRows($warehouseId);
        $totalValue = $rows->sum(fn ($r) => $r['value']);

        $pdf = Pdf::loadView('reports.pdf.stock', [
            'rows' => $rows,
            'totalValue' => $totalValue,
            'org' => $this->orgInfo(),
            'date' => now()->format('d/m/Y H:i'),
        ]);

        return $pdf->download('etat-stock-' . now()->format('Y-m-d') . '.pdf');
    }

    public function movementsPdf(Request $request)
    {
        [$from, $to] = $this->range($request);

        $movements = StockMovement::with(['product', 'warehouse', 'user'])
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->latest()->get();

        $pdf = Pdf::loadView('reports.pdf.movements', [
            'movements' => $movements,
            'org' => $this->orgInfo(),
            'from' => $from,
            'to' => $to,
            'date' => now()->format('d/m/Y H:i'),
        ]);

        return $pdf->download('mouvements-' . now()->format('Y-m-d') . '.pdf');
    }

    protected function stockRows(?int $warehouseId): \Illuminate\Support\Collection
    {
        $products = Product::with(['category', 'stocks.warehouse'])->orderBy('name')->get();

        return $products->flatMap(function ($product) use ($warehouseId) {
            $stocks = $warehouseId
                ? $product->stocks->where('warehouse_id', $warehouseId)
                : $product->stocks;

            if ($stocks->isEmpty()) {
                return [[
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'category' => $product->category?->name ?? '—',
                    'warehouse' => '—',
                    'unit' => $product->unit,
                    'quantity' => 0,
                    'min_stock' => $product->min_stock,
                    'purchase_price' => (float) $product->purchase_price,
                    'value' => 0.0,
                    'low' => $product->min_stock > 0 && 0 <= $product->min_stock,
                ]];
            }

            return $stocks->map(fn ($stock) => [
                'sku' => $product->sku,
                'name' => $product->name,
                'category' => $product->category?->name ?? '—',
                'warehouse' => $stock->warehouse?->name ?? '—',
                'unit' => $product->unit,
                'quantity' => (int) $stock->quantity,
                'min_stock' => $product->min_stock,
                'purchase_price' => (float) $product->purchase_price,
                'value' => (float) $stock->quantity * (float) $product->purchase_price,
                'low' => $product->min_stock > 0 && $stock->quantity <= $product->min_stock,
            ]);
        });
    }

    protected function buildSummary(Carbon $from, Carbon $to): array
    {
        $base = StockMovement::whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        $in = (clone $base)->where('type', StockMovement::TYPE_IN)->sum('quantity');
        $out = (clone $base)->where('type', StockMovement::TYPE_OUT)->sum('quantity');
        $count = (clone $base)->count();

        $topProducts = (clone $base)->selectRaw('product_id, SUM(quantity) as qty')
            ->groupBy('product_id')->orderByDesc('qty')->take(5)
            ->with('product')->get();

        $byCategory = Category::withCount('products')->get();

        return compact('in', 'out', 'count', 'topProducts', 'byCategory');
    }

    protected function range(Request $request): array
    {
        return [
            $request->date('from') ?? Carbon::now()->startOfMonth(),
            $request->date('to') ?? Carbon::now(),
        ];
    }
}
