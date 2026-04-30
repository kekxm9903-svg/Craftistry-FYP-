<?php

namespace App\Http\Controllers;

use App\Models\ArtworkSell;
use App\Models\BulkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BulkOrderController extends Controller
{
    /**
     * Show the bulk order form (buyer)
     */
    public function create($artworkId)
    {
        $artwork = ArtworkSell::with(['artist.user'])
            ->where('id', $artworkId)
            ->where('bulk_sell_enabled', true)
            ->whereNotIn('status', ['sold', 'sold_out'])
            ->firstOrFail();

        return view('bulkOrderCreate', compact('artwork'));
    }

    /**
     * Store the bulk order request (buyer)
     */
    public function store(Request $request, $artworkId)
    {
        $artwork = ArtworkSell::where('id', $artworkId)
            ->where('bulk_sell_enabled', true)
            ->whereNotIn('status', ['sold', 'sold_out'])
            ->firstOrFail();

        $validated = $request->validate([
            'quantity'       => 'required|integer|min:' . ($artwork->bulk_sell_min_qty ?? 1),
            'last_ship_date' => 'required|date|after:today',
            'description'    => 'nullable|string|max:1000',
        ], [
            'quantity.min' => "Minimum quantity for bulk order is {$artwork->bulk_sell_min_qty}.",
            'last_ship_date.after' => 'Last ship date must be a future date.',
        ]);

        $qty           = $validated['quantity'];
        $unitPrice     = $artwork->product_price;
        $discountedPrice = $unitPrice;

        // Apply bulk discount if quantity meets threshold
        if ($artwork->bulk_sell_min_qty && $artwork->bulk_sell_discount && $qty >= $artwork->bulk_sell_min_qty) {
            $discountedPrice = round($unitPrice * (1 - $artwork->bulk_sell_discount / 100), 2);
        }

        $bulkOrder = BulkOrder::create([
            'artwork_sell_id'  => $artwork->id,
            'buyer_id'         => Auth::id(),
            'quantity'         => $qty,
            'last_ship_date'   => $validated['last_ship_date'],
            'description'      => $validated['description'] ?? null,
            'unit_price'       => $unitPrice,
            'discounted_price' => $discountedPrice,
            'status'           => 'pending',
        ]);

        return redirect()->route('bulk-orders.show', $bulkOrder->id)
            ->with('success', 'Bulk order request submitted! The seller will review it shortly.');
    }

    /**
     * Show a single bulk order (buyer)
     */
    public function show($id)
    {
        $bulkOrder = BulkOrder::with(['artworkSell.artist.user', 'buyer'])
            ->where('id', $id)
            ->where('buyer_id', Auth::id())
            ->firstOrFail();

        return view('bulkOrderShow', compact('bulkOrder'));
    }

    /**
     * List all bulk orders for the buyer
     */
    public function index()
    {
        $bulkOrders = BulkOrder::with(['artworkSell'])
            ->where('buyer_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('bulkOrderIndex', compact('bulkOrders'));
    }

    /**
     * Accept a bulk order (seller)
     */
    public function accept($id)
    {
        $bulkOrder = BulkOrder::with('artworkSell')
            ->where('id', $id)
            ->whereHas('artworkSell', function ($q) {
                $q->where('artist_id', Auth::user()->artist->id ?? 0);
            })
            ->where('status', 'pending')
            ->firstOrFail();

        $bulkOrder->update(['status' => 'accepted']);

        return redirect()->back()->with('success', 'Bulk order accepted.');
    }

    /**
     * Refuse a bulk order (seller)
     */
    public function refuse($id)
    {
        $bulkOrder = BulkOrder::with('artworkSell')
            ->where('id', $id)
            ->whereHas('artworkSell', function ($q) {
                $q->where('artist_id', Auth::user()->artist->id ?? 0);
            })
            ->where('status', 'pending')
            ->firstOrFail();

        $bulkOrder->update(['status' => 'refused']);

        return redirect()->back()->with('success', 'Bulk order refused.');
    }

    /**
     * List all bulk orders for the seller
     */
    public function sellerIndex()
    {
        $bulkOrders = BulkOrder::with(['artworkSell', 'buyer'])
            ->whereHas('artworkSell', function ($q) {
                $q->where('artist_id', Auth::user()->artist->id ?? 0);
            })
            ->latest()
            ->paginate(15);

        return view('bulkOrderSellerIndex', compact('bulkOrders'));
    }
}