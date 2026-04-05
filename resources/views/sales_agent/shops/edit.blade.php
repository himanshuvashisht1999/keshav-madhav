@extends('sales_agent.layouts.app', ['title' => 'Edit Shop'])

@section('content')
    <div class="container">
        <div class="mb-4">
            <a href="{{ route('agent.shops.index') }}" class="text-muted small text-decoration-none">
                <i class="fas fa-arrow-left mr-1"></i> Back to Shops
            </a>
            <h2 class="font-weight-bold h4 mt-2">Edit Shop Details</h2>
        </div>

        <div class="app-card shadow-sm border-0">
            <form action="{{ route('agent.shops.update', $shop->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-muted">Shop/Company Name</label>
                    <input type="text" name="name" class="form-control rounded-lg" value="{{ old('name', $shop->name) }}"
                        placeholder="e.g. Fashion Hub" required>
                </div>

                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-muted">Phone Number</label>
                    <input type="tel" name="phone" class="form-control rounded-lg" value="{{ old('phone', $shop->phone) }}"
                        placeholder="e.g. 9876543210" required>
                </div>

                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-muted">Opening Balance</label>
                    <input type="number" name="balance" step="0.01" class="form-control rounded-lg" value="{{ old('balance', abs($shop->balance)) }}" placeholder="e.g. 0.00">
                </div>

                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-muted">Balance Type</label>
                    <select name="balance_type" class="form-control rounded-lg" required>
                        <option value="Credit" {{ old('balance_type', ($shop->balance >= 0 ? 'Credit' : 'Debit')) == 'Credit' ? 'selected' : '' }}>Credit</option>
                        <option value="Debit" {{ old('balance_type', ($shop->balance >= 0 ? 'Credit' : 'Debit')) == 'Debit' ? 'selected' : '' }}>Debit</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-muted">Email Address (Optional)</label>
                    <input type="email" name="email" class="form-control rounded-lg"
                        value="{{ old('email', $shop->email) }}" placeholder="shop@example.com">
                </div>

                <div class="form-group mb-4">
                    <label class="small font-weight-bold text-muted">Full Address</label>
                    <textarea name="address" class="form-control rounded-lg" rows="3"
                        placeholder="Street, City, Zip...">{{ old('address', $shop->address) }}</textarea>
                </div>

                <button type="submit" class="btn btn-app py-3 w-100">
                    Update Shop Details <i class="fas fa-save ml-2"></i>
                </button>
            </form>
        </div>
    </div>
@endsection