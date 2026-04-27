<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ArtworkSell;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class OrderCheckoutController extends Controller
{
    // Show checkout page — summary of cart items OR buy now item
    public function show(Request $request)
    {
        // ── Buy Now: single item bypassing cart ──────────────────────────────
        if ($request->filled('buy_now') && $request->filled('artwork_id')) {
            $artwork = ArtworkSell::find($request->artwork_id);

            if (!$artwork) {
                return redirect()->route('artist.browse')
                                 ->with('error', 'Artwork not found.');
            }

            $qty   = max(1, (int) $request->get('qty', 1));
            $price = (float) ($artwork->product_price ?? 0);

            $cartItems = [[
                'artwork'  => $artwork,
                'quantity' => $qty,
                'subtotal' => $price * $qty,
            ]];
            $total = $price * $qty;

            // Store buy_now context in session so process() knows
            session(['buy_now' => [
                'artwork_id' => $artwork->id,
                'qty'        => $qty,
            ]]);

            return view('orderCheckout', compact('cartItems', 'total'));
        }

        // ── Normal cart checkout ─────────────────────────────────────────────
        session()->forget('buy_now'); // clear any leftover buy_now session

        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')
                             ->with('error', 'Your cart is empty.');
        }

        $cartItems = [];
        $total     = 0;

        foreach ($cart as $id => $item) {
            $artwork = ArtworkSell::find($id);
            if ($artwork) {
                $price       = (float) ($artwork->product_price ?? $artwork->price ?? 0);
                $qty         = (int) ($item['quantity'] ?? 1);
                $cartItems[] = [
                    'artwork'  => $artwork,
                    'quantity' => $qty,
                    'subtotal' => $price * $qty,
                ];
                $total += $price * $qty;
            }
        }

        return view('orderCheckout', compact('cartItems', 'total'));
    }

    // Process payment — redirect to Stripe
    public function process(Request $request)
    {
        $user = auth()->user();

        Stripe::setApiKey(config('services.stripe.secret'));

        $lineItems       = [];
        $total           = 0;
        $groupedByArtist = [];

        // ── Buy Now flow ─────────────────────────────────────────────────────
        $buyNow = session('buy_now');

        if ($buyNow) {
            $artwork = ArtworkSell::with('artist')->find($buyNow['artwork_id']);

            if (!$artwork) {
                return redirect()->route('artist.browse')
                                 ->with('error', 'Artwork not found.');
            }

            $price    = (float) ($artwork->product_price ?? 0);
            $qty      = (int) ($buyNow['qty'] ?? 1);
            $name     = $artwork->product_name ?? 'Artwork';
            $artistId = $artwork->artist_id;

            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'myr',
                    'unit_amount'  => (int) round($price * 100),
                    'product_data' => [
                        'name'        => $name,
                        'description' => 'Craftistry Artwork Purchase',
                    ],
                ],
                'quantity' => $qty,
            ];
            $total = $price * $qty;

            $groupedByArtist[$artistId][] = [
                'artwork' => $artwork,
                'name'    => $name,
                'price'   => $price,
                'qty'     => $qty,
            ];

        } else {
            // ── Normal cart flow ─────────────────────────────────────────────
            $cart = session('cart', []);

            if (empty($cart)) {
                return redirect()->route('cart.index')
                                 ->with('error', 'Your cart is empty.');
            }

            foreach ($cart as $id => $item) {
                $artwork = ArtworkSell::with('artist')->find($id);
                if (!$artwork) continue;

                $price    = (float) ($artwork->product_price ?? $artwork->price ?? 0);
                $name     = $artwork->product_name ?? $artwork->title ?? 'Artwork';
                $qty      = (int) ($item['quantity'] ?? 1);
                $artistId = $artwork->artist_id;

                $lineItems[] = [
                    'price_data' => [
                        'currency'     => 'myr',
                        'unit_amount'  => (int) round($price * 100),
                        'product_data' => [
                            'name'        => $name,
                            'description' => 'Craftistry Artwork Purchase',
                        ],
                    ],
                    'quantity' => $qty,
                ];
                $total += $price * $qty;

                $groupedByArtist[$artistId][] = [
                    'artwork' => $artwork,
                    'name'    => $name,
                    'price'   => $price,
                    'qty'     => $qty,
                ];
            }
        }

        if (empty($lineItems)) {
            return redirect()->route('cart.index')
                             ->with('error', 'No valid items found.');
        }

        $stripeSession = Session::create([
            'payment_method_types' => ['card', 'fpx', 'grabpay'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => route('order.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => route('order.checkout.cancel'),
            'metadata'             => ['user_id' => $user->id],
        ]);

        foreach ($groupedByArtist as $artistId => $items) {
            $artistTotal = collect($items)->sum(fn($i) => $i['price'] * $i['qty']);

            $order = Order::create([
                'user_id'           => $user->id,
                'artist_id'         => $artistId,
                'type'              => 'product',
                'total'             => $artistTotal,
                'payment_status'    => 'pending',
                'status'            => 'pending_payment',
                'stripe_session_id' => $stripeSession->id,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id'        => $order->id,
                    'artwork_sell_id' => $item['artwork']->id,
                    'name'            => $item['name'] ?? $item['artwork']->product_name ?? 'Artwork',
                    'price'           => $item['price'],
                    'quantity'        => $item['qty'],
                ]);
            }
        }

        return redirect($stripeSession->url);
    }

    // Repay an existing pending order
    public function repay(Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);
        abort_if($order->status !== 'pending_payment', 403);

        Stripe::setApiKey(config('services.stripe.secret'));

        // Try to reuse existing Stripe session if still open
        if ($order->stripe_session_id) {
            try {
                $stripeSession = Session::retrieve($order->stripe_session_id);
                if ($stripeSession->status === 'open') {
                    return redirect($stripeSession->url);
                }
            } catch (\Exception $e) {
                // Session expired, create a new one below
            }
        }

        // Build line items from existing order items
        $lineItems = $order->items->map(fn($item) => [
            'price_data' => [
                'currency'     => 'myr',
                'unit_amount'  => (int) round($item->price * 100),
                'product_data' => [
                    'name'        => $item->name ?? 'Artwork',
                    'description' => 'Craftistry Artwork Purchase',
                ],
            ],
            'quantity' => $item->quantity,
        ])->toArray();

        $stripeSession = Session::create([
            'payment_method_types' => ['card', 'fpx', 'grabpay'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => route('order.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => route('order.checkout.cancel'),
            'metadata'             => ['user_id' => auth()->id()],
        ]);

        $order->update(['stripe_session_id' => $stripeSession->id]);

        return redirect($stripeSession->url);
    }

    // Payment success
    public function success(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $stripeSession = Session::retrieve($request->session_id);

            $orders = Order::where('stripe_session_id', $stripeSession->id)
                           ->with('items')
                           ->get();

            if ($orders->isNotEmpty() && $stripeSession->payment_status === 'paid') {
                foreach ($orders as $order) {
                    $order->update([
                        'payment_status' => 'paid',
                        'payment_method' => $stripeSession->payment_method_types[0] ?? 'card',
                        'status'         => 'processing',
                    ]);
                }

                // Clear cart AND buy_now session
                session()->forget('cart');
                session()->forget('buy_now');
            }

            $order = $orders->first();

            return view('orderCheckoutSuccessful', ['order' => $order]);

        } catch (\Exception $e) {
            return response(
                '<h2 style="font-family:monospace;padding:30px;color:red;">Checkout Error</h2>' .
                '<pre style="font-family:monospace;padding:30px;">' . $e->getMessage() .
                "\n\nFile: " . $e->getFile() .
                "\nLine: " . $e->getLine() . '</pre>'
            );
        }
    }

    // Payment cancelled
    public function cancel()
    {
        // If it was a buy_now, clear the session and go back
        if (session('buy_now')) {
            session()->forget('buy_now');
            return redirect()->back()
                             ->with('error', 'Payment was cancelled.');
        }

        return redirect()->route('cart.index')
                         ->with('error', 'Payment was cancelled. Your cart is still saved.');
    }
}