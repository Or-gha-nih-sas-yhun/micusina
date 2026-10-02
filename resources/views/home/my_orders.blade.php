<!DOCTYPE html>
<html lang="en">
<head>
    @include('home.css')
    <style>
        body {
            background: #000;
            color: #111;
            margin: 0;
            overflow: hidden;
        }

        .orders-page {
            align-items: center;
            display: flex;
            min-height: 100vh;
            justify-content: center;
            padding: 130px 28px 28px;
        }

        .orders-shell {
            background: #fff;
            border-radius: 22px;
            max-width: 860px;
            padding: 42px;
            width: min(860px, calc(100vw - 56px));
        }

        .orders-top {
            align-items: center;
            display: none;
            justify-content: space-between;
            margin-bottom: 32px;
        }

        .orders-top a {
            color: #111;
            font-size: 30px;
            text-decoration: none;
        }

        h1 {
            color: #000;
            font-size: 42px;
            font-weight: 900;
            margin: 0 0 28px;
        }

        .order-feature {
            align-items: center;
            border: 1px solid #d8d8d8;
            border-radius: 18px;
            display: grid;
            gap: 24px;
            grid-template-columns: minmax(0, 1fr) 120px;
            padding: 24px;
        }

        .order-feature img,
        .order-row img {
            border-radius: 10px;
            object-fit: cover;
        }

        .order-feature img {
            height: 120px;
            width: 120px;
        }

        .order-feature h2,
        .order-row h3 {
            color: #000;
            font-weight: 900;
            margin: 0;
        }

        .order-number { color: #dc2626; }

        .order-feature h2 {
            font-size: 24px;
        }

        .order-feature p,
        .order-row p {
            color: #666;
            margin: 6px 0;
        }

        .order-status {
            color: #F88379;
            font-weight: 900;
        }

        .order-status.pending { color: #F88379; }
        .order-status.canceled { color: #db3e3e; }

        .order-actions {
            display: flex;
            gap: 14px;
            margin-top: 24px;
        }

        .track-link,
        .all-orders-button {
            align-items: center;
            border-radius: 999px;
            display: inline-flex;
            font-weight: 900;
            justify-content: center;
            min-height: 50px;
            padding: 0 24px;
            text-decoration: none;
        }

        .track-link {
            background: #F88379;
            color: #fff;
        }

        .all-orders-button {
            background: #111;
            border: 0;
            color: #fff;
            cursor: pointer;
        }

        .empty-orders {
            color: #555;
            font-size: 18px;
        }

        /* Match the customer homepage typography. */
        .orders-page,
        .orders-page button,
        .orders-page input,
        .orders-page select,
        .orders-page textarea {
            font-family: Arial, Helvetica, sans-serif !important;
        }

        .orders-page p,
        .orders-page li,
        .orders-page span,
        .orders-page a,
        .orders-page button {
            font-size: 17px !important;
            line-height: 1.6;
        }

        .orders-page h1,
        .orders-page h2,
        .orders-page h3 {
            font-family: Georgia, 'Times New Roman', serif !important;
            font-weight: 400 !important;
            letter-spacing: -.025em !important;
        }

        .orders-page h1 {
            font-size: clamp(55px, 5vw, 88px) !important;
            letter-spacing: -.055em !important;
            line-height: .93 !important;
        }
        .orders-page h2 { font-size: 32px !important; }

        .orders-modal {
            align-items: center;
            background: rgba(0, 0, 0, .72);
            display: none;
            inset: 0;
            justify-content: center;
            padding: 24px;
            position: fixed;
            z-index: 50;
        }

        .orders-modal.is-visible {
            display: flex;
        }

        .orders-panel {
            background: #fff;
            border-radius: 20px;
            max-height: min(720px, calc(100vh - 56px));
            max-width: 760px;
            overflow: auto;
            padding: 28px;
            width: 100%;
        }

        .orders-panel-head {
            align-items: center;
            display: flex;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .orders-panel h2 {
            color: #000;
            font-size: 28px;
            font-weight: 900;
            margin: 0;
        }

        .orders-close {
            align-items: center;
            background: #111;
            border: 0;
            border-radius: 999px;
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            font-size: 22px;
            height: 40px;
            justify-content: center;
            width: 40px;
        }

        .order-row {
            align-items: center;
            border-bottom: 1px solid #eee;
            display: grid;
            gap: 16px;
            grid-template-columns: 1fr 80px;
            padding: 16px 0;
        }

        .order-row img {
            height: 80px;
            width: 80px;
        }

        .order-row a {
            color: #F88379;
            font-weight: 900;
        }
    </style>
</head>
<body class="content-page">
    @include('home.header', ['forceInnerNavbar' => true])
    <main class="orders-page">
        <section class="orders-shell">
            <div class="orders-top">
                <a href="{{ url('/home') }}"><i class="ti-arrow-left"></i></a>
                <a href="{{ url('my_cart') }}"><i class="ti-shopping-cart"></i></a>
            </div>

            <h1>My Orders</h1>

            @php
                $menuImages = [
                    'chicken-burger' => 'chicken burger.png',
                    'egg-bunwich' => 'egg-bunwich.png',
                    'cheesy-chicken' => 'chessy-chicken.png',
                    'cheesy-chicken-hotdog-sandwich' => 'chessy-chicken.png',
                    'cheesy-chicken-hotdog' => 'chessy-chicken.png',
                    'fries' => 'fries.png',
                    'classic-fries' => 'fries.png',
                    'ice-cream' => 'ice-cream.png',
                    'mi-cusina-ice-cream' => 'ice-cream.png',
                    'creamy-carbonara' => 'creamy-carbonara.png',
                    'classic-spaghetti' => 'classic-spaghetti.png',
                    'chicken-nugget' => 'chicken-nugget.png',
                    'chicken-nuggets' => 'chicken-nugget.png',
                    'chicken-nuggets-rice-bowl' => 'chicken-nugget.png',
                    '2-pcs-chicken-meal' => '2-pcs chicken meal.png',
                    '2-pc-chicken-meal' => '2-pcs chicken meal.png',
                    '1-pc-chicken-meal' => '1-pc-chicken meal.png',
                    'chicken-spaghetti' => 'chicken-spaghetti.png',
                    'chicken-adobo-bunwich' => 'chicken-adobo-bundwich.png',
                    'chicken-fillet' => 'chicken-fillet.png',
                    'chicken-burger-spaghetti' => 'chicken-burger-spaghetti.png',
                    'chicken-adobo-flakes' => 'chicken-adobo-flakes.png',
                    'chicken-teriyaki' => 'chicken-tereyaki.png',
                    'ham-bowl' => 'ham-bowl.png',
                    'siomai' => 'siomai.png',
                    'siomai-egg-rice-bowl' => 'siomai.png',
                ];
                $resolveFoodImage = function ($title, $image) use ($menuImages) {
                    $slug = \Illuminate\Support\Str::of($title)
                        ->slug()
                        ->replace('chessy', 'cheesy')
                        ->replace('bundwich', 'bunwich')
                        ->replace('tereyaki', 'teriyaki')
                        ->toString();
                    return isset($menuImages[$slug]) ? asset('assets/imgs/' . $menuImages[$slug]) : asset('food_img/' . $image);
                };
                $latestOrder = $orderGroups->first();
            @endphp

            @if($latestOrder)
                @php
                    $statusClass = in_array($latestOrder->delivery_status, ['In Progress', 'On The Way']) ? 'pending' : ($latestOrder->delivery_status === 'Canceled' ? 'canceled' : '');
                    $latestImage = $resolveFoodImage($latestOrder->title ?? '', $latestOrder->image);
                @endphp
                <article class="order-feature">
                    <div>
                        <h2>Order No.: <span class="order-number">{{ $latestOrder->order_number }}</span></h2>
                        <p>{{ $latestOrder->created_at ? $latestOrder->created_at->format('d-M-Y, g:i A') : '' }}</p>
                        <p>{{ $latestOrder->item_count }} item(s) - &#8369;{{ number_format($latestOrder->total, 2) }}</p>
                        <span class="order-status {{ $statusClass }}">{{ $latestOrder->delivery_status }}</span>
                    </div>
                    <img src="{{ $latestImage }}" alt="Order item">
                </article>

                <div class="order-actions">
                    <a class="track-link" href="{{ url('track_order', $latestOrder->id) }}">Track Order</a>
                    <button class="all-orders-button" id="openOrders" type="button">All Orders</button>
                </div>
            @else
                <p class="empty-orders">No orders yet. Your confirmed orders will show up here.</p>
            @endif
        </section>
    </main>

    <div class="orders-modal" id="ordersModal" aria-hidden="true">
        <section class="orders-panel" role="dialog" aria-modal="true" aria-labelledby="allOrdersTitle">
            <div class="orders-panel-head">
                <h2 id="allOrdersTitle">All Orders</h2>
                <button class="orders-close" id="closeOrders" type="button" aria-label="Close all orders">&times;</button>
            </div>

            @forelse($orderGroups as $order)
                @php
                    $statusClass = in_array($order->delivery_status, ['In Progress', 'On The Way']) ? 'pending' : ($order->delivery_status === 'Canceled' ? 'canceled' : '');
                    $orderImage = $resolveFoodImage($order->title ?? '', $order->image);
                @endphp
                <article class="order-row">
                    <div>
                        <h3>Order No.: <span class="order-number">{{ $order->order_number }}</span></h3>
                        <p>{{ $order->created_at ? $order->created_at->format('d-M-Y, g:i A') : '' }}</p>
                        <p>{{ $order->item_count }} item(s) - &#8369;{{ number_format($order->total, 2) }}</p>
                        <p><span class="order-status {{ $statusClass }}">{{ $order->delivery_status }}</span> | <a href="{{ url('track_order', $order->id) }}">Track</a></p>
                    </div>
                    <img src="{{ $orderImage }}" alt="Order item">
                </article>
            @empty
                <p>No orders yet.</p>
            @endforelse
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('ordersModal');
            var open = document.getElementById('openOrders');
            var close = document.getElementById('closeOrders');

            if (!modal || !open || !close) {
                return;
            }

            function showOrders() {
                modal.classList.add('is-visible');
                modal.setAttribute('aria-hidden', 'false');
            }

            function hideOrders() {
                modal.classList.remove('is-visible');
                modal.setAttribute('aria-hidden', 'true');
            }

            open.addEventListener('click', showOrders);
            close.addEventListener('click', hideOrders);
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    hideOrders();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    hideOrders();
                }
            });
        });
    </script>
</body>
</html>
