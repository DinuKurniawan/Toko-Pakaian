<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\AppCache;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(Order::STATUSES)],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $query = Order::query()
            ->with([
                'user:id,name',
                'items:id,order_id,product_id,quantity,price,product_name',
                'items.product:id,name,image',
            ])
            ->latest()
            ->when($filters['status'] ?? null, fn ($builder, $status) => $builder->where('status', $status))
            ->when($filters['search'] ?? null, function ($builder, $search) {
                $builder->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            });

        return inertia('Admin/Orders/Index', [
            'orders' => $query->paginate(10)->withQueryString(),
            'filters' => $filters,
            'stalePendingOrdersCount' => Order::stalePending()->count(),
            'stalePendingDays' => Order::STALE_PENDING_DAYS,
        ]);
    }

    public function show(Order $order)
    {
        $order->load([
            'user:id,name,email',
            'items:id,order_id,product_id,quantity,size,price,product_name',
            'items.product:id,name,image',
        ]);

        return inertia('Admin/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
        ]);

        $order->update($validated);
        AppCache::forgetAdminDashboard();

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }
}
