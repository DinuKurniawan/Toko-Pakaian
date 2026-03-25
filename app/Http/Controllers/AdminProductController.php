<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\AppCache;
use App\Support\MediaStorage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminProductController extends Controller
{
    public function index()
    {
        return inertia('Admin/Products/Index', [
            'products' => Product::latest()->paginate(10)->withQueryString(),
        ]);
    }

    public function create()
    {
        return inertia('Admin/Products/Create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);

        Product::create($validated);
        AppCache::bustStorefront();
        AppCache::forgetAdminDashboard();

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        return inertia('Admin/Products/Edit', [
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validatedData($request, $product);

        $product->update($validated);
        AppCache::bustStorefront();
        AppCache::forgetAdminDashboard();

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->deleteStoredMedia();
        $product->delete();
        AppCache::bustStorefront();
        AppCache::forgetAdminDashboard();

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Product $product = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'category' => ['required', Rule::in(Product::CATEGORY_OPTIONS)],
            'image' => [$product === null ? 'required' : 'nullable', 'image', 'max:2048'],
            'description' => ['nullable', 'string'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'max:2048'],
            'stock' => ['nullable', 'array'],
            'stock.*' => ['integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('image')) {
            if ($product !== null) {
                $this->deleteStoredFiles([$product?->getRawOriginal('image')]);
            }

            $validated['image'] = MediaStorage::store($request->file('image'), 'products');
        } elseif ($product !== null) {
            unset($validated['image']);
        }

        if ($request->hasFile('images')) {
            if ($product !== null) {
                $this->deleteStoredFiles($product->getRawOriginal('images') ? json_decode((string) $product->getRawOriginal('images'), true) : []);
            }

            $validated['images'] = collect($request->file('images'))
                ->map(fn ($image) => MediaStorage::store($image, 'products'))
                ->all();
        } elseif ($product !== null) {
            unset($validated['images']);
        }

        if (array_key_exists('stock', $validated)) {
            $validated['stock'] = Product::normalizeStock($validated['stock']);
            $validated['sizes'] = Product::availableSizesFromStock($validated['stock']);
        } elseif ($product !== null) {
            unset($validated['stock'], $validated['sizes']);
        }

        return $validated;
    }

    private function deleteStoredFiles(array $paths): void
    {
        MediaStorage::delete($paths);
    }
}
