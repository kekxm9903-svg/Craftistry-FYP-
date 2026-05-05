<?php

namespace App\Http\Controllers;

use App\Models\ArtworkSell;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductFavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Toggle favourite status for a product.
     * POST /products/{artworkSell}/favorite
     */
    public function toggle(ArtworkSell $artworkSell)
    {
        $user = Auth::user();

        if ($user->favoriteProducts()->where('artwork_sell_id', $artworkSell->id)->exists()) {
            $user->favoriteProducts()->detach($artworkSell->id);
            $favorited = false;
        } else {
            $user->favoriteProducts()->attach($artworkSell->id);
            $favorited = true;
        }

        return response()->json([
            'favorited' => $favorited,
            'message'   => $favorited ? 'Added to favourites' : 'Removed from favourites',
        ]);
    }
}