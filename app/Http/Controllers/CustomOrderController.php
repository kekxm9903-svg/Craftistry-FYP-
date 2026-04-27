<?php

namespace App\Http\Controllers;

use App\Models\CustomOrderRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class CustomOrderController extends Controller
{
    /* ── Show request form ── */
    public function create(User $seller)
    {
        abort_if($seller->id === Auth::id(), 403, 'You cannot request from yourself.');
        return view('userRequestForm', compact('seller'));
    }

    /* ── Store new request ── */
    public function store(Request $request, User $seller)
    {
        abort_if($seller->id === Auth::id(), 403);

        $data = $request->validate([
            'title'           => 'required|string|max:120',
            'description'     => 'required|string|max:2000',
            'reference_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'buyer_price'     => 'required|numeric|min:1|max:99999',
            'product_type'    => 'required|in:physical,digital',
        ]);

        $imagePath = null;
        if ($request->hasFile('reference_image')) {
            $imagePath = $request->file('reference_image')->store('custom-orders', 'public');
        }

        CustomOrderRequest::create([
            'buyer_id'        => Auth::id(),
            'seller_id'       => $seller->id,
            'title'           => $data['title'],
            'description'     => $data['description'],
            'reference_image' => $imagePath,
            'buyer_price'     => $data['buyer_price'],
            'product_type'    => $data['product_type'],
            'status'          => 'pending',
        ]);

        return redirect()->route('custom-orders.index')
            ->with('success', 'Your custom order request has been sent!');
    }

    /* ── Buyer: list all their requests ── */
    public function index()
    {
        $requests = CustomOrderRequest::with('seller', 'order')
            ->where('buyer_id', Auth::id())
            ->latest()
            ->paginate(12);

        return view('userRequestList', compact('requests'));
    }

    /* ── Buyer: view a single request ── */
    public function show(CustomOrderRequest $customOrder)
    {
        abort_if($customOrder->buyer_id !== Auth::id(), 403);
        return view('userRequestDetails', compact('customOrder'));
    }

    /* ── Buyer: pay for an accepted custom order via Stripe ── */
    public function pay(CustomOrderRequest $customOrder)
    {
        abort_if($customOrder->buyer_id !== Auth::id(), 403);
        abort_if(! $customOrder->isAccepted(), 403);

        Stripe::setApiKey(config('services.stripe.secret'));

        $price = $customOrder->finalPrice();

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'myr',
                    'unit_amount'  => (int) round($price * 100),
                    'product_data' => [
                        'name'        => 'Custom Order: ' . $customOrder->title,
                        'description' => 'Custom artwork request from Craftistry',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            'success_url' => route('custom-orders.pay.success', $customOrder->id),
            'cancel_url'  => route('custom-orders.show', $customOrder->id),
            'metadata'    => [
                'custom_order_id' => $customOrder->id,
                'buyer_id'        => Auth::id(),
            ],
        ]);

        $customOrder->update(['stripe_session_id' => $session->id]);

        return redirect($session->url);
    }

    /* ── Stripe success callback ── */
    public function paySuccess(CustomOrderRequest $customOrder)
    {
        abort_if($customOrder->buyer_id !== Auth::id(), 403);

        // Avoid duplicate orders on page refresh
        if ($customOrder->order_id) {
            return redirect()->route('custom-orders.index')
                ->with('success', 'Your custom order is confirmed!');
        }

        // Find seller's artist record
        $artist = \App\Models\Artist::where('user_id', $customOrder->seller_id)->first();

        // Create a real order — starts at 'preparing' like normal orders
        $order = \App\Models\Order::create([
            'user_id'        => $customOrder->buyer_id,
            'artist_id'      => $artist?->id,
            'status'         => 'preparing',
            'payment_status' => 'paid',
            'total'          => $customOrder->finalPrice(),
            'notes'          => 'Custom Order: ' . $customOrder->title,
        ]);

        // Link order to custom request — keep status as 'accepted' (not completed)
        // Status will become 'completed' when order is fully done, same as normal flow
        $customOrder->update([
            'order_id' => $order->id,
        ]);

        return redirect()->route('custom-orders.index')
            ->with('success', 'Payment successful! Your custom order is confirmed. The seller will start preparing it.');
    }

    /* ── Buyer: accept counter-price ── */
    public function acceptCounter(CustomOrderRequest $customOrder)
    {
        abort_if($customOrder->buyer_id !== Auth::id(), 403);
        abort_if($customOrder->status !== 'refused' || ! $customOrder->hasCounterPrice(), 403);

        $customOrder->update([
            'status'         => 'accepted',
            'buyer_response' => 'accepted',
        ]);

        return redirect()->route('custom-orders.show', $customOrder)
            ->with('success', 'You accepted the counter offer! Please proceed to payment.');
    }

    /* ── Buyer: refuse counter-price → cancel ── */
    public function refuseCounter(CustomOrderRequest $customOrder)
    {
        abort_if($customOrder->buyer_id !== Auth::id(), 403);
        abort_if($customOrder->status !== 'refused' || ! $customOrder->hasCounterPrice(), 403);

        $customOrder->update([
            'status'         => 'cancelled',
            'buyer_response' => 'refused',
        ]);

        return redirect()->route('custom-orders.index')
            ->with('info', 'The custom order request has been cancelled.');
    }
}