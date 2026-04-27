<?php

namespace App\Http\Controllers;

use App\Models\ArtworkSell;
use App\Models\Review;

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
        $reviewCount  = $reviews->count();
        $averageRating = $reviewCount > 0 ? round($reviews->avg('rating'), 1) : 0;

        // Count per star (5 down to 1)
        $starCounts = [];
        for ($i = 5; $i >= 1; $i--) {
            $starCounts[$i] = $reviews->where('rating', $i)->count();
        }

        return view('product', compact('artwork', 'reviews', 'reviewCount', 'averageRating', 'starCounts'));
    }
}