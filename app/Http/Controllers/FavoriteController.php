<?php

namespace App\Http\Controllers;

use App\Models\ArtworkSell;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Show the favourites page.
     */
    public function index()
    {
        $user = Auth::user();

        $favoriteArtists = $user->favoriteArtists()
            ->with('artist.artworkTypes')
            ->withPivot('created_at')
            ->latest('favorites.created_at')
            ->get();

        $favoriteProducts = $user->favoriteProducts()
            ->with('artist.user')
            ->withPivot('created_at')
            ->latest('user_favorite_products.created_at')
            ->get();

        return view('favoriteList', compact('favoriteArtists', 'favoriteProducts'));
    }

    /**
     * Toggle artist favourite (used by the heart button on artist profile/browse pages).
     */
    public function toggle(User $user)
    {
        $authUser = Auth::user();

        $existing = $authUser->favorites()->where('artist_id', $user->id)->first();

        if ($existing) {
            $existing->delete();
            $favorited = false;
        } else {
            $authUser->favorites()->create(['artist_id' => $user->id]);
            $favorited = true;
        }

        return response()->json(['favorited' => $favorited]);
    }

    /**
     * Always unfavourite an artist — used by the favourites list page.
     */
    public function unfavorite(User $user)
    {
        Auth::user()->favorites()->where('artist_id', $user->id)->delete();

        return response()->json(['favorited' => false]);
    }

    /**
     * Toggle product favourite (used by the heart button on product pages).
     */
    public function toggleProduct(ArtworkSell $artworkSell)
    {
        $user = Auth::user();

        if ($user->favoriteProducts()->where('artwork_sell_id', $artworkSell->id)->exists()) {
            $user->favoriteProducts()->detach($artworkSell->id);
            $favorited = false;
        } else {
            $user->favoriteProducts()->attach($artworkSell->id);
            $favorited = true;
        }

        return response()->json(['favorited' => $favorited]);
    }

    /**
     * Always unfavourite a product — used by the favourites list page.
     */
    public function unfavoriteProduct(ArtworkSell $artworkSell)
    {
        Auth::user()->favoriteProducts()->detach($artworkSell->id);

        return response()->json(['favorited' => false]);
    }
}