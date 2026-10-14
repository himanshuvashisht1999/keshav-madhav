@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- ERP Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="erp-header-title">Edit sales agent</h1>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.sales-agent.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back to list
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="erp-card p-3">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-3" style="border-radius: 6px;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong><i class="fas fa-exclamation-triangle mr-1"></i> Please correct the following errors:</strong>
                <ul class="mb-0 mt-2 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.master.sales-agent.update') }}" method="post">
            @csrf
            <input type="hidden" name="id" value="{{ $data->id }}">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control erp-filter-input" placeholder="Enter name" value="{{ $data->name }}">
                        @error('name')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control erp-filter-input" placeholder="Enter email" value="{{ $data->email }}">
                        @error('email')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control erp-filter-input" placeholder="Enter phone" value="{{ $data->phone }}">
                        @error('phone')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Password <small class="text-muted">(Leave blank to keep current)</small></label>
                        <input type="password" name="password" class="form-control erp-filter-input" placeholder="Enter new password">
                        @error('password')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control select2 erp-filter-select" style="width: 100%;">
                            <option value="1" {{ $data->status == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ $data->status == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3 pt-2">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="isMasterAgent" name="is_master_agent" value="1" {{ $data->is_master_agent ? 'checked' : '' }}>
                            <label class="custom-control-label" for="isMasterAgent">Is master sales agent?</label>
                        </div>
                        <small class="form-text text-muted">Master agents can create orders for any customer or vendor globally.</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3 pt-2">
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" id="see_price" name="see_price" value="1" {{ old('see_price', $data->see_price) ? 'checked' : '' }}>
                            <label for="see_price" class="custom-control-label">Show pricing info (Prices, GST, totals)</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control erp-filter-input" rows="2" placeholder="Enter address">{{ $data->address }}</textarea>
                        @error('address')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label class="form-label">Current balance (Sum of shops)</label>
                        <div class="form-control erp-filter-input d-flex align-items-center justify-content-between" style="background-color: var(--erp-bg-light, #f4f6f9);">
                            @php $total_bal = $data->shops()->sum('balance'); @endphp
                            <span>₹ {{ number_format(abs($total_bal), 2) }}</span>
                            @if($total_bal >= 0)
                                <span class="badge badge-success px-2 py-1">Cr</span>
                            @else
                                <span class="badge badge-danger px-2 py-1">Dr</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label class="form-label">Opening balance</label>
                        <input type="number" step="0.01" name="balance" class="form-control erp-filter-input" placeholder="Enter opening balance" value="{{ $data->currentOpeningBalance ? $data->currentOpeningBalance->amount : 0 }}">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label class="form-label">Opening balance type</label>
                        <select name="balance_type" class="form-control select2 erp-filter-select" style="width: 100%;">
                            <option value="Credit" {{ ($data->currentOpeningBalance && $data->currentOpeningBalance->balance_type == 'Credit') ? 'selected' : 'selected' }}>Credit</option>
                            <option value="Debit" {{ ($data->currentOpeningBalance && $data->currentOpeningBalance->balance_type == 'Debit') ? 'selected' : '' }}>Debit</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-12 mt-3">
                    <hr class="my-2">
                    <h6 class="font-weight-bold mb-2">Per brand discounts</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-2">
                            <thead class="thead-light">
                                <tr>
                                    <th>Brand name</th>
                                    <th style="width: 250px;">Discount percentage (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($brands as $brand)
                                    @php
                                        $brandDiscount = $data->brandDiscounts->where('brand_id', $brand->id)->first();
                                        $discountValue = $brandDiscount ? $brandDiscount->discount_percentage : '';
                                    @endphp
                                    <tr>
                                        <td>{{ $brand->name }}</td>
                                        <td>
                                            <input type="number" step="0.01" min="0" max="100" 
                                                name="brand_discounts[{{ $brand->id }}]" 
                                                class="form-control form-control-sm erp-filter-input" 
                                                placeholder="Enter discount for {{ $brand->name }}"
                                                value="{{ old('brand_discounts.' . $brand->id, $discountValue) }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-md-12 d-flex justify-content-end align-items-center mt-3 pt-2 border-top">
                    <a href="{{ route('admin.master.sales-agent.index') }}" class="btn-erp btn-erp-outline mr-2">Cancel</a>
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-check mr-1"></i> Update sales agent
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection