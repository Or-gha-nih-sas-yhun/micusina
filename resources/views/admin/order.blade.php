<!DOCTYPE html>
<html>
  <head> 
    
  @include('admin.css')

  <style>
    .orders-frame
    {
        background:radial-gradient(circle at top left, rgba(255, 255, 255, 0.08), transparent 28%), #0c0d10;
        border:1px solid #2b2f38;
        border-radius:0;
        box-sizing:border-box;
        color:#fff;
        margin-left:0;
        min-height:calc(100vh - 92px);
        overflow:hidden;
        padding:18px;
        max-width:100%;
        width:100%;
    }

    .orders-table-panel
    {
        background:#15171c;
        border:1px solid #2b2f38;
        border-radius:8px;
        height:auto;
        overflow:hidden;
        width:100%;
    }

    .orders-table
    {
        border-collapse:collapse;
        min-width:0;
        table-layout:fixed;
        width:100%;
    }

    th
    {
        background:#101116;
        border-bottom:1px solid #2b2f38;
        color:#a6abb6;
        font-weight:800;
        font-size: 9px;
        text-align: left;
        padding:8px 4px;
        text-transform:uppercase;
        line-height:1.25;
        white-space:nowrap;
        word-break:normal;

    }

    td
    {
        background:#15171c;
        border-top:1px solid #2b2f38;
        color:white;
        font-weight:500;
        text-align: left;
        font-size:10px;
        line-height:1.25;
        padding:7px 4px;
        vertical-align:middle;
        overflow-wrap:anywhere;
        white-space:normal;
        word-break:normal;
        overflow:visible;
    }

    tr:hover td
    {
        background:#1f2229;
    }

    .table-wrap
    {
        height:100%;
        overflow-x:hidden;
        overflow-y:visible;
        padding-bottom:4px;
        width:100%;
    }

    .orders-table th:nth-child(1), .orders-table td:nth-child(1) { width:7%; }
    .orders-table th:nth-child(2), .orders-table td:nth-child(2) { width:8%; }
    .orders-table th:nth-child(3), .orders-table td:nth-child(3) { width:12%; }
    .orders-table th:nth-child(4), .orders-table td:nth-child(4) { width:10%; }
    .orders-table th:nth-child(5), .orders-table td:nth-child(5) { width:4%; text-align:center; }
    .orders-table th:nth-child(6), .orders-table td:nth-child(6) { width:6%; }
    .orders-table th:nth-child(7), .orders-table td:nth-child(7) { width:7%; }
    .orders-table th:nth-child(8), .orders-table td:nth-child(8) { width:9%; }
    .orders-table th:nth-child(9), .orders-table td:nth-child(9) { width:8%; }
    .orders-table th:nth-child(10), .orders-table td:nth-child(10) { width:8%; }
    .orders-table th:nth-child(11), .orders-table td:nth-child(11) { width:7%; }
    .orders-table th:nth-child(12), .orders-table td:nth-child(12) { width:8%; }
    .orders-table th:nth-child(13), .orders-table td:nth-child(13) { width:6%; }

    .order-image
    {
        background:#0f1015;
        border:1px solid #2b2f38;
        border-radius:6px;
        height:54px;
        object-fit:contain;
        padding:2px;
        width:54px;
    }

    .order-actions
    {
        display:flex;
        flex-wrap:wrap;
        gap:6px;
        justify-content:center;
        min-width:0;
    }

    .order-actions .btn
    {
        font-size:9px;
        padding:4px 5px;
    }

    .payment-badge
    {
        border-radius:999px;
        display:inline-block;
        font-size:9px;
        font-weight:800;
        line-height:1.2;
        padding:4px 6px;
        text-align:center;
    }

    .payment-paid
    {
        background:#0f766e;
        color:#fff;
    }

    .payment-unpaid
    {
        background:#b91c1c;
        color:#fff;
    }

    .payment-method
    {
        color:#e5e7eb;
        display:inline-block;
        font-size:10px;
        line-height:1.35;
        max-width:100%;
    }

    .order-status
    {
        border-radius:999px;
        display:inline-block;
        font-size:9px;
        font-weight:800;
        line-height:1.2;
        padding:4px 6px;
        text-align:center;
    }

    .order-status.is-progress
    {
        background:rgba(37, 99, 235, .24);
        border:1px solid rgba(96, 165, 250, .55);
        color:#dbeafe;
    }

    .order-status.is-way
    {
        background:rgba(14, 116, 144, .22);
        border:1px solid rgba(34, 211, 238, .32);
        color:#67e8f9;
    }

    .order-status.is-delivered
    {
        background:rgba(22, 163, 74, .2);
        border:1px solid rgba(74, 222, 128, .34);
        color:#86efac;
    }

    .order-status.is-canceled
    {
        background:rgba(220, 38, 38, .2);
        border:1px solid rgba(248, 113, 113, .34);
        color:#fca5a5;
    }

    .rider-assignment-cell
    {
        position:relative;
        z-index:100;
        overflow:visible;
    }

    .rider-assignment-cell.is-picker-open
    {
        z-index:10000;
    }

    .rider-form
    {
        background:#0c0f15;
        border:2px solid #0e7490;
        border-radius:8px;
        box-shadow:0 20px 60px rgba(0,0,0,.7);
        display:none !important;
        left:0;
        padding:12px;
        position:fixed;
        top:0;
        bottom:auto;
        transform:none;
        width:240px;
        z-index:9999 !important;
        max-height:none;
        overflow:visible;
        box-sizing:border-box;
        min-width:240px;
    }

    .rider-form.is-open
    {
        display:block !important;
        animation:popupShow 0.4s ease;
    }

    body.no-rider-scroll
    {
        overflow:hidden;
    }

    @keyframes popupShow
    {
        from
        {
            opacity:0;
            transform:scale(0.96);
        }
        to
        {
            opacity:1;
            transform:scale(1);
        }
    }

    .rider-form::before
    {
        content:'Select Rider';
        display:block;
        color:#0e7490;
        font-weight:800;
        font-size:13px;
        margin-bottom:10px;
        padding-bottom:7px;
        border-bottom:1px solid #2b2f38;
    }

    .rider-toggle
    {
        background:#0e7490;
        border-color:#155e75;
        border-radius:6px;
        color:#fff;
        font-size:9px;
        font-weight:800;
        padding:5px 6px;
        white-space:normal;
        width:100%;
    }

    .rider-toggle:hover
    {
        background:#155e75;
        border-color:#164e63;
        color:#fff;
    }

    .rider-choice
    {
        background:#151a22;
        border:2px solid #2b3f4f;
        border-radius:8px;
        cursor:pointer;
        display:flex;
        align-items:center;
        gap:10px;
        margin-bottom:8px;
        padding:10px;
        transition:all 0.3s ease;
        width:100%;
        box-sizing:border-box;
    }

    .rider-choice:hover
    {
        border-color:#0e7490;
        background:#1a2536;
        box-shadow:0 0 12px rgba(14, 116, 144, 0.3);
    }

    .rider-choice input
    {
        cursor:pointer;
        flex-shrink:0;
        width:16px;
        height:16px;
    }

    .rider-choice-text
    {
        display:flex;
        flex-direction:column;
        gap:5px;
        flex:1;
        min-width:0;
    }

    .rider-choice strong
    {
        font-size:14px;
        font-weight:700;
        color:#fff;
        word-break:break-word;
        white-space:normal;
    }

    .rider-status-pill
    {
        border-radius:16px;
        display:inline-block;
        font-size:11px;
        font-weight:800;
        padding:5px 10px;
        white-space:nowrap;
        text-transform:uppercase;
        letter-spacing:1px;
        vertical-align:middle;
    }

    .rider-status-pill.is-available
    {
        background:rgba(16, 185, 129, 0.2);
        color:#10b981;
        border:2px solid #10b981;
    }

    .rider-status-pill.is-unavailable
    {
        background:rgba(239, 68, 68, 0.2);
        color:#ef4444;
        border:2px solid #ef4444;
    }

    .rider-form .btn
    {
        background:#0e7490;
        border:2px solid #0e7490;
        border-radius:7px;
        font-size:13px;
        font-weight:700;
        padding:10px;
        white-space:nowrap;
        width:100%;
        color:#fff;
        cursor:pointer;
        transition:all 0.3s ease;
        margin-top:8px;
    }

    .rider-form .btn:hover
    {
        background:#155e75;
        border-color:#155e75;
    }

    .rider-name
    {
        background:rgba(15, 118, 110, 0.18);
        border:1px solid rgba(15, 118, 110, 0.34);
        border-radius:999px;
        color:#2dd4bf;
        display:block;
        font-weight:800;
        margin-bottom:6px;
        padding:5px 9px;
        width:max-content;
        max-width:100%;
    }

    .rider-phone
    {
        color:#a6abb6;
        display:block;
        font-size:9px;
    }

    .rider-empty
    {
        color:#a6abb6;
        font-size:12px;
    }

    .rider-cell
    {
        overflow:visible;
        position:relative;
        text-align:center;
    }

    .transaction-time
    {
        color:#dbeafe;
        display:block;
        font-size:9px;
        font-weight:700;
        line-height:1.35;
        white-space:normal;
    }

    .transaction-time small
    {
        color:#94a3b8;
        display:block;
        font-size:8px;
        font-weight:800;
        margin-bottom:2px;
        text-transform:uppercase;
    }

    .page-content .page-header .container-fluid.orders-frame
    {
        max-width:none;
    }

    .orders-table th:nth-child(1), .orders-table td:nth-child(1) { width:7%; }
    .orders-table th:nth-child(2), .orders-table td:nth-child(2) { width:7%; }
    .orders-table th:nth-child(3), .orders-table td:nth-child(3) { width:11%; }
    .orders-table th:nth-child(4), .orders-table td:nth-child(4) { width:9%; }
    .orders-table th:nth-child(5), .orders-table td:nth-child(5) { width:4%; }
    .orders-table th:nth-child(6), .orders-table td:nth-child(6) { width:5%; }
    .orders-table th:nth-child(7), .orders-table td:nth-child(7) { width:8%; }
    .orders-table th:nth-child(8), .orders-table td:nth-child(8) { width:9%; }
    .orders-table th:nth-child(9), .orders-table td:nth-child(9) { width:7%; }
    .orders-table th:nth-child(10), .orders-table td:nth-child(10) { width:8%; }
    .orders-table th:nth-child(11), .orders-table td:nth-child(11) { width:7%; }
    .orders-table th:nth-child(12), .orders-table td:nth-child(12) { width:10%; }
    .orders-table th:nth-child(13), .orders-table td:nth-child(13) { width:8%; }

    .mic-swal-popup
    {
        border-radius:8px;
        font-family:inherit;
    }

    .mic-swal-title
    {
        color:#111827;
        font-size:24px;
        font-weight:900;
    }

    .mic-swal-confirm
    {
        background:#0e7490 !important;
        border-radius:6px !important;
        box-shadow:none !important;
        color:#fff !important;
        font-weight:800 !important;
        padding:10px 22px !important;
    }

    .mic-swal-cancel
    {
        background:#f5d0d9 !important;
        border-radius:6px !important;
        color:#7f1235 !important;
        font-weight:800 !important;
        padding:10px 22px !important;
    }
  </style>
  </head>
  <body>
    @php
        $adminMenuImages = [
            'chicken-burger' => 'chicken burger.png',
            'egg-bunwich' => 'egg-bunwich.png',
            'cheesy-chicken' => 'chessy-chicken.png',
            'chessy-chicken' => 'chessy-chicken.png',
            'cheesy-chicken-hotdog-sandwich' => 'chessy-chicken.png',
            'chessy-chicken-hotdog-sandwich' => 'chessy-chicken.png',
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
            'chicken-adobo-bundwich' => 'chicken-adobo-bundwich.png',
            'chicken-fillet' => 'chicken-fillet.png',
            'chicken-burger-spaghetti' => 'chicken-burger-spaghetti.png',
            'chicken-adobo-flakes' => 'chicken-adobo-flakes.png',
            'chicken-teriyaki' => 'chicken-tereyaki.png',
            'chicken-tereyaki' => 'chicken-tereyaki.png',
            'ham-bowl' => 'ham-bowl.png',
            'siomai' => 'siomai.png',
            'siomai-egg-rice-bowl' => 'siomai.png',
        ];
    @endphp


       @include('admin.header')
        @include('admin.sidebar')

        
      <div class="page-content">
        <div class="page-header"> 
          <div class="container-fluid orders-frame">

        @if(session()->has('message'))
            <div class="alert alert-success">
                {{ session()->get('message') }}
            </div>
        @endif

        <div class="orders-table-panel">
        <div class="table-wrap">
        <table class="orders-table">
            <tr>
                <th>Order No.</th>
                <th>Customer</th>

                <th>Phone</th>

                <th>Address</th>

                <th>Food Item</th>

                <th>Qty</th>

                <th>Price</th>

                <th>Image</th>

                <th>Rider</th>

                <th>Status</th>

                <th>Payment</th>

                <th>Payment Status</th>

                <th>Update Status</th>

                <th>Ordered</th>
            </tr>

            @foreach ($data as $order)
                @php
                    $mappedImage = $adminMenuImages[\Illuminate\Support\Str::slug($order->title)] ?? null;
                    $orderImage = $mappedImage ? asset('assets/imgs/' . $mappedImage) : asset('food_img/' . $order->image);
                @endphp


            <tr>
                <td>{{ $order->order_number }}</td>
                <td>{{$order->name }}</td>

                <td>{{$order->phone }}</td>

                <td>{{$order->address }}</td>

                <td>{{$order->title }}</td>

                <td>{{$order->quantity }}</td>

                <td>&#8369;{{ number_format((float) $order->price, 2) }}</td>

                <td class="rider-cell">
                    <img class="order-image" src="{{ $orderImage }}" alt="{{ $order->title }}">
                </td>

                <td class="rider-assignment-cell">
                    @if($order->rider)
                        <span class="rider-name">{{ $order->rider->name }}</span>
                    @elseif(!in_array($order->delivery_status, ['Delivered', 'Canceled'], true) && (Auth::user()->usertype === 'admin' || Auth::user()->staff_role === 'cashier'))
                        <button class="btn btn-success rider-toggle" type="button">Assign Rider</button>
                        <form class="rider-form" action="{{ url('assign_rider', $order->id) }}" method="POST">
                            @csrf
                            @foreach($availableRiders as $rider)
                                <label class="rider-choice">
                                    <input type="radio" name="rider_id" value="{{ $rider->id }}" required>
                                    <span class="rider-choice-text">
                                        <strong>{{ $rider->name }}</strong>
                                        <span class="rider-status-pill {{ $rider->rider_available ? 'is-available' : 'is-unavailable' }}">
                                            {{ $rider->rider_available ? 'Available' : 'Unavailable' }}
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                            <button class="btn btn-success" type="submit">Confirm Rider</button>
                        </form>
                    @else
                        <span class="rider-empty">No rider assigned</span>
                    @endif
                </td>

                <td>
                    @php
                        $statusClass = match($order->delivery_status) {
                            'Delivered' => 'is-delivered',
                            'On The Way' => 'is-way',
                            'Canceled' => 'is-canceled',
                            default => 'is-progress',
                        };
                    @endphp
                    <span class="order-status {{ $statusClass }}">{{ $order->delivery_status }}</span>
                </td>

                <td><span class="payment-method">{{ $order->payment_method ?? 'Cash on Delivery' }}</span></td>

                <td>
                    <span class="payment-badge {{ ($order->payment_status ?? 'Unpaid') == 'Paid' ? 'payment-paid' : 'payment-unpaid' }}">
                        {{ $order->payment_status ?? 'Unpaid' }}
                    </span>
                </td>
                
                <td>
                    <div class="order-actions">
                    @if($order->delivery_status === 'Delivered')
                        <span class="text-success font-weight-bold">Delivered</span>
                    @elseif($order->delivery_status === 'Canceled')
                        <span class="text-danger font-weight-bold">Canceled — final</span>
                    @elseif($order->delivery_status === 'On The Way')
                        <form method="POST" action="{{ url('delivered', $order->id) }}" class="js-confirm-action" data-title="Mark order as delivered?" data-text="This will complete this delivery and make the assigned rider available again." data-confirm="Yes, deliver it">@csrf<button class="btn btn-warning" type="submit">Delivered</button></form>
                    @else
                        <form method="POST" action="{{ url('on_the_way', $order->id) }}" class="js-confirm-action" data-title="Mark order as on the way?" data-text="This will update all items in this order to On The Way." data-confirm="Yes, update it">@csrf<button class="btn btn-info" type="submit">On The Way</button></form>
                        @if(($order->payment_status ?? 'Unpaid') !== 'Paid')
                            <form method="POST" action="{{ url('canceled', $order->id) }}" class="js-confirm-action" data-title="Cancel this order?" data-text="This will mark all items in this order as Canceled." data-confirm="Yes, cancel it">@csrf<button class="btn btn-danger" type="submit">Canceled</button></form>
                        @endif
                    @endif

                    @if(!in_array($order->delivery_status, ['Delivered', 'Canceled'], true) && ($order->payment_status ?? 'Unpaid') !== 'Paid')
                        <form method="POST" action="{{ url('paid', $order->id) }}" class="js-confirm-action" data-title="Mark payment as paid?" data-text="Confirm that this customer already paid." data-confirm="Yes, mark paid">@csrf<button class="btn btn-success" type="submit">Paid</button></form>
                    @endif
                    </div>
                </td>

                <td>
                    <span class="transaction-time">
                        <small>Ordered</small>
                        {{ $order->created_at ? $order->created_at->format('M d, Y g:i A') : 'N/A' }}
                    </span>
                </td>
            </tr>

            @endforeach
        </table>
        </div>
        </div>

        </div>
      </div>
    </div>
    @include('admin.js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
        const riderToggles = document.querySelectorAll('.rider-toggle');
        const pickerGap = 8;

        function placeRiderPicker(button, form) {
          const buttonRect = button.getBoundingClientRect();
          const formRect = form.getBoundingClientRect();
          const pickerWidth = formRect.width || 240;
          const pickerHeight = formRect.height || 260;
          const viewportPadding = 12;
          const left = Math.min(
            Math.max(buttonRect.right - pickerWidth, viewportPadding),
            window.innerWidth - pickerWidth - viewportPadding
          );
          const top = Math.min(
            buttonRect.bottom + pickerGap,
            window.innerHeight - pickerHeight - viewportPadding
          );

          form.style.left = left + 'px';
          form.style.top = Math.max(top, viewportPadding) + 'px';
          form.style.bottom = 'auto';
        }

        function closeRiderPicker(form) {
          form.classList.remove('is-open');
          const riderCell = form.closest('.rider-assignment-cell');
          if (riderCell) riderCell.classList.remove('is-picker-open');
          if (!document.querySelector('.rider-form.is-open')) {
            document.body.classList.remove('no-rider-scroll');
          }
        }
        
        // Add click handler to all Assign Rider buttons
        riderToggles.forEach(function (button) {
          button.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Get the parent rider-assignment-cell
            const riderCell = button.closest('.rider-assignment-cell');
            if (!riderCell) return;
            
            // Get the form within this cell
            const form = riderCell.querySelector('.rider-form');
            if (!form) return;
            
            // Close all other open rider forms
            document.querySelectorAll('.rider-form.is-open').forEach(function(openForm) {
              if (openForm !== form) {
                closeRiderPicker(openForm);
              }
            });
            
            // Toggle current form visibility
            form.classList.toggle('is-open');
            riderCell.classList.toggle('is-picker-open', form.classList.contains('is-open'));
            
            if (form.classList.contains('is-open')) {
              document.body.classList.add('no-rider-scroll');
              placeRiderPicker(button, form);
            } else {
              document.body.classList.remove('no-rider-scroll');
            }
          });
        });

        window.addEventListener('resize', function () {
          document.querySelectorAll('.rider-form.is-open').forEach(function (form) {
            const riderCell = form.closest('.rider-assignment-cell');
            const button = riderCell ? riderCell.querySelector('.rider-toggle') : null;
            if (button) placeRiderPicker(button, form);
          });
        });

        // Close rider form when clicking outside
        document.addEventListener('click', function (e) {
          // If clicked inside a rider assignment cell, don't close
          if (e.target.closest('.rider-assignment-cell')) {
            return;
          }
          
          // Close all open forms
          document.querySelectorAll('.rider-form.is-open').forEach(function (form) {
            closeRiderPicker(form);
          });
        });

        // Prevent closing when clicking inside the form
        document.querySelectorAll('.rider-form').forEach(function(form) {
          form.addEventListener('click', function(e) {
            e.stopPropagation();
          });
        });

        document.querySelectorAll('form.js-confirm-action').forEach(function (form) {
          form.addEventListener('submit', function (event) {
            event.preventDefault();

            const title = form.dataset.title || 'Are you sure?';
            const text = form.dataset.text || 'This action will update the order.';
            const confirmText = form.dataset.confirm || 'Confirm';

            if (!window.Swal) {
              if (window.confirm(title + '\n\n' + text)) {
                form.submit();
              }
              return;
            }

            Swal.fire({
              title: title,
              text: text,
              icon: 'question',
              showCancelButton: true,
              confirmButtonText: confirmText,
              cancelButtonText: 'Cancel',
              reverseButtons: true,
              customClass: {
                popup: 'mic-swal-popup',
                title: 'mic-swal-title',
                confirmButton: 'mic-swal-confirm',
                cancelButton: 'mic-swal-cancel'
              },
              buttonsStyling: false
            }).then(function (result) {
              if (result.isConfirmed) {
                form.submit();
              }
            });
          });
        });

        // Read-only polling keeps the table current without submitting or duplicating data.
        var orderVersion = @json(optional($data->first())->updated_at?->toIso8601String());
        window.setInterval(function () {
          fetch(@json(route('orders.updates')), {headers: {'Accept': 'application/json'}, credentials: 'same-origin'})
            .then(function (response) { return response.ok ? response.json() : null; })
            .then(function (payload) {
              if (payload && payload.latest && payload.latest !== orderVersion) window.location.reload();
            }).catch(function () {});
        }, 15000);
      });
    </script>
  </body>
</html>
