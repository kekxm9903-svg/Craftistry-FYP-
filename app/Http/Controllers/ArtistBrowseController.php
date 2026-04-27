<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Artist;
use App\Models\ArtworkSell;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArtistBrowseController extends Controller
{
    /**
     * Display all artwork sells (one card per artwork, not per artist)
     */
    public function index(Request $request)
    {
        $query = ArtworkSell::with(['artist.user'])
            ->whereHas('artist.user')
            ->whereNotNull('image_path')
            ->whereNotIn('status', ['sold', 'sold_out']);

        // Search by artwork name OR artist name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhereHas('artist.user', function ($q2) use ($search) {
                      $q2->where('fullname', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by artist specialization
        if ($request->filled('specialty')) {
            $query->whereHas('artist', function ($q) use ($request) {
                $q->where('specialization', $request->specialty);
            });
        }

        // Sort options
        switch ($request->get('sort', 'latest')) {
            case 'name':
                $query->orderBy('product_name', 'asc');
                break;
            case 'price':
                $query->orderBy('product_price', 'asc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $artworks = $query->paginate(20)->withQueryString();

        // Specialties for category pills
        $specialties = Artist::whereNotNull('specialization')
            ->where('specialization', '!=', '')
            ->distinct()
            ->pluck('specialization')
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        return view('artistBrowse', compact('artworks', 'specialties'));
    }

    /**
     * Display single artist detail page
     */
    public function show($id)
    {
        Log::info('Artist Profile Accessed', [
            'requested_id' => $id,
            'route_params' => request()->route()->parameters(),
        ]);

        $user = User::with([
            'artist',
            'demoArtworks' => function ($q) {
                $q->orderBy('order', 'asc');
            },
            'artworkSells' => function ($q) {
                $q->orderBy('created_at', 'desc');
            },
        ])->findOrFail($id);

        Log::info('Artist Profile Loaded', [
            'loaded_user_id'      => $user->id,
            'loaded_user_name'    => $user->fullname,
            'demo_artworks_count' => $user->demoArtworks->count(),
            'artworks_sell_count' => $user->artworkSells->count(),
        ]);

        if (!$user->artist) {
            abort(404, 'Artist profile not found');
        }

        return view('artistShow', compact('user'));
    }

    /**
     * DEBUG METHOD — Check database relationships
     * Access via: /artists/debug-check
     */
    public function debugCheck()
    {
        $results = [];

        $artists = User::whereHas('artist')->with('artist')->get();

        foreach ($artists as $artist) {
            $demoCount         = DB::table('demo_artworks')->where('user_id', $artist->id)->count();
            $relationshipCount = $artist->demoArtworks()->count();

            $results[] = [
                'user_id'                        => $artist->id,
                'name'                           => $artist->fullname,
                'email'                          => $artist->email,
                'demo_artworks_in_db'            => $demoCount,
                'demo_artworks_via_relationship' => $relationshipCount,
                'match'                          => $demoCount === $relationshipCount ? '✓' : '✗ MISMATCH!',
                'sample_artwork_ids'             => DB::table('demo_artworks')
                    ->where('user_id', $artist->id)
                    ->pluck('id')
                    ->toArray(),
            ];
        }

        return response()->json([
            'debug_results'         => $results,
            'total_artists'         => count($results),
            'artists_with_artworks' => collect($results)->where('demo_artworks_in_db', '>', 0)->count(),
        ], 200, [], JSON_PRETTY_PRINT);
    }
}