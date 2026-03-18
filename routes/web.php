<?php

use App\Models\Product;
use App\Models\Category;
use App\Models\Banner;
use App\Models\Cart;
use App\Models\Review;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AdminBannerController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminVoucherController;
use App\Http\Controllers\AdminReviewController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ReviewController;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Voucher;
use App\Support\AppCache;

$storeName = 'LUMIÈRE';
$defaultSeoDescription = 'Belanja pakaian modern pria, wanita, dan unisex di LUMIÈRE. Temukan koleksi unggulan, promo terbaru, dan produk fashion favorit untuk gaya harian Anda.';
$catalogProductColumns = ['id', 'name', 'price', 'category', 'image', 'is_featured'];

$resolveMediaUrl = function (?string $path) {
    if (!$path) {
        return asset('favicon.ico');
    }

    $cleanPath = preg_replace('/^["\'\[]+|["\'\]]+$/', '', trim((string) $path));

    if (str_starts_with($cleanPath, '[http') && str_contains($cleanPath, '](')) {
        $cleanPath = Str::beforeLast(Str::after($cleanPath, ']('), ')');
    }

    if (Str::startsWith($cleanPath, ['http://', 'https://'])) {
        return $cleanPath;
    }

    if (str_starts_with($cleanPath, '/storage/')) {
        return url($cleanPath);
    }

    if (str_starts_with($cleanPath, 'storage/')) {
        return url('/' . $cleanPath);
    }

    if (str_starts_with($cleanPath, '/')) {
        return url('/storage' . $cleanPath);
    }

    return url('/storage/' . $cleanPath);
};

$cleanDescription = fn (?string $text, int $limit = 160) => Str::limit(
    trim(preg_replace('/\s+/', ' ', strip_tags((string) $text))),
    $limit,
    '...'
);

$buildSeo = function (array $overrides = []) use ($defaultSeoDescription, $resolveMediaUrl, $storeName) {
    return array_merge([
        'title' => $storeName,
        'description' => $defaultSeoDescription,
        'image' => $resolveMediaUrl(null),
        'url' => request()->fullUrl(),
        'type' => 'website',
        'siteName' => $storeName,
        'robots' => 'index,follow',
    ], array_filter($overrides, fn ($value) => $value !== null && $value !== ''));
};

// 1. Halaman Utama (Welcome)
Route::get('/', function () use ($buildSeo, $catalogProductColumns, $resolveMediaUrl, $storeName) {
    $pageData = AppCache::rememberStorefront('welcome', function () use ($buildSeo, $catalogProductColumns, $resolveMediaUrl, $storeName) {
        $categories = Category::query()
            ->select(['id', 'name', 'image'])
            ->orderBy('name')
            ->get();

        $featuredProducts = Product::query()
            ->select($catalogProductColumns)
            ->featured()
            ->latest()
            ->limit(4)
            ->get();

        $allProducts = Product::query()
            ->select($catalogProductColumns)
            ->latest()
            ->limit(8)
            ->get();

        $heroSlides = Banner::query()
            ->select(['id', 'title', 'subtitle', 'image', 'cta_text', 'cta_link'])
            ->latest()
            ->limit(5)
            ->get();

        return [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'allProducts' => $allProducts,
            'heroSlides' => $heroSlides,
            'seo' => $buildSeo([
                'title' => "Beranda | {$storeName}",
                'description' => 'Belanja pakaian modern pria, wanita, dan unisex di LUMIÈRE. Jelajahi produk unggulan, koleksi terbaru, dan gaya harian terbaik dalam satu tempat.',
                'url' => url('/'),
                'image' => $resolveMediaUrl($heroSlides->first()?->image),
                'structuredData' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => $storeName,
                    'url' => url('/'),
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => url('/products') . '?search={search_term_string}',
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ]),
        ];
    });

    return inertia('Welcome', $pageData);
});

// 2a. Halaman Detail Produk
Route::get('/products/{product}', function (Request $request, Product $product) use ($buildSeo, $catalogProductColumns, $cleanDescription, $resolveMediaUrl, $storeName) {
    $reviewFilters = $request->validate([
        'review_sort' => ['nullable', Rule::in(['latest', 'oldest', 'highest_rating', 'lowest_rating'])],
        'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
    ]);

    $selectedReviewSort = $reviewFilters['review_sort'] ?? 'latest';
    $selectedReviewRating = $reviewFilters['rating'] ?? null;

    $pageData = AppCache::rememberStorefront("product:{$product->id}:reviews:" . md5(json_encode($reviewFilters)), function () use ($buildSeo, $catalogProductColumns, $cleanDescription, $product, $resolveMediaUrl, $selectedReviewRating, $selectedReviewSort, $storeName) {
        $product = Product::query()
            ->select(['id', 'name', 'price', 'category', 'image', 'description', 'images', 'sizes', 'stock', 'is_featured'])
            ->findOrFail($product->id);

        $approvedReviewsQuery = Review::query()
            ->approved()
            ->where('product_id', $product->id);

        $reviewSummary = (clone $approvedReviewsQuery)
            ->selectRaw('COUNT(*) as total_reviews, AVG(rating) as avg_rating')
            ->first();

        $ratingBreakdown = (clone $approvedReviewsQuery)
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->map(fn ($total) => (int) $total)
            ->all();

        $filteredReviewsQuery = (clone $approvedReviewsQuery)
            ->forRating($selectedReviewRating)
            ->sortBySelection($selectedReviewSort);

        $filteredReviewsCount = (clone $filteredReviewsQuery)->count();

        $reviews = (clone $filteredReviewsQuery)
            ->with('user:id,name')
            ->limit(10)
            ->get();

        $reviewsCount = (int) ($reviewSummary?->total_reviews ?? 0);
        $avgRating = $reviewSummary?->avg_rating !== null
            ? round((float) $reviewSummary->avg_rating, 1)
            : null;

        $productStructuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $cleanDescription(
                $product->description ?: "Produk fashion kategori {$product->category} dari {$storeName}.",
                500
            ),
            'image' => [$resolveMediaUrl($product->image)],
            'sku' => (string) $product->id,
            'brand' => [
                '@type' => 'Brand',
                'name' => $storeName,
            ],
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'IDR',
                'price' => $product->price,
                'availability' => $product->isInStock()
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'url' => url("/products/{$product->id}"),
            ],
        ];

        if ($reviewsCount > 0 && $avgRating !== null) {
            $productStructuredData['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $avgRating,
                'reviewCount' => $reviewsCount,
            ];
        }

        return [
            'product' => $product,
            'relatedProducts' => Product::query()
                ->select($catalogProductColumns)
                ->where('category', $product->category)
                ->where('id', '!=', $product->id)
                ->latest()
                ->take(4)
                ->get(),
            'reviews' => $reviews,
            'reviewsCount' => $reviewsCount,
            'filteredReviewsCount' => $filteredReviewsCount,
            'avgRating' => $avgRating ? round($avgRating, 1) : null,
            'reviewFilters' => [
                'sort' => $selectedReviewSort,
                'rating' => $selectedReviewRating,
            ],
            'ratingBreakdown' => $ratingBreakdown,
            'seo' => $buildSeo([
                'title' => "{$product->name} | {$storeName}",
                'description' => $cleanDescription(
                    $product->description ?: "Belanja {$product->name} kategori {$product->category} di {$storeName}.",
                ),
                'url' => url("/products/{$product->id}"),
                'image' => $resolveMediaUrl($product->image),
                'type' => 'product',
                'structuredData' => $productStructuredData,
            ]),
        ];
    });

    $pageData['isWishlisted'] = Auth::check()
        ? Wishlist::query()->where('user_id', Auth::id())->where('product_id', $product->id)->exists()
        : false;

    return inertia('Product/Show', $pageData);
})->name('product.show');

// 2. Halaman Katalog Produk (Filter Kategori, Sortir & Search)
Route::get('/products', function (Request $request) use ($buildSeo, $catalogProductColumns, $cleanDescription, $resolveMediaUrl, $storeName) {
    $filters = $request->validate([
        'category' => ['nullable', Rule::in(Product::CATEGORY_OPTIONS)],
        'search' => ['nullable', 'string', 'max:100'],
        'price_min' => ['nullable', 'integer', 'min:0'],
        'price_max' => ['nullable', 'integer', 'min:0', 'gte:price_min'],
        'sort' => ['nullable', Rule::in(['new', 'price_asc', 'price_desc'])],
        'page' => ['nullable', 'integer', 'min:1'],
    ]);

    $pageData = AppCache::rememberStorefront(
        'catalog:' . md5(json_encode(['filters' => $filters, 'page' => $filters['page'] ?? 1])),
        function () use ($buildSeo, $catalogProductColumns, $cleanDescription, $filters, $resolveMediaUrl, $storeName) {
            $query = Product::query()
                ->select($catalogProductColumns)
                ->when($filters['category'] ?? null, fn ($builder, $category) => $builder->where('category', $category))
                ->when($filters['search'] ?? null, function ($builder, $search) {
                    $builder->where(function ($nestedQuery) use ($search) {
                        $nestedQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                })
                ->when($filters['price_min'] ?? null, fn ($builder, $priceMin) => $builder->where('price', '>=', $priceMin))
                ->when($filters['price_max'] ?? null, fn ($builder, $priceMax) => $builder->where('price', '<=', $priceMax));

            if (($filters['sort'] ?? null) === 'new') {
                $query->latest();
            } elseif (($filters['sort'] ?? null) === 'price_asc') {
                $query->orderBy('price');
            } elseif (($filters['sort'] ?? null) === 'price_desc') {
                $query->orderByDesc('price');
            } else {
                $query->orderBy('name');
            }

            $products = $query->paginate(12)->withQueryString();

            $seoTitle = match (true) {
                filled($filters['search'] ?? null) => "Cari \"{$filters['search']}\" | {$storeName}",
                filled($filters['category'] ?? null) => "Koleksi {$filters['category']} | {$storeName}",
                ($filters['sort'] ?? null) === 'new' => "Produk Terbaru | {$storeName}",
                default => "Semua Produk | {$storeName}",
            };

            $seoDescription = match (true) {
                filled($filters['search'] ?? null) => $cleanDescription("Hasil pencarian produk untuk {$filters['search']} di {$storeName}. Temukan koleksi pakaian yang sesuai dengan gaya Anda."),
                filled($filters['category'] ?? null) => $cleanDescription("Jelajahi koleksi {$filters['category']} dari {$storeName}. Temukan pilihan fashion modern dengan kualitas terbaik."),
                default => 'Jelajahi katalog lengkap pakaian modern dari LUMIÈRE. Temukan produk pria, wanita, dan unisex dengan filter kategori, harga, dan urutan terbaik.',
            };

            $catalogUrlParams = array_filter([
                'search' => $filters['search'] ?? null,
                'category' => $filters['category'] ?? null,
                'sort' => $filters['sort'] ?? null,
                'page' => $filters['page'] ?? null,
            ], static fn ($value) => $value !== null && $value !== '');

            $catalogUrl = url('/products') . (empty($catalogUrlParams) ? '' : '?' . http_build_query($catalogUrlParams));

            return [
                'products' => $products,
                'filters' => $filters,
                'seo' => $buildSeo([
                    'title' => $seoTitle,
                    'description' => $seoDescription,
                    'url' => $catalogUrl,
                    'image' => $resolveMediaUrl($products->getCollection()->first()?->image),
                    'structuredData' => [
                        '@context' => 'https://schema.org',
                        '@type' => 'CollectionPage',
                        'name' => $seoTitle,
                        'description' => $seoDescription,
                        'url' => $catalogUrl,
                    ],
                ]),
            ];
        },
        300
    );

    return inertia('Product/Index', $pageData);
});

// 3. Route Tambah ke Keranjang
Route::post('/cart/add', function(Request $request) {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $request->validate([
        'product_id' => 'required|exists:products,id',
        'size' => 'nullable|string|in:S,M,L,XL',
    ]);

    $userId = Auth::id();
    $cart = Cart::firstOrNew([
        'user_id' => $userId,
        'product_id' => $request->product_id,
        'size' => $request->size,
    ]);

    $cart->quantity = $cart->exists ? $cart->quantity + 1 : 1;
    $cart->save();
    AppCache::forgetCartCount($userId);
    AppCache::forgetAdminDashboard();

    return back();
})->name('cart.add');

// 4. Halaman Keranjang Belanja (View)
Route::get('/cart', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    return inertia('Cart/Index', [
        'cartItems' => Cart::query()
            ->select(['id', 'user_id', 'product_id', 'quantity', 'size'])
            ->with('product:id,name,price,image,stock,sizes')
            ->where('user_id', Auth::id())
            ->get()
    ]);
})->name('cart.index');

// 5. Update Jumlah Item Keranjang
Route::patch('/cart/{cart}', function (Request $request, Cart $cart) {
    if (!Auth::check() || $cart->user_id !== Auth::id()) {
        abort(403);
    }

    $request->validate(['quantity' => 'required|integer|min:1|max:99']);
    $cart->update(['quantity' => $request->quantity]);
    AppCache::forgetCartCount($cart->user_id);
    AppCache::forgetAdminDashboard();

    return back();
})->name('cart.update');

// 6. Hapus Item dari Keranjang
Route::delete('/cart/{cart}', function (Cart $cart) {
    if (!Auth::check() || $cart->user_id !== Auth::id()) {
        abort(403);
    }

    AppCache::forgetCartCount($cart->user_id);
    AppCache::forgetAdminDashboard();
    $cart->delete();

    return back();
})->name('cart.destroy');

// Halaman Pria (Inertia)
Route::get('/pria', function () use ($buildSeo, $catalogProductColumns, $resolveMediaUrl, $storeName) {
    $pageData = AppCache::rememberStorefront('category:pria', function () use ($buildSeo, $catalogProductColumns, $resolveMediaUrl, $storeName) {
        $products = Product::query()
            ->select($catalogProductColumns)
            ->where('category', 'Pria')
            ->latest()
            ->take(8)
            ->get();

        return [
            'products' => $products,
            'seo' => $buildSeo([
                'title' => "Koleksi Pria | {$storeName}",
                'description' => 'Temukan koleksi pakaian pria modern untuk gaya kasual hingga formal hanya di LUMIÈRE.',
                'url' => url('/pria'),
                'image' => $resolveMediaUrl($products->first()?->image),
            ]),
        ];
    });

    return inertia('Pria', $pageData);
})->name('pria');

// Halaman Wanita (Inertia)
Route::get('/wanita', function () use ($buildSeo, $catalogProductColumns, $resolveMediaUrl, $storeName) {
    $pageData = AppCache::rememberStorefront('category:wanita', function () use ($buildSeo, $catalogProductColumns, $resolveMediaUrl, $storeName) {
        $products = Product::query()
            ->select($catalogProductColumns)
            ->where('category', 'Wanita')
            ->latest()
            ->take(8)
            ->get();

        return [
            'products' => $products,
            'seo' => $buildSeo([
                'title' => "Koleksi Wanita | {$storeName}",
                'description' => 'Jelajahi koleksi pakaian wanita modern dari LUMIÈRE untuk tampilan elegan dan percaya diri setiap hari.',
                'url' => url('/wanita'),
                'image' => $resolveMediaUrl($products->first()?->image),
            ]),
        ];
    });

    return inertia('Wanita', $pageData);
})->name('wanita');

// Halaman Koleksi Baru (Inertia)
Route::get('/koleksi-baru', function () use ($buildSeo, $catalogProductColumns, $resolveMediaUrl, $storeName) {
    $pageData = AppCache::rememberStorefront('category:koleksi-baru', function () use ($buildSeo, $catalogProductColumns, $resolveMediaUrl, $storeName) {
        $products = Product::query()
            ->select($catalogProductColumns)
            ->latest()
            ->take(8)
            ->get();

        return [
            'products' => $products,
            'seo' => $buildSeo([
                'title' => "Koleksi Baru | {$storeName}",
                'description' => 'Lihat koleksi pakaian terbaru dari LUMIÈRE. Temukan produk yang sedang tren dan siap melengkapi gaya Anda.',
                'url' => url('/koleksi-baru'),
                'image' => $resolveMediaUrl($products->first()?->image),
            ]),
        ];
    });

    return inertia('KoleksiBaru', $pageData);
})->name('koleksi-baru');

// Route Dashboard (wajib untuk auth)
Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user->role === 'admin') {
        $data = AppCache::rememberAdminDashboard(function () {
            $lowStockProducts = Product::select(['id', 'name', 'image', 'category', 'stock'])
                ->whereNotNull('stock')
                ->latest()
                ->take(24)
                ->get()
                ->filter(fn ($product) => $product->hasLowStock())
                ->map(function ($product) {
                    $stockValues = collect($product->stock ?? [])
                        ->map(fn ($quantity) => max((int) $quantity, 0))
                        ->filter(fn ($quantity) => $quantity > 0)
                        ->values();

                    $product->stock_summary = [
                        'total' => $stockValues->sum(),
                        'lowest' => $stockValues->min() ?? 0,
                    ];

                    return $product;
                })
                ->take(5)
                ->values();

            $topCustomers = Order::query()
                ->successful()
                ->selectRaw('user_id, COUNT(*) as total_orders, SUM(total_price) as total_spent, MAX(created_at) as last_order_at')
                ->whereNotNull('user_id')
                ->with('user:id,name,email')
                ->groupBy('user_id')
                ->orderByDesc('total_spent')
                ->take(5)
                ->get();

            $repeatCustomersCount = Order::query()
                ->successful()
                ->selectRaw('user_id, COUNT(*) as total_orders')
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) >= 2')
                ->get()
                ->count();

            $averageOrderValue = (int) round((float) Order::query()->successful()->avg('total_price'));

            return [
                'totalProducts' => Product::count(),
                'totalUsers' => User::where('role', User::ROLE_USER)->count(),
                'totalCategories' => Category::count(),
                'totalCartItems' => Cart::sum('quantity'),
                'totalOrders' => Order::count(),
                'pendingOrders' => Order::where('status', 'pending')->count(),
                'totalRevenue' => Order::successful()->sum('total_price'),
                'latestProducts' => Product::latest()->take(5)->get(),
                'recentUsers' => User::where('role', User::ROLE_USER)->latest()->take(5)->get(['id', 'name', 'email', 'created_at']),
                'productsByCategory' => Product::selectRaw('category, count(*) as total')->groupBy('category')->get(),
                'featuredProducts' => Product::where('is_featured', true)->count(),
                'recentOrders' => Order::with('user')->latest()->take(5)->get(),
                'pendingReviews' => Review::pending()->count(),
                'lowStockProducts' => $lowStockProducts,
                'stalePendingOrdersCount' => Order::stalePending()->count(),
                'stalePendingRevenue' => Order::stalePending()->sum('total_price'),
                'stalePendingDays' => Order::STALE_PENDING_DAYS,
                'topCustomers' => $topCustomers,
                'repeatCustomersCount' => $repeatCustomersCount,
                'averageOrderValue' => $averageOrderValue,
            ];
        });
    } else {
        $orderStatusSummary = Order::query()
            ->where('user_id', $user->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $promoNotifications = Voucher::query()
            ->active()
            ->get(['id', 'code', 'description', 'type', 'value', 'min_order', 'expires_at'])
            ->filter(fn ($voucher) => $voucher->isValid())
            ->sortBy(fn ($voucher) => $voucher->expires_at?->timestamp ?? PHP_INT_MAX)
            ->take(3)
            ->values();

        $wishlistAlerts = Wishlist::query()
            ->with(['product:id,name,price,image,category,stock,is_featured'])
            ->where('user_id', $user->id)
            ->get()
            ->filter(fn ($wishlist) => $wishlist->product && $wishlist->product->isInStock())
            ->map(function ($wishlist) {
                $stockValues = collect($wishlist->product->stock ?? [])
                    ->map(fn ($quantity) => max((int) $quantity, 0))
                    ->filter(fn ($quantity) => $quantity > 0)
                    ->values();

                $wishlist->product->stock_summary = [
                    'total' => $stockValues->sum(),
                    'lowest' => $stockValues->min() ?? 0,
                    'is_low' => $wishlist->product->hasLowStock(),
                ];

                return $wishlist;
            })
            ->take(4)
            ->values();

        $data = [
            'cartItems' => Cart::with('product')->where('user_id', $user->id)->get(),
            'cartTotal' => Cart::where('user_id', $user->id)->sum('quantity'),
            'totalOrders' => Order::where('user_id', $user->id)->count(),
            'totalSpent' => Order::where('user_id', $user->id)->successful()->sum('total_price'),
            'recentOrders' => Order::where('user_id', $user->id)->latest()->take(5)->get(),
            'wishlistCount' => Wishlist::where('user_id', $user->id)->count(),
            'orderStatusSummary' => [
                Order::STATUS_PENDING => (int) ($orderStatusSummary[Order::STATUS_PENDING] ?? 0),
                Order::STATUS_PROCESSING => (int) ($orderStatusSummary[Order::STATUS_PROCESSING] ?? 0),
                Order::STATUS_SHIPPED => (int) ($orderStatusSummary[Order::STATUS_SHIPPED] ?? 0),
                Order::STATUS_COMPLETED => (int) ($orderStatusSummary[Order::STATUS_COMPLETED] ?? 0),
                Order::STATUS_CANCELLED => (int) ($orderStatusSummary[Order::STATUS_CANCELLED] ?? 0),
            ],
            'promoNotifications' => $promoNotifications,
            'wishlistAlerts' => $wishlistAlerts,
        ];
    }

    return inertia('Dashboard', $data);
})->middleware(['auth'])->name('dashboard');

// Route Proxy JagoOngkir
Route::prefix('ongkir')->name('ongkir.')->group(function () {
    Route::get('/provinces', function () {
        $response = Http::withHeaders(['key' => env('JAGOONGKIR_API_KEY')])
            ->get('https://api.jagoongkir.com/v1/province');
        return $response->json();
    })->name('provinces');

    Route::get('/cities', function (Request $request) {
        $response = Http::withHeaders(['key' => env('JAGOONGKIR_API_KEY')])
            ->get('https://api.jagoongkir.com/v1/city', [
                'province' => $request->province_id,
            ]);
        return $response->json();
    })->name('cities');

    Route::post('/cost', function (Request $request) {
        $response = Http::withHeaders(['key' => env('JAGOONGKIR_API_KEY')])
            ->post('https://api.jagoongkir.com/v1/cost', [
                'origin'      => env('STORE_CITY_ID'),
                'destination' => $request->city_id,
                'weight'      => $request->weight,
                'courier'     => $request->courier,
            ]);
        return $response->json();
    })->name('cost');
});

// Route Checkout (Buat Pesanan dari Keranjang)
Route::post('/checkout', function (Request $request) {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $request->validate([
        'shipping_address' => 'required|string',
        'phone'            => 'required|string',
        'notes'            => 'nullable|string',
        'shipping_cost'    => 'required|integer|min:0',
        'courier'          => 'required|string',
        'voucher_code'     => 'nullable|string',
    ]);

    $cartItems = Cart::query()
        ->select(['id', 'user_id', 'product_id', 'quantity', 'size'])
        ->with('product:id,name,price')
        ->where('user_id', Auth::id())
        ->get();

    if ($cartItems->isEmpty()) {
        return back()->with('error', 'Keranjang belanja kosong.');
    }

    $subtotal = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);
    $shippingCost = (int) $request->shipping_cost;
    $discountAmount = 0;
    $voucherCode = null;

    if ($request->filled('voucher_code')) {
        $voucher = Voucher::where('code', strtoupper($request->voucher_code))->first();
        if ($voucher && $voucher->isValid()) {
            $discountAmount = $voucher->calculateDiscount($subtotal);
            $voucherCode = $voucher->code;
            $voucher->increment('used_count');
        }
    }

    $order = Order::create([
        'user_id'          => Auth::id(),
        'order_number'     => 'ORD-' . strtoupper(uniqid()),
        'total_price'      => $subtotal + $shippingCost - $discountAmount,
        'shipping_cost'    => $shippingCost,
        'courier'          => $request->courier,
        'status'           => 'pending',
        'shipping_address' => $request->shipping_address,
        'phone'            => $request->phone,
        'notes'            => $request->notes,
        'voucher_code'     => $voucherCode,
        'discount_amount'  => $discountAmount,
    ]);

    foreach ($cartItems as $item) {
        OrderItem::create([
            'order_id'     => $order->id,
            'product_id'   => $item->product_id,
            'quantity'     => $item->quantity,
            'size'         => $item->size,
            'price'        => $item->product->price,
            'product_name' => $item->product->name,
        ]);
    }

    Cart::where('user_id', Auth::id())->delete();
    AppCache::forgetCartCount(Auth::id());
    AppCache::forgetAdminDashboard();

    return redirect()->route('dashboard')->with('success', 'Pesanan berhasil dibuat! Nomor pesanan: ' . $order->order_number);
})->middleware(['auth'])->name('checkout');

// Riwayat Pesanan User
Route::get('/orders', function () {
    $orders = Order::query()
        ->with([
            'items:id,order_id,product_id,quantity,size,price,product_name',
            'items.product:id,name,image',
        ])
        ->where('user_id', Auth::id())
        ->latest()
        ->get();
    return inertia('Orders/Index', ['orders' => $orders]);
})->middleware(['auth'])->name('orders.index');

// Detail Pesanan User
Route::get('/orders/{order}', function (Order $order) {
    if ($order->user_id !== Auth::id()) {
        abort(403);
    }
    $order->load([
        'items:id,order_id,product_id,quantity,size,price,product_name',
        'items.product:id,name,image',
    ]);

    // Load existing reviews for this order
    $reviews = Review::where('order_id', $order->id)
        ->where('user_id', Auth::id())
        ->pluck('product_id')
        ->toArray();

    return inertia('Orders/Show', ['order' => $order, 'reviewedProductIds' => $reviews]);
})->middleware(['auth'])->name('orders.show');

Route::post('/orders/{order}/cancel', function (Order $order) {
    if ($order->user_id !== Auth::id()) {
        abort(403);
    }

    if (!$order->canBeCancelled()) {
        return back()->with('error', 'Pesanan ini sudah diproses dan tidak bisa dibatalkan lagi.');
    }

    $order->update(['status' => Order::STATUS_CANCELLED]);
    AppCache::forgetAdminDashboard();

    return back()->with('success', 'Pesanan berhasil dibatalkan. Tim kami tidak akan memproses order ini lebih lanjut.');
})->middleware(['auth'])->name('orders.cancel');

Route::post('/orders/{order}/reorder', function (Order $order) {
    if ($order->user_id !== Auth::id()) {
        abort(403);
    }

    $order->loadMissing([
        'items:id,order_id,product_id,quantity,size,price,product_name',
        'items.product:id,name,stock',
    ]);

    $addedItems = 0;
    $skippedItems = 0;

    foreach ($order->items as $item) {
        $product = $item->product;

        if (!$product || !$product->isInStock()) {
            $skippedItems++;
            continue;
        }

        if ($item->size && is_array($product->stock) && $product->stock !== [] && $product->getStockForSize($item->size) <= 0) {
            $skippedItems++;
            continue;
        }

        $cart = Cart::firstOrNew([
            'user_id' => Auth::id(),
            'product_id' => $item->product_id,
            'size' => $item->size,
        ]);

        $cart->quantity = $cart->exists
            ? min($cart->quantity + $item->quantity, 99)
            : min($item->quantity, 99);

        $cart->save();
        $addedItems++;
    }

    if ($addedItems === 0) {
        return back()->with('error', 'Produk dari pesanan ini sedang tidak tersedia untuk ditambahkan kembali ke keranjang.');
    }

    AppCache::forgetCartCount(Auth::id());
    AppCache::forgetAdminDashboard();

    $message = $addedItems === 1
        ? '1 item berhasil ditambahkan kembali ke keranjang.'
        : "{$addedItems} item berhasil ditambahkan kembali ke keranjang.";

    if ($skippedItems > 0) {
        $message .= " {$skippedItems} item dilewati karena stoknya sudah tidak tersedia.";
    }

    return back()->with('success', $message);
})->middleware(['auth'])->name('orders.reorder');

// Wishlist Routes (auth required)
Route::middleware(['auth'])->group(function () {
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

// Voucher Validation
Route::post('/voucher/check', function (Request $request) {
    if (!Auth::check()) return response()->json(['valid' => false]);

    $request->validate(['code' => 'required|string', 'subtotal' => 'required|integer|min:0']);

    $voucher = Voucher::where('code', strtoupper($request->code))->first();

    if (!$voucher || !$voucher->isValid()) {
        return response()->json(['valid' => false, 'message' => 'Voucher tidak valid atau sudah kedaluwarsa.']);
    }

    if ($request->subtotal < $voucher->min_order) {
        return response()->json(['valid' => false, 'message' => "Minimum pembelian Rp " . number_format($voucher->min_order, 0, ',', '.')]);
    }

    $discount = $voucher->calculateDiscount($request->subtotal);

    return response()->json([
        'valid' => true,
        'discount' => $discount,
        'message' => $voucher->type === 'percent'
            ? "Diskon {$voucher->value}% berhasil diterapkan!"
            : "Diskon Rp " . number_format($discount, 0, ',', '.') . " berhasil diterapkan!",
    ]);
})->middleware(['auth'])->name('voucher.check');

// Route Admin Product CRUD
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('products', AdminProductController::class)->except(['show']);
    Route::resource('banners', AdminBannerController::class)->except(['show']);
    Route::resource('categories', AdminCategoryController::class)->except(['show']);
    Route::resource('vouchers', AdminVoucherController::class)->except(['show']);

    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');

    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::get('users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::patch('reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
    Route::delete('reviews/{review}', [AdminReviewController::class, 'reject'])->name('reviews.reject');

    Route::get('reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [AdminReportController::class, 'exportCsv'])->name('reports.export');
});

require __DIR__.'/auth.php';
