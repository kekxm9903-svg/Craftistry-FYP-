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
    public function index(Request $request)
    {
        $pref = auth()->check() ? auth()->user()->preferred_artwork_type : null;

        $query = ArtworkSell::with(['artist.user'])
            ->whereHas('artist.user')
            ->whereNotNull('image_path')
            ->whereNotIn('status', ['sold', 'sold_out']);

        // ── Hide the logged-in user's own listings ──
        if (auth()->check() && auth()->user()->artist) {
            $query->where('artist_id', '!=', auth()->user()->artist->id);
        }

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

        // Filter by artwork_type (physical / digital)
        if ($request->filled('type')) {
            $query->where('artwork_type', $request->type);
        }

        // Filter by product_category (Drawing, Knitting, etc.)
        if ($request->filled('category')) {
            $query->where('product_category', $request->category);
        }

        // Sort
        $sort = $request->get('sort', 'latest');

        if ($sort === 'name') {
            $query->orderBy('product_name', 'asc');
        } elseif ($sort === 'price') {
            $query->orderBy('product_price', 'asc');
        } else {
            // Default: latest — push preferred category to top if no explicit category filter
            if ($pref && !$request->filled('category')) {
                $query->orderByRaw("CASE WHEN product_category = ? THEN 0 ELSE 1 END", [$pref])
                      ->latest();
            } else {
                $query->latest();
            }
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

        // Product categories for filter pills
        $categories = ArtworkSell::whereNotNull('product_category')
            ->where('product_category', '!=', '')
            ->whereNotIn('status', ['sold', 'sold_out'])
            ->distinct()
            ->pluck('product_category')
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        return view('artistBrowse', compact('artworks', 'specialties', 'categories', 'pref'));
    }

    public function show($id)
    {
        Log::info('Artist Profile Accessed', ['requested_id' => $id]);

        $pref = auth()->check() ? auth()->user()->preferred_artwork_type : null;

        $user = User::with([
            'artist.artworkTypes',
            'demoArtworks' => fn($q) => $q->orderBy('order', 'asc'),
        ])->findOrFail($id);

        if (!$user->artist) {
            abort(404, 'Artist profile not found');
        }

        // Sort artworks — preferred category first
        $artworkSellsQuery = ArtworkSell::where('artist_id', $user->artist->id);

        if ($pref) {
            $artworkSellsQuery
                ->orderByRaw("CASE WHEN product_category = ? THEN 0 ELSE 1 END", [$pref])
                ->orderBy('created_at', 'desc');
        } else {
            $artworkSellsQuery->orderBy('created_at', 'desc');
        }

        $user->setRelation('artworkSells', $artworkSellsQuery->get());

        Log::info('Artist Profile Loaded', [
            'loaded_user_id'      => $user->id,
            'loaded_user_name'    => $user->fullname,
            'demo_artworks_count' => $user->demoArtworks->count(),
            'artworks_sell_count' => $user->artworkSells->count(),
        ]);

        return view('artistShow', compact('user', 'pref'));
    }

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