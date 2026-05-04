<?php

namespace App\Http\Controllers;

use App\Models\ArtworkSell;
use App\Models\DemoArtwork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ArtworkSellController extends Controller
{
    public function sellPage()
    {
        return view('sellUploadPage');
    }

    public function editPage($id)
    {
        $user    = Auth::user();
        $artwork = ArtworkSell::where('id', $id)->where('artist_id', $user->id)->firstOrFail();
        return view('sellEditPage', compact('artwork'));
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user->artist) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                }
                return redirect()->back()->with('error', 'Unauthorized');
            }

            $validated = $request->validate([
                'product_name'        => 'required|string|max:255',
                'product_description' => 'nullable|string|max:2000',
                'product_price'       => 'required|numeric|min:0.01|max:999999.99',
                'shipping_fee'        => 'nullable|numeric|min:0|max:9999.99',
                'images'              => 'required|array|min:1',
                'images.*'            => 'image|mimes:jpeg,jpg,png,gif,webp|max:5120',
                'artwork_type'        => 'required|in:physical,digital',
                'material'            => 'required|string|max:255',
                'height'              => 'required|numeric|min:0',
                'width'               => 'required|numeric|min:0',
                'depth'               => 'nullable|numeric|min:0',
                'unit'                => 'required|in:cm,inch,px',
                'status'              => 'required|in:available,sold_out',
                'also_demo'           => 'nullable',
                'bulk_sell_enabled'   => 'nullable|boolean',
                'bulk_sell_min_qty'   => 'nullable|integer|min:2',
                'bulk_sell_discount'  => 'nullable|numeric|min:1|max:99',
                'promotion_enabled'   => 'nullable|boolean',
                'promotion_discount'  => 'nullable|numeric|min:1|max:99',
                'promotion_starts_at' => 'nullable|date',
                'promotion_ends_at'   => 'nullable|date|after_or_equal:promotion_starts_at',
            ], [
                'images.required' => 'At least one image is required',
                'images.min'      => 'At least one image is required',
                'images.*.image'  => 'Each file must be an image',
                'images.*.mimes'  => 'Images must be jpeg, jpg, png, gif, or webp',
                'images.*.max'    => 'Each image must be under 5MB',
            ]);

            // Upload first image as main cover
            $path = null;
            Log::info('SELL FILES RECEIVED: ' . json_encode(array_keys($request->allFiles())));
            Log::info('SELL IMAGES COUNT: ' . count($request->file('images', [])));
            if ($request->hasFile('images')) {
                $images   = $request->file('images');
                Log::info('SELL IMAGES ARRAY COUNT: ' . count($images));
                $main     = $images[0];
                $filename = time() . '_' . uniqid() . '.' . $main->getClientOriginalExtension();
                $path     = $main->storeAs('artwork-sells', $filename, 'public');

                // Store additional images
                $extraPaths = [];
                foreach (array_slice($images, 1) as $extra) {
                    $extraFilename = time() . '_' . uniqid() . '.' . $extra->getClientOriginalExtension();
                    $extraPath     = $extra->storeAs('artwork-sells', $extraFilename, 'public');
                    if ($extraPath) $extraPaths[] = $extraPath;
                }
            }

            DB::beginTransaction();

            $bulkEnabled = $request->boolean('bulk_sell_enabled');

            $newDemo = null;
            if ($request->has('also_demo')) {
                $maxOrder = DemoArtwork::where('artist_id', $user->id)->max('order');
                $order    = $maxOrder !== null ? $maxOrder + 1 : 0;

                $newDemo = DemoArtwork::create([
                    'artist_id'       => $user->id,
                    'title'           => $validated['product_name'],
                    'description'     => $validated['product_description'] ?? null,
                    'image_path'      => $path,
                    'extra_images'    => !empty($extraPaths) ? $extraPaths : null,
                    'order'           => $order,
                    'artwork_type'    => $validated['artwork_type'],
                    'material'        => $validated['material'],
                    'height'          => $validated['height'],
                    'width'           => $validated['width'],
                    'depth'           => $request->input('depth'),
                    'unit'            => $validated['unit'],
                    'price'           => $validated['product_price'],
                    'is_cross_posted' => false,
                ]);
            }

            $artworkSell = ArtworkSell::create([
                'artist_id'            => $user->id,
                'product_name'         => $validated['product_name'],
                'product_description'  => $validated['product_description'] ?? null,
                'product_price'        => $validated['product_price'],
                'shipping_fee'         => $request->input('shipping_fee') ?? 0,
                'image_path'           => $path,
                'status'               => $validated['status'],
                'artwork_type'         => $validated['artwork_type'],
                'material'             => $validated['material'],
                'height'               => $validated['height'],
                'width'                => $validated['width'],
                'depth'                => $request->input('depth'),
                'unit'                 => $validated['unit'],
                'extra_images'         => !empty($extraPaths) ? $extraPaths : null,
                'is_cross_posted'      => $newDemo ? true : false,
                'cross_posted_from_id' => $newDemo ? $newDemo->id : null,
                'bulk_sell_enabled'    => $bulkEnabled,
                'bulk_sell_min_qty'    => $bulkEnabled ? $request->input('bulk_sell_min_qty') : null,
                'bulk_sell_discount'   => $bulkEnabled ? $request->input('bulk_sell_discount') : null,
                'promotion_enabled'    => $request->boolean('promotion_enabled'),
                'promotion_discount'   => $request->boolean('promotion_enabled') ? $request->input('promotion_discount') : null,
                'promotion_starts_at'  => $request->boolean('promotion_enabled') ? $request->input('promotion_starts_at') : null,
                'promotion_ends_at'    => $request->boolean('promotion_enabled') ? $request->input('promotion_ends_at') : null,
            ]);

            if ($newDemo) {
                $newDemo->is_cross_posted    = true;
                $newDemo->cross_posted_to_id = $artworkSell->id;
                $newDemo->save();
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Artwork listed successfully!',
                    'artwork' => [
                        'id'                 => $artworkSell->id,
                        'product_name'       => $artworkSell->product_name,
                        'formatted_price'    => $artworkSell->formatted_price,
                        'shipping_fee'       => $artworkSell->shipping_fee,
                        'image_url'          => $artworkSell->image_url,
                        'status'             => $artworkSell->status,
                        'status_label'       => $artworkSell->status_label,
                        'artwork_type'       => $artworkSell->artwork_type,
                        'bulk_sell_enabled'  => $artworkSell->bulk_sell_enabled,
                        'bulk_sell_min_qty'  => $artworkSell->bulk_sell_min_qty,
                        'bulk_sell_discount' => $artworkSell->bulk_sell_discount,
                    ]
                ]);
            }

            return redirect()->route('artist.profile')->with('success', 'Artwork listed successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            Log::error('Sell upload error: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $user = Auth::user();
            if (!$user->artist) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            $artwork = ArtworkSell::where('id', $id)->where('artist_id', $user->id)->firstOrFail();
            return response()->json([
                'success'             => true,
                'id'                  => $artwork->id,
                'product_name'        => $artwork->product_name,
                'product_description' => $artwork->product_description,
                'product_price'       => $artwork->product_price,
                'shipping_fee'        => $artwork->shipping_fee ?? 0,
                'image_url'           => $artwork->image_url,
                'image_path'          => $artwork->image_path,
                'artwork_type'        => $artwork->artwork_type,
                'material'            => $artwork->material,
                'height'              => $artwork->height,
                'width'               => $artwork->width,
                'depth'               => $artwork->depth,
                'unit'                => $artwork->unit,
                'status'              => $artwork->status,
                'is_cross_posted'     => $artwork->is_cross_posted,
                'bulk_sell_enabled'   => (bool) $artwork->bulk_sell_enabled,
                'bulk_sell_min_qty'   => $artwork->bulk_sell_min_qty,
                'bulk_sell_discount'  => $artwork->bulk_sell_discount,
            ]);
        } catch (\Exception $e) {
            Log::error('Edit fetch error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to load artwork'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user->artist) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                }
                return redirect()->back()->with('error', 'Unauthorized');
            }

            $validated = $request->validate([
                'product_name'        => 'required|string|max:255',
                'product_description' => 'nullable|string|max:2000',
                'product_price'       => 'required|numeric|min:0.01|max:999999.99',
                'shipping_fee'        => 'nullable|numeric|min:0|max:9999.99',
                'new_images'          => 'nullable|array',
                'new_images.*'        => 'image|mimes:jpeg,jpg,png,gif,webp|max:5120',
                'delete_images'       => 'nullable|array',
                'delete_images.*'     => 'nullable|string',
                'artwork_type'        => 'required|in:physical,digital',
                'material'            => 'required|string|max:255',
                'height'              => 'required|numeric|min:0',
                'width'               => 'required|numeric|min:0',
                'depth'               => 'nullable|numeric|min:0',
                'unit'                => 'required|in:cm,inch,px',
                'status'              => 'required|in:available,sold_out',
                'bulk_sell_enabled'   => 'nullable|boolean',
                'bulk_sell_min_qty'   => 'nullable|integer|min:2',
                'bulk_sell_discount'  => 'nullable|numeric|min:1|max:99',
                'promotion_enabled'   => 'nullable|boolean',
                'promotion_discount'  => 'nullable|numeric|min:1|max:99',
                'promotion_starts_at' => 'nullable|date',
                'promotion_ends_at'   => 'nullable|date|after_or_equal:promotion_starts_at',
                'promotion_enabled'   => 'nullable|boolean',
                'promotion_discount'  => 'nullable|numeric|min:1|max:99',
                'promotion_starts_at' => 'nullable|date',
                'promotion_ends_at'   => 'nullable|date|after_or_equal:promotion_starts_at',
            ]);

            $artwork = ArtworkSell::where('id', $id)->where('artist_id', $user->id)->firstOrFail();

            DB::beginTransaction();

            $bulkEnabled = $request->boolean('bulk_sell_enabled');

            $artwork->product_name        = $validated['product_name'];
            $artwork->product_description = $validated['product_description'];
            $artwork->product_price       = $validated['product_price'];
            $artwork->shipping_fee        = $request->input('shipping_fee') ?? 0;
            $artwork->artwork_type        = $validated['artwork_type'];
            $artwork->material            = $validated['material'];
            $artwork->height              = $validated['height'];
            $artwork->width               = $validated['width'];
            $artwork->depth               = $request->input('depth');
            $artwork->unit                = $validated['unit'];
            $artwork->status              = $validated['status'];
            $artwork->bulk_sell_enabled   = $bulkEnabled;
            $artwork->bulk_sell_min_qty   = $bulkEnabled ? $request->input('bulk_sell_min_qty') : null;
            $artwork->bulk_sell_discount  = $bulkEnabled ? $request->input('bulk_sell_discount') : null;
            $promoEnabled = $request->boolean('promotion_enabled');
            $artwork->promotion_enabled   = $promoEnabled;
            $artwork->promotion_discount  = $promoEnabled ? $request->input('promotion_discount') : null;
            $artwork->promotion_starts_at = $promoEnabled ? $request->input('promotion_starts_at') : null;
            $artwork->promotion_ends_at   = $promoEnabled ? $request->input('promotion_ends_at') : null;

            // Handle image deletions
            $deleteImages   = $request->input('delete_images', []);
            $existingExtras = $artwork->extra_images ?? [];

            foreach ($deleteImages as $deletePath) {
                if (Storage::disk('public')->exists($deletePath)) {
                    Storage::disk('public')->delete($deletePath);
                }
                $existingExtras = array_values(array_filter($existingExtras, fn($p) => $p !== $deletePath));
                if ($deletePath === $artwork->image_path) {
                    if (!empty($existingExtras)) {
                        $artwork->image_path = array_shift($existingExtras);
                    } else {
                        $artwork->image_path = null;
                    }
                }
            }
            $artwork->extra_images = !empty($existingExtras) ? array_values($existingExtras) : null;

            // Handle new image uploads
            $imageUpdated = false;
            if ($request->hasFile('new_images')) {
                $newImages = $request->file('new_images');
                $newPaths  = $artwork->extra_images ?? [];

                foreach ($newImages as $img) {
                    $filename = time() . '_' . uniqid() . '.' . $img->getClientOriginalExtension();
                    $stored   = $img->storeAs('artwork-sells', $filename, 'public');
                    if ($stored) {
                        if (!$artwork->image_path) {
                            $artwork->image_path = $stored;
                        } else {
                            $newPaths[] = $stored;
                        }
                        $imageUpdated = true;
                    }
                }
                $artwork->extra_images = !empty($newPaths) ? array_values($newPaths) : null;
            }

            if ($artwork->is_cross_posted && $artwork->crossPostedFrom) {
                $demo              = $artwork->crossPostedFrom;
                $demo->title       = $validated['product_name'];
                $demo->description = $validated['product_description'];
                if ($imageUpdated) $demo->image_path = $artwork->image_path;
                $demo->save();
            }

            $artwork->save();
            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Artwork updated successfully!',
                    'artwork' => [
                        'id'                  => $artwork->id,
                        'product_name'        => $artwork->product_name,
                        'product_description' => $artwork->product_description,
                        'formatted_price'     => $artwork->formatted_price,
                        'shipping_fee'        => $artwork->shipping_fee,
                        'image_url'           => $artwork->image_url,
                        'artwork_type'        => $artwork->artwork_type,
                        'status'              => $artwork->status,
                        'status_label'        => $artwork->status_label,
                        'bulk_sell_enabled'   => $artwork->bulk_sell_enabled,
                        'bulk_sell_min_qty'   => $artwork->bulk_sell_min_qty,
                        'bulk_sell_discount'  => $artwork->bulk_sell_discount,
                    ]
                ]);
            }

            return redirect()->route('artist.profile')->with('success', 'Artwork updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update error: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user->artist) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                }
                return redirect()->back()->with('error', 'Unauthorized');
            }

            $artworkSell = ArtworkSell::where('id', $id)->where('artist_id', $user->id)->first();
            if (!$artworkSell) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Not found'], 404);
                }
                return redirect()->back()->with('error', 'Not found');
            }

            DB::beginTransaction();

            // Unlink from demo — keep demo record intact
            if ($artworkSell->is_cross_posted && $artworkSell->crossPostedFrom) {
                $demo                     = $artworkSell->crossPostedFrom;
                $demo->is_cross_posted    = false;
                $demo->cross_posted_to_id = null;
                $demo->save();
            }

            if ($artworkSell->image_path && Storage::disk('public')->exists($artworkSell->image_path)) {
                Storage::disk('public')->delete($artworkSell->image_path);
            }
            $artworkSell->delete();
            DB::commit();

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Artwork deleted successfully!']);
            }
            return redirect()->route('artist.profile')->with('success', 'Artwork deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete error: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete'], 500);
            }
            return redirect()->back()->with('error', 'Failed to delete');
        }
    }

    public function unlinkDemo($id)
    {
        try {
            $user = Auth::user();
            if (!$user->artist) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            $artworkSell = ArtworkSell::where('id', $id)->where('artist_id', $user->id)->first();
            if (!$artworkSell || !$artworkSell->is_cross_posted) {
                return response()->json(['success' => false, 'message' => 'Not cross-posted'], 404);
            }
            DB::beginTransaction();
            if ($artworkSell->crossPostedFrom) {
                $demo                     = $artworkSell->crossPostedFrom;
                $demo->is_cross_posted    = false;
                $demo->cross_posted_to_id = null;
                $demo->save();
            }
            $artworkSell->is_cross_posted      = false;
            $artworkSell->cross_posted_from_id = null;
            $artworkSell->save();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Unlinked successfully! Both items now separate.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to unlink'], 500);
        }
    }
}