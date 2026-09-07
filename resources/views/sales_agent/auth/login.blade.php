@extends('sales_agent.layouts.app', ['title' => 'SnapKid - Agent Login'])

@section('content')
    <div class="container">
        <div class="row min-vh-100 align-items-center justify-content-center py-4">
            <div class="col-12 col-md-5 col-lg-4">
                <div class="text-center mb-4">
                    <div class="snapkid-logo-wrap d-inline-flex align-items-center justify-content-center p-3 rounded-circle bg-white shadow-sm mb-3">
                        <img src="{{ asset('images/snapkid_logo.png') }}" alt="SnapKid Logo" class="img-fluid" style="max-height: 95px; width: auto; object-fit: contain;">
                    </div>
                    <h3 class="font-weight-bold text-dark mb-1">Agent Portal</h3>
                    <p class="text-muted small">Sign in to manage shops, catalog & orders</p>
                </div>

                <div class="app-card border-0 shadow-lg position-relative overflow-hidden" style="border-radius: 20px; background: #ffffff;">
                    <!-- SnapKid Brand Top Accent Bar -->
                    <div style="height: 5px; background: linear-gradient(90deg, #ffd600 0%, #22c55e 100%); width: 100%; position: absolute; top: 0; left: 0;"></div>

                    <form action="{{ route('agent.login') }}" method="POST" class="pt-2">
                        @csrf
                        <div class="form-group mb-4">
                            <label class="small font-weight-bold text-muted uppercase">Email Address</label>
                            <div class="input-group bg-light rounded-lg border">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-transparent border-0"><i
                                            class="fas fa-envelope text-success"></i></span>
                                </div>
                                <input type="email" name="email" class="form-control bg-transparent border-0 py-4 font-weight-bold"
                                    placeholder="agent@snapkid.com" required value="{{ old('email') }}">
                            </div>
                            @error('email')
                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label class="small font-weight-bold text-muted uppercase">Password</label>
                            <div class="input-group bg-light rounded-lg border">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-transparent border-0"><i
                                            class="fas fa-lock text-success"></i></span>
                                </div>
                                <input type="password" name="password" class="form-control bg-transparent border-0 py-4"
                                    placeholder="••••••••" required>
                            </div>
                            @error('password')
                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                                <label class="custom-control-label small text-muted font-weight-bold" for="remember">Remember me</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-snapkid-login py-3 shadow-sm btn-block">
                            Login Now <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </form>
                </div>

                <div class="text-center text-muted small mt-4">
                    <p class="mb-1"><i class="fas fa-shield-alt text-success mr-1"></i> SnapKid Sales Agent Secure Access</p>
                    <span class="text-muted" style="font-size: 11px;">Forgot password? Contact Administrator</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        body {
            background: radial-gradient(circle at 15% 15%, rgba(255, 214, 0, 0.14) 0%, transparent 45%),
                        radial-gradient(circle at 85% 85%, rgba(34, 197, 94, 0.14) 0%, transparent 45%),
                        #f8fafc;
            padding-bottom: 0 !important;
            min-height: 100vh;
        }

        .snapkid-logo-wrap {
            width: 120px;
            height: 120px;
            border: 3px solid #fef08a;
            transition: transform 0.3s ease;
        }
        .snapkid-logo-wrap:hover {
            transform: scale(1.05);
        }

        .input-group {
            transition: all 0.25s ease;
        }

        .input-group:focus-within {
            border-color: #26A744 !important;
            box-shadow: 0 0 0 3px rgba(38, 167, 68, 0.2) !important;
            background: #ffffff !important;
        }

        .form-control:focus {
            box-shadow: none;
        }

        .btn-snapkid-login {
            background: linear-gradient(135deg, #26A744 0%, #1a8733 100%);
            color: #ffffff;
            font-weight: 700;
            border-radius: 12px;
            border: none;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 14px rgba(38, 167, 68, 0.35);
            transition: all 0.25s ease;
        }

        .btn-snapkid-login:hover {
            background: linear-gradient(135deg, #1a8733 0%, #136626 100%);
            color: #ffffff;
            box-shadow: 0 6px 18px rgba(38, 167, 68, 0.45);
            transform: translateY(-1px);
        }

        .btn-snapkid-login:active {
            transform: translateY(1px);
        }

        .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #26A744;
            border-color: #26A744;
        }
    </style>
@endpush