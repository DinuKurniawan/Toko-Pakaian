<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;

class AdminReportController extends Controller
{
    public function index(Request $request)
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);

        $orders = $this->salesOrdersQuery($startDate, $endDate)->get();

        $totalRevenue = $orders->sum('total_price');
        $totalOrders = $orders->count();

        $bestSellers = OrderItem::query()
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(quantity * price) as total_revenue')
            ->whereHas('order', fn (Builder $query) => $query->successful()->whereBetween('created_at', [$startDate, $endDate]))
            ->with('product:id,name,image')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        $dailyRevenue = Order::query()
            ->selectRaw('DATE(created_at) as date, SUM(total_price) as revenue, COUNT(*) as orders')
            ->successful()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return inertia('Admin/Reports/Index', [
            'orders' => $orders,
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'bestSellers' => $bestSellers,
            'dailyRevenue' => $dailyRevenue,
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
        ]);
    }

    public function exportCsv(Request $request)
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);

        $orders = $this->salesOrdersQuery($startDate, $endDate)->get();

        $filename = 'laporan_penjualan_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No. Order', 'Pelanggan', 'Tanggal', 'Status', 'Produk', 'Total', 'Ongkir', 'Diskon', 'Grand Total']);

            foreach ($orders as $order) {
                $products = $order->items->map(fn($i) => ($i->product_name ?? $i->product?->name) . ' x' . $i->quantity)->join(', ');
                fputcsv($file, [
                    $order->order_number,
                    $order->user?->name,
                    $order->created_at->format('d/m/Y'),
                    $order->status,
                    $products,
                    $order->subtotal,
                    $order->shipping_cost,
                    $order->discount_amount,
                    $order->total_price,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function resolveDateRange(Request $request): array
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : Carbon::now()->startOfMonth();

        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : Carbon::now()->endOfDay();

        return [$startDate, $endDate];
    }

    private function salesOrdersQuery(Carbon $startDate, Carbon $endDate): Builder
    {
        return Order::query()
            ->with([
                'user:id,name',
                'items:id,order_id,product_id,quantity,price,product_name',
                'items.product:id,name',
            ])
            ->successful()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest();
    }
}
