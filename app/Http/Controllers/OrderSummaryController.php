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
        $month    = (int) $request->get('month', now()->month);
        $year     = (int) $request->get('year',  now()->year);

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

        [$currentStart, $currentEnd, $prevStart, $prevEnd] = $this->getPeriodBounds($period, $month, $year);

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
            : ($currentRevenue > 0 ? 100 : null);

        $currentOrderCount = Order::forArtist($artistId)
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->count();

        $prevOrderCount = Order::forArtist($artistId)
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();

        $orderCountChange = $prevOrderCount > 0
            ? round((($currentOrderCount - $prevOrderCount) / $prevOrderCount) * 100, 1)
            : ($currentOrderCount > 0 ? 100 : null);

        $chartData = $this->buildChartData($artistId, $period, $month, $year);

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

        // Build month options for the month selector (current year)
        $monthOptions = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthOptions[$m] = Carbon::create($year, $m)->format('F');
        }

        return view('orderSummary', compact(
            'totalOrders', 'totalRevenue', 'completedOrders', 'avgOrderValue',
            'currentRevenue', 'revenueChange', 'currentOrderCount', 'orderCountChange',
            'chartData', 'statusCounts', 'topArtworks', 'completedOrderHistory',
            'period', 'month', 'year', 'monthOptions'
        ));
    }

    public function chartData(Request $request)
    {
        $artistId = Auth::user()->artist?->id;
        $period   = $request->get('period', 'month');
        $month    = (int) $request->get('month', now()->month);
        $year     = (int) $request->get('year',  now()->year);

        [$currentStart, $currentEnd, $prevStart, $prevEnd] = $this->getPeriodBounds($period, $month, $year);

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
            : ($currentRevenue > 0 ? 100 : null);

        $currentOrderCount = Order::forArtist($artistId)
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->count();

        return response()->json([
            'chart'          => $this->buildChartData($artistId, $period, $month, $year),
            'currentRevenue' => $currentRevenue,
            'revenueChange'  => $revenueChange,
            'orderCount'     => $currentOrderCount,
        ]);
    }

    private function getPeriodBounds(string $period, int $month, int $year): array
    {
        $now = Carbon::now();
        return match ($period) {
            'day'   => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay(),
            ],
            'week'  => [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
                $now->copy()->subWeek()->startOfWeek(),
                $now->copy()->subWeek()->endOfWeek(),
            ],
            'year'  => [
                Carbon::create($year)->startOfYear(),
                Carbon::create($year)->endOfYear(),
                Carbon::create($year - 1)->startOfYear(),
                Carbon::create($year - 1)->endOfYear(),
            ],
            default => [ // month
                Carbon::create($year, $month)->startOfMonth(),
                Carbon::create($year, $month)->endOfMonth(),
                Carbon::create($year, $month)->subMonth()->startOfMonth(),
                Carbon::create($year, $month)->subMonth()->endOfMonth(),
            ],
        };
    }

    private function buildChartData(int $artistId, string $period, int $month, int $year): array
    {
        $now = Carbon::now();
        return match ($period) {
            'day'   => $this->buildDayChart($artistId, $now),
            'week'  => $this->buildWeekChart($artistId, $now),
            'year'  => $this->buildYearChart($artistId, $year),
            default => $this->buildMonthChart($artistId, $month, $year),
        };
    }

    private function buildDayChart(int $artistId, Carbon $now): array
    {
        $days = collect(range(6, 0))->map(fn($i) => $now->copy()->subDays($i));
        $labels = $revenues = $orders = [];
        foreach ($days as $day) {
            $labels[]   = $day->format('D d/m');
            $revenues[] = (float) Order::forArtist($artistId)->where('status', 'completed')->where('payment_status', 'paid')->whereDate('created_at', $day->toDateString())->sum('total');
            $orders[]   = (int)   Order::forArtist($artistId)->whereDate('created_at', $day->toDateString())->count();
        }
        return compact('labels', 'revenues', 'orders');
    }

    private function buildWeekChart(int $artistId, Carbon $now): array
    {
        // Last 8 weeks
        $labels = $revenues = $orders = [];
        for ($i = 7; $i >= 0; $i--) {
            $start = $now->copy()->subWeeks($i)->startOfWeek();
            $end   = $start->copy()->endOfWeek();
            $labels[]   = $start->format('d M');
            $revenues[] = (float) Order::forArtist($artistId)->where('status', 'completed')->where('payment_status', 'paid')->whereBetween('created_at', [$start, $end])->sum('total');
            $orders[]   = (int)   Order::forArtist($artistId)->whereBetween('created_at', [$start, $end])->count();
        }
        return compact('labels', 'revenues', 'orders');
    }

    private function buildMonthChart(int $artistId, int $month, int $year): array
    {
        // Show all days in the selected month
        $start      = Carbon::create($year, $month)->startOfMonth();
        $daysInMonth = $start->daysInMonth;
        $labels = $revenues = $orders = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = Carbon::create($year, $month, $d);
            $labels[]   = (string) $d;
            $revenues[] = (float) Order::forArtist($artistId)->where('status', 'completed')->where('payment_status', 'paid')->whereDate('created_at', $date->toDateString())->sum('total');
            $orders[]   = (int)   Order::forArtist($artistId)->whereDate('created_at', $date->toDateString())->count();
        }
        return compact('labels', 'revenues', 'orders');
    }

    private function buildYearChart(int $artistId, int $year): array
    {
        $labels = $revenues = $orders = [];
        for ($m = 1; $m <= 12; $m++) {
            $start = Carbon::create($year, $m)->startOfMonth();
            $end   = $start->copy()->endOfMonth();
            $labels[]   = Carbon::create($year, $m)->format('M');
            $revenues[] = (float) Order::forArtist($artistId)->where('status', 'completed')->where('payment_status', 'paid')->whereBetween('created_at', [$start, $end])->sum('total');
            $orders[]   = (int)   Order::forArtist($artistId)->whereBetween('created_at', [$start, $end])->count();
        }
        return compact('labels', 'revenues', 'orders');
    }
}