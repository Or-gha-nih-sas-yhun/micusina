<header class="header">
  <nav class="navbar navbar-expand-lg">
    <div class="search-panel">
      <div class="search-inner d-flex align-items-center justify-content-center">
        <div class="close-btn">Close <i class="fa fa-close"></i></div>
        <form id="searchForm" action="#">
          <div class="form-group">
            <input type="search" name="search" placeholder="What are you searching for...">
            <button type="submit" class="submit">Search</button>
          </div>
        </form>
      </div>
    </div>
    <div class="container-fluid d-flex align-items-center">
      <a href="{{ route('dashboard') }}" class="admin-brand" aria-label="Mi Cusina admin dashboard">
        <img src="{{ asset('assets/imgs/mi-cusina-transparent.png') }}" alt="Mi Cusina">
        <span>Mi Cusina</span>
      </a>

      <div class="admin-actions">
      @if(Auth::check() && Auth::user()->usertype === 'admin')
      <div class="admin-notifications dropdown">
        <button class="admin-notification-trigger" type="button" id="adminNotificationMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Admin notifications" title="Notifications">
          <i class="fa fa-bell" aria-hidden="true"></i>
          @if($headerNotificationCount > 0)<span id="adminNotificationCount" class="admin-notification-count" aria-label="{{ $headerNotificationCount }} notifications">{{ $headerNotificationCount }}</span>@endif
        </button>
        <div class="dropdown-menu dropdown-menu-right admin-user-menu admin-notification-menu" aria-labelledby="adminNotificationMenu">
          <div class="admin-notification-title">Notifications</div><div class="dropdown-divider"></div>
          @if($headerNewUserCount > 0)
            <a class="admin-notification-item" href="{{ url('users') }}"><i class="fa fa-user-plus" aria-hidden="true"></i><span><strong>{{ $headerNewUserCount }} new {{ Str::plural('customer', $headerNewUserCount) }}</strong><small>Registered today</small></span></a>
          @endif
          @if($headerPendingOrderCount > 0)
            <a class="admin-notification-item" href="{{ url('orders') }}"><i class="fa fa-shopping-bag" aria-hidden="true"></i><span><strong>{{ $headerPendingOrderCount }} {{ Str::plural('order', $headerPendingOrderCount) }} need attention</strong><small>In progress and awaiting fulfillment</small></span></a>
          @endif
          @if($headerPendingReservationCount > 0)
            <a class="admin-notification-item" href="{{ url('reservations') }}"><i class="fa fa-calendar" aria-hidden="true"></i><span><strong>{{ $headerPendingReservationCount }} pending {{ Str::plural('reservation', $headerPendingReservationCount) }}</strong><small>Paid and waiting for approval</small></span></a>
          @endif
          @if($headerLowStockFoods->isNotEmpty() && ($headerNewUserCount > 0 || $headerPendingOrderCount > 0 || $headerPendingReservationCount > 0))<div class="dropdown-divider"></div>@endif
          @if($headerLowStockFoods->isNotEmpty())<div class="admin-notification-title">Low-stock products</div>@endif
          @forelse($headerLowStockFoods as $food)
            <a class="admin-notification-item" href="{{ url('inventory') }}"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i><span><strong>{{ $food->title }}</strong><small>Current stocks: {{ max(0, $food->stock) }}</small></span></a>
          @empty
            @if($headerNotificationCount === 0)<div class="admin-notification-empty">No new notifications.</div>@endif
          @endforelse
        </div>
      </div>
      @endif
      <div class="admin-user dropdown">
        <button class="admin-user-trigger dropdown-toggle" type="button" id="adminUserMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <img src="{{ Auth::user()->profile_photo_path ? Auth::user()->profile_photo_url : asset('admin/img/avatar-6.jpg') }}" alt="{{ Auth::user()->name ?? 'Admin' }}">
        </button>

        <div class="dropdown-menu dropdown-menu-right admin-user-menu" aria-labelledby="adminUserMenu">
          <div class="admin-user-info">
            <strong>{{ Auth::user()->name ?? 'Mi Cusina' }}</strong>
            <span>Logged in as {{ ucfirst(Auth::user()->staff_role ?? Auth::user()->usertype ?? 'Admin') }}</span>
          </div>
          <div class="dropdown-divider"></div>
          <form class="admin-photo-form" method="POST" action="{{ route('admin.profile-photo.update') }}" enctype="multipart/form-data">
            @csrf
            <label class="admin-photo-label" for="adminProfilePhoto">
              <i class="fa fa-camera"></i>
              Upload Profile Picture
            </label>
            <input id="adminProfilePhoto" type="file" name="photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" hidden>
            @error('photo')<small class="admin-photo-error">{{ $message }}</small>@enderror
          </form>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item home-action" href="{{ route('dashboard') }}">Home</a>
          <form method="POST" action="{{ route('logout') }}" data-logout-form>
            @csrf
            <button class="dropdown-item logout-action" type="button" data-logout-trigger>Log Out</button>
          </form>
        </div>
      </div>
      </div>
    </div>
  </nav>
</header>

@include('components.logout-confirmation')

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var photoInput = document.getElementById('adminProfilePhoto');
    if (photoInput) {
      photoInput.addEventListener('change', function () {
        if (photoInput.files.length) photoInput.form.submit();
      });
    }

    var notificationButton = document.getElementById('adminNotificationMenu');
    if (notificationButton) {
      notificationButton.addEventListener('click', function () {
        var count = document.getElementById('adminNotificationCount');
        if (!count) return;

        count.remove();
        fetch('{{ route('admin.notifications.read') }}', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
          }
        });
      }, { once: true });
    }
  });
</script>
