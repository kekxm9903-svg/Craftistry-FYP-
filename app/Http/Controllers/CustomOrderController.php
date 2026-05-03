<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\BulkOrder;
use App\Models\CustomOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class CustomOrderController extends Controller
{
    /**
     * Show the custom order request form (buyer)
     */
    public function create(User $seller)
    {
        abort_if($seller->id === Auth::id(), 403, 'You cannot request from yourself.');

        $artist = $seller->artist;
        abort_if(!$artist || !$artist->allow_customization, 403, 'This artist does not accept custom orders.');

        return view('userRequestForm', compact('seller', 'artist'));
    }

    /**
     * Store the custom order request (buyer)
     */
    public function store(Request $request, User $seller)
    {
        abort_if($seller->id === Auth::id(), 403);

        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'required|string|max:2000',
            'buyer_price'     => 'required|numeric|min:1',
            'product_type'    => 'required|in:digital,physical',
            'reference_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('reference_image')) {
            $imagePath = $request->file('reference_image')
                ->store('custom-order-references', 'public');
        }

        CustomOrderRequest::create([
            'buyer_id'        => Auth::id(),
            'seller_id'       => $seller->id,
            'title'           => $validated['title'],
            'description'     => $validated['description'],
            'buyer_price'     => $validated['buyer_price'],
            'product_type'    => $validated['product_type'],
            'reference_image' => $imagePath,
            'status'          => 'pending',
        ]);

        return redirect()->route('custom-orders.index')
            ->with('success', 'Your custom order request has been sent to the artist!');
    }

    /**
     * List all custom + bulk order requests for the buyer
     */
    public function index()
    {
        $requests = CustomOrderRequest::with(['seller', 'order'])
            ->where('buyer_id', Auth::id())
            ->latest()
            ->paginate(10, ['*'], 'custom_page');

        $bulkOrders = BulkOrder::with(['artworkSell', 'order'])
            ->where('buyer_id', Auth::id())
            ->latest()
            ->paginate(10, ['*'], 'bulk_page');

        return view('userRequestList', compact('requests', 'bulkOrders'));
    }

    /**
     * Show a single custom order request (buyer)
     */
    public function show(CustomOrderRequest $customOrder)
    {
        abort_if($customOrder->buyer_id !== Auth::id(), 403);
        $customOrder->load('order');
        return view('userRequestDetails', compact('customOrder'));
    }

    /**
     * Buyer accepts seller's counter price
     */
    public function acceptCounter(CustomOrderRequest $customOrder)
    {
        abort_if($customOrder->buyer_id !== Auth::id(), 403);
        abort_if(!$customOrder->isRefused() || !$customOrder->hasCounterPrice(), 403);

        $customOrder->update([
            'status'      => 'accepted',
            'buyer_price' => $customOrder->counter_price,
        ]);

        return redirect()->back()->with('success', 'Counter offer accepted! Please proceed to payment.');
    }

    /**
     * Buyer refuses seller's counter price — cancels the request
     */
    public function refuseCounter(CustomOrderRequest $customOrder)
    {
        abort_if($customOrder->buyer_id !== Auth::id(), 403);
        abort_if(!$customOrder->isRefused() || !$customOrder->hasCounterPrice(), 403);

        $customOrder->update([
            'status'         => 'cancelled',
            'buyer_response' => 'refused',
        ]);

        return redirect()->route('custom-orders.index')
            ->with('info', 'The custom order request has been cancelled.');
    }

    /**
     * Initiate Stripe payment for accepted custom order (buyer)
     */
    public function pay(CustomOrderRequest $customOrder)
    {
        abort_if($customOrder->buyer_id !== Auth::id(), 403);
        abort_if(!$customOrder->isAccepted(), 403, 'This order is not ready for payment.');
        abort_if($customOrder->order_id, 403, 'This order has already been paid.');

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'myr',
                    'unit_amount'  => (int) round($customOrder->finalPrice() * 100),
                    'product_data' => [
                        'name'        => 'Custom Order: ' . $customOrder->title,
                        'description' => 'Custom artwork order from Craftistry',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            'success_url' => route('custom-orders.pay.success', $customOrder->id) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('custom-orders.show', $customOrder->id),
        ]);

        $customOrder->update(['stripe_session_id' => $session->id]);

        return redirect($session->url);
    }

    /**
     * Stripe payment success callback — create real Order + OrderItem
     */
    public function paySuccess(CustomOrderRequest $customOrder)
    {
        abort_if($customOrder->buyer_id !== Auth::id(), 403);

        // Avoid duplicate order creation on page refresh
        if ($customOrder->order_id) {
            return redirect()->route('orders.show', $customOrder->order_id)
                ->with('success', 'Your custom order is confirmed!');
        }

        // Resolve the seller's artist record
        $artist = Artist::where('user_id', $customOrder->seller_id)->first();

        // Create a real Order so it appears in both buyer and seller order lists
        $order = Order::create([
            'user_id'        => $customOrder->buyer_id,
            'artist_id'      => $artist?->id,
            'status'         => 'preparing',
            'payment_status' => 'paid',
            'total'          => $customOrder->finalPrice(),
            'notes'          => 'Custom Order: ' . $customOrder->title,
        ]);

        // Create an OrderItem so the order detail page renders correctly
        OrderItem::create([
            'order_id'        => $order->id,
            'artwork_sell_id' => null,   // custom order — no artwork_sell record
            'name'            => $customOrder->title,
            'price'           => $customOrder->finalPrice(),
            'quantity'        => 1,
        ]);

        // Link the order back to the request and mark completed
        $customOrder->update([
            'status'   => 'completed',
            'order_id' => $order->id,
        ]);

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Payment successful! Your custom order is confirmed and the seller is preparing it.');
    }
}