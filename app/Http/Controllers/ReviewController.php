<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function create($orderId)
    {
        $order = Order::with(['artist.user', 'artwork', 'items'])->findOrFail($orderId);

        abort_if($order->user_id !== Auth::id(), 403);
        abort_if($order->status !== 'completed', 403, 'You can only review completed orders.');

        if ($order->has_review) {
            return redirect()->route('orders.index')
                ->with('info', 'You have already reviewed this order.');
        }

        $artworkSellId = $order->items->first()->artwork_sell_id ?? null;

        return view('review', compact('order', 'artworkSellId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id'     => 'required|exists:orders,id',
            'artist_id'    => 'required|exists:artists,id',
            'rating'       => 'required|integer|min:1|max:5',
            'description'  => 'nullable|string|max:1000',
            'is_anonymous' => 'nullable|boolean',
            'image'        => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
            'video'        => 'nullable|file|mimes:mp4,mov,webm|max:51200',
        ]);

        $order = Order::with('items')->findOrFail($request->order_id);

        abort_if($order->user_id !== Auth::id(), 403);
        abort_if($order->status !== 'completed', 403);
        abort_if($order->has_review, 422, 'You have already reviewed this order.');

        $artworkSellId = $request->artwork_sell_id
            ?: ($order->items->first()->artwork_sell_id ?? null);

        $imagePath = null;
        $videoPath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('reviews/images', 'public');
        }
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('reviews/videos', 'public');
        }

        $review = Review::create([
            'order_id'        => $order->id,
            'user_id'         => Auth::id(),
            'artist_id'       => $request->artist_id,
            'artwork_sell_id' => $artworkSellId,
            'rating'          => $request->rating,
            'description'     => $request->description,
            'is_anonymous'    => $request->boolean('is_anonymous'),
            'image_path'      => $imagePath,
            'video_path'      => $videoPath,
        ]);

        \DB::table('orders')->where('id', $order->id)->update(['has_review' => 1]);

        return redirect()->route('reviews.complete', [
            'order'  => $order->id,
            'review' => $review->id,
        ]);
    }

    public function complete($orderId, $reviewId)
    {
        $order  = Order::with(['artist.user', 'artwork'])->findOrFail($orderId);
        $review = Review::findOrFail($reviewId);

        abort_if($review->user_id !== Auth::id(), 403);

        return view('reviewComplete', compact('order', 'review'));
    }

    // Show edit form
    public function edit($reviewId)
    {
        $review = Review::with('order.artist.user')->findOrFail($reviewId);

        abort_if($review->user_id !== Auth::id(), 403);
        abort_if($review->created_at->diffInDays(now()) > 30, 403, 'You can only edit reviews within 30 days.');

        $order = $review->order;

        return view('reviewEdit', compact('review', 'order'));
    }

    // Update the review
    public function update(Request $request, $reviewId)
    {
        $review = Review::findOrFail($reviewId);

        abort_if($review->user_id !== Auth::id(), 403);
        abort_if($review->created_at->diffInDays(now()) > 30, 403, 'You can only edit reviews within 30 days.');

        $request->validate([
            'rating'       => 'required|integer|min:1|max:5',
            'description'  => 'nullable|string|max:1000',
            'is_anonymous' => 'nullable|boolean',
            'image'        => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
            'video'        => 'nullable|file|mimes:mp4,mov,webm|max:51200',
        ]);

        // Handle new image upload
        if ($request->hasFile('image')) {
            if ($review->image_path) Storage::disk('public')->delete($review->image_path);
            $review->image_path = $request->file('image')->store('reviews/images', 'public');
        }

        // Handle new video upload
        if ($request->hasFile('video')) {
            if ($review->video_path) Storage::disk('public')->delete($review->video_path);
            $review->video_path = $request->file('video')->store('reviews/videos', 'public');
        }

        // Remove image if requested
        if ($request->has('remove_image') && $review->image_path) {
            Storage::disk('public')->delete($review->image_path);
            $review->image_path = null;
        }

        // Remove video if requested
        if ($request->has('remove_video') && $review->video_path) {
            Storage::disk('public')->delete($review->video_path);
            $review->video_path = null;
        }

        $review->rating       = $request->rating;
        $review->description  = $request->description;
        $review->is_anonymous = $request->boolean('is_anonymous');
        $review->save();

        return redirect()->route('orders.index')
            ->with('success', 'Your review has been updated.');
    }

    // Delete the review
    public function destroy($reviewId)
    {
        $review = Review::findOrFail($reviewId);

        abort_if($review->user_id !== Auth::id(), 403);
        abort_if($review->created_at->diffInDays(now()) > 30, 403, 'You can only delete reviews within 30 days.');

        // Delete media files
        if ($review->image_path) Storage::disk('public')->delete($review->image_path);
        if ($review->video_path) Storage::disk('public')->delete($review->video_path);

        $orderId = $review->order_id;

        $review->delete();

        // Reset has_review so buyer can re-review
        \DB::table('orders')->where('id', $orderId)->update(['has_review' => 0]);

        return redirect()->route('orders.index')
            ->with('success', 'Your review has been deleted.');
    }
}