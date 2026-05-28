<?php
$general_setting = App\Models\GeneralSettings::where('status', 1)->first();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Owner Login | {{$general_setting->title}}</title>
    <link rel="icon" type="image/png" href="{{ $general_setting->fav_icon }}">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{asset('admin_assets/plugins/fontawesome-free/css/all.min.css')}}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{asset('admin_assets/dist/css/adminlte.min.css')}}">

    <style>
        body {
            background: var(--primary-gradient);
            font-family: 'Source Sans Pro', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            width: 400px;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .card-header {
            background: white;
            border-bottom: none;
            padding: 30px 20px 10px;
        }

        .card-header img {
            max-width: 150px;
            height: auto;
        }

        .login-box-msg {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-main);
            padding-bottom: 25px;
        }

        .form-control {
            height: 50px;
            border-radius: 12px;
            padding-left: 20px;
            border: 1px solid #e5e7eb;
        }

        .input-group-text {
            border-radius: 0 12px 12px 0;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-left: none;
            color: var(--text-muted);
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            height: 50px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            margin-top: 10px;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: #1e40af;
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 12px;
        }
    </style>
</head>

<body class="hold-transition">
    <div class="login-box">
        <div class="card">
            <div class="card-header text-center">
                <img src="{{$general_setting->logo}}" alt="Logo">
            </div>
            <div class="card-body">
                <p class="login-box-msg">Owner Management</p>

                @if(Session::has('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mr-2"></i> {{ Session::get('success') }}
                    </div>
                @elseif(Session::has('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle mr-2"></i> {{ Session::get('error') }}
                    </div>
                @endif

                <form method="post" action="{{route('owner.login.post')}}">
                    @csrf
                    <div class="input-group mb-4">
                        <input type="email" class="form-control" name="email" placeholder="Owner Email"
                            value="{{old('email')}}" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user-tie"></span>
                            </div>
                        </div>
                    </div>

                    <div class="input-group mb-4">
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Access Dashboard</button>
                </form>
            </div>
        </div>
    </div>

    <script src="{{asset('admin_assets/plugins/jquery/jquery.min.js')}}"></script>
    <script src="{{asset('admin_assets/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
</body>

</html>