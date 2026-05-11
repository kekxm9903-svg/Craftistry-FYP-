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

        // ── Badge counts ──
        $activeOrdersPending = Order::where('user_id', $user->id)
                                    ->where('payment_status', 'paid')
                                    ->where('status', 'shipped')
                                    ->count();

        $customOrdersPending = CustomOrderRequest::where('buyer_id', $user->id)
                                ->where('status', 'refused')
                                ->whereNotNull('counter_price')
                                ->whereNull('buyer_response')
                                ->count();

        // ── Hot Artworks — ranked by total units sold in completed orders ──
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
            ->take(8)
            ->get();

        // If not enough sold artworks, pad with latest unsold ones
        if ($hotProducts->count() < 4) {
            $existingIds = $hotProducts->pluck('id')->toArray();
            $padding = ArtworkSell::with(['artist.user'])
                ->whereHas('artist.user')
                ->whereNotNull('image_path')
                ->whereNotIn('status', ['sold', 'sold_out'])
                ->whereNotIn('id', $existingIds)
                ->latest()
                ->take(8 - $hotProducts->count())
                ->get()
                ->map(function ($item) {
                    $item->total_sold = 0;
                    return $item;
                });
            $hotProducts = $hotProducts->merge($padding);
        }

        // ── Hot Artists — ranked by total units sold across all their artworks ──
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
            'upcomingClasses'
        ));
    }
}