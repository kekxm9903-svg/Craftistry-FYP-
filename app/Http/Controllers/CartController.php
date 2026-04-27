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
            // Always re-fetch shipping_fee fresh from DB in case it was updated or session is stale
            $fresh = ArtworkSell::select('shipping_fee', 'product_price')
                        ->find($artworkId);

            $item['shipping_fee'] = $fresh ? (float)($fresh->shipping_fee ?? 0) : 0;
            $item['price']        = $fresh ? (float)($fresh->product_price ?? $item['price']) : $item['price'];

            // Sync the session with fresh values
            $cart[$artworkId]['shipping_fee'] = $item['shipping_fee'];

            $cartItems[] = $item;
            $subtotal   += $item['price']        * $item['quantity'];
            $shipping   += $item['shipping_fee'];
        }

        // Save refreshed shipping fees back to session
        session(['cart' => $cart]);

        $total = $subtotal + $shipping;

        return view('cart', compact('cartItems', 'subtotal', 'shipping', 'total'));
    }

    /**
     * Add an artwork to the cart.
     * POST /cart/add  { artwork_id: int }
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
            // Already in cart — increment by requested quantity and refresh shipping_fee
            $cart[$artworkId]['quantity']     += $addQty;
            $cart[$artworkId]['shipping_fee']  = (float)($artwork->shipping_fee ?? 0);
            $message = 'Quantity updated in cart.';
        } else {
            // Add new item
            $cart[$artworkId] = [
                'id'           => $artworkId,
                'name'         => $artwork->product_name ?? 'Untitled Artwork',
                'price'        => (float) ($artwork->product_price ?? 0),
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
        $this->syncToDb(); // ← persist to DB

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
        $this->syncToDb(); // ← persist to DB

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
        $this->syncToDb(); // ← persist to DB

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
        CartItem::where('user_id', auth()->id())->delete(); // ← clear from DB too

        return response()->json([
            'success'    => true,
            'cart_count' => 0,
        ]);
    }
}