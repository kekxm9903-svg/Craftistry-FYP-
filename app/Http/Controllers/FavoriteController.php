<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Favorite;
use App\Models\ArtworkSell;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Toggle favourite status for an artist.
     * POST /artist/{user}/favorite
     */
    public function toggle(User $user)
    {
        $authUser = auth()->user();

        if ($authUser->id === $user->id) {
            return response()->json([
                'favorited' => false,
                'message'   => 'You cannot favourite yourself.',
            ], 422);
        }

        $existing = $authUser->favorites()->where('artist_id', $user->id)->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'favorited' => false,
                'message'   => 'Removed from favourites.',
            ]);
        }

        $authUser->favorites()->create(['artist_id' => $user->id]);

        return response()->json([
            'favorited' => true,
            'message'   => 'Added to favourites!',
        ]);
    }

    /**
     * Toggle favourite status for a product.
     * POST /products/{artworkSell}/favorite
     */
    public function toggleProduct(ArtworkSell $artworkSell)
    {
        $authUser = auth()->user();

        if ($authUser->favoriteProducts()->where('artwork_sell_id', $artworkSell->id)->exists()) {
            $authUser->favoriteProducts()->detach($artworkSell->id);
            return response()->json([
                'favorited' => false,
                'message'   => 'Removed from favourites.',
            ]);
        }

        $authUser->favoriteProducts()->attach($artworkSell->id);

        return response()->json([
            'favorited' => true,
            'message'   => 'Added to favourites!',
        ]);
    }

    /**
     * Show the favourites page with both artists and products.
     * GET /my-favorites
     */
    public function index()
    {
        $favoriteArtists = auth()->user()
            ->favoriteArtists()
            ->with(['artist.artworkTypes', 'artist.demoArtworks', 'artist.artworkSells'])
            ->get();

        $favoriteProducts = auth()->user()
            ->favoriteProducts()
            ->with(['artist', 'artist.user'])
            ->withPivot('created_at')
            ->get();

        return view('favoriteList', compact('favoriteArtists', 'favoriteProducts'));
    }
}