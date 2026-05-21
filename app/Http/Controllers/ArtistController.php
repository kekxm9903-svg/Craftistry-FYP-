<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\ArtworkType;
use App\Models\Order;
use App\Models\CustomOrderRequest;
use App\Models\BulkOrder;
use App\Models\DemoArtwork;
use App\Models\ArtworkSell;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ArtistController extends Controller
{
    // ========================================
    // PROFILE METHODS
    // ========================================

    public function profile()
    {
        $user = Auth::user();

        if (!$user->artist) {
            return redirect()->route('studio')->with('error', 'Please register as an artist first.');
        }

        $artist = $user->artist()->with(['artworkTypes', 'demoArtworks', 'artworkSells'])->first();

        $pendingOrdersCount = Order::where('artist_id', $artist->id)
            ->whereIn('status', ['processing', 'preparing'])
            ->where('payment_status', 'paid')
            ->count();

        $pendingRequestsCount = CustomOrderRequest::where('seller_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $pendingBulkCount = BulkOrder::whereHas('artworkSell', function ($q) use ($artist) {
                $q->where('artist_id', $artist->id);
            })
            ->where('status', 'pending')
            ->count();

        $pendingRequestsCount = $pendingRequestsCount + $pendingBulkCount;

        return view('artistProfile', compact(
            'artist',
            'pendingOrdersCount',
            'pendingRequestsCount'
        ));
    }

    public function edit()
    {
        $user = Auth::user();

        if (!$user->artist) {
            return redirect()->route('studio')->with('error', 'Please register as an artist first.');
        }

        $artist       = $user->artist()->with('artworkTypes')->first();
        $artworkTypes = ArtworkType::all();

        return view('artistEditProfile', compact('artist', 'artworkTypes', 'user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user->artist) {
            return redirect()->route('studio')->with('error', 'Please register as an artist first.');
        }

        $validated = $request->validate([
            'fullname'             => 'required|string|max:255',
            'specialization'       => 'required|string|max:255',
            'bio'                  => 'required|string|max:1000',
            'artwork_types'        => 'required|array|min:1',
            'artwork_types.*'      => 'exists:artwork_types,id',
            'profile_image'        => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'remove_profile_image' => 'nullable|boolean',
        ], [
            'fullname.required'        => 'Full name is required.',
            'specialization.required'  => 'Specialization is required.',
            'bio.required'             => 'Bio is required.',
            'bio.max'                  => 'Bio cannot exceed 1000 characters.',
            'artwork_types.required'   => 'Please select at least one artwork type.',
            'artwork_types.min'        => 'Please select at least one artwork type.',
            'artwork_types.*.exists'   => 'One or more selected artwork types are invalid.',
            'profile_image.image'      => 'Profile picture must be an image.',
            'profile_image.mimes'      => 'Profile picture must be a JPEG, JPG, PNG, or WEBP file.',
            'profile_image.max'        => 'Profile picture must not exceed 5MB.',
        ]);

        $artist = $user->artist;

        try {
            DB::beginTransaction();

            $user->update(['fullname' => $validated['fullname']]);

            if ($request->input('remove_profile_image') == '1') {
                if ($user->profile_image && $user->profile_image !== 'images/Profile.png') {
                    Storage::disk('public')->delete($user->profile_image);
                }
                $user->update(['profile_image' => null]);
            } elseif ($request->hasFile('profile_image')) {
                if ($user->profile_image && $user->profile_image !== 'images/Profile.png') {
                    Storage::disk('public')->delete($user->profile_image);
                }
                $path = $request->file('profile_image')->store('profile_image', 'public');
                $user->update(['profile_image' => $path]);
            }

            $artist->update([
                'specialization'     => $validated['specialization'],
                'bio'                => $validated['bio'],
                'allow_customization' => $request->boolean('allow_customization'),
            ]);

            $artist->artworkTypes()->sync($validated['artwork_types']);

            DB::commit();

            return redirect()->route('artist.profile')->with('success', 'Profile updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Artist profile update failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update profile. Please try again.');
        }
    }

    public function dashboard()
    {
        $user = Auth::user();

        if (!$user->artist) {
            return redirect()->route('studio');
        }

        $artist = $user->artist()->with('artworkTypes')->first();

        $stats = [
            'total_artworks' => $artist->demoArtworks()->count(),
            'total_sales'    => 0,
            'pending_orders' => 0,
            'total_revenue'  => 0,
        ];

        return view('artist-dashboard', compact('artist', 'stats', 'user'));
    }

    // ========================================
    // DEMO ARTWORK METHODS
    // ========================================

    public function uploadDemo(Request $request)
    {
        $request->validate([
            'images'               => 'required|array|min:1',
            'images.*'             => 'image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'title'                => 'required|string|max:255',
            'description'          => 'nullable|string|max:1000',
            'also_sell'            => 'nullable|boolean',
            // Sale fields — required only when also_sell is checked
            'artwork_type'         => 'required_if:also_sell,1|in:physical,digital',
            'product_price'        => 'required_if:also_sell,1|numeric|min:0.01|max:999999.99',
            'shipping_fee'         => 'nullable|numeric|min:0',
            'material'             => 'required_if:also_sell,1|string|max:255',
            'height'               => 'required_if:also_sell,1|numeric|min:0',
            'width'                => 'required_if:also_sell,1|numeric|min:0',
            'depth'                => 'nullable|numeric|min:0',
            'unit'                 => 'nullable|in:cm,inch,px',
            'status'               => 'required_if:also_sell,1|in:available,sold_out',
            'product_category'     => 'nullable|string|max:255',
            'product_description'  => 'nullable|string|max:2000',
            // Bulk sell
            'bulk_sell_enabled'    => 'nullable|boolean',
            'bulk_sell_min_qty'    => 'nullable|integer|min:2',
            'bulk_sell_discount'   => 'nullable|numeric|min:1|max:99',
            // Promotion
            'promotion_enabled'    => 'nullable|boolean',
            'promotion_discount'   => 'nullable|numeric|min:1|max:99',
            'promotion_starts_at'  => 'nullable|date',
            'promotion_ends_at'    => 'nullable|date|after_or_equal:promotion_starts_at',
        ]);

        $artist = Auth::user()->artist;

        if (!$artist) {
            return redirect()->back()->with('error', 'Artist profile not found');
        }

        try {
            DB::beginTransaction();

            // Use first image as main
            $imagePath = $request->file('images')[0]->store('demo_artworks', 'public');

            // Create demo artwork
            DemoArtwork::create([
                'artist_id'  => $artist->id,
                'title'      => $request->title,
                'description' => $request->description,
                'image_path' => $imagePath,
            ]);

            // If also_sell is checked, create artwork sell entry
            if ($request->input('also_sell') == '1') {

                $promoEnabled = $request->boolean('promotion_enabled');
                $bulkEnabled  = $request->boolean('bulk_sell_enabled');

                ArtworkSell::create([
                    'artist_id'           => $artist->id,
                    'product_name'        => $request->product_name ?: $request->title,
                    'product_description' => $request->product_description ?: $request->description,
                    'product_price'       => $request->product_price,
                    'shipping_fee'        => $request->shipping_fee ?? 0,
                    'artwork_type'        => $request->artwork_type,
                    'product_category'    => $request->product_category,
                    'material'            => $request->material,
                    'height'              => $request->height,
                    'width'               => $request->width,
                    'depth'               => $request->depth,
                    'unit'                => $request->unit ?? 'cm',
                    'status'              => $request->status,
                    'image_path'          => $imagePath,
                    // Bulk sell
                    'bulk_sell_enabled'   => $bulkEnabled,
                    'bulk_sell_min_qty'   => $bulkEnabled ? $request->bulk_sell_min_qty : null,
                    'bulk_sell_discount'  => $bulkEnabled ? $request->bulk_sell_discount : null,
                    // Promotion
                    'promotion_enabled'   => $promoEnabled,
                    'promotion_discount'  => $promoEnabled ? $request->promotion_discount : null,
                    'promotion_starts_at' => $promoEnabled ? $request->promotion_starts_at : null,
                    'promotion_ends_at'   => $promoEnabled ? $request->promotion_ends_at : null,
                ]);
            }

            DB::commit();

            return redirect()->route('artist.profile')->with('success', 'Demo artwork uploaded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Demo upload failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to upload demo artwork. Please try again.');
        }
    }

    public function editDemo($id)
    {
        try {
            $demo = DemoArtwork::findOrFail($id);

            if ($demo->artist_id !== Auth::user()->artist->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return response()->json([
                'success'   => true,
                'id'        => $demo->id,
                'title'     => $demo->title,
                'description' => $demo->description,
                'image_url' => $demo->image_url,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to load demo data', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateDemo(Request $request, $id)
    {
        try {
            $demo = DemoArtwork::findOrFail($id);

            if ($demo->artist_id !== Auth::user()->artist->id) {
                return redirect()->back()->with('error', 'Unauthorized action');
            }

            $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'image'       => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            ]);

            $demo->title       = $request->title;
            $demo->description = $request->description;

            if ($request->hasFile('image')) {
                if ($demo->image_path && Storage::disk('public')->exists($demo->image_path)) {
                    Storage::disk('public')->delete($demo->image_path);
                }
                $demo->image_path = $request->file('image')->store('demo_artworks', 'public');
            }

            $demo->save();

            return redirect()->route('artist.profile')->with('success', 'Demo artwork updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Demo update failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to update demo artwork. Please try again.');
        }
    }

    public function deleteDemo($id)
    {
        try {
            $demo = DemoArtwork::findOrFail($id);

            if ($demo->artist_id !== Auth::user()->artist->id) {
                return redirect()->back()->with('error', 'Unauthorized action');
            }

            if ($demo->image_path && Storage::disk('public')->exists($demo->image_path)) {
                Storage::disk('public')->delete($demo->image_path);
            }

            $demo->delete();

            return redirect()->route('artist.profile')->with('success', 'Demo artwork deleted successfully!');

        } catch (\Exception $e) {
            \Log::error('Demo delete failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete demo artwork. Please try again.');
        }
    }

    // ========================================
    // ARTWORK SELL METHODS
    // ========================================

    public function uploadArtworkSell(Request $request)
    {
        $request->validate([
            'images'              => 'required|array|min:1',
            'images.*'            => 'image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'product_name'        => 'required|string|max:255',
            'product_price'       => 'required|numeric|min:0.01|max:999999.99',
            'shipping_fee'        => 'nullable|numeric|min:0',
            'product_description' => 'nullable|string|max:2000',
            'artwork_type'        => 'required|in:physical,digital',
            'product_category'    => 'nullable|string|max:255',
            'material'            => 'required|string|max:255',
            'height'              => 'required|numeric|min:0',
            'width'               => 'required|numeric|min:0',
            'depth'               => 'nullable|numeric|min:0',
            'unit'                => 'nullable|in:cm,inch,px',
            'status'              => 'required|in:available,sold_out',
            'also_demo'           => 'nullable|boolean',
            // Bulk sell
            'bulk_sell_enabled'   => 'nullable|boolean',
            'bulk_sell_min_qty'   => 'nullable|integer|min:2',
            'bulk_sell_discount'  => 'nullable|numeric|min:1|max:99',
            // Promotion
            'promotion_enabled'   => 'nullable|boolean',
            'promotion_discount'  => 'nullable|numeric|min:1|max:99',
            'promotion_starts_at' => 'nullable|date',
            'promotion_ends_at'   => 'nullable|date|after_or_equal:promotion_starts_at',
        ]);

        $artist = Auth::user()->artist;

        if (!$artist) {
            return redirect()->back()->with('error', 'Artist profile not found');
        }

        try {
            DB::beginTransaction();

            $imagePath = $request->file('images')[0]->store('artwork_sells', 'public');

            $promoEnabled = $request->boolean('promotion_enabled');
            $bulkEnabled  = $request->boolean('bulk_sell_enabled');

            $artwork = ArtworkSell::create([
                'artist_id'           => $artist->id,
                'product_name'        => $request->product_name,
                'product_price'       => $request->product_price,
                'shipping_fee'        => $request->shipping_fee ?? 0,
                'product_description' => $request->product_description,
                'artwork_type'        => $request->artwork_type,
                'product_category'    => $request->product_category,
                'material'            => $request->material,
                'height'              => $request->height,
                'width'               => $request->width,
                'depth'               => $request->depth,
                'unit'                => $request->unit ?? 'cm',
                'status'              => $request->status,
                'image_path'          => $imagePath,
                // Bulk sell
                'bulk_sell_enabled'   => $bulkEnabled,
                'bulk_sell_min_qty'   => $bulkEnabled ? $request->bulk_sell_min_qty : null,
                'bulk_sell_discount'  => $bulkEnabled ? $request->bulk_sell_discount : null,
                // Promotion
                'promotion_enabled'   => $promoEnabled,
                'promotion_discount'  => $promoEnabled ? $request->promotion_discount : null,
                'promotion_starts_at' => $promoEnabled ? $request->promotion_starts_at : null,
                'promotion_ends_at'   => $promoEnabled ? $request->promotion_ends_at : null,
            ]);

            if ($request->input('also_demo') == '1') {
                DemoArtwork::create([
                    'artist_id'   => $artist->id,
                    'title'       => $request->product_name,
                    'description' => $request->product_description,
                    'image_path'  => $imagePath,
                ]);
            }

            DB::commit();

            return redirect()->route('artist.profile')->with('success', 'Artwork listed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Artwork upload failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to upload artwork. Please try again.');
        }
    }

    public function editArtwork($id)
    {
        try {
            $artwork = ArtworkSell::findOrFail($id);

            if ($artwork->artist_id !== Auth::user()->artist->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return response()->json([
                'success'             => true,
                'id'                  => $artwork->id,
                'product_name'        => $artwork->product_name,
                'product_price'       => $artwork->product_price,
                'product_description' => $artwork->product_description,
                'artwork_type'        => $artwork->artwork_type,
                'material'            => $artwork->material,
                'height'              => $artwork->height,
                'width'               => $artwork->width,
                'depth'               => $artwork->depth,
                'unit'                => $artwork->unit,
                'status'              => $artwork->status,
                'image_url'           => $artwork->image_url,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to load artwork data', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateArtwork(Request $request, $id)
    {
        try {
            $artwork = ArtworkSell::findOrFail($id);

            if ($artwork->artist_id !== Auth::user()->artist->id) {
                return redirect()->back()->with('error', 'Unauthorized action');
            }

            $request->validate([
                'product_name'        => 'required|string|max:255',
                'product_price'       => 'required|numeric|min:0.01|max:999999.99',
                'product_description' => 'nullable|string|max:2000',
                'artwork_type'        => 'required|in:physical,digital',
                'material'            => 'required|string|max:255',
                'height'              => 'required|numeric|min:0',
                'width'               => 'required|numeric|min:0',
                'depth'               => 'nullable|numeric|min:0',
                'unit'                => 'nullable|in:cm,inch,px',
                'status'              => 'required|in:available,sold_out',
                'image'               => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            ]);

            $artwork->product_name        = $request->product_name;
            $artwork->product_price       = $request->product_price;
            $artwork->product_description = $request->product_description;
            $artwork->artwork_type        = $request->artwork_type;
            $artwork->material            = $request->material;
            $artwork->height              = $request->height;
            $artwork->width               = $request->width;
            $artwork->depth               = $request->depth;
            $artwork->unit                = $request->unit ?? 'cm';
            $artwork->status              = $request->status;

            if ($request->hasFile('image')) {
                if ($artwork->image_path && Storage::disk('public')->exists($artwork->image_path)) {
                    Storage::disk('public')->delete($artwork->image_path);
                }
                $artwork->image_path = $request->file('image')->store('artwork_sells', 'public');
            }

            $artwork->save();

            return redirect()->route('artist.profile')->with('success', 'Artwork updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Artwork update failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to update artwork. Please try again.');
        }
    }

    public function deleteArtwork($id)
    {
        try {
            $artwork = ArtworkSell::findOrFail($id);

            if ($artwork->artist_id !== Auth::user()->artist->id) {
                return redirect()->back()->with('error', 'Unauthorized action');
            }

            if ($artwork->image_path && Storage::disk('public')->exists($artwork->image_path)) {
                Storage::disk('public')->delete($artwork->image_path);
            }

            $artwork->delete();

            return redirect()->route('artist.profile')->with('success', 'Artwork deleted successfully!');

        } catch (\Exception $e) {
            \Log::error('Artwork delete failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete artwork. Please try again.');
        }
    }

    // ========================================
    // REPORT METHOD
    // ========================================

    public function report(Request $request, $id)
    {
        $request->validate([
            'reason'  => 'required|string',
            'details' => 'nullable|string|max:500',
        ]);

        if (auth()->id() == $id) {
            return response()->json(['error' => 'You cannot report yourself.'], 422);
        }

        $alreadyReported = Report::where('reporter_id', auth()->id())
            ->where('reported_user_id', $id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyReported) {
            return response()->json(['error' => 'You have already submitted a pending report for this artist.'], 422);
        }

        Report::create([
            'reporter_id'      => auth()->id(),
            'reported_user_id' => $id,
            'reason'           => $request->reason,
            'details'          => $request->details,
            'status'           => 'pending',
        ]);

        return response()->json(['success' => true]);
    }
}