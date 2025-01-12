<nav class="sb-sidenav accordion sb-sidenav-dark bg-success" id="sidenavAccordion">
    <div class="sb-sidenav-menu">
        <div class="nav"><br>

            <!-- Charge Transaction -->
            <a class="nav-link text-white {{ request()->routeIs('accounting.chargeTransaction') ? 'active' : '' }}" href="{{ route('accounting.chargeTransaction') }}">
                <div class="sb-nav-link-icon text-white"><i class="fas fa-credit-card"></i></div>
                Charge Transaction
            </a>

            <!-- Reports Dropdown -->
            <a class="nav-link collapsed text-white 
                {{ request()->routeIs('accounting.total_item_report') || request()->routeIs('accounting.sales_report') || request()->routeIs('accounting.damage_items') || request()->routeIs('accounting.return_item_report') || request()->routeIs('accounting.void_report') ? 'active' : '' }}" 
                href="#" data-bs-toggle="collapse" 
                data-bs-target="#collapseReports" 
                aria-expanded="{{ request()->routeIs('accounting.total_item_report') || request()->routeIs('accounting.sales_report') || request()->routeIs('accounting.damage_items') || request()->routeIs('accounting.return_item_report') || request()->routeIs('accounting.void_report') ? 'true' : 'false' }}" 
                aria-controls="collapseReports">
                <div class="sb-nav-link-icon text-white"><i class="fas fa-chart-line"></i></div>
                Reports
                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down text-white"></i></div>
            </a>
            <div class="collapse {{ request()->routeIs('accounting.total_item_report') || request()->routeIs('accounting.sales_report') || request()->routeIs('accounting.damage_items') || request()->routeIs('accounting.return_item_report') || request()->routeIs('accounting.void_report') ? 'show' : '' }}" id="collapseReports" aria-labelledby="headingReports" data-bs-parent="#sidenavAccordion">
                <nav class="sb-sidenav-menu-nested">
                    <!-- Total Items -->
                    <a class="nav-link text-white {{ request()->routeIs('accounting.total_item_report') ? 'active' : '' }}" href="{{ route('accounting.total_item_report') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-clipboard-list"></i></div>
                        All Items
                    </a>
                    <!-- Sales Report -->
                    <a class="nav-link text-white {{ request()->routeIs('accounting.sales_report') ? 'active' : '' }}" href="{{ route('accounting.sales_report') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-dollar-sign"></i></div>
                        Sales
                    </a>
                    <!-- Returned Items -->
                    <a class="nav-link text-white {{ request()->routeIs('accounting.return_item_report') ? 'active' : '' }}" href="{{ route('accounting.return_item_report') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-undo-alt"></i></div>
                        Returned Items
                    </a>
                    <!-- Void Logs -->
                    <a class="nav-link text-white {{ request()->routeIs('accounting.void_report') ? 'active' : '' }}" href="{{ route('accounting.void_report') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-ban"></i></div>
                        Void Logs 
                    </a>
                    <!-- Damage Items -->
                    <a class="nav-link text-white {{ request()->routeIs('accounting.damage_items') ? 'active' : '' }}" href="{{ route('accounting.damage_items') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-exclamation-triangle"></i></div>
                        Damage Items
                    </a>
                </nav>
            </div>
            
            <!-- User Profile -->
            <a class="nav-link text-white {{ request()->routeIs('accounting.userprofile') ? 'active' : '' }}" href="{{ route('accounting.userprofile') }}">
                <div class="sb-nav-link-icon text-white"><i class="fas fa-user"></i></div>
                User Profile
            </a>
        </div>
    </div>

    <!-- Footer -->
    <div class="sb-sidenav-footer bg-transparent text-white">
        <div class="small">Logged in as:</div>
        @auth('accounting')
            {{ Auth::guard('accounting')->user()->full_name }}
        @else
            Guest
        @endauth
    </div>
</nav>
