<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inventory Dashboard')</title>
    <link rel="icon" href="{{ asset('images/dwcclogo.ico') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="{{ asset('superadmin/css/styles.css') }}" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Include jQuery before Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Include Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Include Select2 Bootstrap 5 Theme CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-5-theme/1.1.1/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
</head>
<body class="sb-nav-fixed">

    @include('layout.partials.inventory.navbar_inventory') <!-- Update navbar for inventory -->

    <div id="layoutSidenav">

        <div id="layoutSidenav_nav">
            @include('layout.partials.inventory.sidebar_inventory') <!-- Update sidebar for inventory -->
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    @yield('content')
                </div>
            </main>
            @include('layout.partials.footer')
        </div>
    </div>
    <script>
        document.addEventListener('contextmenu', function (e) {
          e.preventDefault();
        });

        document.addEventListener('keydown', function (e) {
            // Disable F12, Ctrl+Shift+I, and Ctrl+U
            if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I') || (e.ctrlKey && e.key === 'U')) {
            e.preventDefault();
            }
        });
      </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('superadmin/js/scripts.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('superadmin/js/datatables-simple-demo.js') }}"></script> 
@yield('scripts') 
</body>
</html>

