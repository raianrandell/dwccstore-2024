<nav class="sb-sidenav accordion sb-sidenav-dark bg-success" id="sidenavAccordion">
    <div class="sb-sidenav-menu">
        <div class="nav">
            <br>
            <a class="nav-link text-white {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                <div class="sb-nav-link-icon text-white"><i class="fas fa-tachometer-alt"></i></div>
                Dashboard
            </a>
            <a class="nav-link text-white {{ request()->routeIs('admin.reports') ? 'active' : '' }}" href="{{ route('admin.reports') }}">
                <div class="sb-nav-link-icon text-white"><i class="fa-solid fa-file"></i></div>
                Reports
            </a>
            <a class="nav-link text-white {{ request()->routeIs('admin.toga_fines') ? 'active' : '' }}" href="{{ route('admin.toga_fines') }}">
                <div class="sb-nav-link-icon text-white"><i class="fa-solid fa-graduation-cap"></i></div>
                Toga & Fines
            </a>
            <a class="nav-link text-white {{ request()->routeIs('admin.userprofile') ? 'active' : '' }}" href="{{ route('admin.userprofile') }}">
                <div class="sb-nav-link-icon text-white"><i class="fas fa-user"></i></div>
                User Profile
            </a>
            <!-- Other admin links -->
        </div>
    </div>
    <div class="sb-sidenav-footer bg-transparent text-white">
        <div class="small">Logged in as:</div>
        @auth('admin')
            {{ Auth::guard('admin')->user()->full_name }}
        @else
            Guest
        @endauth
    </div>
</nav>
