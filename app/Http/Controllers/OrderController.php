<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $cat    = $request->input('cat', '');
        $status = $request->input('status', '');

        // ── Badge counts for quick-tabs ──────────────────────────────────────
        $base = fn() => Order::where('user_id', $userId);

        $totalCounts = [
            'all'             => $base()->count(),
            'pending_payment' => $base()->where('status', 'pending_payment')->count(),
            'processing'      => $base()->where('status', 'processing')->count(),
            'preparing'       => $base()->where('status', 'preparing')->count(),
            'shipped'         => $base()->where('status', 'shipped')->count(),
            'completed'       => $base()->where('status', 'completed')->count(),
            'cancelled'       => $base()->where('status', 'cancelled')->count(),
        ];

        // ── Main query ───────────────────────────────────────────────────────
        $query = Order::where('user_id', $userId)
            ->with(['artist.user', 'items.artwork.artist.user'])  // deeper load for image + artist name
            ->latest();

        // Category tab filter
        if ($cat) {
            match ($cat) {
                'to-pay'     => $query->where('status', 'pending_payment'),
                'to-ship'    => $query->whereIn('status', ['processing', 'preparing']),
                'to-receive' => $query->where('status', 'shipped'),
                'completed'  => $query->whereIn('status', ['completed', 'cancelled']),
                default      => null,
            };
        }

        // Status pill filter
        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('myOrders', compact('orders', 'totalCounts'));
    }

    public function show(Order $order)
    {
        abort_if($order->user_id !== Auth::id(), 403);
        $order->load(['artist.user', 'items.artwork.artist.user']);  // deeper load
        return view('myOrderDetail', compact('order'));
    }

    public function complete(Order $order)
    {
        abort_if($order->user_id !== Auth::id(), 403);
        abort_if($order->status !== 'shipped', 422);

        $order->update(['status' => 'completed']);

        return back()->with('success', 'Order marked as received! You can now leave a review.');
    }

    public function downloadReceipt(Order $order)
    {
        abort_if($order->user_id !== Auth::id(), 403);

        if ($order->status !== 'completed') {
            return back()->with('error', 'Receipt is only available for completed orders.');
        }

        $order->load(['artist.user', 'items.artwork.artist.user']);

        $firstArtwork = $order->items->first()?->artwork;
        $artistUser   = $order->artist?->user ?? $firstArtwork?->artist?->user;
        $artistName   = $artistUser?->fullname
                     ?? $artistUser?->name
                     ?? $order->artist?->name
                     ?? $firstArtwork?->artist?->name
                     ?? 'Unknown Artist';

        $statusLabels = [
            'pending_payment' => 'Pending Payment',
            'processing'      => 'Order Placed',
            'preparing'       => 'Preparing',
            'shipped'         => 'Shipped',
            'completed'       => 'Completed',
            'cancelled'       => 'Cancelled',
        ];

        $data = [
            'order'       => $order,
            'buyer'       => Auth::user(),
            'artistName'  => $artistName,
            'statusLabel' => $statusLabels[$order->status] ?? ucfirst(str_replace('_', ' ', $order->status)),
            'receiptNo'   => 'RCP-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
            'generatedAt' => now()->format('d M Y, h:i A'),
        ];

        $pdf = Pdf::loadView('orderReceipt', $data)
                  ->setPaper('a4', 'portrait');

        $filename = 'Craftistry-Receipt-' . str_pad($order->id, 5, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->download($filename);
    }
}