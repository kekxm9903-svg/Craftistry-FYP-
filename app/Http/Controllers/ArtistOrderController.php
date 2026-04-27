<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArtistOrderController extends Controller
{
    /**
     * List all orders for this artist.
     */
    public function index(Request $request)
    {
        $artist = Auth::user()->artist;

        if (!$artist) {
            return redirect()->route('studio')->with('error', 'Artist profile not found.');
        }

        // All orders for stats (unfiltered)
        $orders = Order::where('artist_id', $artist->id)
                       ->with(['user', 'items'])
                       ->get();

        // Filtered orders for the list
        $status = $request->get('status');

        $filteredOrders = Order::where('artist_id', $artist->id)
                               ->with(['user', 'items'])
                               ->when($status, fn($q) => $q->where('status', $status))
                               ->latest()
                               ->paginate(10)
                               ->withQueryString();

        return view('artistOrders', compact('orders', 'filteredOrders'));
    }

    /**
     * Seller accepts → processing becomes preparing.
     * Triggered when seller clicks "Accept Order".
     */
    public function accept(Order $order)
    {
        $this->authorizeOrder($order);

        if ($order->status !== 'processing') {
            return back()->with('error', 'This order cannot be accepted at this stage.');
        }

        $order->update(['status' => 'preparing']);

        return back()->with('success', 'Order accepted! Start preparing the item.');
    }

    /**
     * Seller ships → preparing becomes shipped + tracking saved.
     * Triggered when seller clicks "Mark as Shipped".
     */
    public function ship(Request $request, Order $order)
    {
        $this->authorizeOrder($order);

        $request->validate([
            'courier'         => 'required|string|max:50',
            'tracking_number' => 'required|string|max:100',
        ]);

        if ($order->status !== 'preparing') {
            return back()->with('error', 'Order must be in preparing status before shipping.');
        }

        $order->update([
            'status'          => 'shipped',
            'courier'         => $request->courier,
            'tracking_number' => $request->tracking_number,
        ]);

        return back()->with('success', 'Order marked as shipped! Buyer can now track the parcel.');
    }

    /**
     * Ensure the order belongs to this artist.
     */
    private function authorizeOrder(Order $order): void
    {
        $artist = Auth::user()->artist;
        abort_if(!$artist || $order->artist_id !== $artist->id, 403);
    }
}