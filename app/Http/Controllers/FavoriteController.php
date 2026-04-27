<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Toggle favorite status for an artist.
     * POST /artist/{user}/favorite
     */
    public function toggle(User $user)
    {
        $authUser = auth()->user();

        if ($authUser->id === $user->id) {
            return response()->json([
                'favorited' => false,
                'message'   => 'You cannot favorite yourself.',
            ], 422);
        }

        $existing = $authUser->favorites()->where('artist_id', $user->id)->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'favorited' => false,
                'message'   => 'Removed from favorites.',
            ]);
        }

        $authUser->favorites()->create(['artist_id' => $user->id]);

        return response()->json([
            'favorited' => true,
            'message'   => 'Added to favorites!',
        ]);
    }

    /**
     * List all favorited artists for the authenticated user.
     * GET /my-favorites
     */
    public function index()
    {
        $favorites = auth()->user()
            ->favoriteArtists()
            ->with(['artist.artworkTypes', 'artist.demoArtworks', 'artist.artworkSells'])
            ->get();

        return view('favoriteList', compact('favorites'));
    }
}