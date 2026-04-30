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
    /**
     * Store a new artwork for sale
     */
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
                'image'               => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
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
            ]);

            // Upload Image
            $path = null;
            if ($request->hasFile('image')) {
                $image    = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path     = $image->storeAs('artwork-sells', $filename, 'public');
            }

            DB::beginTransaction();

            $bulkEnabled = $request->boolean('bulk_sell_enabled');

            // 1. Create Demo Record (if checkbox checked)
            $newDemo = null;
            if ($request->has('also_demo')) {
                $maxOrder = DemoArtwork::where('artist_id', $user->id)->max('order');
                $order    = $maxOrder !== null ? $maxOrder + 1 : 0;

                $newDemo = DemoArtwork::create([
                    'artist_id'       => $user->id,
                    'title'           => $validated['product_name'],
                    'description'     => $validated['product_description'] ?? null,
                    'image_path'      => $path,
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

            // 2. Create Sell Record
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
                'is_cross_posted'      => $newDemo ? true : false,
                'cross_posted_from_id' => $newDemo ? $newDemo->id : null,
                'bulk_sell_enabled'    => $bulkEnabled,
                'bulk_sell_min_qty'    => $bulkEnabled ? $request->input('bulk_sell_min_qty') : null,
                'bulk_sell_discount'   => $bulkEnabled ? $request->input('bulk_sell_discount') : null,
            ]);

            // 3. Link Demo to Sell Record
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

            return redirect()->back()->with('success', 'Artwork listed successfully!');

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

    /**
     * Get artwork data for editing (AJAX)
     */
    public function edit($id)
    {
        try {
            $user = Auth::user();
            if (!$user->artist) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $artwork = ArtworkSell::where('id', $id)
                ->where('artist_id', $user->id)
                ->firstOrFail();

            return response()->json([
                'success'              => true,
                'id'                   => $artwork->id,
                'product_name'         => $artwork->product_name,
                'product_description'  => $artwork->product_description,
                'product_price'        => $artwork->product_price,
                'shipping_fee'         => $artwork->shipping_fee ?? 0,
                'image_url'            => $artwork->image_url,
                'image_path'           => $artwork->image_path,
                'artwork_type'         => $artwork->artwork_type,
                'material'             => $artwork->material,
                'height'               => $artwork->height,
                'width'                => $artwork->width,
                'depth'                => $artwork->depth,
                'unit'                 => $artwork->unit,
                'status'               => $artwork->status,
                'is_cross_posted'      => $artwork->is_cross_posted,
                'bulk_sell_enabled'    => (bool) $artwork->bulk_sell_enabled,
                'bulk_sell_min_qty'    => $artwork->bulk_sell_min_qty,
                'bulk_sell_discount'   => $artwork->bulk_sell_discount,
            ]);

        } catch (\Exception $e) {
            Log::error('Edit fetch error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to load artwork'], 500);
        }
    }

    /**
     * Update existing artwork
     */
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

            Log::info('Update Request Data: ' . json_encode($request->except(['image', '_token'])));
            Log::info('Has Image File: ' . ($request->hasFile('image') ? 'true' : 'false'));

            $validated = $request->validate([
                'product_name'        => 'required|string|max:255',
                'product_description' => 'nullable|string|max:2000',
                'product_price'       => 'required|numeric|min:0.01|max:999999.99',
                'shipping_fee'        => 'nullable|numeric|min:0|max:9999.99',
                'image'               => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
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
            ]);

            $artwork = ArtworkSell::where('id', $id)
                ->where('artist_id', $user->id)
                ->firstOrFail();

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

            // Handle image update
            $imageUpdated = false;
            if ($request->hasFile('image')) {
                Log::info('Processing new image upload');

                if ($artwork->image_path && Storage::disk('public')->exists($artwork->image_path)) {
                    Storage::disk('public')->delete($artwork->image_path);
                }

                $image    = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $newPath  = $image->storeAs('artwork-sells', $filename, 'public');

                if ($newPath) {
                    $artwork->image_path = $newPath;
                    $imageUpdated        = true;
                    Log::info('New image saved: ' . $newPath);
                }
            }

            // Sync with Demo (only Title, Description, Image)
            if ($artwork->is_cross_posted && $artwork->crossPostedFrom) {
                $demo              = $artwork->crossPostedFrom;
                $demo->title       = $validated['product_name'];
                $demo->description = $validated['product_description'];
                if ($imageUpdated) {
                    $demo->image_path = $artwork->image_path;
                }
                $demo->save();
                Log::info('Cross-posted demo updated');
            }

            $artwork->save();

            DB::commit();

            Log::info('Artwork updated successfully - ID: ' . $artwork->id);

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

            return redirect()->back()->with('success', 'Artwork updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Validation error: ' . json_encode($e->errors()));
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Delete artwork
     */
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

            $artworkSell = ArtworkSell::where('id', $id)
                ->where('artist_id', $user->id)
                ->first();

            if (!$artworkSell) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Not found'], 404);
                }
                return redirect()->back()->with('error', 'Not found');
            }

            DB::beginTransaction();

            $wasCrossListed = $artworkSell->isCrossListed();

            if ($wasCrossListed && $artworkSell->crossPostedFrom) {
                $artworkSell->crossPostedFrom->delete();
            }

            if (Storage::disk('public')->exists($artworkSell->image_path)) {
                Storage::disk('public')->delete($artworkSell->image_path);
            }

            $artworkSell->delete();

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Artwork deleted successfully!']);
            }
            return redirect()->back()->with('success', 'Artwork deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete error: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete'], 500);
            }
            return redirect()->back()->with('error', 'Failed to delete');
        }
    }

    /**
     * Unlink cross-posted demo
     */
    public function unlinkDemo($id)
    {
        try {
            $user = Auth::user();
            if (!$user->artist) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $artworkSell = ArtworkSell::where('id', $id)
                ->where('artist_id', $user->id)
                ->first();

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