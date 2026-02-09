@extends('sales_agent.layouts.app', ['title' => 'Agent Login'])

@section('content')
    <div class="container">
        <div class="row min-vh-100 align-items-center justify-content-center">
            <div class="col-12 col-md-4">
                <div class="text-center mb-5">
                    <div class="bg-white d-inline-block p-4 rounded-circle shadow-sm mb-3">
                        <i class="fas fa-user-tie fa-3x text-primary"></i>
                    </div>
                    <h2 class="font-weight-bold">Agent Portal</h2>
                    <p class="text-muted">Sign in to start your session</p>
                </div>

                <div class="app-card border-0 shadow-lg">
                    <form action="{{ route('agent.login') }}" method="POST">
                        @csrf
                        <div class="form-group mb-4">
                            <label class="small font-weight-bold text-muted">Email Address</label>
                            <div class="input-group bg-light rounded-lg">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-transparent border-0"><i
                                            class="fas fa-envelope text-muted"></i></span>
                                </div>
                                <input type="email" name="email" class="form-control bg-transparent border-0 py-4"
                                    placeholder="name@company.com" required>
                            </div>
                            @error('email')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label class="small font-weight-bold text-muted">Password</label>
                            <div class="input-group bg-light rounded-lg">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-transparent border-0"><i
                                            class="fas fa-lock text-muted"></i></span>
                                </div>
                                <input type="password" name="password" class="form-control bg-transparent border-0 py-4"
                                    placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                                <label class="custom-control-label small text-muted" for="remember">Remember me</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-app py-3 shadow-sm">
                            Login Now <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </form>
                </div>

                <p class="text-center text-muted small mt-4">
                    Forgot password? Contact admin.
                </p>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
            padding-bottom: 0 !important;
        }

        .input-group:focus-within {
            box-shadow: 0 0 0 2px var(--secondary-color);
        }

        .form-control:focus {
            box-shadow: none;
        }
    </style>
@endpush