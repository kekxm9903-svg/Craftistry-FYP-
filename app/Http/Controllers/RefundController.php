<?php

namespace App\Http\Controllers;

use App\Models\BulkOrder;
use App\Models\CustomOrderRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\Refund as StripeRefund;
use Stripe\Stripe;

class RefundController extends Controller
{
    private const REFUND_WINDOW_DAYS = 7;

    // ── BUYER: submit refund request ─────────────────────────────────────────

    public function storeForOrder(Request $request, Order $order)
    {
        abort_if(Auth::id() !== $order->user_id, 403);
        $this->assertRefundable($order->status, $order->payment_status, $order->refund_status ?? 'none', $order->updated_at);
        $request->validate(['reason' => 'required|string|min:10|max:1000']);

        DB::table('orders')->where('id', $order->id)->update([
            'refund_status'       => 'requested',
            'refund_reason'       => $request->reason,
            'refund_requested_at' => now(),
        ]);

        return redirect()->route('orders.index')->with('success', 'Refund request submitted. The seller will review it shortly.');
    }

    public function storeForBulk(Request $request, BulkOrder $bulkOrder)
    {
        abort_if(Auth::id() !== $bulkOrder->buyer_id, 403);
        abort_if(($bulkOrder->refund_status ?? 'none') !== 'none', 403);
        $request->validate(['reason' => 'required|string|min:10|max:1000']);

        DB::table('bulk_orders')->where('id', $bulkOrder->id)->update([
            'refund_status'       => 'requested',
            'refund_reason'       => $request->reason,
            'refund_requested_at' => now(),
        ]);

        return redirect()->route('bulk-orders.show', $bulkOrder->id)->with('success', 'Refund request submitted. The seller will review it shortly.');
    }

    public function storeForCustom(Request $request, CustomOrderRequest $customOrder)
    {
        abort_if(Auth::id() !== $customOrder->buyer_id, 403);
        abort_if(($customOrder->refund_status ?? 'none') !== 'none', 403);
        $request->validate(['reason' => 'required|string|min:10|max:1000']);

        DB::table('custom_order_requests')->where('id', $customOrder->id)->update([
            'refund_status'       => 'requested',
            'refund_reason'       => $request->reason,
            'refund_requested_at' => now(),
        ]);

        return redirect()->route('custom-orders.show', $customOrder->id)->with('success', 'Refund request submitted. The seller will review it shortly.');
    }

    // ── SELLER: approve ──────────────────────────────────────────────────────

    public function approveOrder(Order $order)
    {
        $this->authoriseSeller($order->artist_id);
        abort_if(($order->refund_status ?? 'none') !== 'requested', 403);

        $refundId = $this->issueStripeRefund($order->stripe_session_id);

        DB::table('orders')->where('id', $order->id)->update([
            'refund_status'    => 'refunded',
            'stripe_refund_id' => $refundId,
            'refund_amount'    => $order->total,
            'refunded_at'      => now(),
            'status'           => 'cancelled',
        ]);

        return redirect()->back()->with('success', 'Refund approved and processed via Stripe.');
    }

    public function approveBulk(BulkOrder $bulkOrder)
    {
        $this->authoriseSeller($bulkOrder->artworkSell?->artist_id);
        abort_if(($bulkOrder->refund_status ?? 'none') !== 'requested', 403);

        $refundId = $this->issueStripeRefund($bulkOrder->stripe_session_id);

        DB::table('bulk_orders')->where('id', $bulkOrder->id)->update([
            'refund_status'    => 'refunded',
            'stripe_refund_id' => $refundId,
            'refund_amount'    => $bulkOrder->total_price,
            'refunded_at'      => now(),
        ]);

        return redirect()->back()->with('success', 'Refund approved and processed via Stripe.');
    }

    public function approveCustom(CustomOrderRequest $customOrder)
    {
        $this->authoriseSeller(
            \App\Models\Artist::where('user_id', $customOrder->seller_id)->value('id')
        );
        abort_if(($customOrder->refund_status ?? 'none') !== 'requested', 403);

        $amount   = $customOrder->final_price ?? $customOrder->agreed_price;
        $refundId = $this->issueStripeRefund($customOrder->stripe_session_id);

        DB::table('custom_order_requests')->where('id', $customOrder->id)->update([
            'refund_status'    => 'refunded',
            'stripe_refund_id' => $refundId,
            'refund_amount'    => $amount,
            'refunded_at'      => now(),
        ]);

        return redirect()->back()->with('success', 'Refund approved and processed via Stripe.');
    }

    // ── SELLER: reject ───────────────────────────────────────────────────────

    public function rejectOrder(Request $request, Order $order)
    {
        $this->authoriseSeller($order->artist_id);
        abort_if(($order->refund_status ?? 'none') !== 'requested', 403);
        $request->validate(['reject_reason' => 'required|string|min:5|max:500']);

        DB::table('orders')->where('id', $order->id)->update([
            'refund_status'        => 'rejected',
            'refund_reject_reason' => $request->reject_reason,
        ]);

        return redirect()->back()->with('success', 'Refund request rejected.');
    }

    public function rejectBulk(Request $request, BulkOrder $bulkOrder)
    {
        $this->authoriseSeller($bulkOrder->artworkSell?->artist_id);
        abort_if(($bulkOrder->refund_status ?? 'none') !== 'requested', 403);
        $request->validate(['reject_reason' => 'required|string|min:5|max:500']);

        DB::table('bulk_orders')->where('id', $bulkOrder->id)->update([
            'refund_status'        => 'rejected',
            'refund_reject_reason' => $request->reject_reason,
        ]);

        return redirect()->back()->with('success', 'Refund request rejected.');
    }

    public function rejectCustom(Request $request, CustomOrderRequest $customOrder)
    {
        $this->authoriseSeller(
            \App\Models\Artist::where('user_id', $customOrder->seller_id)->value('id')
        );
        abort_if(($customOrder->refund_status ?? 'none') !== 'requested', 403);
        $request->validate(['reject_reason' => 'required|string|min:5|max:500']);

        DB::table('custom_order_requests')->where('id', $customOrder->id)->update([
            'refund_status'        => 'rejected',
            'refund_reject_reason' => $request->reject_reason,
        ]);

        return redirect()->back()->with('success', 'Refund request rejected.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function authoriseSeller(?int $artistId): void
    {
        $myArtistId = Auth::user()->artist?->id;
        abort_if(!$myArtistId || $myArtistId !== $artistId, 403);
    }

    private function assertRefundable(string $status, string $paymentStatus, string $refundStatus, $updatedAt): void
    {
        abort_if($paymentStatus !== 'paid', 403, 'Order has not been paid.');
        abort_if(!in_array($status, ['completed','shipped','preparing','processing']), 403, 'Order not eligible for refund.');
        abort_if($refundStatus !== 'none', 403, 'A refund has already been requested or processed.');
        if ($status === 'completed') {
            abort_if(now()->diffInDays(\Carbon\Carbon::parse($updatedAt)) > self::REFUND_WINDOW_DAYS, 403, 'Refund window has expired.');
        }
    }

    private function issueStripeRefund(?string $stripeSessionId): string
    {
        abort_if(!$stripeSessionId, 422, 'No Stripe session found for this order.');
        Stripe::setApiKey(config('services.stripe.secret'));
        $session = \Stripe\Checkout\Session::retrieve($stripeSessionId);
        $refund  = StripeRefund::create([
            'payment_intent' => $session->payment_intent,
            'reason'         => 'requested_by_customer',
        ]);
        return $refund->id;
    }
}