<!DOCTYPE html>
<html>
  <head>
    @include('admin.css')
    <style>
      .rider-dashboard { background:#fff; border:1px solid #1f2937; border-radius:12px; color:#111827; min-height:calc(100vh - 120px); padding:28px; }
      .rider-dashboard h2 { font-size:28px; font-weight:900; margin:0 0 8px; }
      .rider-dashboard > p { color:#64748b; margin:0 0 24px; }
      .rider-stats { display:grid; gap:16px; grid-template-columns:repeat(2, minmax(0, 1fr)); margin-bottom:24px; }
      .rider-stat { border:1px solid #d1d5db; border-radius:10px; padding:18px; }
      .rider-stat span { color:#64748b; display:block; font-size:13px; font-weight:700; }
      .rider-stat strong { color:#dc2626; display:block; font-size:30px; margin-top:5px; }
      .rider-orders { border:1px solid #d1d5db; border-radius:10px; overflow-x:auto; }
      .rider-orders table { border-collapse:collapse; min-width:680px; width:100%; }
      .rider-orders th, .rider-orders td { border-bottom:1px solid #e5e7eb; padding:14px 16px; text-align:left; }
      .rider-orders th { background:#fff7f7; font-size:12px; text-transform:uppercase; }
      .rider-order-number { color:#dc2626; font-weight:900; }
      .rider-status { background:#fff1f2; border:1px solid #fecdd3; border-radius:999px; color:#be123c; display:inline-flex; font-size:12px; font-weight:800; padding:5px 9px; }
      .rider-action { border:1px solid #dc2626; border-radius:999px; color:#dc2626; font-size:12px; font-weight:800; padding:6px 10px; text-decoration:none; }
      .rider-action:hover { background:#fff1f2; color:#b91c1c; text-decoration:none; }
      @media (max-width:767px) { .rider-dashboard { padding:18px; } .rider-stats { grid-template-columns:1fr; } }
    </style>
  </head>
  <body>
    @include('admin.header')
    @include('admin.sidebar')
    <div class="page-content"><div class="page-header"><div class="container-fluid">
      <main class="rider-dashboard">
        <h2>Rider Dashboard</h2>
        <p>Manage delivery details and status for orders assigned to you.</p>
        <section class="rider-stats">
          <div class="rider-stat"><span>Active deliveries</span><strong>{{ $assignedOrders->count() }}</strong></div>
          <div class="rider-stat"><span>Completed deliveries</span><strong>{{ $completedDeliveries }}</strong></div>
        </section>
        <section class="rider-orders">
          <table>
            <thead><tr><th>Order No.</th><th>Customer</th><th>Delivery address</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
              @forelse($assignedOrders as $order)
                <tr>
                  <td class="rider-order-number">{{ $order->order_number }}</td>
                  <td>{{ $order->name }}<br><small>{{ $order->phone }}</small></td>
                  <td>{{ $order->address }}</td>
                  <td><span class="rider-status">{{ $order->delivery_status }}</span></td>
                  <td><a class="rider-action" href="{{ url('orders') }}">View delivery</a></td>
                </tr>
              @empty
                <tr><td colspan="5">No delivery is currently assigned to you.</td></tr>
              @endforelse
            </tbody>
          </table>
        </section>
      </main>
    </div></div></div>
    @include('admin.js')
  </body>
</html>
