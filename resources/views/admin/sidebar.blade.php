 <div class="d-flex align-items-stretch">
      <!-- Sidebar Navigation-->
      <nav id="sidebar">
        <!-- Sidebar Header-->
        <div class="sidebar-header d-flex align-items-center">
          <div class="avatar"><img src="admin/img/avatar-6.jpg" alt="..." class="img-fluid rounded-circle"></div>
          <div class="title">
            <h1 class="h5">{{ str(Auth::user()->name ?? 'Mi Cusina')->title() }}</h1>
            <p>
              {{ (Auth::user()->staff_role ?? Auth::user()->usertype) === 'admin' ? 'Administrator' : str(Auth::user()->staff_role ?? Auth::user()->usertype ?? 'User')->title() }}
            </p>
          </div>
        </div>
        <!-- Sidebar Navidation Menus--><span class="heading">Main</span>
        <ul class="list-unstyled">
                <li class="{{ request()->is('dashboard') ? 'active' : '' }}"><a href="{{ route('dashboard') }}"> <i class="icon-home"></i>Dashboard </a></li>
                @if(Auth::check() && Auth::user()->usertype == 'admin')
                <li class="food-menu {{ request()->is('view_food') || request()->is('add_food') || request()->is('inventory') ? 'active' : '' }}">
                  <a href="#foodMenu" data-toggle="collapse" aria-expanded="false">
                    <i class="icon-windows"></i>Food
                  </a>
                  <ul id="foodMenu" class="collapse list-unstyled">
                    <li class="{{ request()->is('view_food') ? 'active' : '' }}">
                      <a href="{{ url('view_food') }}">View Food</a>
                    </li>
                    <li class="{{ request()->is('add_food') ? 'active' : '' }}">
                      <a href="{{ url('add_food') }}">Add Food</a>
                    </li>
                    <li class="{{ request()->is('inventory') ? 'active' : '' }}">
                      <a href="{{ url('inventory') }}">Inventory</a>
                    </li>
                  </ul>
                </li>
                @endif
                <li class="{{ request()->is('orders') ? 'active' : '' }}">
                  <a href="{{ url('orders') }}"> <i class="icon-logout"></i>Orders @if($headerUnreadPendingOrderCount > 0)<span class="admin-sidebar-count" style="background:#dc2626;color:#fff;font-weight:900;" aria-label="{{ $headerUnreadPendingOrderCount }} new orders awaiting fulfillment">{{ $headerUnreadPendingOrderCount }}</span>@endif</a>
                </li>

                @if(Auth::check() && Auth::user()->usertype == 'admin')
                <li class="{{ request()->is('sales-report') || request()->is('transaction-history') ? 'active' : '' }}">
                  <a href="#salesMenu" data-toggle="collapse" aria-expanded="{{ request()->is('sales-report') || request()->is('transaction-history') ? 'true' : 'false' }}"><i class="fa fa-money" style="margin-right:10px; width:20px;"></i>Sales</a>
                  <ul id="salesMenu" class="collapse list-unstyled {{ request()->is('sales-report') || request()->is('transaction-history') ? 'show' : '' }}">
                    <li class="{{ request()->is('sales-report') ? 'active' : '' }}"><a href="{{ route('admin.sales-report') }}">Sales Report</a></li>
                    <li class="{{ request()->is('transaction-history') ? 'active' : '' }}"><a href="{{ route('admin.transaction-history') }}">Transaction History</a></li>
                  </ul>
                </li>
                @endif

                @if(Auth::check() && Auth::user()->staff_role !== 'rider')
                  <li class="{{ request()->is('riders') ? 'active' : '' }}">
                    <a href="{{ url('riders') }}"> <i class="fa fa-motorcycle"></i>Riders</a>
                  </li>

                  <li class="{{ request()->is('reservations') ? 'active' : '' }}">
                    <a href="{{ url('reservations') }}"> <i class="icon-logout"></i>Book a Table @if($headerPendingReservationCount > 0)<span class="admin-sidebar-count" aria-label="{{ $headerPendingReservationCount }} reservations awaiting approval">{{ $headerPendingReservationCount }}</span>@endif</a>
                  </li>
                @endif
                @if(Auth::check() && Auth::user()->usertype == 'admin')
                <li class="{{ request()->is('users') ? 'active' : '' }}">
                  <a href="{{ url('users') }}"> <i class="icon-user"></i>Users @if($headerNewUserCount > 0)<span class="admin-sidebar-count" aria-label="{{ $headerNewUserCount }} customers registered today">{{ $headerNewUserCount }}</span>@endif</a>
                </li>

                <li class="{{ request()->is('add_staff') ? 'active' : '' }}">
                  <a href="{{ url('add_staff') }}"> <i class="icon-user-1"></i>Create Staff</a>
                </li>
                @endif
        </ul>
      </nav>
    </div>
