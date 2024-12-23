<nav class="sb-sidenav accordion sb-sidenav-dark bg-success" id="sidenavAccordion">
    <div class="sb-sidenav-menu">
        <div class="nav">
            <br>
            <a class="nav-link text-white {{ request()->routeIs('inventory.dashboard') ? 'active' : '' }}" href="{{ route('inventory.dashboard') }}">
                <div class="sb-nav-link-icon text-white"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="grid-2" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-grid-2 fa-lg"><path fill="currentColor" d="M224 80c0-26.5-21.5-48-48-48L80 32C53.5 32 32 53.5 32 80l0 96c0 26.5 21.5 48 48 48l96 0c26.5 0 48-21.5 48-48l0-96zm0 256c0-26.5-21.5-48-48-48l-96 0c-26.5 0-48 21.5-48 48l0 96c0 26.5 21.5 48 48 48l96 0c26.5 0 48-21.5 48-48l0-96zM288 80l0 96c0 26.5 21.5 48 48 48l96 0c26.5 0 48-21.5 48-48l0-96c0-26.5-21.5-48-48-48l-96 0c-26.5 0-48 21.5-48 48zM480 336c0-26.5-21.5-48-48-48l-96 0c-26.5 0-48 21.5-48 48l0 96c0 26.5 21.5 48 48 48l96 0c26.5 0 48-21.5 48-48l0-96z" class=""></path></svg></div>
                Dashboard
            </a>
            <a class="nav-link text-white {{ request()->routeIs('inventory.sectionmanagement') ? 'active' : '' }}" href="{{ route('inventory.sectionmanagement') }}">
                <div class="sb-nav-link-icon text-white"><i class="fas fa-layer-group"></i></div>
                Section Management
            </a>
            <a class="nav-link text-white {{ request()->routeIs('inventory.categorymanagement') ? 'active' : '' }}" href="{{ route('inventory.categorymanagement') }}">
                <div class="sb-nav-link-icon text-white"><i class="fas fa-list"></i></div>
                Category Management
            </a>
            <a class="nav-link text-white {{ request()->routeIs('inventory.stockmanagement') ? 'active' : '' }}" href="{{ route('inventory.stockmanagement') }}">
                <div class="sb-nav-link-icon text-white"><i class="fas fa-cubes"></i></div>
                Stock Management
            </a>
            <a class="nav-link text-white {{ request()->routeIs('inventory.damagetransaction') ? 'active' : '' }}" href="{{ route('inventory.damagetransaction') }}">
                <div class="sb-nav-link-icon text-white"><i class="fas fa-exclamation-circle"></i></div>
                Damage Transactions
            </a>
            <a class="nav-link text-white {{ request()->routeIs('inventory.toga_renting') ? 'active' : '' }}" href="{{ route('inventory.toga_renting') }}">
                <div class="sb-nav-link-icon text-white"><i class="fa-solid fa-graduation-cap"></i></div>
                Toga Renting
            </a>
            <a class="nav-link text-white {{ request()->routeIs('inventory.services') ? 'active' : '' }}" href="{{ route('inventory.services') }}">
                <div class="sb-nav-link-icon text-white"><i class="fa-solid fa-hand-holding-medical"></i></div>
                Services
            </a>
            
            <!-- Reports Dropdown -->
            <a class="nav-link collapsed text-white {{ request()->routeIs('inventory.damage_item_report') || request()->routeIs('inventory.total_item_report')  || request()->routeIs('inventory.low_stock_item_report') || request()->routeIs('inventory.expired_item_report') ? 'active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#collapseReports" aria-expanded="{{ request()->routeIs('inventory.damage_item_report') || request()->routeIs('inventory.totalitems') || request()->routeIs('inventory.expired_item_report') ? 'true' : 'false' }}" aria-controls="collapseReports">
                <div class="sb-nav-link-icon text-white"><i class="fas fa-chart-line"></i></div>
                Reports
                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down text-white"></i></div>
            </a>
            <div class="collapse {{ request()->routeIs('inventory.damage_item_report') || request()->routeIs('inventory.total_item_report') || request()->routeIs('inventory.low_stock_item_report') || request()->routeIs('inventory.expired_item_report') ? 'show' : '' }}" id="collapseReports" aria-labelledby="headingReports" data-bs-parent="#sidenavAccordion">
                <nav class="sb-sidenav-menu-nested">
                    <a class="nav-link text-white {{ request()->routeIs('inventory.total_item_report') ? 'active' : '' }}" href="{{ route('inventory.total_item_report') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-clipboard-list"></i></div>
                        All Items
                    </a>
                    <a class="nav-link text-white {{ request()->routeIs('inventory.damage_item_report') ? 'active' : '' }}" href="{{ route('inventory.damage_item_report') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-exclamation-triangle"></i></div>
                        Damage Items
                    </a>
                    <a class="nav-link text-white {{ request()->routeIs('inventory.low_stock_item_report') ? 'active' : '' }}" href="{{ route('inventory.low_stock_item_report') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-clipboard-list"></i></div>
                        Low Stock Items
                    </a>
                    <a class="nav-link text-white {{ request()->routeIs('inventory.expired_item_report') ? 'active' : '' }}" href="{{ route('inventory.expired_item_report') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-clipboard-list"></i></div>
                        Expired Items
                    </a>
                </nav>
            </div>
            
            <a class="nav-link text-white {{ request()->routeIs('inventory.userprofile') ? 'active' : '' }}" href="{{ route('inventory.userprofile') }}">
                <div class="sb-nav-link-icon text-white"><i class="fas fa-user"></i></div>
                User Profile
            </a>
            <!-- Other inventory links -->
        </div>
    </div>
    <div class="sb-sidenav-footer bg-transparent text-white">
        <div class="small">Logged in as:</div>
        @auth
            {{ Auth::user()->full_name }}
        @else
            Guest
        @endauth
    </div>
    
</nav>
