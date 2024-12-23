{{-- resources/views/layout/cashier.blade.php --}}

<nav class="sb-sidenav accordion sb-sidenav-dark bg-success" id="sidenavAccordion">
    <div class="sb-sidenav-menu">
        <div class="nav"><br>
            <a class="nav-link text-white {{ request()->routeIs('cashier.cashier_dashboard') ? 'active' : '' }}" href="{{ route('cashier.cashier_dashboard') }}">
                <div class="sb-nav-link-icon text-white">
                    <!-- Dashboard Icon SVG -->
                    <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="grid-2" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-grid-2 fa-lg">
                        <path fill="currentColor" d="M224 80c0-26.5-21.5-48-48-48L80 32C53.5 32 32 53.5 32 80l0 96c0 26.5 21.5 48 48 48l96 0c26.5 0 48-21.5 48-48l0-96zm0 256c0-26.5-21.5-48-48-48l-96 0c-26.5 0-48 21.5-48 48l0 96c0 26.5 21.5 48 48 48l96 0c26.5 0 48-21.5 48-48l0-96zM288 80l0 96c0 26.5 21.5 48 48 48l96 0c26.5 0 48-21.5 48-48l0-96c0-26.5-21.5-48-48-48l-96 0c-26.5 0-48 21.5-48 48zM480 336c0-26.5-21.5-48-48-48l-96 0c-26.5 0-48 21.5-48 48l0 96c0 26.5 21.5 48 48 48l96 0c26.5 0 48-21.5 48-48l0-96z" class=""></path>
                    </svg>
                </div>
                Dashboard
            </a>
            <!-- Transactions Dropdown -->
            <a class="nav-link collapsed text-white 
                {{ request()->routeIs('cashier.sales') ||  request()->routeIs('cashier.services') || request()->routeIs('cashier.sales_history') || request()->routeIs('cashier.services_history') || request()->routeIs('cashier.void_records') || request()->routeIs('cashier.credit') || request()->routeIs('cashier.fines') || request()->routeIs('cashier.fines_history') || request()->routeIs('cashier.returns') ? 'active' : '' }}" 
                href="#" data-bs-toggle="collapse" 
                data-bs-target="#collapseTransactions" 
                aria-expanded="{{ request()->routeIs('cashier.sales') ||  request()->routeIs('cashier.services') || request()->routeIs('cashier.sales_history') || request()->routeIs('cashier.services_history')||request()->routeIs('cashier.void_records') || request()->routeIs('cashier.credit') || request()->routeIs('cashier.fines') || request()->routeIs('cashier.fines_history') || request()->routeIs('cashier.returns') ? 'true' : 'false' }}" 
                aria-controls="collapseTransactions">
                <div class="sb-nav-link-icon text-white"><i class="fas fa-chart-line"></i></div>
                Transactions
                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down text-white"></i></div>
            </a>
            <div class="collapse {{ request()->routeIs('cashier.sales') || request()->routeIs('cashier.services') || request()->routeIs('cashier.sales_history') || request()->routeIs('cashier.services_history') || request()->routeIs('cashier.void_records') || request()->routeIs('cashier.credit') || request()->routeIs('cashier.fines') || request()->routeIs('cashier.fines_history') || request()->routeIs('cashier.returns') ? 'show' : '' }}" id="collapseTransactions" aria-labelledby="headingTransactions" data-bs-parent="#sidenavAccordion">
                <nav class="sb-sidenav-menu-nested">
                    <a class="nav-link text-white {{ request()->routeIs('cashier.sales') ? 'active' : '' }}" href="{{ route('cashier.sales') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-cash-register"></i></div>
                        Sales
                    </a>
                    <!-- Services Link -->
                    <a class="nav-link text-white {{ request()->routeIs('cashier.services') ? 'active' : '' }}" href="{{ route('cashier.services') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-concierge-bell"></i></div>
                        Services
                    </a>
                    <a class="nav-link text-white {{ request()->routeIs('cashier.sales_history') ? 'active' : '' }}" href="{{ route('cashier.sales_history') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-history"></i></div>
                        Sales History
                    </a>
                    <!-- New Services History Link -->
                    <a class="nav-link text-white {{ request()->routeIs('cashier.services_history') ? 'active' : '' }}" href="{{ route('cashier.services_history') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-concierge-bell"></i></div>
                        Services History
                    </a>
                    <a class="nav-link text-white {{ request()->routeIs('cashier.void_records') ? 'active' : '' }}" href="{{ route('cashier.void_records') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-ban"></i></div>
                        Void Records
                    </a>
                    <a class="nav-link text-white {{ request()->routeIs('cashier.credit') ? 'active' : '' }}" href="{{ route('cashier.credit') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-credit-card"></i></div>
                        Credit
                    </a>
                    <a class="nav-link text-white {{ request()->routeIs('cashier.fines') ? 'active' : '' }}" href="{{ route('cashier.fines') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-coins"></i></div>
                        Fines
                    </a>
                    <a class="nav-link text-white {{ request()->routeIs('cashier.fines_history') ? 'active' : '' }}" href="{{ route('cashier.fines_history') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-book"></i></div>
                        Fines History
                    </a>
                    <a class="nav-link text-white {{ request()->routeIs('cashier.returns') ? 'active' : '' }}" href="{{ route('cashier.returns') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-undo-alt"></i></div>
                        Returns
                    </a>
                </nav>
            </div>

            <!-- Reports Dropdown -->
            <a class="nav-link collapsed text-white
                {{ request()->routeIs('cashier.sales_report') || request()->routeIs('cashier.void_report') || request()->routeIs('cashier.return_item_report') ? 'active' : '' }}" 
                href="#" data-bs-toggle="collapse" 
                data-bs-target="#collapseReports" 
                aria-expanded="{{ request()->routeIs('cashier.sales_report') || request()->routeIs('cashier.void_report') || request()->routeIs('cashier.return_item_report') ? 'true' : 'false' }}" 
                aria-controls="collapseReports">
                <div class="sb-nav-link-icon text-white"><i class="fa-regular fa-file"></i></div>
                Reports
                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down text-white"></i></div>
            </a>
            <div class="collapse {{ request()->routeIs('cashier.sales_report') || request()->routeIs('cashier.void_report') || request()->routeIs('cashier.return_item_report') ? 'show' : '' }}" id="collapseReports" aria-labelledby="headingReports" data-bs-parent="#sidenavAccordion">
                <nav class="sb-sidenav-menu-nested">
                    <a class="nav-link text-white {{ request()->routeIs('cashier.sales_report') ? 'active' : '' }}" href="{{ route('cashier.sales_report') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-cash-register"></i></div>
                        Sales Report
                    </a>
                    <a class="nav-link text-white {{ request()->routeIs('cashier.void_report') ? 'active' : '' }}" href="{{ route('cashier.void_report') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-history"></i></div>
                        Void Logs Report
                    </a>
                    <a class="nav-link text-white {{ request()->routeIs('cashier.return_item_report') ? 'active' : '' }}" href="{{ route('cashier.return_item_report') }}">
                        <div class="sb-nav-link-icon text-white"><i class="fas fa-undo-alt"></i></div>
                        Returned Items
                    </a>
                </nav>
            </div>
            
            <a class="nav-link text-white {{ request()->routeIs('cashier.userprofile') ? 'active' : '' }}" href="{{ route('cashier.userprofile') }}">
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
