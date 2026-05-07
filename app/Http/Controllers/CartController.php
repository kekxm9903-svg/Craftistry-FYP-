<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ArtworkSell;
use App\Models\CartItem;

class CartController extends Controller
{
    /**
     * Sync session cart → DB (called after every mutation)
     */
    private function syncToDb(): void
    {
        $userId = auth()->id();
        if (!$userId) return;

        $cart = session('cart', []);

        CartItem::where('user_id', $userId)->delete();

        foreach ($cart as $artworkId => $item) {
            CartItem::create([
                'user_id'    => $userId,
                'artwork_id' => $artworkId,
                'quantity'   => $item['quantity'],
            ]);
        }
    }

    /**
     * Display the cart page.
     */
    public function index()
    {
        $cart      = session('cart', []);
        $cartItems = [];
        $subtotal  = 0;
        $shipping  = 0;

        foreach ($cart as $artworkId => $item) {
            // Always re-fetch fresh from DB so promotion changes are reflected immediately
            $fresh = ArtworkSell::find($artworkId);

            $effectivePrice   = $fresh ? $fresh->effective_price            : (float) ($item['price'] ?? 0);
            $originalPrice    = $fresh ? (float) $fresh->product_price      : $effectivePrice;
            $promotionPrice   = $fresh ? $fresh->promotion_price            : null;
            $promotionDiscount= $fresh ? $fresh->promotion_discount         : null;
            $shippingFee      = $fresh ? (float) ($fresh->shipping_fee ?? 0): 0;

            // Keep session in sync
            $cart[$artworkId]['price']        = $effectivePrice;
            $cart[$artworkId]['shipping_fee'] = $shippingFee;

            // Build item array for blade
            $item['price']              = $effectivePrice;
            $item['original_price']     = $originalPrice;
            $item['promotion_price']    = $promotionPrice;
            $item['promotion_discount'] = $promotionDiscount;
            $item['shipping_fee']       = $shippingFee;

            $cartItems[] = $item;
            $subtotal   += $effectivePrice * $item['quantity'];
            $shipping   += $shippingFee;
        }

        // Persist refreshed values back to session
        session(['cart' => $cart]);

        $total = $subtotal + $shipping;

        return view('cart', compact('cartItems', 'subtotal', 'shipping', 'total'));
    }

    /**
     * Add an artwork to the cart.
     * POST /cart/add  { artwork_id: int, quantity?: int }
     */
    public function add(Request $request)
    {
        $request->validate([
            'artwork_id' => 'required|integer|exists:artwork_sells,id',
            'quantity'   => 'sometimes|integer|min:1|max:99',
        ]);

        $artworkId = $request->artwork_id;
        $addQty    = (int) ($request->input('quantity', 1));
        $artwork   = ArtworkSell::with(['artist.user'])->findOrFail($artworkId);

        // Block sold-out items
        if (in_array(strtolower($artwork->status ?? ''), ['sold', 'sold_out'])) {
            return response()->json([
                'success' => false,
                'message' => 'This artwork has already been sold.',
            ]);
        }

        $cart = session('cart', []);

        if (isset($cart[$artworkId])) {
            // Already in cart — increment and refresh price
            $cart[$artworkId]['quantity']      += $addQty;
            $cart[$artworkId]['price']          = $artwork->effective_price; // ← promotion-aware
            $cart[$artworkId]['shipping_fee']   = (float) ($artwork->shipping_fee ?? 0);
            $message = 'Quantity updated in cart.';
        } else {
            $cart[$artworkId] = [
                'id'           => $artworkId,
                'name'         => $artwork->product_name ?? 'Untitled Artwork',
                'price'        => $artwork->effective_price, // ← promotion-aware
                'shipping_fee' => (float) ($artwork->shipping_fee ?? 0),
                'image_path'   => $artwork->image_path ?? null,
                'artwork_type' => $artwork->artwork_type ?? null,
                'artist_name'  => optional(optional($artwork->artist)->user)->fullname
                                  ?? optional(optional($artwork->artist)->user)->name
                                  ?? null,
                'quantity'     => $addQty,
            ];
            $message = 'Added to cart!';
        }

        session(['cart' => $cart]);
        $this->syncToDb();

        return response()->json([
            'success'    => true,
            'message'    => $message,
            'cart_count' => count($cart),
        ]);
    }

    /**
     * Update quantity of a cart item.
     * POST /cart/update  { artwork_id: int, delta: +1 or -1 }
     */
    public function update(Request $request)
    {
        $request->validate([
            'artwork_id' => 'required|integer',
            'delta'      => 'required|integer|in:-1,1',
        ]);

        $artworkId = $request->artwork_id;
        $delta     = $request->delta;
        $cart      = session('cart', []);

        if (!isset($cart[$artworkId])) {
            return response()->json(['success' => false, 'message' => 'Item not found in cart.']);
        }

        $cart[$artworkId]['quantity'] += $delta;

        $removed = false;
        if ($cart[$artworkId]['quantity'] <= 0) {
            unset($cart[$artworkId]);
            $removed = true;
        }

        session(['cart' => $cart]);
        $this->syncToDb();

        $subtotal  = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);
        $shipping  = collect($cart)->sum(fn($i) => $i['shipping_fee'] ?? 0);
        $cartTotal = $subtotal + $shipping;

        return response()->json([
            'success'    => true,
            'removed'    => $removed,
            'new_qty'    => $removed ? 0 : $cart[$artworkId]['quantity'],
            'item_total' => $removed ? '0.00' : number_format($cart[$artworkId]['price'] * $cart[$artworkId]['quantity'], 2),
            'subtotal'   => number_format($subtotal, 2),
            'shipping'   => number_format($shipping, 2),
            'cart_total' => number_format($cartTotal, 2),
            'cart_count' => count($cart),
        ]);
    }

    /**
     * Remove a single item from the cart.
     * POST /cart/remove  { artwork_id: int }
     */
    public function remove(Request $request)
    {
        $request->validate(['artwork_id' => 'required|integer']);

        $artworkId = $request->artwork_id;
        $cart      = session('cart', []);

        unset($cart[$artworkId]);
        session(['cart' => $cart]);
        $this->syncToDb();

        $subtotal  = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);
        $shipping  = collect($cart)->sum(fn($i) => $i['shipping_fee'] ?? 0);
        $cartTotal = $subtotal + $shipping;

        return response()->json([
            'success'    => true,
            'subtotal'   => number_format($subtotal, 2),
            'shipping'   => number_format($shipping, 2),
            'cart_total' => number_format($cartTotal, 2),
            'cart_count' => count($cart),
        ]);
    }

    /**
     * Clear the entire cart.
     * POST /cart/clear
     */
    public function clear()
    {
        session()->forget('cart');
        CartItem::where('user_id', auth()->id())->delete();

        return response()->json([
            'success'    => true,
            'cart_count' => 0,
        ]);
    }
}