{{-- resources/views/orderReceipt.blade.php --}}
{{-- Rendered by dompdf — all styles must be inline, no external CSS --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $receiptNo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #2d2d2d;
            background: #ffffff;
        }

        /* ── Top accent bar ── */
        .top-bar {
            background: #4a2d8f;
            height: 6px;
            width: 100%;
        }

        .page-wrap {
            padding: 36px 48px 40px;
        }

        /* ════════════════════════════════
           HEADER
        ════════════════════════════════ */
        .header-table {
            display: table;
            width: 100%;
            margin-bottom: 28px;
            padding-bottom: 22px;
            border-bottom: 1px solid #e2e2e2;
        }
        .header-left {
            display: table-cell;
            vertical-align: top;
            width: 55%;
        }
        .header-right {
            display: table-cell;
            vertical-align: top;
            text-align: right;
            width: 45%;
        }

        /* Brand */
        .brand-name {
            font-size: 28px;
            font-weight: 700;
            color: #4a2d8f;
            letter-spacing: -0.5px;
            line-height: 1;
        }
        .brand-sub {
            font-size: 10.5px;
            color: #888;
            margin-top: 4px;
            letter-spacing: 0.2px;
        }
        .brand-contact {
            margin-top: 10px;
            font-size: 10px;
            color: #777;
            line-height: 1.8;
        }

        /* Receipt label */
        .receipt-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #888;
            margin-bottom: 6px;
        }
        .receipt-number {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
            line-height: 1.1;
        }
        .receipt-meta {
            font-size: 10.5px;
            color: #888;
            margin-top: 6px;
            line-height: 1.7;
        }

        /* ════════════════════════════════
           PAID STAMP
        ════════════════════════════════ */
        .paid-stamp {
            display: inline-block;
            border: 3px solid #1a7a4a;
            border-radius: 4px;
            padding: 3px 14px;
            font-size: 16px;
            font-weight: 700;
            color: #1a7a4a;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 10px;
            opacity: 0.85;
            transform: rotate(-6deg);
        }
        .cancelled-stamp {
            display: inline-block;
            border: 3px solid #c62828;
            border-radius: 4px;
            padding: 3px 14px;
            font-size: 16px;
            font-weight: 700;
            color: #c62828;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 10px;
            opacity: 0.85;
        }

        /* ════════════════════════════════
           BILL TO / SHIP TO / SOLD BY
        ════════════════════════════════ */
        .parties-table {
            display: table;
            width: 100%;
            margin-bottom: 28px;
        }
        .party-col {
            display: table-cell;
            vertical-align: top;
            width: 33.33%;
            padding-right: 20px;
        }
        .party-col:last-child { padding-right: 0; }

        .party-heading {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #ffffff;
            background: #4a2d8f;
            padding: 4px 8px;
            margin-bottom: 8px;
        }
        .party-body {
            font-size: 11.5px;
            color: #333;
            line-height: 1.75;
        }
        .party-body strong {
            font-size: 12.5px;
            color: #1a1a2e;
            display: block;
            margin-bottom: 1px;
        }

        /* ════════════════════════════════
           ORDER INFO STRIP
        ════════════════════════════════ */
        .info-strip {
            display: table;
            width: 100%;
            background: #f7f5ff;
            border: 1px solid #e2daf5;
            border-radius: 4px;
            margin-bottom: 24px;
            padding: 10px 0;
        }
        .info-strip-cell {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            border-right: 1px solid #e2daf5;
            padding: 0 16px;
        }
        .info-strip-cell:last-child { border-right: none; }
        .strip-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 3px;
        }
        .strip-value {
            font-size: 12px;
            font-weight: 700;
            color: #1a1a2e;
        }
        .strip-value.purple { color: #4a2d8f; }

        /* ════════════════════════════════
           ITEMS TABLE
        ════════════════════════════════ */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .items-table thead tr {
            background: #1a1a2e;
        }
        .items-table thead th {
            padding: 9px 12px;
            font-size: 10px;
            font-weight: 700;
            color: #ffffff;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .items-table thead th.align-right { text-align: right; }
        .items-table thead th.align-center { text-align: center; }

        .items-table tbody tr { border-bottom: 1px solid #efefef; }
        .items-table tbody tr:last-child { border-bottom: 2px solid #1a1a2e; }
        .items-table tbody tr:nth-child(even) { background: #fafafa; }

        .items-table tbody td {
            padding: 11px 12px;
            font-size: 11.5px;
            vertical-align: middle;
            color: #333;
        }
        .items-table tbody td.align-right { text-align: right; }
        .items-table tbody td.align-center { text-align: center; }

        .item-name { font-weight: 700; color: #1a1a2e; font-size: 12px; }
        .item-sub  { font-size: 10px; color: #999; margin-top: 2px; }

        /* ════════════════════════════════
           TOTALS
        ════════════════════════════════ */
        .totals-section {
            display: table;
            width: 100%;
            margin-bottom: 28px;
        }
        .totals-left {
            display: table-cell;
            vertical-align: top;
            width: 52%;
        }
        .totals-right {
            display: table-cell;
            vertical-align: top;
            width: 48%;
        }

        /* Payment & notes on left */
        .payment-box {
            border: 1px solid #e2e2e2;
            border-radius: 4px;
            padding: 12px 14px;
            margin-bottom: 10px;
        }
        .payment-box-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 5px;
        }
        .payment-box-value {
            font-size: 12.5px;
            font-weight: 700;
            color: #1a1a2e;
        }
        .payment-box-ref {
            font-size: 9.5px;
            color: #aaa;
            margin-top: 3px;
            word-break: break-all;
        }

        .tracking-box {
            border: 1px solid #e2e2e2;
            border-left: 4px solid #4a2d8f;
            border-radius: 4px;
            padding: 12px 14px;
        }
        .tracking-box-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #4a2d8f;
            margin-bottom: 5px;
        }
        .tracking-box-value {
            font-size: 13px;
            font-weight: 700;
            color: #1a1a2e;
        }
        .tracking-box-courier {
            font-size: 10px;
            color: #888;
            margin-top: 2px;
        }

        /* Totals rows on right */
        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        .total-row-key {
            display: table-cell;
            font-size: 11.5px;
            color: #666;
        }
        .total-row-val {
            display: table-cell;
            font-size: 11.5px;
            color: #1a1a2e;
            font-weight: 500;
            text-align: right;
        }
        .total-row-val.green { color: #1a7a4a; }

        .totals-line {
            border: none;
            border-top: 1px solid #ddd;
            margin: 8px 0;
        }

        .grand-total-box {
            background: #4a2d8f;
            border-radius: 4px;
            padding: 12px 14px;
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        .grand-total-label {
            display: table-cell;
            font-size: 12px;
            font-weight: 700;
            color: #c9b8f5;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .grand-total-value {
            display: table-cell;
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
            text-align: right;
        }

        /* ════════════════════════════════
           FOOTER
        ════════════════════════════════ */
        .footer-divider {
            border: none;
            border-top: 1px solid #e2e2e2;
            margin-bottom: 14px;
        }
        .footer-table {
            display: table;
            width: 100%;
        }
        .footer-left {
            display: table-cell;
            vertical-align: middle;
            width: 60%;
            font-size: 10px;
            color: #aaa;
            line-height: 1.7;
        }
        .footer-left strong { color: #4a2d8f; }
        .footer-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 40%;
        }
        .footer-tagline {
            font-size: 11px;
            font-weight: 700;
            color: #4a2d8f;
        }
        .footer-sub {
            font-size: 9.5px;
            color: #bbb;
            margin-top: 2px;
        }

        /* ── Bottom accent bar ── */
        .bottom-bar {
            background: #4a2d8f;
            height: 4px;
            width: 100%;
            margin-top: 28px;
        }

    </style>
</head>
<body>

<div class="top-bar"></div>

<div class="page-wrap">

    {{-- ════ HEADER ════ --}}
    <div class="header-table">
        <div class="header-left">
            <img src="{{ public_path('images/Logo.png') }}"
                 alt="Craftistry"
                 style="height: 52px; width: auto; display: block; margin-bottom: 8px;">
            <div class="brand-sub">The Marketplace for Art in Malaysia</div>
            <div class="brand-contact">
                craftistry.my &nbsp;&middot;&nbsp; support@craftistry.my<br>
                Malaysia (SST Reg: MY-XXXX-XXXX)
            </div>
        </div>
        <div class="header-right">
            <div class="receipt-label">Official Receipt</div>
            <div class="receipt-number">{{ $receiptNo }}</div>
            <div class="receipt-meta">
                Date Issued: {{ $generatedAt }}<br>
                Order Date: {{ $order->created_at->format('d M Y') }}<br>
                Order Ref: <strong>#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</strong>
            </div>
            @if(in_array($order->status, ['completed', 'shipped', 'processing', 'preparing']))
                <div class="paid-stamp">PAID</div>
            @elseif($order->status === 'cancelled')
                <div class="cancelled-stamp">CANCELLED</div>
            @endif
        </div>
    </div>

    {{-- ════ BILL TO / SHIP TO / SOLD BY ════ --}}
    <div class="parties-table">
        <div class="party-col">
            <div class="party-heading">Bill To</div>
            <div class="party-body">
                <strong>{{ $buyer->fullname ?? $buyer->name }}</strong>
                {{ $buyer->email }}
                @if($buyer->phone ?? null)<br>{{ $buyer->phone }}@endif
            </div>
        </div>
        <div class="party-col">
            <div class="party-heading">Ship To</div>
            <div class="party-body">
                @if($order->shipping_address ?? null)
                    {!! nl2br(e($order->shipping_address)) !!}
                @else
                    <span style="color:#aaa;">Same as billing address</span>
                @endif
            </div>
        </div>
        <div class="party-col">
            <div class="party-heading">Sold By</div>
            <div class="party-body">
                <strong>{{ $artistName }}</strong>
                Independent Artist<br>
                via Craftistry Marketplace<br>
                craftistry.my
            </div>
        </div>
    </div>

    {{-- ════ ORDER INFO STRIP ════ --}}
    <div class="info-strip">
        <div class="info-strip-cell">
            <div class="strip-label">Order Status</div>
            <div class="strip-value purple">{{ $statusLabel }}</div>
        </div>
        <div class="info-strip-cell">
            <div class="strip-label">Payment Method</div>
            <div class="strip-value">{{ ucwords(str_replace('_', ' ', $order->payment_method ?? 'Online Payment')) }}</div>
        </div>
        <div class="info-strip-cell">
            <div class="strip-label">Payment Status</div>
            <div class="strip-value">{{ ucfirst($order->payment_status ?? 'Paid') }}</div>
        </div>
        <div class="info-strip-cell">
            <div class="strip-label">Currency</div>
            <div class="strip-value">MYR (RM)</div>
        </div>
    </div>

    {{-- ════ ITEMS TABLE ════ --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 42%;">Description</th>
                <th style="width: 20%;">Artist</th>
                <th class="align-center" style="width: 8%;">Qty</th>
                <th class="align-right" style="width: 12%;">Unit Price</th>
                <th class="align-right" style="width: 13%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $rowNum = 1; @endphp
            @forelse($order->items as $item)
            @php
                $itemName   = $item->name ?? $item->artwork?->product_name ?? 'Artwork';
                $itemPrice  = $item->price ?? $item->artwork?->product_price ?? 0;
                $itemQty    = $item->quantity ?? 1;
                $itemTotal  = $itemPrice * $itemQty;
                $itemArtist = $item->artwork?->artist?->user?->fullname
                           ?? $item->artwork?->artist?->name
                           ?? $artistName;
            @endphp
            <tr>
                <td style="color:#aaa;">{{ $rowNum++ }}</td>
                <td>
                    <div class="item-name">{{ $itemName }}</div>
                    @if($item->variant ?? null)
                        <div class="item-sub">Variant: {{ $item->variant }}</div>
                    @endif
                    <div class="item-sub">Original artwork &mdash; Craftistry Marketplace</div>
                </td>
                <td style="font-size:11px; color:#555;">{{ $itemArtist }}</td>
                <td class="align-center">{{ $itemQty }}</td>
                <td class="align-right">RM {{ number_format($itemPrice, 2) }}</td>
                <td class="align-right" style="font-weight:700;">RM {{ number_format($itemTotal, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td>1</td>
                <td colspan="4">
                    <div class="item-name">{{ $order->title ?? 'Artwork Order' }}</div>
                    <div class="item-sub">Original artwork &mdash; Craftistry Marketplace</div>
                </td>
                <td class="align-right" style="font-weight:700;">RM {{ number_format($order->total ?? 0, 2) }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ════ TOTALS + PAYMENT ════ --}}
    @php
        $subtotal    = $order->items->sum(fn($i) => ($i->price ?? 0) * ($i->quantity ?? 1));
        $shippingFee = $order->shipping_fee ?? 0;
        $discount    = $order->discount ?? 0;
        $grandTotal  = $order->total ?? $order->price ?? ($subtotal + $shippingFee - $discount);
    @endphp

    <div class="totals-section" style="margin-top: 16px;">

        {{-- Left: payment & tracking --}}
        <div class="totals-left" style="padding-right: 24px;">

            <div class="payment-box">
                <div class="payment-box-label">Payment Method</div>
                <div class="payment-box-value">
                    {{ ucwords(str_replace('_', ' ', $order->payment_method ?? 'Online Payment')) }}
                </div>
                @if($order->stripe_session_id ?? null)
                    <div class="payment-box-ref">
                        Stripe Session: {{ $order->stripe_session_id }}
                    </div>
                @endif
            </div>

            @if($order->tracking_number ?? null)
            <div class="tracking-box">
                <div class="tracking-box-label">Shipment Tracking</div>
                <div class="tracking-box-value">{{ $order->tracking_number }}</div>
                <div class="tracking-box-courier">
                    Courier: {{ strtoupper($order->courier ?? 'N/A') }}
                </div>
            </div>
            @endif

            @if($order->notes ?? null)
            <div style="margin-top: 10px; border: 1px solid #f0e8c4; border-left: 4px solid #e6a817; border-radius: 4px; padding: 10px 12px;">
                <div style="font-size:9px; text-transform:uppercase; letter-spacing:1px; color:#e6a817; margin-bottom:4px;">Order Notes</div>
                <div style="font-size:11px; color:#555;">{{ $order->notes }}</div>
            </div>
            @endif

        </div>

        {{-- Right: totals --}}
        <div class="totals-right">

            <div class="total-row">
                <div class="total-row-key">Subtotal</div>
                <div class="total-row-val">RM {{ number_format($subtotal, 2) }}</div>
            </div>

            @if($shippingFee > 0)
            <div class="total-row">
                <div class="total-row-key">Shipping &amp; Handling</div>
                <div class="total-row-val">RM {{ number_format($shippingFee, 2) }}</div>
            </div>
            @else
            <div class="total-row">
                <div class="total-row-key">Shipping &amp; Handling</div>
                <div class="total-row-val" style="color:#aaa;">—</div>
            </div>
            @endif

            @if($discount > 0)
            <div class="total-row">
                <div class="total-row-key">Discount</div>
                <div class="total-row-val green">- RM {{ number_format($discount, 2) }}</div>
            </div>
            @endif

            <div class="total-row">
                <div class="total-row-key">SST (0%)</div>
                <div class="total-row-val" style="color:#aaa;">RM 0.00</div>
            </div>

            <hr class="totals-line">

            <div class="grand-total-box">
                <div class="grand-total-label">Total Paid</div>
                <div class="grand-total-value">RM {{ number_format($grandTotal, 2) }}</div>
            </div>

            <div style="font-size:9.5px; color:#aaa; text-align:right; margin-top:6px;">
                All amounts in Malaysian Ringgit (MYR)
            </div>

        </div>
    </div>

    {{-- ════ FOOTER ════ --}}
    <hr class="footer-divider">
    <div class="footer-table">
        <div class="footer-left">
            This is a computer-generated receipt and does not require a physical signature.<br>
            For enquiries, contact <strong>support@craftistry.my</strong> with your receipt number.<br>
            Goods sold are non-refundable unless item is defective or not as described.<br>
            <strong>Craftistry</strong> &middot; craftistry.my &middot; Malaysia
        </div>
        <div class="footer-right">
            <div class="footer-tagline">Thank you for your purchase!</div>
            <div class="footer-sub">{{ $receiptNo }} &middot; {{ $generatedAt }}</div>
        </div>
    </div>

</div>{{-- /page-wrap --}}

<div class="bottom-bar"></div>

</body>
</html>