<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // ── Existing stats ──
        $favoriteArtists = $user->favorites()->count();

        $activeOrders = Order::where('user_id', $user->id)
                             ->where('payment_status', 'paid')
                             ->whereIn('status', ['processing', 'preparing', 'shipped'])
                             ->count();

        $enrolledClasses = Booking::where('user_id', $user->id)->count();

        // ── Custom Orders stats ──
        $customOrdersCount = CustomOrderRequest::where('buyer_id', $user->id)->count();

        $customOrdersPending = CustomOrderRequest::where('buyer_id', $user->id)
                                ->where('status', 'refused')
                                ->whereNotNull('counter_price')
                                ->whereNull('buyer_response')
                                ->count();

        // ── Top Artists (most artworks for sale) ──
        $topArtists = Artist::with(['user', 'artworkSells' => function ($q) {
                                $q->whereNotIn('status', ['sold', 'sold_out'])
                                  ->whereNotNull('image_path');
                            }])
                            ->whereHas('user')
                            ->withCount('artworkSells')
                            ->orderBy('artwork_sells_count', 'desc')
                            ->take(10)
                            ->get();

        // ── Hot Products (latest available artworks) ──
        $hotProducts = ArtworkSell::with(['artist.user'])
                            ->whereHas('artist.user')
                            ->whereNotIn('status', ['sold', 'sold_out'])
                            ->whereNotNull('image_path')
                            ->latest()
                            ->take(8)
                            ->get();

        // ── Upcoming Classes ──
        $upcomingClasses = ClassEvent::with('user')
                            ->where('start_date', '>', today())
                            ->orderBy('start_date', 'asc')
                            ->take(5)
                            ->get();

        return view('dashboard', compact(
            'user',
            'favoriteArtists',
            'activeOrders',
            'enrolledClasses',
            'customOrdersCount',
            'customOrdersPending',
            'topArtists',
            'hotProducts',
            'upcomingClasses'
        ));
    }
}