<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/dwcclogo.ico') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>SuperAdmin Registration</title>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4" style="margin-top: 100px; margin-left: 500px;">
                <!-- Logo Image -->
                <img src="{{ asset('images/dwcclogo.png') }}" alt="Logo" style="width: 150px; height: auto; margin-bottom: 20px; margin-left: 120px;">
                <h4 style="text-align: center;">SUPERADMIN REGISTRATION</h4>
                <hr>
                <form action="{{ route('register_superadmin') }}" method="post">
                    @if(Session::has('success'))
                        <div class="alert alert-success">{{ Session::get('success') }}</div>
                    @endif
                    @if(Session::has('fail'))
                        <div class="alert alert-danger">{{ Session::get('fail') }}</div>
                    @endif

                    @csrf
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" class="form-control" placeholder="Enter Full Name" name="full_name" value="{{ old('full_name') }}">
                        <span class="text-danger">@error('full_name') {{ $message }} @enderror</span>
                    </div>

                    <div class="form-group">
                        <label for="emp_id">Employee ID</label>
                        <input type="text" class="form-control" placeholder="Enter Employee ID" id="emp_id" name="emp_id" value="{{ old('emp_id') }}" oninput="updateUsername()">
                        <span class="text-danger">@error('emp_id') {{ $message }} @enderror</span>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <!-- Auto-fill the username field with emp_id value and make it read-only -->
                        <input type="text" class="form-control" placeholder="Username" id="username" name="username" value="{{ old('emp_id') }}" readonly>
                        <span class="text-danger">@error('username') {{ $message }} @enderror</span>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" placeholder="Enter Password" name="password" value="">
                        <span class="text-danger">@error('password') {{ $message }} @enderror</span>
                        <!-- Provide an instructional message -->
                        @if(Session::has('fail'))
                        <small class="text-muted">Please re-enter your password.</small>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="confirmpassword">Confirm Password</label>
                        <input type="password" class="form-control" placeholder="Confirm Password" name="confirmpassword" value="">
                        <span class="text-danger">@error('confirmpassword') {{ $message }} @enderror</span>
                        <!-- Provide an instructional message -->
                        @if(Session::has('fail'))
                        <small class="text-muted">Please re-enter the confirmation password.</small>
                        @endif
                    </div>
                    
                    <br>
                    <div class="form-group">
                        <button class="btn btn-block btn-primary" type="submit">Register</button>
                    </div>
                    <br>
                    <a href="superadminlogin">Already Registered? Click here to Login.</a>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Function to update the username field in real-time as emp_id is typed
        function updateUsername() {
            var empId = document.getElementById("emp_id").value;
            document.getElementById("username").value = empId;
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
