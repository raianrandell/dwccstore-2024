<nav class="sb-sidenav accordion sb-sidenav-dark bg-success" id="sidenavAccordion">
    <div class="sb-sidenav-menu">
        <div class="nav">
            <br>
            <a class="nav-link text-white {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}" href="{{ route('superadmin.dashboard')}}">
                <div class="sb-nav-link-icon text-white"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="grid-2" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-grid-2 fa-lg"><path fill="currentColor" d="M224 80c0-26.5-21.5-48-48-48L80 32C53.5 32 32 53.5 32 80l0 96c0 26.5 21.5 48 48 48l96 0c26.5 0 48-21.5 48-48l0-96zm0 256c0-26.5-21.5-48-48-48l-96 0c-26.5 0-48 21.5-48 48l0 96c0 26.5 21.5 48 48 48l96 0c26.5 0 48-21.5 48-48l0-96zM288 80l0 96c0 26.5 21.5 48 48 48l96 0c26.5 0 48-21.5 48-48l0-96c0-26.5-21.5-48-48-48l-96 0c-26.5 0-48 21.5-48 48zM480 336c0-26.5-21.5-48-48-48l-96 0c-26.5 0-48 21.5-48 48l0 96c0 26.5 21.5 48 48 48l96 0c26.5 0 48-21.5 48-48l0-96z" class=""></path></svg></div>
                Dashboard
            </a>
            <a class="nav-link text-white {{ request()->routeIs('superadmin.usermanagement') ? 'active' : '' }}" href="{{ route('superadmin.usermanagement')}}">
                <div class="sb-nav-link-icon text-white"><i class="fa-solid fas fa-users"></i></div>
                User Management
            </a>
            
            <div class="sb-sidenav-menu-heading"></div>
        </div>
    </div>
    <div class="sb-sidenav-footer bg-transparent">
        <div class="small text-white">Logged in as:</div>
        <div class="text-white">{{ $loggedInUser->full_name }}</div>
    </div>
</nav>


