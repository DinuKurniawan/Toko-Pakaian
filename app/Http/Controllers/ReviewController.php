<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use App\Support\AppCache;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        $order = Order::query()
            ->whereKey($validated['order_id'])
            ->where('user_id', $user->id)
            ->where('status', Order::STATUS_COMPLETED)
            ->firstOrFail();

        if (!$order->items()->where('product_id', $validated['product_id'])->exists()) {
            return back()->with('error', 'Produk tidak ditemukan dalam pesanan ini.');
        }

        $review = Review::firstOrNew([
            'user_id' => $user->id,
            'product_id' => $validated['product_id'],
            'order_id' => $validated['order_id'],
        ]);

        if ($review->exists) {
            return back()->with('error', 'Anda sudah memberikan ulasan untuk produk ini.');
        }

        $review->fill([
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_approved' => false,
        ]);
        $review->save();
        AppCache::forgetAdminDashboard();

        return back()->with('success', 'Ulasan berhasil dikirim dan menunggu persetujuan.');
    }
}
