@extends('sales_agent.layouts.app', ['title' => 'Add New Shop'])

@section('content')
    <div class="container">
        <div class="mb-4">
            <a href="{{ route('agent.shops.index') }}" class="text-muted small text-decoration-none">
                <i class="fas fa-arrow-left mr-1"></i> Back to Shops
            </a>
            <h2 class="font-weight-bold h4 mt-2">Add New Shop</h2>
        </div>

        <div class="app-card shadow-sm border-0">
            <form action="{{ route('agent.shops.store') }}" method="POST">
                @csrf
                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-muted">Shop/Company Name</label>
                    <input type="text" name="name" class="form-control rounded-lg" placeholder="e.g. Fashion Hub" required>
                </div>

                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-muted">Phone Number</label>
                    <input type="tel" name="phone" class="form-control rounded-lg" placeholder="e.g. 9876543210" required>
                </div>

                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-muted">Email Address (Optional)</label>
                    <input type="email" name="email" class="form-control rounded-lg" placeholder="shop@example.com">
                </div>

                <div class="form-group mb-4">
                    <label class="small font-weight-bold text-muted">Full Address</label>
                    <textarea name="address" class="form-control rounded-lg" rows="3"
                        placeholder="Street, City, Zip..."></textarea>
                </div>

                <button type="submit" class="btn btn-app py-3">
                    Save Shop Details <i class="fas fa-save ml-2"></i>
                </button>
            </form>
        </div>
    </div>
@endsection