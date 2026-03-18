<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlists = $request->user()
            ->wishlists()
            ->with('product')
            ->latest()
            ->get();

        return inertia('Wishlist/Index', ['wishlists' => $wishlists]);
    }

    public function toggle(Request $request)
    {
        $validated = $request->validate(['product_id' => ['required', 'exists:products,id']]);
        $user = $request->user();

        $existing = $user->wishlists()
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existing) {
            $existing->delete();
            return back()->with('wishlist_status', 'removed');
        }

        $user->wishlists()->create($validated);

        return back()->with('wishlist_status', 'added');
    }
}
