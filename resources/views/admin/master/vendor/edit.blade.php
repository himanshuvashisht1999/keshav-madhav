@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-truck text-primary"></i> Edit Vendor
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.vendor.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Vendors
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-2" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="erp-card">
        <div class="px-3 py-2 border-bottom bg-light font-weight-bold" style="font-size: 13px; color: var(--erp-text-main);">
            <i class="fas fa-edit mr-1 text-primary"></i> Vendor Information
        </div>
        <form action="{{ route('admin.master.vendor.update') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $data->id }}">
            <div class="erp-card-body p-3">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control erp-filter-input" placeholder="Enter name" value="{{ old('name', $data->name) }}" required>
                        @if ($errors->has('name'))
                            <span class="invalid-feedback d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Phone</label>
                        <input type="number" name="phone" class="form-control erp-filter-input" placeholder="Enter phone" value="{{ old('phone', $data->phone) }}">
                        @if ($errors->has('phone'))
                            <span class="invalid-feedback d-block">{{ $errors->first('phone') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Email</label>
                        <input type="email" name="email" class="form-control erp-filter-input" placeholder="Enter email" value="{{ old('email', $data->email) }}">
                        @if ($errors->has('email'))
                            <span class="invalid-feedback d-block">{{ $errors->first('email') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Address</label>
                        <input type="text" name="address" class="form-control erp-filter-input" placeholder="Enter address" value="{{ old('address', $data->address) }}">
                        @if ($errors->has('address'))
                            <span class="invalid-feedback d-block">{{ $errors->first('address') }}</span>
                        @endif
                    </div>

                    @php
                        $selectedItems = unserialize(optional($data)->items) ?: [];
                    @endphp
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Items <span class="text-danger">*</span></label>
                        <select name="items[]" class="form-control select2 erp-filter-select" style="width: 100%;" multiple required>
                            <option value="0" @if(in_array(0, $selectedItems)) selected @endif>Fabric</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" @if(in_array($item->id, $selectedItems)) selected @endif>{{ $item->name }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('items'))
                            <span class="invalid-feedback d-block">{{ $errors->first('items') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Purchase Agent</label>
                        <select name="purchase_agent_id" class="form-control select2 erp-filter-select" style="width: 100%;">
                            <option value="">Select Purchase Agent</option>
                            @foreach($purchase_agents as $agent)
                                <option value="{{ $agent->id }}" {{ old('purchase_agent_id', $data->purchase_agent_id) == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('purchase_agent_id'))
                            <span class="invalid-feedback d-block">{{ $errors->first('purchase_agent_id') }}</span>
                        @endif
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Current Balance</label>
                        <div class="form-control erp-filter-input d-flex align-items-center justify-content-between" style="background-color: #f8fafc;">
                            <span class="font-weight-bold">₹ {{ number_format(abs($data->balance), 2) }}</span>
                            @if($data->balance >= 0)
                                <span class="badge badge-success font-weight-bold">Cr</span>
                            @else
                                <span class="badge badge-danger font-weight-bold">Dr</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Opening Balance</label>
                        <input type="number" step="0.01" name="balance" class="form-control erp-filter-input" placeholder="Enter opening balance" value="{{ $data->currentOpeningBalance ? $data->currentOpeningBalance->amount : 0 }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Opening Balance Type</label>
                        <select name="type" class="form-control select2 erp-filter-select" style="width: 100%;">
                            <option value="Credit" {{ ($data->currentOpeningBalance && $data->currentOpeningBalance->balance_type == 'Credit') ? 'selected' : ($data->balance >= 0 ? 'selected' : '') }}>Credit</option>
                            <option value="Debit" {{ ($data->currentOpeningBalance && $data->currentOpeningBalance->balance_type == 'Debit') ? 'selected' : ($data->balance < 0 ? 'selected' : '') }}>Debit</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Status</label>
                        <select name="status" class="form-control select2 erp-filter-select" style="width: 100%;">
                            <option value="1" {{ optional($data)->status == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ optional($data)->status == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @if ($errors->has('status'))
                            <span class="invalid-feedback d-block">{{ $errors->first('status') }}</span>
                        @endif
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Description</label>
                        <textarea name="description" class="form-control erp-filter-input" placeholder="Enter description" rows="2">{{ old('description', $data->description) }}</textarea>
                        @if ($errors->has('description'))
                            <span class="invalid-feedback d-block">{{ $errors->first('description') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="px-3 py-2 border-top bg-white d-flex justify-content-end" style="gap: 8px;">
                <a href="{{ route('admin.master.vendor.index') }}" class="btn-erp btn-erp-outline">Cancel</a>
                <button type="submit" class="btn-erp btn-erp-primary">
                    <i class="fas fa-save"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
