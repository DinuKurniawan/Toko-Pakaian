<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Support\AppCache;
use App\Support\MediaStorage;
use Illuminate\Http\Request;

class AdminBannerController extends Controller
{
    public function index()
    {
        return inertia('Admin/Banners/Index', [
            'banners' => Banner::latest()->get(),
        ]);
    }

    public function create()
    {
        return inertia('Admin/Banners/Create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request, true);

        $validated['image'] = MediaStorage::store($request->file('image'), 'banners');

        Banner::create($validated);
        AppCache::bustStorefront();
        AppCache::forgetAdminDashboard();

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil ditambahkan.');
    }

    public function edit(Banner $banner)
    {
        return inertia('Admin/Banners/Edit', [
            'banner' => $banner,
        ]);
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $this->validatedData($request, false);

        if ($request->hasFile('image')) {
            $banner->deleteStoredImage();
            $validated['image'] = MediaStorage::store($request->file('image'), 'banners');
        } else {
            unset($validated['image']);
        }

        $banner->update($validated);
        AppCache::bustStorefront();
        AppCache::forgetAdminDashboard();

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil diperbarui.');
    }

    public function destroy(Banner $banner)
    {
        $banner->deleteStoredImage();
        $banner->delete();
        AppCache::bustStorefront();
        AppCache::forgetAdminDashboard();

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil dihapus.');
    }

    private function validatedData(Request $request, bool $imageIsRequired): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['required', 'string', 'max:255'],
            'image' => [$imageIsRequired ? 'required' : 'nullable', 'image', 'max:2048'],
            'cta_text' => ['required', 'string', 'max:255'],
            'cta_link' => ['required', 'string', 'max:255'],
        ]);
    }
}
