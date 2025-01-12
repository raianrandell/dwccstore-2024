<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting Login</title>
    <link rel="icon" href="{{ asset('images/dwcclogo.ico') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style/styles.css') }}">
</head>
<body>
    <!-- Green Navbar -->
    <nav class="navbar"></nav>

    <div class="login-wrapper">
        <div class="login-box">
            <div class="logo">
                <img src="{{ asset('images/dwcclogo.png') }}" alt="College Logo">
                <h4>DWCCSTORE: SALES AND INVENTORY</h4>
            </div>
            <h2 class="form-title">Accounting Login</h2>

            <!-- Error or Success Messages -->
            @if(Session::has('success'))
                <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif
            @if(Session::has('fail'))
                <div class="alert alert-danger">{{ Session::get('fail') }}</div>
            @endif

            <form action="{{ route('accounting_login') }}" method="post" class="login-form">
                @csrf
                <!-- Username Field -->
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter Username" value="{{ old('username') }}">
                    <span class="text-danger">@error('username') {{ $message }} @enderror</span>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" placeholder="Enter Password">
                        <span class="toggle-password fas fa-eye-slash"></span>
                    </div>
                    <span class="text-danger">@error('password') {{ $message }} @enderror</span>
                </div>

                <!-- Login Button -->
                <button type="submit" class="login-btn">Login</button>
            </form>
        </div>
    </div>
    <script type="text/javascript" src="{{asset ('javascripts/toggle_password.js')}}"></script>  
</body>
</html>
