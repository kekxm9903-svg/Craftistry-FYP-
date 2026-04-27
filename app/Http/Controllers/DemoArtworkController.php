<?php

namespace App\Http\Controllers;

use App\Models\DemoArtwork;
use App\Models\ArtworkSell;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DemoArtworkController extends Controller
{
    /**
     * Store a new demo artwork
     * Can optionally cross-post to artwork sell section
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user->artist) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Unauthorized - Artist profile not found'
                    ], 403);
                }
                return redirect()->back()->with('error', 'Unauthorized - Artist profile not found');
            }

            // Handle Input Name Mismatch
            if ($request->has('product_price') && !$request->has('price')) {
                $request->merge(['price' => $request->input('product_price')]);
            }

            // Normalize Boolean Logic
            $alsoSell = $request->boolean('also_sell') || 
                       $request->input('also_sell') === 'true' || 
                       $request->input('also_sell') === '1' || 
                       $request->input('also_sell') === 'on';

            // Base validation rules (always required for demo)
            $rules = [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:2000',
                'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            ];

            $messages = [
                'title.required' => 'Title is required',
                'title.max' => 'Title cannot exceed 255 characters',
                'image.required' => 'Image is required',
                'image.image' => 'File must be an image',
                'image.mimes' => 'Image must be jpeg, jpg, png, gif, or webp',
                'image.max' => 'Image size cannot exceed 5MB',
            ];

            // Only require artwork details if user wants to cross-post
            if ($alsoSell) {
                $rules = array_merge($rules, [
                    'artwork_type' => 'required|in:physical,digital',
                    'material' => 'required|string|max:255',
                    'height' => 'required|numeric|min:0',
                    'width' => 'required|numeric|min:0',
                    'depth' => 'nullable|numeric|min:0',
                    'unit' => 'required|in:cm,inch,px',
                    'price' => 'required|numeric|min:0.01|max:999999.99',
                    'status' => 'nullable|in:available,sold_out',
                ]);

                $messages = array_merge($messages, [
                    'artwork_type.required' => 'Artwork type is required when listing for sale',
                    'material.required' => 'Material is required when listing for sale',
                    'height.required' => 'Height is required when listing for sale',
                    'width.required' => 'Width is required when listing for sale',
                    'unit.required' => 'Unit is required when listing for sale',
                    'price.required' => 'Price is required when listing for sale',
                    'price.numeric' => 'Price must be a valid number',
                    'price.min' => 'Price must be at least 0.01',
                ]);
            } else {
                // Optional if not cross-posting
                $rules = array_merge($rules, [
                    'artwork_type' => 'nullable|in:physical,digital',
                    'material' => 'nullable|string|max:255',
                    'height' => 'nullable|numeric|min:0',
                    'width' => 'nullable|numeric|min:0',
                    'depth' => 'nullable|numeric|min:0',
                    'unit' => 'nullable|in:cm,inch,px',
                    'price' => 'nullable|numeric|min:0',
                    'status' => 'nullable|in:available,sold_out',
                ]);
            }

            $validated = $request->validate($rules, $messages);

            // Upload Image
            $path = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('demo-artworks', $filename, 'public');
                
                if (!$path) {
                    throw new \Exception('File upload failed. Please try again.');
                }
            }

            DB::beginTransaction();

            // Get next order number
            $maxOrder = DemoArtwork::where('artist_id', $user->id)->max('order');
            $order = $maxOrder !== null ? $maxOrder + 1 : 0;

            // Create Demo Record with optional fields
            $demoData = [
                'artist_id' => $user->id,
                'title' => $validated['title'],
                'description' => $request->input('description'),
                'image_path' => $path,
                'order' => $order,
                'is_cross_posted' => false,
            ];

            // Add optional artwork details if provided
            if ($request->filled('artwork_type')) {
                $demoData['artwork_type'] = $validated['artwork_type'];
            }
            if ($request->filled('material')) {
                $demoData['material'] = $validated['material'];
            }
            if ($request->filled('height')) {
                $demoData['height'] = $validated['height'];
            }
            if ($request->filled('width')) {
                $demoData['width'] = $validated['width'];
            }
            if ($request->filled('depth')) {
                $demoData['depth'] = $request->input('depth');
            }
            if ($request->filled('unit')) {
                $demoData['unit'] = $validated['unit'];
            }
            if ($request->filled('price')) {
                $demoData['price'] = $request->input('price');
            }

            $demoArtwork = DemoArtwork::create($demoData);

            // Cross-Post Logic
            $newSell = null;
            
            if ($alsoSell) {
                Log::info('Creating cross-post artwork sell for Demo ID: ' . $demoArtwork->id);
                
                $newSell = ArtworkSell::create([
                    'artist_id' => $user->id,
                    'product_name' => $validated['title'],
                    'product_description' => $request->input('description'),
                    'product_price' => $request->input('price'),
                    'image_path' => $path,
                    'status' => $request->input('status', 'available'),
                    'artwork_type' => $validated['artwork_type'],
                    'material' => $validated['material'],
                    'height' => $validated['height'],
                    'width' => $validated['width'],
                    'depth' => $request->input('depth'),
                    'unit' => $validated['unit'],
                    'is_cross_posted' => true,
                    'cross_posted_from_id' => $demoArtwork->id,
                ]);

                $demoArtwork->is_cross_posted = true;
                $demoArtwork->cross_posted_to_id = $newSell->id;
                $demoArtwork->save();
                
                Log::info('Cross-post created successfully', ['sell_id' => $newSell->id]);
            }

            DB::commit();

            // Return JSON for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Demo artwork added successfully!',
                    'demo' => [
                        'id' => $demoArtwork->id,
                        'title' => $demoArtwork->title,
                        'description' => $demoArtwork->description,
                        'image_url' => $demoArtwork->image_url,
                        'artwork_type' => $demoArtwork->artwork_type,
                        'is_cross_listed' => $demoArtwork->is_cross_posted,
                    ],
                    'sell' => $newSell ? [
                        'id' => $newSell->id,
                        'product_name' => $newSell->product_name,
                        'product_price' => $newSell->product_price,
                        'formatted_price' => $newSell->formatted_price ?? number_format($newSell->product_price, 2),
                    ] : null
                ]);
            }

            // Redirect for regular form submission
            return redirect()->route('artist.profile')->with('success', 'Demo artwork added successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            if (isset($path) && $path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            
            Log::error('Demo validation failed', ['errors' => $e->errors()]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($path) && $path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            Log::error('Demo upload error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Get demo artwork data for editing
     */
    public function edit($id)
    {
        try {
            $user = Auth::user();
            if (!$user->artist) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Unauthorized'
                ], 403);
            }

            $demo = DemoArtwork::where('id', $id)
                ->where('artist_id', $user->id)
                ->first();

            if (!$demo) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Demo artwork not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'id' => $demo->id,
                'title' => $demo->title,
                'description' => $demo->description,
                'image_url' => $demo->image_url,
                'image_path' => $demo->image_path,
                'artwork_type' => $demo->artwork_type,
                'material' => $demo->material,
                'height' => $demo->height,
                'width' => $demo->width,
                'depth' => $demo->depth,
                'unit' => $demo->unit,
                'price' => $demo->price,
                'is_cross_posted' => $demo->is_cross_posted,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load demo: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Failed to load demo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update existing demo artwork
     * Only syncs shared fields (title, description, image) with linked artwork sell
     */
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user->artist) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Unauthorized'
                    ], 403);
                }
                return redirect()->back()->with('error', 'Unauthorized');
            }

            // Get the existing demo to check if it's cross-posted
            $demo = DemoArtwork::where('id', $id)
                ->where('artist_id', $user->id)
                ->first();

            if (!$demo) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Demo artwork not found'
                    ], 404);
                }
                return redirect()->back()->with('error', 'Demo artwork not found');
            }

            // Simple validation - only title/description/image are common fields
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:2000',
                'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
                // All other fields are optional
                'artwork_type' => 'nullable|in:physical,digital',
                'material' => 'nullable|string|max:255',
                'height' => 'nullable|numeric|min:0',
                'width' => 'nullable|numeric|min:0',
                'depth' => 'nullable|numeric|min:0',
                'unit' => 'nullable|in:cm,inch,px',
                'price' => 'nullable|numeric|min:0|max:999999.99',
            ], [
                'title.required' => 'Title is required',
                'image.image' => 'File must be an image',
                'image.mimes' => 'Image must be jpeg, jpg, png, gif, or webp',
                'image.max' => 'Image size cannot exceed 5MB',
            ]);

            DB::beginTransaction();

            // Update Demo - Always update title and description
            $demo->title = $validated['title'];
            $demo->description = $request->input('description');
            
            // Only update optional fields if they are provided
            if ($request->filled('artwork_type')) {
                $demo->artwork_type = $validated['artwork_type'];
            }
            if ($request->filled('material')) {
                $demo->material = $validated['material'];
            }
            if ($request->filled('height')) {
                $demo->height = $validated['height'];
            }
            if ($request->filled('width')) {
                $demo->width = $validated['width'];
            }
            if ($request->filled('depth')) {
                $demo->depth = $request->input('depth');
            }
            if ($request->filled('unit')) {
                $demo->unit = $validated['unit'];
            }
            if ($request->filled('price')) {
                $demo->price = $request->input('price');
            }

            // Handle image update
            $imageUpdated = false;
            if ($request->hasFile('image')) {
                // Delete old image
                if ($demo->image_path && Storage::disk('public')->exists($demo->image_path)) {
                    Storage::disk('public')->delete($demo->image_path);
                }

                // Upload new image
                $image = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $newPath = $image->storeAs('demo-artworks', $filename, 'public');
                
                if ($newPath) {
                    $demo->image_path = $newPath;
                    $imageUpdated = true;
                }
            }

            // Sync ONLY shared fields with linked artwork sell
            if ($demo->is_cross_posted && $demo->cross_posted_to_id) {
                $sell = ArtworkSell::find($demo->cross_posted_to_id);
                
                if ($sell) {
                    // Sync shared fields: title, description, image
                    $sell->product_name = $validated['title'];
                    $sell->product_description = $request->input('description');
                    
                    // Update image if changed
                    if ($imageUpdated) {
                        $sell->image_path = $demo->image_path;
                    }
                    
                    $sell->save();
                }
            }

            $demo->save();

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Demo artwork updated successfully!',
                    'demo' => [
                        'id' => $demo->id,
                        'title' => $demo->title,
                        'description' => $demo->description,
                        'image_url' => $demo->image_url,
                        'artwork_type' => $demo->artwork_type,
                        'price' => $demo->price,
                    ]
                ]);
            }

            return redirect()->route('artist.profile')->with('success', 'Demo artwork updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            Log::error('Demo update validation failed', ['errors' => $e->errors()]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Demo update error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Delete demo artwork
     * If cross-listed, also deletes the linked sell
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            if (!$user->artist) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Unauthorized'
                    ], 403);
                }
                return redirect()->back()->with('error', 'Unauthorized');
            }

            // Find the demo artwork
            $demo = DemoArtwork::where('id', $id)
                ->where('artist_id', $user->id)
                ->first();
            
            if (!$demo) {
                Log::warning('Demo artwork not found for deletion', [
                    'id' => $id, 
                    'artist_id' => $user->id
                ]);
                
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Demo artwork not found'
                    ], 404);
                }
                
                return redirect()->back()->with('error', 'Demo artwork not found');
            }

            DB::beginTransaction();

            $wasCrossListed = $demo->is_cross_posted;
            $imagePath = $demo->image_path;

            // Delete linked sell if exists
            if ($wasCrossListed && $demo->cross_posted_to_id) {
                $linkedSell = ArtworkSell::find($demo->cross_posted_to_id);
                
                if ($linkedSell) {
                    if ($linkedSell->image_path && 
                        $linkedSell->image_path !== $imagePath && 
                        Storage::disk('public')->exists($linkedSell->image_path)) {
                        Storage::disk('public')->delete($linkedSell->image_path);
                    }
                    
                    $linkedSell->delete();
                    Log::info('Deleted linked sell', ['sell_id' => $linkedSell->id]);
                }
            }

            // Delete demo's image file
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
                Log::info('Deleted demo image', ['path' => $imagePath]);
            }

            // Delete the demo record
            $demoId = $demo->id;
            $demo->delete();
            
            Log::info('Demo artwork deleted successfully', [
                'demo_id' => $demoId,
                'was_cross_listed' => $wasCrossListed
            ]);

            DB::commit();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Demo artwork deleted successfully!',
                    'was_cross_listed' => $wasCrossListed
                ]);
            }
            
            return redirect()->route('artist.profile')->with('success', 'Demo artwork deleted successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Demo delete error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Failed to delete demo artwork: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to delete demo artwork: ' . $e->getMessage());
        }
    }

    /**
     * Reorder demo artworks
     */
    public function reorder(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user->artist) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validated = $request->validate([
                'order' => 'required|array',
                'order.*' => 'required|integer|exists:demo_artworks,id'
            ]);

            DB::beginTransaction();

            foreach ($validated['order'] as $index => $demoId) {
                DemoArtwork::where('id', $demoId)
                    ->where('artist_id', $user->id)
                    ->update(['order' => $index]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reorder error: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Failed to reorder: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unlink cross-posted sell (keep both separate)
     */
    public function unlinkSell($id)
    {
        try {
            $user = Auth::user();
            if (!$user->artist) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Unauthorized'
                ], 403);
            }

            $demo = DemoArtwork::where('id', $id)
                ->where('artist_id', $user->id)
                ->first();
                
            if (!$demo) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Demo artwork not found'
                ], 404);
            }
            
            if (!$demo->is_cross_posted) {
                return response()->json([
                    'success' => false, 
                    'message' => 'This artwork is not cross-posted'
                ], 400);
            }

            DB::beginTransaction();

            // Unlink from sell
            if ($demo->cross_posted_to_id) {
                $sell = ArtworkSell::find($demo->cross_posted_to_id);
                
                if ($sell) {
                    $sell->is_cross_posted = false;
                    $sell->cross_posted_from_id = null;
                    $sell->save();
                }
            }

            // Unlink from demo
            $demo->is_cross_posted = false;
            $demo->cross_posted_to_id = null;
            $demo->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Unlinked successfully! Both items are now separate.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Unlink error: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Failed to unlink: ' . $e->getMessage()
            ], 500);
        }
    }
}