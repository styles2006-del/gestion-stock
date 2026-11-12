<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Document;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ChatbotController extends Controller
{
    public function ask(Request $request): JsonResponse
    {
        $message = (string) $request->input('message', '');
        $normalized = $this->normalize($message);
        $locale = app()->getLocale();
        $isFr = $locale === 'fr';

        $reply = $this->respond($normalized, $message, $isFr);
        $suggestions = $this->suggestions($isFr);

        return response()->json([
            'reply' => $reply,
            'suggestions' => $suggestions,
        ]);
    }

    protected function respond(string $normalized, string $raw, bool $isFr): string
    {
        if ($normalized === '' || in_array($normalized, ['?', 'help', 'aide'], true)) {
            return $this->help($isFr);
        }

        // Salutations
        if ($this->containsAny($normalized, ['bonjour', 'salut', 'coucou', 'hello', 'hi', 'hey', 'bonsoir'])) {
            return $isFr
                ? "Bonjour ! Je suis l'assistant de gestion de stock. Posez-moi une question, par exemple : \"quels sont les articles disponibles ?\""
                : "Hello! I'm the stock management assistant. Ask me something, e.g. \"which articles are available?\"";
        }

        // Aide
        if ($this->containsAny($normalized, ['aide', 'help', 'que peux', 'quoi faire', 'commandes', 'questions', 'fonctionnalites'])) {
            return $this->help($isFr);
        }

        $product = $this->findProduct($raw);

        // Informations / prix / stock d'un article précis
        if ($product && $this->containsAny($normalized, ['stock', 'quantite', 'quantity', 'prix', 'price', 'info', 'details', 'fiche', 'article'])) {
            return $this->productInfo($product, $isFr);
        }

        // Stock faible / ruptures
        if ($this->containsAny($normalized, ['stock faible', 'rupture', 'seuil', 'low stock', 'alerte', 'manque', 'reapprovisionner', 'reappro'])) {
            return $this->lowStock($isFr);
        }

        // Articles disponibles / en stock
        if ($this->containsAny($normalized, ['disponible', 'disponibles', 'articles', 'produits', 'liste', 'inventory', 'stock disponible', 'en stock'])) {
            // si un produit précis est mentionné, on donne son info
            if ($product) {
                return $this->productInfo($product, $isFr);
            }

            return $this->availableProducts($isFr);
        }

        // Catégories
        if ($this->containsAny($normalized, ['categorie', 'categories', 'famille', 'familles'])) {
            return $this->categories($isFr);
        }

        // Entrepôts / dépôts
        if ($this->containsAny($normalized, ['depot', 'depots', 'entrepot', 'entrepots', 'warehouse', 'warehouses'])) {
            return $this->warehouses($isFr);
        }

        // Fournisseurs
        if ($this->containsAny($normalized, ['fournisseur', 'fournisseurs', 'supplier', 'suppliers'])) {
            return $this->suppliers($isFr);
        }

        // Mouvements
        if ($this->containsAny($normalized, ['mouvement', 'mouvements', 'historique', 'entree', 'sortie', 'transfert', 'movement', 'movements'])) {
            return $this->movements($isFr);
        }

        // Documents
        if ($this->containsAny($normalized, ['document', 'documents', 'facture', 'bon', 'commande', 'attente', 'pending'])) {
            return $this->documents($isFr);
        }

        // Statistiques / tableau de bord
        if ($this->containsAny($normalized, ['statistique', 'statistiques', 'stats', 'tableau de bord', 'dashboard', 'resume', 'total', 'valeur stock', 'valeur'])) {
            return $this->stats($isFr);
        }

        // Recherche d'un article par nom même sans mot-clé explicite
        if ($product) {
            return $this->productInfo($product, $isFr);
        }

        return $this->fallback($isFr);
    }

    protected function help(bool $isFr): string
    {
        if ($isFr) {
            return "Je peux répondre à plusieurs questions :\n"
                ."• Articles disponibles / en stock\n"
                ."• Stock d'un article précis (ex. : \"stock de Crayon\")\n"
                ."• Articles en stock faible / ruptures\n"
                ."• Catégories, entrepôts, fournisseurs\n"
                ."• Derniers mouvements de stock\n"
                ."• Documents en attente\n"
                ."• Statistiques du tableau de bord\n\n"
                .'Tapez simplement votre question.';
        }

        return "I can answer several questions:\n"
            ."• Available / in-stock articles\n"
            ."• Stock of a specific article (e.g. \"stock of Pencil\")\n"
            ."• Low stock / out of stock items\n"
            ."• Categories, warehouses, suppliers\n"
            ."• Recent stock movements\n"
            ."• Pending documents\n"
            ."• Dashboard statistics\n\n"
            .'Just type your question.';
    }

    protected function availableProducts(bool $isFr): string
    {
        $products = Product::where('is_active', true)
            ->with('stocks')
            ->orderBy('name')
            ->get();

        if ($products->isEmpty()) {
            return $isFr
                ? 'Aucun article enregistré pour le moment.'
                : 'No articles registered yet.';
        }

        $inStock = $products->filter(fn (Product $p) => $p->total_quantity > 0);
        $lines = [];
        $limit = 15;

        foreach ($inStock->take($limit) as $p) {
            $lines[] = "- {$p->name} ({$p->sku}) — {$p->total_quantity} {$p->unit}";
        }

        $reply = $isFr
            ? "Articles disponibles (en stock) : {$inStock->count()} sur {$products->count()} articles actifs.\n"
            : "Available articles (in stock): {$inStock->count()} of {$products->count()} active articles.\n";

        if ($lines) {
            $reply .= implode("\n", $lines);
            if ($inStock->count() > $limit) {
                $remaining = $inStock->count() - $limit;
                $reply .= "\n… ".($isFr ? "et {$remaining} autre(s)" : "and {$remaining} more");
            }
        } else {
            $reply .= $isFr
                ? 'Aucun article en stock actuellement.'
                : 'No articles in stock currently.';
        }

        return $reply;
    }

    protected function productInfo(Product $product, bool $isFr): string
    {
        $product->loadMissing(['category', 'supplier', 'stocks.warehouse']);

        $category = $product->category?->name ?? ($isFr ? 'Aucune' : 'None');
        $supplier = $product->supplier?->name ?? ($isFr ? 'Aucun' : 'None');
        $status = $product->is_active
            ? ($isFr ? 'Actif' : 'Active')
            : ($isFr ? 'Inactif' : 'Inactive');

        $stockLines = [];
        foreach ($product->stocks as $stock) {
            $stockLines[] = "  • {$stock->warehouse?->name}: {$stock->quantity} {$product->unit}";
        }

        $reply = $isFr
            ? "Article : {$product->name}\n"
            : "Article: {$product->name}\n";
        $reply .= "SKU : {$product->sku}\n";
        $reply .= ($isFr ? 'Catégorie' : 'Category')." : {$category}\n";
        $reply .= ($isFr ? 'Fournisseur' : 'Supplier')." : {$supplier}\n";
        $reply .= ($isFr ? "Prix d'achat" : 'Purchase price')." : {$product->purchase_price}\n";
        $reply .= ($isFr ? 'Prix de vente' : 'Sale price')." : {$product->sale_price}\n";
        $reply .= ($isFr ? 'Stock total' : 'Total stock')." : {$product->total_quantity} {$product->unit}\n";
        $reply .= ($isFr ? 'Statut' : 'Status')." : {$status}\n";

        if ($stockLines) {
            $reply .= ($isFr ? "Par entrepôt :\n" : "By warehouse:\n").implode("\n", $stockLines);
        }

        if ($product->isLowStock()) {
            $reply .= "\n⚠️ ".($isFr ? "Stock bas (seuil : {$product->min_stock})" : "Low stock (threshold: {$product->min_stock})");
        }

        return $reply;
    }

    protected function lowStock(bool $isFr): string
    {
        $products = Product::where('is_active', true)
            ->with('stocks')
            ->get()
            ->filter(fn (Product $p) => $p->isLowStock())
            ->sortBy('name');

        if ($products->isEmpty()) {
            return $isFr
                ? 'Aucun article en stock bas. Tout va bien !'
                : 'No low stock items. All good!';
        }

        $lines = [];
        foreach ($products->take(20) as $p) {
            $lines[] = "- {$p->name} ({$p->sku}) : {$p->total_quantity}/{$p->min_stock} {$p->unit}";
        }

        $reply = $isFr
            ? "Articles en stock bas ({$products->count()}) :\n"
            : "Low stock items ({$products->count()}):\n";
        $reply .= implode("\n", $lines);

        if ($products->count() > 20) {
            $reply .= "\n… ".($isFr ? 'et plus' : 'and more');
        }

        return $reply;
    }

    protected function categories(bool $isFr): string
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        if ($categories->isEmpty()) {
            return $isFr ? 'Aucune catégorie.' : 'No categories.';
        }

        $lines = [];
        foreach ($categories as $c) {
            $lines[] = "- {$c->name} ({$c->products_count} ".($isFr ? 'articles' : 'items').')';
        }

        return ($isFr ? "Catégories :\n" : "Categories:\n").implode("\n", $lines);
    }

    protected function warehouses(bool $isFr): string
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        if ($warehouses->isEmpty()) {
            return $isFr ? 'Aucun entrepôt actif.' : 'No active warehouses.';
        }

        $lines = [];
        foreach ($warehouses as $w) {
            $qty = (int) $w->stocks()->sum('quantity');
            $default = $w->is_default ? ($isFr ? ' (par défaut)' : ' (default)') : '';
            $lines[] = "- {$w->name}{$default} — {$qty} ".($isFr ? 'unités' : 'units')." — {$w->location}";
        }

        return ($isFr ? "Entrepôts actifs :\n" : "Active warehouses:\n").implode("\n", $lines);
    }

    protected function suppliers(bool $isFr): string
    {
        $suppliers = Supplier::where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        if ($suppliers->isEmpty()) {
            return $isFr ? 'Aucun fournisseur actif.' : 'No active suppliers.';
        }

        $lines = [];
        foreach ($suppliers->take(20) as $s) {
            $lines[] = "- {$s->name} ({$s->products_count} ".($isFr ? 'articles' : 'items').')';
        }

        $reply = ($isFr ? "Fournisseurs actifs :\n" : "Active suppliers:\n").implode("\n", $lines);
        if ($suppliers->count() > 20) {
            $reply .= "\n… ".($isFr ? 'et plus' : 'and more');
        }

        return $reply;
    }

    protected function movements(bool $isFr): string
    {
        $movements = StockMovement::with(['product', 'warehouse', 'user'])
            ->latest()
            ->take(10)
            ->get();

        if ($movements->isEmpty()) {
            return $isFr ? 'Aucun mouvement récent.' : 'No recent movements.';
        }

        $typeLabel = function (string $type) use ($isFr): string {
            return match ($type) {
                StockMovement::TYPE_IN => $isFr ? 'Entrée' : 'In',
                StockMovement::TYPE_OUT => $isFr ? 'Sortie' : 'Out',
                StockMovement::TYPE_ADJUSTMENT => $isFr ? 'Ajustement' : 'Adjustment',
                StockMovement::TYPE_TRANSFER => $isFr ? 'Transfert' : 'Transfer',
                default => $type,
            };
        };

        $lines = [];
        foreach ($movements as $m) {
            $lines[] = "- {$m->created_at->format('d/m H:i')} | {$typeLabel($m->type)} | {$m->product?->name} | {$m->quantity} | {$m->warehouse?->name}";
        }

        return ($isFr ? "Derniers mouvements :\n" : "Recent movements:\n").implode("\n", $lines);
    }

    protected function documents(bool $isFr): string
    {
        $documents = Document::with('supplier')
            ->whereIn('status', [Document::STATUS_DRAFT, Document::STATUS_CONFIRMED])
            ->latest()
            ->take(10)
            ->get();

        if ($documents->isEmpty()) {
            return $isFr ? 'Aucun document en attente.' : 'No pending documents.';
        }

        $lines = [];
        foreach ($documents as $d) {
            $lines[] = "- #{$d->id} | {$d->type} | {$d->supplier?->name} | {$d->status} | {$d->created_at->format('d/m')}";
        }

        return ($isFr ? "Documents en attente :\n" : "Pending documents:\n").implode("\n", $lines);
    }

    protected function stats(bool $isFr): string
    {
        $totalProducts = Product::where('is_active', true)->count();
        $lowCount = Product::where('is_active', true)->get()->filter(fn (Product $p) => $p->isLowStock())->count();
        $totalMovements = StockMovement::count();
        $warehousesCount = Warehouse::where('is_active', true)->count();
        $pendingDocs = Document::whereIn('status', [Document::STATUS_DRAFT, Document::STATUS_CONFIRMED])->count();

        $totalStockValue = Product::where('is_active', true)
            ->selectRaw('COALESCE(SUM(purchase_price * (SELECT COALESCE(SUM(quantity),0) FROM stocks WHERE stocks.product_id = products.id)), 0) as total')
            ->value('total');

        $inThisMonth = (int) StockMovement::where('type', StockMovement::TYPE_IN)
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->sum('quantity');
        $outThisMonth = (int) StockMovement::where('type', StockMovement::TYPE_OUT)
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->sum('quantity');

        if ($isFr) {
            return "Tableau de bord :\n"
                ."• Articles actifs : {$totalProducts}\n"
                ."• Articles en stock bas : {$lowCount}\n"
                .'• Valeur du stock : '.number_format((float) $totalStockValue, 2, ',', ' ')."\n"
                ."• Entrepôts actifs : {$warehousesCount}\n"
                ."• Mouvements totaux : {$totalMovements}\n"
                ."• Entrées ce mois : {$inThisMonth}\n"
                ."• Sorties ce mois : {$outThisMonth}\n"
                ."• Documents en attente : {$pendingDocs}";
        }

        return "Dashboard:\n"
            ."• Active articles: {$totalProducts}\n"
            ."• Low stock items: {$lowCount}\n"
            .'• Stock value: '.number_format((float) $totalStockValue, 2, '.', ',')."\n"
            ."• Active warehouses: {$warehousesCount}\n"
            ."• Total movements: {$totalMovements}\n"
            ."• Entries this month: {$inThisMonth}\n"
            ."• Exits this month: {$outThisMonth}\n"
            ."• Pending documents: {$pendingDocs}";
    }

    protected function fallback(bool $isFr): string
    {
        return $isFr
            ? "Je n'ai pas compris la question. Tapez \"aide\" pour voir ce que je peux faire, ou essayez : \"articles disponibles\", \"stock faible\", \"statistiques\"."
            : "I didn't understand the question. Type \"help\" to see what I can do, or try: \"available articles\", \"low stock\", \"statistics\".";
    }

    protected function suggestions(bool $isFr): array
    {
        if ($isFr) {
            return [
                'Articles disponibles',
                'Stock faible',
                'Statistiques',
                'Derniers mouvements',
                'Entrepôts',
            ];
        }

        return [
            'Available articles',
            'Low stock',
            'Statistics',
            'Recent movements',
            'Warehouses',
        ];
    }

    protected function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $this->normalize($needle))) {
                return true;
            }
        }

        return false;
    }

    protected function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($converted !== false) {
            $text = $converted;
        }
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    protected function findProduct(string $raw): ?Product
    {
        $normalizedMessage = $this->normalize($raw);

        $best = null;
        $bestLength = 0;

        foreach (Product::where('is_active', true)->get() as $product) {
            $nameNormalized = $this->normalize($product->name);
            if ($nameNormalized === '') {
                continue;
            }
            if (str_contains($normalizedMessage, $nameNormalized) && mb_strlen($nameNormalized) > $bestLength) {
                $best = $product;
                $bestLength = mb_strlen($nameNormalized);
            }
        }

        // fallback: try inactive too
        if (! $best) {
            foreach (Product::where('is_active', false)->get() as $product) {
                $nameNormalized = $this->normalize($product->name);
                if ($nameNormalized === '' || mb_strlen($nameNormalized) < 4) {
                    continue;
                }
                if (str_contains($normalizedMessage, $nameNormalized) && mb_strlen($nameNormalized) > $bestLength) {
                    $best = $product;
                    $bestLength = mb_strlen($nameNormalized);
                }
            }
        }

        return $best;
    }
}
