<!DOCTYPE html>
<html lang="en">
<head>
    @include('home.css')
    <style>
        body {
            background: #000;
            color: #fff;
            margin: 0;
        }

        .receipt-page {
            min-height: 100vh;
            padding: 42px 18px;
        }

        .receipt-card {
            background: #fff;
            border-radius: 8px;
            color: #151515;
            margin: 0 auto;
            max-width: 720px;
            padding: 30px;
        }

        .receipt-card h1 {
            color: #151515;
            font-size: 30px;
            font-weight: 900;
            margin: 0 0 6px;
        }

        .receipt-muted {
            color: #666;
            margin-bottom: 24px;
        }

        .receipt-meta {
            border-bottom: 1px solid #e5e7eb;
            border-top: 1px solid #e5e7eb;
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(2, 1fr);
            margin-bottom: 22px;
            padding: 18px 0;
        }

        .receipt-meta strong,
        .receipt-total strong {
            color: #111;
            display: block;
        }

        .receipt-table {
            border-collapse: collapse;
            width: 100%;
        }

        .receipt-table th,
        .receipt-table td {
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 8px;
            text-align: left;
        }

        .receipt-table th:last-child,
        .receipt-table td:last-child {
            text-align: right;
        }

        .receipt-total {
            display: flex;
            font-size: 22px;
            justify-content: space-between;
            margin: 22px 0;
        }

        .receipt-actions {
            display: flex;
            gap: 12px;
        }

        .receipt-actions a,
        .receipt-actions button {
            background: #F88379;
            border: 0;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            font-weight: 800;
            padding: 11px 16px;
            text-decoration: none;
        }

        .receipt-actions .secondary {
            background: #151515;
        }

        @media (max-width: 575.98px) {
            .receipt-card { padding: 22px 16px; }
            .receipt-meta { grid-template-columns: 1fr; }
            .receipt-actions { flex-direction: column; }
        }
    </style>
</head>
<body>
    @php
        $firstOrder = $orders->first();
        $total = $orders->sum(fn ($order) => (float) $order->price);
        $orderDateTime = $firstOrder->created_at
            ? $firstOrder->created_at->copy()->timezone('Asia/Manila')->format('M d, Y g:i A')
            : '';
    @endphp

    <main class="receipt-page">
        <section class="receipt-card">
            <h1>Payment Receipt</h1>
            <p class="receipt-muted">Your order is confirmed. Please keep this receipt for reference.</p>

            <div class="receipt-meta">
                <div>
                    <span>Order No.</span>
                    <strong>{{ $firstOrder->order_number }}</strong>
                </div>
                <div>
                    <span>Customer</span>
                    <strong>{{ $firstOrder->name }}</strong>
                </div>
                <div>
                    <span>Order Date & Time</span>
                    <strong>{{ $orderDateTime }}</strong>
                </div>
                <div>
                    <span>Payment Method</span>
                    <strong>{{ $firstOrder->payment_method ?? 'Cash on Delivery' }}</strong>
                </div>
                <div>
                    <span>Payment Status</span>
                    <strong>{{ $firstOrder->payment_status ?? 'Unpaid' }}</strong>
                </div>
                @php
                    $referenceNumber = $paymentReference ?? $firstOrder->payment_reference ?? null;
                @endphp
                @if($referenceNumber)
                    <div>
                        <span>Reference #</span>
                        <strong>{{ $referenceNumber }}</strong>
                    </div>
                @endif
            </div>

            <table class="receipt-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>{{ $order->title }}</td>
                            <td>{{ $order->quantity }}</td>
                            <td>&#8369;{{ number_format((float) $order->price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="receipt-total">
                <span>Total</span>
                <strong>&#8369;{{ number_format($total, 2) }}</strong>
            </div>

            <p class="receipt-muted">{{ $firstOrder->address }}</p>

            <div class="receipt-actions">
                @if($guestReceipt ?? false)
                <a class="secondary" href="{{ url('/?section=food') }}">Back to Menu</a>
                @else
                <a href="{{ url('track_order', $firstOrder->id) }}">Track Order</a>
                <a class="secondary" href="{{ url('my_orders') }}">My Orders</a>
                @endif
                <button type="button" onclick="window.print()">Print</button>
            </div>
        </section>
    </main>
</body>
</html>
