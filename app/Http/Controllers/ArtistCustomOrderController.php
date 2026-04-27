<?php

namespace App\Http\Controllers;

use App\Models\CustomOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArtistCustomOrderController extends Controller
{
    /* ── Seller: list incoming requests ── */
    public function index()
    {
        $requests = CustomOrderRequest::with('buyer', 'order')
            ->where('seller_id', Auth::id())
            ->latest()
            ->paginate(12);

        return view('artistRequestList', compact('requests'));
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

    /* ── Seller: refuse (with optional counter-price) ── */
    public function refuse(Request $request, CustomOrderRequest $customOrder)
    {
        abort_if($customOrder->seller_id !== Auth::id(), 403);
        abort_if($customOrder->status !== 'pending', 403);

        $data = $request->validate([
            'seller_reason' => 'required|string|max:1000',
            'counter_price' => 'nullable|numeric|min:1|max:99999',
        ]);

        $customOrder->update([
            'status'        => 'refused',
            'seller_reason' => $data['seller_reason'],
            'counter_price' => $data['counter_price'] ?? null,
        ]);

        return redirect()->route('artist.custom-orders.show', $customOrder)
            ->with('success', 'Your response has been sent to the buyer.');
    }
}