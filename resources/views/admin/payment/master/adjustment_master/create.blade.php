@extends('admin.layouts.app')
@section('title', 'Add Adjustment Master')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas fa-sliders-h text-primary mr-2"></i> Add Adjustment Master
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.payment.master.adjustment_master.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Adjustment Masters
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="erp-card p-3 mb-2">
        <form action="{{ route('admin.payment.master.adjustment_master.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="erp-label">Display Name (e.g. Committee) <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control erp-input" required placeholder="Enter Display Name">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="erp-label">Model Class <span class="text-danger">*</span></label>
                    <select name="model_name" class="form-control select2 erp-input" required style="width: 100%;">
                        <option value="">-- Select Model --</option>
                        <option value="App\Models\Committee">Committee</option>
                        <option value="App\Models\Commission">Commission</option>
                        <option value="App\Models\GeneralExpense">General Expense</option>
                        <option value="App\Models\ElectricityExpense">Electricity Expense</option>
                        <option value="App\Models\Rent">Rent</option>
                        <option value="App\Models\TelephoneExpense">Telephone Expense</option>
                        <option value="App\Models\Tax">Tax</option>
                        <option value="App\Models\Interest">Interest</option>
                        <option value="App\Models\TourExpense">Tour Expense</option>
                        <option value="App\Models\Contractor">Contractor</option>
                        <option value="App\Models\ConsumableGood">Consumable Good</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="erp-label">Status</label>
                    <select name="status" class="form-control erp-input">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-md-12 text-right mt-2">
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-save mr-1"></i> Create Master
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
