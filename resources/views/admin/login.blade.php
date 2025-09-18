<?php
  $general_setting = App\Models\GeneralSettings::where('status',1)->first();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{$general_setting->title}}</title>
  <link rel="icon" type="image/png" href="{{ $general_setting->fav_icon }}">

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{asset('admin_assets/plugins/fontawesome-free/css/all.min.css')}}">

  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="{{asset('admin_assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">

  <!-- Theme style -->
  <link rel="stylesheet" href="{{asset('admin_assets/dist/css/adminlte.min.css')}}">

  <!-- Custom styles for enhanced design -->
  <style>
    body {
      font-family: 'Source Sans Pro', sans-serif;
      background-color: #e9ecef;
    }

    .login-box {
      width: 400px;
      margin: auto;
    }

    .card {
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .card-header {
      text-align: center;
      padding: 20px;
      background-color: #31187e;
      border-top-left-radius: 10px;
      border-top-right-radius: 10px;
    }

    .card-header img {
      width: 100px;
      height: 100px;
    }

    .card-body {
      padding: 30px;
    }

    .login-box-msg {
      font-size: 18px;
      font-weight: bold;
      color: #00796b;
      margin-bottom: 20px;
    }

    .server-messages {
      margin-bottom: 20px;
    }

    .alert {
      border-radius: 5px;
    }

    .input-group .form-control {
      border-radius: 20px;
      box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .input-group-text {
      background-color: #00796b;
      color: white;
      border-radius: 20px;
    }

    .btn-primary {
      background-color: #31187e;
      border-radius: 20px;
      border: none;
      width: 100%;
      padding: 10px;
      font-size: 16px;
      font-weight: bold;
    }

    .btn-primary:hover {
      background-color: #004d40;
    }

    .forgot-password {
      font-size: 14px;
      text-align: center;
    }

    .forgot-password a {
      color: #00796b;
    }

    .forgot-password a:hover {
      text-decoration: underline;
    }

    /* Responsive Design */
    @media (max-width: 576px) {
      .login-box {
        width: 90%;
      }
    }
  </style>
</head>

<body class="hold-transition login-page">
  <div class="login-box">
    <div class="card card-outline card-primary">
      <div class="card-header text-center">
        <img src="{{$general_setting->logo}}" alt="Logo">
      </div>

      <div class="card-body">
        <p class="login-box-msg">Sign in to start your session</p>

        <div class="server-messages">
          @if(Session::has('success'))
          <div class="alert alert-success">
            {{ Session::get('success') }}
          </div>
          @elseif(Session::has('error'))
          <div class="alert alert-danger">
            {{ Session::get('error') }}
          </div>
          @endif
        </div>

        <form method="post" action="{{route('admin.postLogin')}}">
          @csrf
          <div class="input-group mb-3">
            <input type="email" class="form-control" name="email" placeholder="Email" value="{{old('email')}}" required>
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-envelope"></span>
              </div>
            </div>
          </div>

          <div class="input-group mb-3">
            <input type="password" class="form-control" name="password" placeholder="Password" required>
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-lock"></span>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-8">
              <div class="icheck-primary">
                <input type="checkbox" id="remember">
                <label for="remember">
                  Remember Me
                </label>
              </div>
            </div>
            <!-- /.col -->
            <div class="col-4">
              <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </div>
            <!-- /.col -->
          </div>
        </form>

        <!-- <div class="forgot-password">
          <a href="#">Forgot Password?</a>
        </div> -->
      </div>
      <!-- /.card-body -->
    </div>
    <!-- /.card -->
  </div>
  <!-- /.login-box -->

  <!-- jQuery -->
  <script src="{{asset('admin_assets/plugins/jquery/jquery.min.js')}}"></script>
  <!-- Bootstrap 4 -->
  <script src="{{asset('admin_assets/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
  <!-- AdminLTE App -->
  <script src="{{asset('admin_assets/dist/js/adminlte.min.js')}}"></script>
</body>

</html>
