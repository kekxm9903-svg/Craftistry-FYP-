<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;

class OrderSummaryController extends Controller
{
    public function index(Request $request)
    {
        $artistId = Auth::user()->artist?->id;
        $period   = $request->get('period', 'month');

        $totalOrders = Order::forArtist($artistId)->count();

        $totalRevenue = Order::forArtist($artistId)
            ->where('status', 'completed')
            ->where('payment_status', 'paid')
            ->sum('total');

        $completedOrders = Order::forArtist($artistId)
            ->where('status', 'completed')
            ->count();

        $avgOrderValue = $completedOrders > 0
            ? round($totalRevenue / $completedOrders, 2)
            : 0;

        [$currentStart, $currentEnd, $prevStart, $prevEnd] = $this->getPeriodBounds($period);

        $currentRevenue = Order::forArtist($artistId)
            ->where('status', 'completed')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->sum('total');

        $prevRevenue = Order::forArtist($artistId)
            ->where('status', 'completed')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->sum('total');

        $revenueChange = $prevRevenue > 0
            ? round((($currentRevenue - $prevRevenue) / $prevRevenue) * 100, 1)
            : 0;

        $currentOrderCount = Order::forArtist($artistId)
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->count();

        $prevOrderCount = Order::forArtist($artistId)
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();

        $orderCountChange = $prevOrderCount > 0
            ? round((($currentOrderCount - $prevOrderCount) / $prevOrderCount) * 100, 1)
            : 0;

        $chartData = $this->buildChartData($artistId, $period);

        $statusCounts = Order::forArtist($artistId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        foreach (['pending_payment', 'processing', 'preparing', 'shipped', 'completed', 'cancelled'] as $s) {
            $statusCounts[$s] = $statusCounts[$s] ?? 0;
        }

        $topArtworks = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('artwork_sells', 'order_items.artwork_sell_id', '=', 'artwork_sells.id')
            ->where('orders.artist_id', $artistId)
            ->where('orders.status', 'completed')
            ->where('orders.payment_status', 'paid')
            ->selectRaw('
                artwork_sells.id,
                artwork_sells.product_name as title,
                artwork_sells.image_path as image,
                SUM(order_items.quantity) as total_sold,
                SUM(order_items.quantity * order_items.price) as total_revenue
            ')
            ->groupBy('artwork_sells.id', 'artwork_sells.product_name', 'artwork_sells.image_path')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $completedOrderHistory = Order::with(['user', 'items'])
            ->forArtist($artistId)
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('orderSummary', compact(
            'totalOrders', 'totalRevenue', 'completedOrders', 'avgOrderValue',
            'currentRevenue', 'revenueChange', 'currentOrderCount', 'orderCountChange',
            'chartData', 'statusCounts', 'topArtworks', 'completedOrderHistory', 'period'
        ));
    }

    public function chartData(Request $request)
    {
        $artistId = Auth::user()->artist?->id;
        $period   = $request->get('period', 'month');

        [$currentStart, $currentEnd, $prevStart, $prevEnd] = $this->getPeriodBounds($period);

        $currentRevenue = Order::forArtist($artistId)
            ->where('status', 'completed')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->sum('total');

        $prevRevenue = Order::forArtist($artistId)
            ->where('status', 'completed')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->sum('total');

        $revenueChange = $prevRevenue > 0
            ? round((($currentRevenue - $prevRevenue) / $prevRevenue) * 100, 1)
            : 0;

        $currentOrderCount = Order::forArtist($artistId)
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->count();

        return response()->json([
            'chart'          => $this->buildChartData($artistId, $period),
            'currentRevenue' => $currentRevenue,
            'revenueChange'  => $revenueChange,
            'orderCount'     => $currentOrderCount,
        ]);
    }

    private function getPeriodBounds(string $period): array
    {
        $now = Carbon::now();
        return match ($period) {
            'day'  => [$now->copy()->startOfDay(), $now->copy()->endOfDay(), $now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear(), $now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), $now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
        };
    }

    private function buildChartData(int $artistId, string $period): array
    {
        $now = Carbon::now();
        return match ($period) {
            'day'  => $this->buildDayChart($artistId, $now),
            'year' => $this->buildYearChart($artistId, $now),
            default => $this->buildMonthChart($artistId, $now),
        };
    }

    private function buildDayChart(int $artistId, Carbon $now): array
    {
        $days = collect(range(6, 0))->map(fn($i) => $now->copy()->subDays($i));
        $labels = $revenues = $orders = [];
        foreach ($days as $day) {
            $labels[]   = $day->format('D');
            $revenues[] = (float) Order::forArtist($artistId)->where('status', 'completed')->where('payment_status', 'paid')->whereDate('created_at', $day->toDateString())->sum('total');
            $orders[]   = (int) Order::forArtist($artistId)->whereDate('created_at', $day->toDateString())->count();
        }
        return compact('labels', 'revenues', 'orders');
    }

    private function buildMonthChart(int $artistId, Carbon $now): array
    {
        $labels = $revenues = $orders = [];
        for ($m = 1; $m <= 12; $m++) {
            $start = Carbon::create($now->year, $m)->startOfMonth();
            $end   = $start->copy()->endOfMonth();
            $labels[]   = Carbon::create($now->year, $m)->format('M');
            $revenues[] = (float) Order::forArtist($artistId)->where('status', 'completed')->where('payment_status', 'paid')->whereBetween('created_at', [$start, $end])->sum('total');
            $orders[]   = (int) Order::forArtist($artistId)->whereBetween('created_at', [$start, $end])->count();
        }
        return compact('labels', 'revenues', 'orders');
    }

    private function buildYearChart(int $artistId, Carbon $now): array
    {
        $labels = $revenues = $orders = [];
        for ($y = $now->year - 4; $y <= $now->year; $y++) {
            $start = Carbon::create($y)->startOfYear();
            $end   = $start->copy()->endOfYear();
            $labels[]   = (string) $y;
            $revenues[] = (float) Order::forArtist($artistId)->where('status', 'completed')->where('payment_status', 'paid')->whereBetween('created_at', [$start, $end])->sum('total');
            $orders[]   = (int) Order::forArtist($artistId)->whereBetween('created_at', [$start, $end])->count();
        }
        return compact('labels', 'revenues', 'orders');
    }
}