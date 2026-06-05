<?php

namespace App\Http\Controllers;

use App\Models\CustomOrderRequest;
use App\Models\BulkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArtistCustomOrderController extends Controller
{
    /* ── Seller: list incoming requests (custom + bulk) ── */
    public function index()
    {
        $requests = CustomOrderRequest::with('buyer', 'order')
            ->where('seller_id', Auth::id())
            ->latest()
            ->paginate(12);

        $bulkOrders = BulkOrder::with(['artworkSell', 'buyer'])
            ->whereHas('artworkSell', function ($q) {
                $q->where('artist_id', Auth::user()->artist->id ?? 0);
            })
            ->latest()
            ->get();

        return view('artistRequestList', compact('requests', 'bulkOrders'));
    }

    /* ── Seller: view a single request ── */
    public function show(CustomOrderRequest $customOrder)
    {
        abort_if($customOrder->seller_id !== Auth::id(), 403);
        $customOrder->load('buyer', 'order');
        return view('artistRequestDetails', compact('customOrder'));
    }

    /* ── Seller: accept request ── */
    public function accept(CustomOrderRequest $customOrder)
    {
        abort_if($customOrder->seller_id !== Auth::id(), 403);
        abort_if($customOrder->status !== 'pending', 403);

        $customOrder->update(['status' => 'accepted']);

        return redirect()->route('artist.custom-orders.show', $customOrder)
            ->with('success', 'You accepted this custom order request.');
    }

    /* ── Seller: refuse (reason OR counter price, at least one required) ── */
    public function refuse(Request $request, CustomOrderRequest $customOrder)
    {
        abort_if($customOrder->seller_id !== Auth::id(), 403);
        abort_if($customOrder->status !== 'pending', 403);

        $request->validate([
            'seller_reason' => 'nullable|string|max:1000',
            'counter_price' => 'nullable|numeric|min:1|max:99999',
        ]);

        // At least one must be provided
        if (!$request->filled('seller_reason') && !$request->filled('counter_price')) {
            return back()
                ->withErrors(['seller_reason' => 'Please provide a refusal reason or a counter price — at least one is required.'])
                ->withInput();
        }

        $customOrder->update([
            'status'        => 'refused',
            'seller_reason' => $request->input('seller_reason') ?: null,
            'counter_price' => $request->filled('counter_price') ? $request->input('counter_price') : null,
        ]);

        return redirect()->route('artist.custom-orders.show', $customOrder)
            ->with('success', 'Your response has been sent to the buyer.');
    }
}