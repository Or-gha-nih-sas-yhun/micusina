<!DOCTYPE html>
<html>
  <head>
    @include('admin.css')
    <style>
      .riders-frame {
        background: radial-gradient(circle at top left, rgba(255, 255, 255, 0.08), transparent 28%), #0c0d10;
        border: 1px solid #2b2f38;
        border-radius: 8px;
        color: #fff;
        margin: 0;
        min-height: calc(100vh - 120px);
        padding: 24px;
      }

      .riders-top {
        display: grid;
        gap: 16px;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        margin: 18px 0 24px;
      }

      .rider-stat {
        background: #15171c;
        border: 1px solid #2b2f38;
        border-radius: 8px;
        min-height: 104px;
        padding: 16px;
      }

      .rider-stat-icon {
        align-items: center;
        background: rgba(23, 201, 100, 0.18);
        border-radius: 8px;
        color: #17c964;
        display: inline-flex;
        height: 34px;
        justify-content: center;
        margin-bottom: 10px;
        width: 34px;
      }

      .rider-stat-label {
        color: #a6abb6;
        font-size: 13px;
      }

      .rider-stat-value {
        color: #fff;
        font-size: 28px;
        font-weight: 800;
      }

      .riders-panel {
        background: #15171c;
        border: 1px solid #2b2f38;
        border-radius: 8px;
        overflow: visible;
      }

      .riders-table {
        border-collapse: collapse;
        table-layout: fixed;
        width: 100%;
      }

      .riders-table th {
        background: #101116;
        border-bottom: 1px solid #2b2f38;
        color: #a6abb6;
        font-size: 12px;
        font-weight: 800;
        padding: 14px 16px;
        text-align: left;
        text-transform: uppercase;
        white-space: normal;
        word-break: break-word;
      }

      .riders-table td {
        background: #15171c;
        border-top: 1px solid #2b2f38;
        color: #fff;
        padding: 14px 16px;
        vertical-align: middle;
        white-space: normal;
        word-break: break-word;
      }

      .riders-table tr:hover td {
        background: #1f2229;
      }

      .rider-status {
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        font-size: 12px;
        font-weight: 800;
        padding: 5px 10px;
      }

      .rider-available {
        background: #15803d;
      }

      .rider-unavailable {
        background: #b91c1c;
      }

      .assign-order-link {
        border: 1px solid #dc2626;
        border-radius: 999px;
        color: #dc2626;
        display: inline-flex;
        font-size: 12px;
        font-weight: 800;
        margin-bottom: 8px;
        padding: 6px 10px;
        text-decoration: none;
      }

      .assign-order-link:hover { background: #fff1f2; color: #b91c1c; text-decoration: none; }

      @media (max-width: 767px) {
        .riders-top {
          grid-template-columns: 1fr;
        }
      }

      /* Light riders table and colored summary symbols. */
      html body .riders-panel,
      html body .riders-table th,
      html body .riders-table td {
        background: #fff !important;
        color: #111827 !important;
        border-color: #e5e7eb !important;
      }

      html body .riders-table tr:hover td { background: #fff7f6 !important; }
      html body .rider-stat:nth-child(1) .rider-stat-icon { background: #dbeafe !important; color: #2563eb !important; }
      html body .rider-stat:nth-child(2) .rider-stat-icon { background: #dcfce7 !important; color: #15803d !important; }
      html body .rider-stat:nth-child(3) .rider-stat-icon { background: #fee2e2 !important; color: #dc2626 !important; }
      html body .rider-stat-icon i { color: inherit !important; }
      html body .rider-available { background: #dcfce7 !important; color: #15803d !important; }
      html body .rider-unavailable { background: #fee2e2 !important; color: #b91c1c !important; }
    </style>
  </head>
  <body>
    @include('admin.header')
    @include('admin.sidebar')

    <div class="page-content">
      <div class="page-header">
        <div class="container-fluid riders-frame">
          <h2>Rider Availability</h2>

          @if(session()->has('message'))
            <div class="alert alert-success">{{ session()->get('message') }}</div>
          @endif

          <div class="riders-top">
            <div class="rider-stat">
              <div class="rider-stat-icon"><i class="fa fa-motorcycle"></i></div>
              <div class="rider-stat-label">Total Riders</div>
              <div class="rider-stat-value">{{ $riders->count() }}</div>
            </div>

            <div class="rider-stat">
              <div class="rider-stat-icon"><i class="fa fa-check"></i></div>
              <div class="rider-stat-label">Available</div>
              <div class="rider-stat-value">{{ $availableRiders }}</div>
            </div>

            <div class="rider-stat">
              <div class="rider-stat-icon"><i class="fa fa-ban"></i></div>
              <div class="rider-stat-label">Unavailable</div>
              <div class="rider-stat-value">{{ $unavailableRiders }}</div>
            </div>
          </div>

          <div class="riders-panel">
            <table class="riders-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Address</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($riders as $rider)
                  <tr>
                    <td>{{ $rider->name }}</td>
                    <td>{{ $rider->email }}</td>
                    <td>{{ $rider->phone ?? 'No phone' }}</td>
                    <td>{{ $rider->address ?? 'No address' }}</td>
                    <td>
                      <span class="rider-status {{ $rider->rider_available ? 'rider-available' : 'rider-unavailable' }}">
                        {{ $rider->rider_available ? 'Available' : 'Unavailable' }}
                      </span>
                    </td>
                    <td>
                      @if(Auth::user()->usertype === 'admin' || Auth::user()->staff_role === 'cashier')
                        @if($rider->rider_available)
                          <a class="assign-order-link" href="{{ url('orders') }}" title="Choose this rider from an eligible order">Assign to Order</a>
                        @endif
                        <form action="{{ url('rider_availability', $rider->id) }}" method="POST">
                          @csrf
                          <input type="hidden" name="rider_available" value="{{ $rider->rider_available ? 0 : 1 }}">
                          <button class="btn btn-sm {{ $rider->rider_available ? 'btn-danger' : 'btn-success' }}" type="submit">
                            {{ $rider->rider_available ? 'Set Unavailable' : 'Set Available' }}
                          </button>
                        </form>
                      @elseif(Auth::id() === $rider->id)
                        <form action="{{ url('rider_availability', $rider->id) }}" method="POST">
                          @csrf
                          <input type="hidden" name="rider_available" value="{{ $rider->rider_available ? 0 : 1 }}">
                          <button class="btn btn-sm {{ $rider->rider_available ? 'btn-danger' : 'btn-success' }}" type="submit">
                            {{ $rider->rider_available ? 'Set Unavailable' : 'Set Available' }}
                          </button>
                        </form>
                      @else
                        View only
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6">No riders created yet.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    @include('admin.js')
  </body>
</html>
