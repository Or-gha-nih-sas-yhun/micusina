<!DOCTYPE html>
<html>
  <head> 
    
  @include('admin.css')

  <style>
    .reservation-frame
    {
        background:radial-gradient(circle at top left, rgba(255, 255, 255, 0.08), transparent 28%), #0c0d10;
        border:1px solid #2b2f38;
        border-radius:8px;
        color:#fff;
        margin:0;
        min-height:calc(100vh - 120px);
        padding:24px;
    }

    .reservation-panel
    {
        background:#15171c;
        border:1px solid #2b2f38;
        border-radius:8px;
        overflow-x:visible;
    }

    .reservation-analytics
    {
        display:grid;
        gap:18px;
        grid-template-columns:repeat(12, 1fr);
        margin:18px 0 22px;
    }

    .reservation-chart
    {
        background:#15171c;
        border:1px solid #2b2f38;
        border-radius:8px;
        grid-column:span 7;
        padding:22px;
    }

    .reservation-summary
    {
        background:#15171c;
        border:1px solid #2b2f38;
        border-radius:8px;
        display:grid;
        gap:12px;
        grid-column:span 5;
        grid-template-columns:repeat(2, minmax(0, 1fr));
        padding:22px;
    }

    .reservation-chart h3,
    .reservation-summary h3
    {
        color:#fff;
        font-size:18px;
        font-weight:800;
        grid-column:1 / -1;
        margin:0 0 8px;
    }

    .reservation-chart-box
    {
        height:260px;
        min-height:260px;
    }

    .reservation-stat
    {
        background:#101116;
        border:1px solid #2b2f38;
        border-radius:8px;
        padding:14px;
    }

    .reservation-stat span
    {
        color:#a6abb6;
        display:block;
        font-size:13px;
        margin-bottom:6px;
    }

    .reservation-stat strong
    {
        color:#fff;
        display:block;
        font-size:22px;
    }

    table
    {
        border:0;
        border-collapse:collapse;
        margin:0;
        table-layout:fixed;
        width:100%;
    }

    th
    {
        background:#101116;
        border-bottom:1px solid #2b2f38;
        color:#a6abb6;
        font-size:12px;
        font-weight:800;
        text-align:left;
        padding:14px 16px;
        text-transform:uppercase;
        white-space:normal;
        word-break:break-word;
    }

    td
    {
        background:#15171c;
        border-top:1px solid #2b2f38;
        color:white;
        font-weight:500;
        text-align:left;
        padding:14px 16px;
        vertical-align:middle;
        white-space:normal;
        word-break:break-word;
    }

    tr:hover td
    {
        background:#1f2229;
    }

    .reservation-alert
    {
        margin-bottom:20px;
    }

    .reservation-status
    {
        border-radius:999px;
        display:inline-block;
        padding:5px 10px;
    }

    .reservation-pending { background:#F88379; }
    .reservation-approved { background:#0f766e; }

    @media (max-width: 991px)
    {
        .reservation-chart,
        .reservation-summary
        {
            grid-column:1 / -1;
        }
    }
  </style>
  </head>
  <body>


       @include('admin.header')
        @include('admin.sidebar')

        
      <div class="page-content">
        <div class="page-header"> 
            <div class="container-fluid reservation-frame">
          <h2>Book a Table</h2>

          @if(session()->has('message'))
            <div class="reservation-alert alert alert-success">{{ session()->get('message') }}</div>
          @endif

          <div class="reservation-analytics">
            <div class="reservation-chart">
              <h3>Book a Table Overview</h3>
              <div class="reservation-chart-box">
                <canvas id="reservationOverviewChart"></canvas>
              </div>
            </div>

            <div class="reservation-summary">
              <h3>Reservation Summary</h3>
              <div class="reservation-stat">
                <span>Total Bookings</span>
                <strong>{{ $book->count() }}</strong>
              </div>
              <div class="reservation-stat">
                <span>Pending</span>
                <strong>{{ $book->where('status', 'Pending')->count() }}</strong>
              </div>
              <div class="reservation-stat">
                <span>Approved</span>
                <strong>{{ $book->where('status', 'Approved')->count() }}</strong>
              </div>
              <div class="reservation-stat">
                <span>Deposits</span>
                <strong>&#8369;{{ number_format((float) $book->where('status', 'Approved')->sum('deposit_amount'), 2) }}</strong>
              </div>
            </div>
          </div>

          <div class="reservation-panel">
          <table class="reservation-table">
            <tr>
              <th>Booking Ref</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Guests</th>
              <th>Reserved Date/Time</th>
              <th>Use Date</th>
              <th>Use Time</th>
              <th>Price</th>
              <th>50% Deposit</th>
              <th>Payment</th>
              <th>Payment Status</th>
              <th>GCash Transaction Ref</th>
              <th>Status</th>
              <th>Action</th>
            </tr>

            @foreach($book as $booking)
            <tr>
              <td>{{ $booking->gcash_reference ?? ('BK-' . str_pad((string) $booking->id, 6, '0', STR_PAD_LEFT)) }}</td>
              <td>{{ $booking->name ?? 'N/A' }}</td>
              <td>{{ $booking->email ?? 'N/A' }}</td>
              <td>{{ $booking->phone }}</td>
              <td>{{ $booking->guest }}</td>
              <td>{{ $booking->created_at ? $booking->created_at->format('M d, Y h:i A') : 'N/A' }}</td>
              <td>{{ $booking->date }}</td>
              <td>{{ $booking->time }}</td>
              <td>&#8369;{{ number_format((float) ($booking->reservation_price ?? 0), 2) }}</td>
              <td>&#8369;{{ number_format((float) ($booking->deposit_amount ?? 0), 2) }}</td>
              <td>{{ $booking->payment_method ?? 'GCash' }}</td>
              <td>{{ $booking->payment_status ?? 'Pending' }}</td>
              <td>{{ $booking->gcash_transaction_reference ?? '—' }}</td>
              <td>
                <span class="reservation-status {{ ($booking->status ?? 'Pending') === 'Approved' ? 'reservation-approved' : 'reservation-pending' }}">
                  {{ $booking->status ?? 'Pending' }}
                </span>
              </td>
              <td>
                @if(($booking->payment_status ?? 'Pending') === 'Pending Verification' && $booking->payment_method === 'GCash')
                  <form method="POST" action="{{ url('verify_reservation_payment', $booking->id) }}">@csrf<button class="btn btn-success" type="submit">Verify Payment</button></form>
                @elseif(($booking->payment_status ?? 'Pending') !== 'Paid')
                  Awaiting verified payment
                @elseif(($booking->status ?? 'Pending') === 'Pending')
                  <form method="POST" action="{{ url('approve_reservation', $booking->id) }}">@csrf<button class="btn btn-success" type="submit">Approve</button></form>
                @else
                  Approved
                @endif
              </td>
            </tr>
            @endforeach
          </table>
          </div>

        </div>
      </div>
    </div>
    @include('admin.js')
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        var chartEl = document.getElementById('reservationOverviewChart');

        if (!chartEl || typeof Chart === 'undefined') {
          return;
        }

        new Chart(chartEl, {
          type: 'bar',
          data: {
            labels: @json($salesLabels),
            datasets: [
              {
                label: 'Deposits',
                data: @json($depositValues),
                backgroundColor: '#0ea5e9',
                borderColor: '#38bdf8',
                borderWidth: 1,
                yAxisID: 'deposit-axis'
              },
              {
                label: 'Bookings',
                data: @json($bookingValues),
                backgroundColor: '#F88379',
                borderColor: '#FFA69E',
                borderWidth: 1,
                yAxisID: 'booking-axis'
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: { labels: { fontColor: '#ffffff' } },
            scales: {
              xAxes: [{
                ticks: { fontColor: '#a6abb6' },
                gridLines: { display: false }
              }],
              yAxes: [
                {
                  id: 'deposit-axis',
                  position: 'left',
                  ticks: { beginAtZero: true, fontColor: '#a6abb6' },
                  gridLines: { color: 'rgba(255,255,255,0.08)' }
                },
                {
                  id: 'booking-axis',
                  position: 'right',
                  ticks: { beginAtZero: true, fontColor: '#a6abb6', precision: 0 },
                  gridLines: { display: false }
                }
              ]
            }
          }
        });
      });
    </script>
  </body>
</html>
