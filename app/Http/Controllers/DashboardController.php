<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true);
        $totalProducts = (clone $products)->count();

        $totalStockValue = (clone $products)
            ->selectRaw('COALESCE(SUM(purchase_price * (
                SELECT COALESCE(SUM(quantity),0) FROM stocks WHERE stocks.product_id = products.id
            )), 0) as total')
            ->value('total');

        $lowStockProducts = (clone $products)
            ->whereColumn('min_stock', '>', 0)
            ->get()
            ->filter(fn ($p) => $p->isLowStock());

        $totalMovements = StockMovement::count();

        $todayMovements = StockMovement::whereDate('created_at', Carbon::today())->count();

        $inThisMonth = StockMovement::where('type', StockMovement::TYPE_IN)
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->sum('quantity');

        $outThisMonth = StockMovement::where('type', StockMovement::TYPE_OUT)
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->sum('quantity');

        // Graphique : mouvements des 14 derniers jours
        $chartData = $this->movementChart();

        // Derniers mouvements
        $recentMovements = StockMovement::with(['product', 'warehouse', 'user'])
            ->latest()->take(8)->get();

        // Documents en attente
        $pendingDocuments = Document::with('supplier')
            ->whereIn('status', [Document::STATUS_DRAFT, Document::STATUS_CONFIRMED])
            ->latest()->take(5)->get();

        $warehousesCount = Warehouse::where('is_active', true)->count();

        return view('dashboard', compact(
            'totalProducts', 'totalStockValue', 'lowStockProducts', 'totalMovements',
            'todayMovements', 'inThisMonth', 'outThisMonth', 'chartData',
            'recentMovements', 'pendingDocuments', 'warehousesCount'
        ));
    }

    protected function movementChart(): array
    {
        $labels = [];
        $in = [];
        $out = [];

        for ($i = 13; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $labels[] = $day->format('d/m');

            $in[] = (int) StockMovement::where('type', StockMovement::TYPE_IN)
                ->whereDate('created_at', $day)->sum('quantity');

            $out[] = (int) StockMovement::where('type', StockMovement::TYPE_OUT)
                ->whereDate('created_at', $day)->sum('quantity');
        }

        return ['labels' => $labels, 'in' => $in, 'out' => $out];
    }
}
