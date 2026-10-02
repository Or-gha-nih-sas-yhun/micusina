<!DOCTYPE html>
<html>
  <head> 
    
  @include('admin.css')

  <style>
    .foods-frame
    {
        background:radial-gradient(circle at top left, rgba(255, 255, 255, 0.08), transparent 28%), #0c0d10;
        border:1px solid #2b2f38;
        border-radius:8px;
        color:#fff;
        margin:0;
        min-height:calc(100vh - 120px);
        padding:14px;
    }

    .foods-panel
    {
        background:#15171c;
        border:1px solid #2b2f38;
        border-radius:8px;
        overflow-x:hidden;
    }

    table
    {
        border-collapse:collapse;
        margin:0;
        table-layout:fixed;
        min-width:0;
        width: 100%;
    }
    th
    {
        background:#101116;
        border-bottom:1px solid #2b2f38;
        color:#a6abb6;
        font-size:12px;
        font-weight:800;
        padding: 9px 7px;
        text-align:left;
        text-transform:uppercase;
        white-space:normal;
        word-break:break-word;

    }
    td
    {
        background:#15171c;
        border-top:1px solid #2b2f38;
        color:white;
        font-size:12px;
        line-height:1.35;
        padding: 8px 7px;
        vertical-align:middle;
        white-space:normal;
        word-break:break-word;
      
    }

    tr:hover td
    {
        background:#1f2229;
    }

    .food-thumb
    {
        background:#0f1015;
        border:1px solid #2b2f38;
        border-radius:6px;
        height:82px;
        object-fit:contain;
        padding:3px;
        width:82px;
    }

    .foods-table th:nth-child(1), .foods-table td:nth-child(1) { width:12%; }
    .foods-table th:nth-child(2), .foods-table td:nth-child(2) { width:20%; }
    .foods-table th:nth-child(3), .foods-table td:nth-child(3) { width:10%; }
    .foods-table th:nth-child(4), .foods-table td:nth-child(4) { width:5%; }
    .foods-table th:nth-child(5), .foods-table td:nth-child(5) { width:5%; }
    .foods-table th:nth-child(6), .foods-table td:nth-child(6) { width:12%; }
    .foods-table th:nth-child(7), .foods-table td:nth-child(7) { width:12%; }
    .foods-table th:nth-child(8),
    .foods-table td:nth-child(8)
    {
        text-align:center;
        width:10%;
    }

    .foods-table th:nth-child(9),
    .foods-table td:nth-child(9),
    .foods-table th:nth-child(10),
    .foods-table td:nth-child(10)
    {
        text-align:center;
        width:8%;
    }

    .food-action
    {
        align-items:center;
        display:flex;
        justify-content:center;
    }

    .food-action .btn
    {
        border-radius:7px;
        font-size:11px;
        font-weight:700;
        min-width:0;
        padding:6px 8px;
    }

    .food-action .btn-danger
    {
        background:#dc3545;
        border-color:#dc3545;
    }

    .transaction-time
    {
        color:#dbeafe;
        display:block;
        font-size:10px;
        font-weight:700;
        line-height:1.35;
    }

    .transaction-time small
    {
        color:#94a3b8;
        display:block;
        font-size:9px;
        font-weight:800;
        margin-bottom:2px;
        text-transform:uppercase;
    }

    .mic-swal-popup
    {
        border-radius:20px;
        box-shadow:0 18px 50px rgba(43,58,85,.16);
        font-family:inherit;
    }

    .mic-swal-title
    {
        color:#3d4149;
        font-size:21px;
        font-weight:900;
    }

    .mic-swal-confirm
    {
        background:#f56060 !important;
        border-radius:9px !important;
        box-shadow:0 7px 14px rgba(245,96,96,.22) !important;
        color:#fff !important;
        font-weight:800 !important;
        min-height:44px;
        padding:10px 22px !important;
    }

    .mic-swal-cancel
    {
        background:#eeeeef !important;
        border-radius:9px !important;
        color:#858992 !important;
        font-weight:800 !important;
        min-height:44px;
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
          <div class="container-fluid foods-frame">

          <h1>All Foods</h1>

          @if(session()->has('message'))
            <div class="alert alert-success">
                {{ session()->get('message') }}
            </div>
          @endif

          <div class="foods-panel">

          <table class="foods-table">
              <tr>
                <th>Food Title</th>
                <th>Details</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Added At</th>
                <th>Updated At</th>
                <th>Image</th>
                <th>Delete</th>
                <th>Update</th>
              </tr>

              @foreach ($data as $data)
                @php
                    $mappedImage = $adminMenuImages[\Illuminate\Support\Str::slug($data->title)] ?? null;
                    $foodImage = $mappedImage ? asset('assets/imgs/' . $mappedImage) : asset('food_img/' . $data->image);
                @endphp

              <tr>
                <td>{{ $data->title }}</td>
                <td>{{ $data->detail }}</td>
                <td>{{ $data->category ?? 'All menu' }}</td>
                <td>&#8369;{{ $data->price }}</td>
                <td>{{ $data->stock }}</td>
                <td>
                  <span class="transaction-time">
                    <small>Added</small>
                    {{ $data->created_at ? $data->created_at->format('M d, Y g:i A') : 'N/A' }}
                  </span>
                </td>
                <td>
                  <span class="transaction-time">
                    <small>Latest</small>
                    {{ $data->updated_at ? $data->updated_at->format('M d, Y g:i A') : 'N/A' }}
                  </span>
                </td>
                <td>
                    <img class="food-thumb" src="{{ $foodImage }}" alt="{{ $data->title }}">
                </td>
                <td>
                  <div class="food-action">
                    <form method="POST" action="{{ url('delete_food', $data->id) }}" class="js-confirm-action" data-title="Delete this food?" data-text="This food will be removed from the menu." data-confirm="Yes, delete it">@csrf @method('DELETE')<button class="btn btn-danger" type="submit">Delete</button></form>
                  </div>
                </td>
                <td>
                  <div class="food-action">
                    <a class="btn btn-warning" href="{{url('update_food', $data->id) }}">Update</a>
                  </div>
                </td>
              </tr>

              @endforeach
          </table>

          </div>

        </div>
      </div>
    </div>
    @include('admin.js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form.js-confirm-action').forEach(function (form) {
          form.addEventListener('submit', function (event) {
            event.preventDefault();

            const title = form.dataset.title || 'Are you sure?';
            const text = form.dataset.text || 'This action cannot be undone.';
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
              icon: 'warning',
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
      });
    </script>
  </body>
</html>
