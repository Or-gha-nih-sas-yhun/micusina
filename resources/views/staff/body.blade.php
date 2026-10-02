<style>
  .staff-dashboard {
    background: radial-gradient(circle at top left, rgba(255, 255, 255, 0.08), transparent 28%), #0c0d10;
    border: 1px solid #2b2f38;
    border-radius: 8px;
    color: #ffffff;
    margin: 0;
    min-height: calc(100vh - 120px);
    padding: 28px;
  }

  .staff-dashboard h2 {
    color: #fff;
    font-size: 24px;
    font-weight: 800;
    margin-bottom: 26px;
  }

  .staff-card {
    align-items: center;
    background: #15171c;
    border: 1px solid #2b2f38;
    border-radius: 8px;
    display: flex;
    gap: 16px;
    min-height: 96px;
    padding: 18px;
  }

  .staff-icon {
    align-items: center;
    border-radius: 999px;
    color: #fff;
    display: flex;
    font-size: 20px;
    height: 48px;
    justify-content: center;
    width: 48px;
  }

  .staff-label {
    color: #a6abb6;
    font-size: 14px;
  }

  .staff-value {
    color: #fff;
    font-size: 24px;
    font-weight: 800;
  }

  .bg-teal { background: #0f766e; }
  .bg-orange { background: #F88379; }
  .bg-blue { background: #2563eb; }
  .bg-green { background: #15803d; }
  .bg-red { background: #b91c1c; }

  .staff-panel {
    background: #15171c;
    border: 1px solid #2b2f38;
    border-radius: 8px;
    margin-top: 30px;
    overflow-x: visible;
  }

  .staff-panel-header {
    align-items: center;
    border-bottom: 1px solid #2b2f38;
    display: flex;
    justify-content: space-between;
    padding: 18px 20px;
  }

  .staff-panel-header h3 {
    color: #fff;
    font-size: 18px;
    margin: 0;
  }

  .staff-table {
    margin: 0;
    table-layout: fixed;
    width: 100%;
  }

  .staff-table th {
    background: #101116;
    color: #a6abb6;
    font-size: 12px;
    padding: 14px 16px;
    text-align: left;
    text-transform: uppercase;
    white-space: normal;
    word-break: break-word;
  }

  .staff-table td {
    background: #15171c;
    border-top: 1px solid #2b2f38;
    color: #fff;
    padding: 14px 16px;
    vertical-align: middle;
    white-space: normal;
    word-break: break-word;
  }

  .staff-table tr:hover td {
    background: #1f2229;
  }

  .staff-actions {
    display: flex;
    gap: 10px;
  }

  .staff-gap {
    margin-bottom: 18px;
  }

  .rider-status {
    border-radius: 999px;
    color: #fff;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    padding: 4px 9px;
  }

  .rider-available { background: #15803d; }
  .rider-unavailable { background: #b91c1c; }
  .staff-order-number { color: #dc2626; font-weight: 900; }
  .staff-assign-link { border: 1px solid #dc2626; border-radius: 999px; color: #dc2626; display: inline-flex; font-size: 12px; font-weight: 800; padding: 5px 9px; text-decoration: none; }
  .staff-assign-link:hover { background: #fff1f2; color: #b91c1c; text-decoration: none; }
</style>

<div class="staff-dashboard">
  <h2>Staff Dashboard</h2>

  <div class="row">
    <div class="col-lg-4 col-md-6 staff-gap">
      <div class="staff-card">
        <div class="staff-icon bg-teal"><i class="fa fa-money"></i></div>
        <div>
          <div class="staff-label">Today Delivery Sales</div>
          <small style="color:#9ca3af;">Paid delivered orders</small>
          <div class="staff-value">&#8369;{{ number_format($today_delivery_sales, 2) }}</div>
        </div>
      </div>
    </div>

    <div class="col-lg-4 col-md-6 staff-gap">
      <div class="staff-card">
        <div class="staff-icon bg-blue"><i class="fa fa-calendar"></i></div>
        <div>
          <div class="staff-label">This Month Delivery Sales</div>
          <small style="color:#9ca3af;">Paid delivered orders</small>
          <div class="staff-value">&#8369;{{ number_format($monthly_delivery_sales, 2) }}</div>
        </div>
      </div>
    </div>

    <div class="col-lg-4 col-md-6 staff-gap">
      <div class="staff-card">
        <div class="staff-icon bg-green"><i class="fa fa-line-chart"></i></div>
        <div>
          <div class="staff-label">Total Delivery Sales</div>
          <small style="color:#9ca3af;">Paid delivered orders</small>
          <div class="staff-value">&#8369;{{ number_format($total_delivery_sales, 2) }}</div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 staff-gap">
      <div class="staff-card">
        <div class="staff-icon bg-orange"><i class="fa fa-refresh"></i></div>
        <div>
          <div class="staff-label">Pending Orders</div>
          <div class="staff-value">{{ $pending_orders }}</div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 staff-gap">
      <div class="staff-card">
        <div class="staff-icon bg-blue"><i class="fa fa-truck"></i></div>
        <div>
          <div class="staff-label">On The Way</div>
          <div class="staff-value">{{ $on_the_way_orders }}</div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 staff-gap">
      <div class="staff-card">
        <div class="staff-icon bg-green"><i class="fa fa-check"></i></div>
        <div>
          <div class="staff-label">Delivered</div>
          <div class="staff-value">{{ $delivered_orders }}</div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 staff-gap">
      <div class="staff-card">
        <div class="staff-icon bg-red"><i class="fa fa-ban"></i></div>
        <div>
          <div class="staff-label">Canceled</div>
          <div class="staff-value">{{ $canceled_orders }}</div>
        </div>
      </div>
    </div>

    <div class="col-lg-6 col-md-6 staff-gap">
      <div class="staff-card">
        <div class="staff-icon bg-green"><i class="fa fa-check-circle"></i></div>
        <div>
          <div class="staff-label">Paid Orders</div>
          <div class="staff-value">{{ $paid_orders }}</div>
        </div>
      </div>
    </div>

    <div class="col-lg-6 col-md-6 staff-gap">
      <div class="staff-card">
        <div class="staff-icon bg-red"><i class="fa fa-exclamation-circle"></i></div>
        <div>
          <div class="staff-label">Unpaid Orders</div>
          <div class="staff-value">{{ $unpaid_orders }}</div>
        </div>
      </div>
    </div>

  </div>

  <div class="row">
    <div class="col-lg-12">
      <div class="staff-panel">
        <div class="staff-panel-header">
          <h3>Recent Deliveries & Rider Assignment</h3>
          <a class="btn btn-primary" href="{{ url('orders') }}">Assign Riders</a>
        </div>
        <table class="staff-table">
          <tr>
            <th>Order No.</th>
            <th>Customer</th>
            <th>Food</th>
            <th>Price</th>
            <th>Status</th>
            <th>Rider</th>
            <th>Payment</th>
            <th>Action</th>
          </tr>
          @foreach($recent_orders as $order)
            <tr>
              <td class="staff-order-number">{{ $order->order_number }}</td>
              <td>{{ $order->name }}</td>
              <td>{{ $order->title }}</td>
              <td>&#8369;{{ number_format((float) $order->price, 2) }}</td>
              <td>{{ $order->delivery_status }}</td>
              <td>{{ $order->rider?->name ?? 'Awaiting assignment' }}</td>
              <td>{{ $order->payment_method ?? 'Cash on Delivery' }} - {{ $order->payment_status ?? 'Unpaid' }}</td>
              <td>
                @if(!$order->rider && !in_array($order->delivery_status, ['Delivered', 'Canceled'], true))
                  <a class="staff-assign-link" href="{{ url('orders') }}">Assign Rider</a>
                @else
                  —
                @endif
              </td>
            </tr>
          @endforeach
        </table>
      </div>
    </div>
  </div>
</div>
