<?php
  $general_setting = App\Models\GeneralSettings::where('status',1)->first();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <title>{{$general_setting->title}} | Unit Login</title>
  <link rel="icon" type="image/png" href="{{ $general_setting->fav_icon }}">

  <link rel="stylesheet" href="{{asset('admin_assets/plugins/fontawesome-free/css/all.min.css')}}">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      -webkit-tap-highlight-color: transparent;
    }

    :root {
      --primary: #667eea;
      --secondary: #764ba2;
      --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: var(--bg-gradient);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .login-container {
      width: 100%;
      max-width: 420px;
      animation: slideUp 0.5s ease;
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .logo-section {
      text-align: center;
      margin-bottom: 40px;
    }

    .logo-circle {
      width: 100px;
      height: 100px;
      background: white;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 20px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }

    .logo-circle img {
      width: 70px;
      height: 70px;
      object-fit: contain;
    }

    .app-title {
      color: white;
      font-size: 28px;
      font-weight: 800;
      letter-spacing: 1px;
      text-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }

    .app-subtitle {
      color: rgba(255,255,255,0.9);
      font-size: 16px;
      font-weight: 500;
      margin-top: 8px;
    }

    .card {
      background: white;
      border-radius: 24px;
      padding: 32px 28px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }

    .welcome-text {
      font-size: 20px;
      font-weight: 700;
      color: #1f2937;
      margin-bottom: 8px;
    }

    .welcome-subtext {
      font-size: 14px;
      color: #6b7280;
      margin-bottom: 28px;
    }

    .alert {
      padding: 16px;
      border-radius: 16px;
      margin-bottom: 24px;
      font-size: 15px;
      display: flex;
      align-items: center;
      gap: 12px;
      animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateX(-10px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    .alert-success {
      background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
      color: #065f46;
    }

    .alert-danger {
      background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
      color: #991b1b;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
      background: #f9fafb;
      border-radius: 16px;
      border: 2px solid #e5e7eb;
      transition: all 0.3s;
    }

    .input-wrapper:focus-within {
      border-color: var(--primary);
      background: white;
      box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .input-icon {
      padding: 0 18px;
      color: var(--primary);
      font-size: 20px;
    }

    .form-control {
      flex: 1;
      border: none;
      background: transparent;
      padding: 18px 18px 18px 0;
      font-size: 16px;
      outline: none;
      color: #1f2937;
      font-weight: 500;
    }

    .form-control::placeholder {
      color: #9ca3af;
    }

    .btn-login {
      width: 100%;
      background: var(--bg-gradient);
      color: white;
      border: none;
      border-radius: 16px;
      padding: 18px;
      font-size: 17px;
      font-weight: 700;
      letter-spacing: 0.5px;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-top: 28px;
      box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
    }

    .btn-login:active {
      transform: scale(0.98);
    }

    .footer-text {
      text-align: center;
      color: rgba(255,255,255,0.8);
      font-size: 13px;
      margin-top: 24px;
    }

    @media (max-width: 480px) {
      body {
        padding: 16px;
      }

      .logo-circle {
        width: 90px;
        height: 90px;
      }

      .logo-circle img {
        width: 60px;
        height: 60px;
      }

      .app-title {
        font-size: 24px;
      }

      .card {
        padding: 28px 24px;
      }
    }

    @supports (padding: max(0px)) {
      body {
        padding-top: max(20px, env(safe-area-inset-top));
        padding-bottom: max(20px, env(safe-area-inset-bottom));
      }
    }
  </style>
</head>

<body>
  <div class="login-container">
    <div class="logo-section">
      <div class="logo-circle">
        <img src="{{$general_setting->logo}}" alt="Logo">
      </div>
      <div class="app-title">UNIT PORTAL</div>
      <div class="app-subtitle">Production Management System</div>
    </div>

    <div class="card">
      <div class="welcome-text">Welcome Back!</div>
      <div class="welcome-subtext">Sign in to continue</div>

      @if(Session::has('success'))
      <div class="alert alert-success">
        <i class="fas fa-check-circle" style="font-size: 20px;"></i>
        <span>{{ Session::get('success') }}</span>
      </div>
      @elseif(Session::has('error'))
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle" style="font-size: 20px;"></i>
        <span>{{ Session::get('error') }}</span>
      </div>
      @endif

      <form method="post" action="{{route('unit.login.post')}}">
        @csrf
        
        <div class="form-group">
          <div class="input-wrapper">
            <div class="input-icon">
              <i class="fas fa-id-badge"></i>
            </div>
            <input 
              type="text" 
              class="form-control" 
              name="employee_id" 
              placeholder="Employee ID" 
              value="{{old('employee_id')}}" 
              required 
              autofocus
              autocomplete="username"
            >
          </div>
        </div>

        <div class="form-group">
          <div class="input-wrapper">
            <div class="input-icon">
              <i class="fas fa-lock"></i>
            </div>
            <input 
              type="password" 
              class="form-control" 
              name="password" 
              placeholder="Password" 
              required
              autocomplete="current-password"
            >
          </div>
        </div>

        <button type="submit" class="btn-login">
          <i class="fas fa-sign-in-alt"></i>
          <span>SIGN IN</span>
        </button>
      </form>
    </div>

    <div class="footer-text">
      © {{ date('Y') }} {{$general_setting->title}}. All rights reserved.
    </div>
  </div>

  <script src="{{asset('admin_assets/plugins/jquery/jquery.min.js')}}"></script>
</body>

</html>