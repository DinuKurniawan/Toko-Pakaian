<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Support\AppCache;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminReviewController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['approved', 'pending'])],
        ]);

        $query = Review::query()
            ->with(['user:id,name', 'product:id,name,image'])
            ->latest();

        if (($filters['status'] ?? null) === 'approved') {
            $query->approved();
        } elseif (($filters['status'] ?? null) === 'pending') {
            $query->pending();
        }

        return inertia('Admin/Reviews/Index', [
            'reviews' => $query->paginate(15)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function approve(Review $review)
    {
        $review->update(['is_approved' => true]);
        AppCache::bustStorefront();
        AppCache::forgetAdminDashboard();
        return back()->with('success', 'Ulasan disetujui.');
    }

    public function reject(Review $review)
    {
        $review->delete();
        AppCache::bustStorefront();
        AppCache::forgetAdminDashboard();
        return back()->with('success', 'Ulasan ditolak dan dihapus.');
    }
}
