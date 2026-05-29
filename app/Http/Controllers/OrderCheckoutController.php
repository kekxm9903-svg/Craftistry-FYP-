<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ArtworkSell;
use App\Models\CartItem;
use App\Services\NotificationService;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class OrderCheckoutController extends Controller
{
    private function hasPhysicalItem(array $cartItems): bool
    {
        foreach ($cartItems as $item) {
            $type = $item['artwork']->artwork_type ?? '';
            if (in_array($type, ['physical', 'both'])) return true;
        }
        return false;
    }

    private function userHasAddress($user): bool
    {
        return !empty($user->address)
            && !empty($user->city)
            && !empty($user->state)
            && !empty($user->postcode);
    }

    public function show(Request $request)
    {
        $user = auth()->user();

        if ($request->filled('buy_now') && $request->filled('artwork_id')) {
            $artwork = ArtworkSell::find($request->artwork_id);
            if (!$artwork) {
                return redirect()->route('artist.browse')->with('error', 'Artwork not found.');
            }

            $qty         = max(1, (int) $request->get('qty', 1));
            $price       = $artwork->resolveUnitPrice($qty);
            $shippingFee = (float) ($artwork->shipping_fee ?? 0);

            $cartItems = [[
                'artwork'      => $artwork,
                'price'        => $price,
                'quantity'     => $qty,
                'subtotal'     => $price * $qty,
                'shipping_fee' => $shippingFee,
            ]];
            $total = ($price * $qty) + $shippingFee;

            session(['buy_now' => ['artwork_id' => $artwork->id, 'qty' => $qty]]);

            $needsAddress = $this->hasPhysicalItem($cartItems);
            $hasAddress   = $this->userHasAddress($user);

            return view('orderCheckout', compact('cartItems', 'total', 'user', 'needsAddress', 'hasAddress'));
        }

        session()->forget('buy_now');
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $cartItems = [];
        $total     = 0;

        foreach ($cart as $id => $item) {
            $artwork = ArtworkSell::find($id);
            if ($artwork) {
                $qty         = (int) ($item['quantity'] ?? 1);
                $price       = $artwork->resolveUnitPrice($qty);
                $shippingFee = (float) ($artwork->shipping_fee ?? 0);

                $cartItems[] = [
                    'artwork'      => $artwork,
                    'price'        => $price,
                    'quantity'     => $qty,
                    'subtotal'     => $price * $qty,
                    'shipping_fee' => $shippingFee,
                ];
                $total += ($price * $qty) + $shippingFee;
            }
        }

        $needsAddress = $this->hasPhysicalItem($cartItems);
        $hasAddress   = $this->userHasAddress($user);

        return view('orderCheckout', compact('cartItems', 'total', 'user', 'needsAddress', 'hasAddress'));
    }

    public function process(Request $request)
    {
        $user   = auth()->user();
        $buyNow = session('buy_now');

        $cartItems = [];
        if ($buyNow) {
            $artwork = ArtworkSell::find($buyNow['artwork_id']);
            if ($artwork) $cartItems[] = ['artwork' => $artwork];
        } else {
            foreach (session('cart', []) as $id => $item) {
                $artwork = ArtworkSell::find($id);
                if ($artwork) $cartItems[] = ['artwork' => $artwork];
            }
        }

        if ($this->hasPhysicalItem($cartItems) && !$this->userHasAddress($user)) {
            return redirect()->route('order.checkout.show')
                ->with('error', 'Please set your shipping address before placing an order for physical items.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $lineItems       = [];
        $total           = 0;
        $groupedByArtist = [];

        if ($buyNow) {
            $artwork = ArtworkSell::with('artist')->find($buyNow['artwork_id']);
            if (!$artwork) {
                return redirect()->route('artist.browse')->with('error', 'Artwork not found.');
            }

            $qty         = (int) ($buyNow['qty'] ?? 1);
            $price       = $artwork->resolveUnitPrice($qty);
            $shippingFee = (float) ($artwork->shipping_fee ?? 0);
            $name        = $artwork->product_name ?? 'Artwork';
            $artistId    = $artwork->artist_id;

            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'myr',
                    'unit_amount'  => (int) round($price * 100),
                    'product_data' => ['name' => $name, 'description' => 'Craftistry Artwork Purchase'],
                ],
                'quantity' => $qty,
            ];

            if ($shippingFee > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency'     => 'myr',
                        'unit_amount'  => (int) round($shippingFee * 100),
                        'product_data' => ['name' => 'Shipping Fee', 'description' => 'Shipping for ' . $name],
                    ],
                    'quantity' => 1,
                ];
            }

            $total = ($price * $qty) + $shippingFee;
            $groupedByArtist[$artistId][] = [
                'artwork'      => $artwork,
                'name'         => $name,
                'price'        => $price,
                'qty'          => $qty,
                'shipping_fee' => $shippingFee,
            ];

        } else {
            $cart = session('cart', []);
            if (empty($cart)) {
                return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
            }

            foreach ($cart as $id => $item) {
                $artwork = ArtworkSell::with('artist')->find($id);
                if (!$artwork) continue;

                $qty         = (int) ($item['quantity'] ?? 1);
                $price       = $artwork->resolveUnitPrice($qty);
                $shippingFee = (float) ($artwork->shipping_fee ?? 0);
                $name        = $artwork->product_name ?? 'Artwork';
                $artistId    = $artwork->artist_id;

                $lineItems[] = [
                    'price_data' => [
                        'currency'     => 'myr',
                        'unit_amount'  => (int) round($price * 100),
                        'product_data' => ['name' => $name, 'description' => 'Craftistry Artwork Purchase'],
                    ],
                    'quantity' => $qty,
                ];

                if ($shippingFee > 0) {
                    $lineItems[] = [
                        'price_data' => [
                            'currency'     => 'myr',
                            'unit_amount'  => (int) round($shippingFee * 100),
                            'product_data' => ['name' => 'Shipping Fee', 'description' => 'Shipping for ' . $name],
                        ],
                        'quantity' => 1,
                    ];
                }

                $total += ($price * $qty) + $shippingFee;
                $groupedByArtist[$artistId][] = [
                    'artwork'      => $artwork,
                    'name'         => $name,
                    'price'        => $price,
                    'qty'          => $qty,
                    'shipping_fee' => $shippingFee,
                ];
            }
        }

        if (empty($lineItems)) {
            return redirect()->route('cart.index')->with('error', 'No valid items found.');
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
            $artistSubtotal  = collect($items)->sum(fn($i) => $i['price'] * $i['qty']);
            $artistShipping  = collect($items)->sum(fn($i) => $i['shipping_fee']);
            $artistTotal     = $artistSubtotal + $artistShipping;

            $order = Order::create([
                'user_id'           => $user->id,
                'artist_id'         => $artistId,
                'type'              => 'product',
                'total'             => $artistTotal,
                'shipping_fee'      => $artistShipping,
                'payment_status'    => 'pending',
                'status'            => 'pending_payment',
                'stripe_session_id' => $stripeSession->id,
                'shipping_address'  => $user->address,
                'shipping_city'     => $user->city,
                'shipping_state'    => $user->state,
                'shipping_postcode' => $user->postcode,
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

    public function repay(Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);
        abort_if($order->status !== 'pending_payment', 403);

        Stripe::setApiKey(config('services.stripe.secret'));

        if ($order->stripe_session_id) {
            try {
                $stripeSession = Session::retrieve($order->stripe_session_id);
                if ($stripeSession->status === 'open') {
                    return redirect($stripeSession->url);
                }
            } catch (\Exception $e) {}
        }

        $lineItems = [];

        foreach ($order->items as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'myr',
                    'unit_amount'  => (int) round($item->price * 100),
                    'product_data' => ['name' => $item->name ?? 'Artwork', 'description' => 'Craftistry Artwork Purchase'],
                ],
                'quantity' => $item->quantity,
            ];
        }

        if (($order->shipping_fee ?? 0) > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'myr',
                    'unit_amount'  => (int) round($order->shipping_fee * 100),
                    'product_data' => ['name' => 'Shipping Fee', 'description' => 'Shipping fee'],
                ],
                'quantity' => 1,
            ];
        }

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

    public function cancelOrder(Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);

        if ($order->status !== 'pending_payment') {
            return back()->with('error', 'This order can no longer be cancelled.');
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('success', 'Order cancelled successfully.');
    }

    public function success(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $stripeSession = Session::retrieve($request->session_id);

            $orders = Order::where('stripe_session_id', $stripeSession->id)
                           ->with('items', 'artist.user')
                           ->get();

            if ($orders->isNotEmpty() && $stripeSession->payment_status === 'paid') {
                $buyer = auth()->user();

                foreach ($orders as $order) {
                    $alreadyPaid = $order->payment_status === 'paid';

                    $order->update([
                        'payment_status' => 'paid',
                        'payment_method' => $stripeSession->payment_method_types[0] ?? 'card',
                        'status'         => 'processing',
                    ]);

                    if (!$alreadyPaid) {
                        $sellerUserId = $order->artist->user_id ?? null;
                        $firstItem    = $order->items->first();
                        $productName  = $firstItem->name ?? 'Artwork';

                        if ($sellerUserId) {
                            NotificationService::newOrder(
                                $sellerUserId,
                                $order->id,
                                $buyer->fullname ?? $buyer->name ?? 'A buyer',
                                $productName
                            );
                        }
                    }
                }

                if (session('buy_now')) {
                    session()->forget('buy_now');
                } else {
                    session()->forget('cart');
                    CartItem::where('user_id', auth()->id())->delete();
                }
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

    public function cancel()
    {
        if (session('buy_now')) {
            session()->forget('buy_now');
            return redirect()->back()->with('error', 'Payment was cancelled.');
        }

        return redirect()->route('cart.index')
                         ->with('error', 'Payment was cancelled. Your cart is still saved.');
    }
}