<nav class="sb-sidenav accordion sb-sidenav-dark bg-success" id="sidenavAccordion">
    <div class="sb-sidenav-menu">
        <div class="nav">
            <br>
            <!-- Dashboard Link -->
            <a class="nav-link text-white {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                href="{{ route('admin.dashboard') }}">
                <div class="sb-nav-link-icon text-white">
                    <!-- Dashboard Icon SVG -->
                    <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="grid-2" role="img"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-grid-2 fa-lg">
                        <path fill="currentColor"
                            d="M224 80c0-26.5-21.5-48-48-48L80 32C53.5 32 32 53.5 32 80l0 96c0 26.5 21.5 48 48 48l96 0c26.5 0 48-21.5 48-48l0-96zm0 256c0-26.5-21.5-48-48-48l-96 0c-26.5 0-48 21.5-48 48l0 96c0 26.5 21.5 48 48 48l96 0c26.5 0 48-21.5 48-48l0-96zM288 80l0 96c0 26.5 21.5 48 48 48l96 0c26.5 0 48-21.5 48-48l0-96c0-26.5-21.5-48-48-48l-96 0c-26.5 0-48 21.5-48 48zM480 336c0-26.5-21.5-48-48-48l-96 0c-26.5 0-48 21.5-48 48l0 96c0 26.5 21.5 48 48 48l96 0c26.5 0 48-21.5 48-48l0-96z"
                            class=""></path>
                    </svg>
                </div>
                Dashboard
            </a>

            <!-- Reports Dropdown -->
            <a class="nav-link collapsed text-white {{ request()->routeIs('admin.damage_item_report') || request()->routeIs('admin.total_item_report') || request()->routeIs('admin.sales_report') || request()->routeIs('admin.void_report') || request()->routeIs('admin.return_item_report') || request()->routeIs('admin.toga_fines_report') ? 'active' : '' }}"
                href="#" data-bs-toggle="collapse" data-bs-target="#collapseReports"
                aria-expanded="{{ request()->routeIs('admin.damage_item_report') || request()->routeIs('admin.total_item_report') || request()->routeIs('admin.sales_report') || request()->routeIs('admin.return_item_report') || request()->routeIs('admin.toga_fines_report') ? 'true' : 'false' }}"
                aria-controls="collapseReports">
                <div class="sb-nav-link-icon text-white"><i class="fas fa-chart-line"></i></div>
                Reports
                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down text-white"></i></div>
            </a>
            <div class="collapse {{ request()->routeIs('admin.damage_item_report') || request()->routeIs('admin.total_item_report') || request()->routeIs('admin.sales_report') || request()->routeIs('admin.void_report') || request()->routeIs('admin.return_item_report') || request()->routeIs('admin.toga_fines_report') ? 'show' : '' }}"
                id="collapseReports" aria-labelledby="headingReports" data-bs-parent="#sidenavAccordion">
                <nav class="sb-sidenav-menu-nested">
                    <!-- All Items Report -->
                    <a class="nav-link text-white {{ request()->routeIs('admin.total_item_report') ? 'active' : '' }}"
                        href="{{ route('admin.total_item_report') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-clipboard-list"></i></div>
                        All Items
                    </a>
                    <!-- All Items Report -->
                    <a class="nav-link text-white {{ request()->routeIs('admin.sales_report') ? 'active' : '' }}"
                        href="{{ route('admin.sales_report') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-dollar-sign"></i></div>
                        Sales
                    </a>
                    <!-- Damage Items Report -->
                    <a class="nav-link text-white {{ request()->routeIs('admin.damage_item_report') ? 'active' : '' }}"
                        href="{{ route('admin.damage_item_report') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-exclamation-triangle"></i></div>
                        Damage Items
                    </a>
                    <!-- Void Logs Report -->
                    <a class="nav-link text-white {{ request()->routeIs('admin.void_report') ? 'active' : '' }}"
                        href="{{ route('admin.void_report') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-ban"></i></div>
                        Void Logs
                    </a>
                    <!-- Returned Items Report -->
                    <a class="nav-link text-white {{ request()->routeIs('admin.return_item_report') ? 'active' : '' }}"
                        href="{{ route('admin.return_item_report') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-undo"></i></div>
                        Returned Items
                    </a>
                    <!-- Toga Fines Report -->
                    <a class="nav-link text-white {{ request()->routeIs('admin.toga_fines_report') ? 'active' : '' }}" href="{{ route('admin.toga_fines_report') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-coins"></i></div>
                        Toga Fines
                    </a>
                </nav>
            </div>

            <!-- Toga & Fines Link -->
            <a class="nav-link text-white {{ request()->routeIs('admin.toga_fines') ? 'active' : '' }}"
                href="{{ route('admin.toga_fines') }}">
                <div class="sb-nav-link-icon text-white"><i class="fas fa-graduation-cap"></i></div>
                Toga & Fines
            </a>

            <!-- User Profile Link -->
            <a class="nav-link text-white {{ request()->routeIs('admin.userprofile') ? 'active' : '' }}"
                href="{{ route('admin.userprofile') }}">
                <div class="sb-nav-link-icon text-white"><i class="fas fa-user"></i></div>
                User Profile
            </a>

            <!-- Add more admin links with respective icons here -->
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
