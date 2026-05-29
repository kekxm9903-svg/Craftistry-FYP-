<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Booking;
use App\Models\Order;
use App\Models\Artist;
use App\Models\ArtworkSell;
use App\Models\ClassEvent;
use App\Models\CustomOrderRequest;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $pref = $user->preferred_artwork_type;

        // ── Favourite count ──
        $favoriteArtists = $user->favorites()->count()
                         + $user->favoriteProducts()->count();

        // ── Stats ──
        $activeOrders = Order::where('user_id', $user->id)
                             ->where('payment_status', 'paid')
                             ->whereIn('status', ['processing', 'preparing', 'shipped'])
                             ->count();

        $enrolledClasses   = Booking::where('user_id', $user->id)->count();
        $customOrdersCount = CustomOrderRequest::where('buyer_id', $user->id)->count();

        // ── Badge: Orders (shipped = awaiting receipt confirmation) ──
        $activeOrdersPending = Order::where('user_id', $user->id)
                                    ->where('payment_status', 'paid')
                                     ->whereIn('status', ['processing', 'preparing', 'shipped'])
                                    ->count();

        // ── Badge: Custom Orders ──
        // Show noti when:
        //   1. pending   — waiting for seller to respond
        //   2. refused + counter_price + no buyer_response — seller sent counter-offer
        //   3. accepted + no order_id — seller accepted, buyer hasn't paid yet
        // No noti when:
        //   - refused with no counter (outright rejected)
        //   - completed (paid, order created)
        //   - cancelled
        $customOrdersPending = CustomOrderRequest::where('buyer_id', $user->id)
            ->where(function ($q) {
                // Waiting for seller to respond
                $q->where('status', 'pending');
            })
            ->orWhere(function ($q) use ($user) {
                // Seller sent counter-offer, buyer hasn't responded
                $q->where('buyer_id', $user->id)
                  ->where('status', 'refused')
                  ->whereNotNull('counter_price')
                  ->whereNull('buyer_response');
            })
            ->orWhere(function ($q) use ($user) {
                // Seller accepted, buyer hasn't paid yet
                $q->where('buyer_id', $user->id)
                  ->where('status', 'accepted')
                  ->whereNull('order_id');
            })
            ->count();

        // ── Hot Artworks ──
        $hotProducts = ArtworkSell::with(['artist.user'])
            ->whereHas('artist.user')
            ->whereNotNull('image_path')
            ->whereNotIn('status', ['sold', 'sold_out'])
            ->addSelect([
                'artwork_sells.*',
                DB::raw('(
                    SELECT COALESCE(SUM(oi.quantity), 0)
                    FROM order_items oi
                    JOIN orders o ON oi.order_id = o.id
                    WHERE oi.artwork_sell_id = artwork_sells.id
                      AND o.status = "completed"
                ) as total_sold'),
            ])
            ->orderByDesc('total_sold')
            ->orderByDesc('created_at')
            ->take(18)
            ->get();

        // ── On Sale ──
        $onSaleProducts = ArtworkSell::with(['artist.user'])
            ->whereHas('artist.user')
            ->whereNotNull('image_path')
            ->whereNotIn('status', ['sold', 'sold_out'])
            ->where('promotion_enabled', true)
            ->whereNotNull('promotion_discount')
            ->orderByDesc('created_at')
            ->take(18)
            ->get();

        // ── Hot Artists ──
        $hotArtists = Artist::with(['user', 'artworkSells' => function ($q) {
                                $q->whereNotIn('status', ['sold', 'sold_out'])
                                  ->whereNotNull('image_path');
                            }])
                            ->whereHas('user')
                            ->addSelect([
                                'artists.*',
                                DB::raw('(
                                    SELECT COALESCE(SUM(oi.quantity), 0)
                                    FROM order_items oi
                                    JOIN orders o ON oi.order_id = o.id
                                    JOIN artwork_sells aws ON oi.artwork_sell_id = aws.id
                                    WHERE aws.artist_id = artists.id
                                      AND o.status = "completed"
                                ) as total_sold'),
                            ])
                            ->orderByDesc('total_sold')
                            ->take(10)
                            ->get();

        // ── Upcoming Classes ──
        $upcomingClasses = ClassEvent::with('user')
                            ->where('start_date', '>', today())
                            ->orderBy('start_date', 'asc')
                            ->take(5)
                            ->get();

        return view('dashboard', compact(
            'user',
            'pref',
            'favoriteArtists',
            'activeOrders',
            'enrolledClasses',
            'customOrdersCount',
            'activeOrdersPending',
            'customOrdersPending',
            'hotArtists',
            'hotProducts',
            'onSaleProducts',
            'upcomingClasses'
        ));
    }
}