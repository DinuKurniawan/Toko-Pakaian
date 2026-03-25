<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Support\AppCache;
use App\Support\MediaStorage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCategoryController extends Controller
{
    public function index()
    {
        return inertia('Admin/Categories/Index', [
            'categories' => Category::latest()->paginate(20)->withQueryString(),
        ]);
    }

    public function create()
    {
        return inertia('Admin/Categories/Create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);

        if ($request->hasFile('image')) {
            $validated['image'] = MediaStorage::store($request->file('image'), 'categories');
        }

        Category::create($validated);
        AppCache::bustStorefront();
        AppCache::forgetAdminDashboard();

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return inertia('Admin/Categories/Edit', ['category' => $category]);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $this->validatedData($request, $category);

        if ($request->hasFile('image')) {
            $category->deleteStoredImage();
            $validated['image'] = MediaStorage::store($request->file('image'), 'categories');
        } else {
            unset($validated['image']);
        }

        $category->update($validated);
        AppCache::bustStorefront();
        AppCache::forgetAdminDashboard();

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $category->deleteStoredImage();
        $category->delete();
        AppCache::bustStorefront();
        AppCache::forgetAdminDashboard();

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Category $category = null): array
    {
        $nameRule = Rule::unique('categories', 'name');

        if ($category !== null) {
            $nameRule = $nameRule->ignore($category);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:100', $nameRule],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);
    }
}
