<?php

namespace App\Http\Controllers;

use App\Models\ArtworkSell;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function show($id)
    {
        $artwork = ArtworkSell::with(['artist.user'])->findOrFail($id);

        // Load reviews for this artwork with buyer info, newest first
        $reviews = Review::with('user')
            ->where('artwork_sell_id', $id)
            ->latest()
            ->get();

        // Summary stats
        $reviewCount   = $reviews->count();
        $averageRating = $reviewCount > 0 ? round($reviews->avg('rating'), 1) : 0;

        // Count per star (5 down to 1)
        $starCounts = [];
        for ($i = 5; $i >= 1; $i--) {
            $starCounts[$i] = $reviews->where('rating', $i)->count();
        }

        // Whether the logged-in user has favourited this product
        $isFavorited = false;
        if (Auth::check()) {
            $isFavorited = Auth::user()
                ->favoriteProducts()
                ->where('artwork_sell_id', $id)
                ->exists();
        }

        return view('product', compact(
            'artwork',
            'reviews',
            'reviewCount',
            'averageRating',
            'starCounts',
            'isFavorited'
        ));
    }
}