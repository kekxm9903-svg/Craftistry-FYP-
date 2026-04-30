<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\ArtworkSell;
use App\Models\BulkOrder;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

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
            'quantity.min'         => "Minimum quantity for bulk order is {$artwork->bulk_sell_min_qty}.",
            'last_ship_date.after' => 'Last ship date must be a future date.',
        ]);

        $qty             = $validated['quantity'];
        $unitPrice       = $artwork->product_price;
        $discountedPrice = $unitPrice;

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
     * Initiate Stripe payment for an accepted bulk order (buyer)
     */
    public function pay($id)
    {
        $bulkOrder = BulkOrder::with('artworkSell')
            ->where('id', $id)
            ->where('buyer_id', Auth::id())
            ->where('status', 'accepted')
            ->firstOrFail();

        // Already paid — redirect straight to the order
        if ($bulkOrder->order_id) {
            return redirect()->route('orders.show', $bulkOrder->order_id)
                ->with('success', 'This bulk order has already been paid.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $total       = $bulkOrder->discounted_price * $bulkOrder->quantity;
        $productName = $bulkOrder->artworkSell->product_name ?? 'Bulk Order';

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'myr',
                    'unit_amount'  => (int) round($total * 100),
                    'product_data' => [
                        'name'        => 'Bulk Order: ' . $productName,
                        'description' => "Qty: {$bulkOrder->quantity} × RM " . number_format($bulkOrder->discounted_price, 2),
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            'success_url' => route('bulk-orders.pay.success', $bulkOrder->id) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('bulk-orders.show', $bulkOrder->id),
        ]);

        $bulkOrder->update(['stripe_session_id' => $session->id]);

        return redirect($session->url);
    }

    /**
     * Stripe payment success callback — create real Order + OrderItem (buyer)
     */
    public function paySuccess($id)
    {
        $bulkOrder = BulkOrder::with('artworkSell')
            ->where('id', $id)
            ->where('buyer_id', Auth::id())
            ->firstOrFail();

        // Avoid duplicate order creation on page refresh
        if ($bulkOrder->order_id) {
            return redirect()->route('orders.show', $bulkOrder->order_id)
                ->with('success', 'Your bulk order is confirmed!');
        }

        $artwork = $bulkOrder->artworkSell;
        $total   = $bulkOrder->discounted_price * $bulkOrder->quantity;

        // Resolve the seller's artist record
        $artist = Artist::find($artwork->artist_id);

        // Create a real Order so it appears in both buyer and seller order lists
        $order = Order::create([
            'user_id'        => $bulkOrder->buyer_id,
            'artist_id'      => $artist?->id,
            'status'         => 'preparing',
            'payment_status' => 'paid',
            'total'          => $total,
            'notes'          => "Bulk Order: {$bulkOrder->quantity}× {$artwork->product_name}",
        ]);

        // Create an OrderItem so order detail page renders the artwork correctly
        OrderItem::create([
            'order_id'        => $order->id,
            'artwork_sell_id' => $artwork->id,
            'name'            => $artwork->product_name,
            'price'           => $bulkOrder->discounted_price,
            'quantity'        => $bulkOrder->quantity,
        ]);

        // Link the order back to the bulk request and mark it paid
        $bulkOrder->update([
            'status'   => 'paid',
            'order_id' => $order->id,
        ]);

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Payment successful! Your bulk order is confirmed and the seller is preparing it.');
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

        return redirect()->back()->with('success', 'Bulk order accepted. The buyer will be notified to complete payment.');
    }

    /**
     * Refuse a bulk order (seller)
     */
    public function refuse(Request $request, $id)
    {
        $bulkOrder = BulkOrder::with('artworkSell')
            ->where('id', $id)
            ->whereHas('artworkSell', function ($q) {
                $q->where('artist_id', Auth::user()->artist->id ?? 0);
            })
            ->where('status', 'pending')
            ->firstOrFail();

        $validated = $request->validate([
            'seller_reason' => 'required|string|max:1000',
        ]);

        $bulkOrder->update([
            'status'        => 'refused',
            'seller_reason' => $validated['seller_reason'],
        ]);

        return redirect()->back()->with('success', 'Bulk order refused.');
    }

    /**
     * List all bulk orders for the seller (standalone page)
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

    /**
     * Show a single bulk order detail page (seller)
     */
    public function sellerShow($id)
    {
        $bulk = BulkOrder::with(['artworkSell', 'buyer'])
            ->where('id', $id)
            ->whereHas('artworkSell', function ($q) {
                $q->where('artist_id', Auth::user()->artist->id ?? 0);
            })
            ->firstOrFail();

        return view('bulkOrderDetail', compact('bulk'));
    }
}