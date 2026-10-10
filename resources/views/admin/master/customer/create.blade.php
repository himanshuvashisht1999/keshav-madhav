@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- ERP Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="erp-header-title">Create customer</h1>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.customer.index') }}" class="btn-erp btn-erp-outline">
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

        <form action="{{ route('admin.master.customer.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Customer type</label>
                        <select name="type" id="customer_type" class="form-control select2 erp-filter-select" style="width: 100%;">
                            <option value="corporate" {{ old('type') == 'corporate' ? 'selected' : '' }}>Corporate</option>
                            <option value="domestic" {{ old('type') == 'domestic' ? 'selected' : '' }}>Domestic</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6" id="subtype_wrapper" style="display: none;">
                    <div class="form-group mb-3">
                        <label class="form-label">Domestic type</label>
                        <select name="subtype" id="customer_subtype" class="form-control select2 erp-filter-select" style="width: 100%;">
                            <option value="">Select subtype</option>
                            <option value="direct" {{ old('subtype') == 'direct' ? 'selected' : '' }}>Direct</option>
                            <option value="agent" {{ old('subtype') == 'agent' ? 'selected' : '' }}>Agent</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-12 p-0" id="standard_fields">
                    <div class="row mx-0">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control erp-filter-input" placeholder="Enter name" value="{{ old('name') }}">
                                @if ($errors->has('name'))
                                    <span class="invalid-feedback d-block">{{ $errors->first('name') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Phone</label>
                                <input type="number" name="phone" class="form-control erp-filter-input" placeholder="Enter phone" value="{{ old('phone') }}">
                                @if ($errors->has('phone'))
                                    <span class="invalid-feedback d-block">{{ $errors->first('phone') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Email</label>
                                <input type="text" name="email" class="form-control erp-filter-input" placeholder="Enter email" value="{{ old('email') }}">
                                @if ($errors->has('email'))
                                    <span class="invalid-feedback d-block">{{ $errors->first('email') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">GST number</label>
                                <input type="text" name="gst_number" class="form-control erp-filter-input" placeholder="Enter GST number" value="{{ old('gst_number') }}">
                                @if ($errors->has('gst_number'))
                                    <span class="invalid-feedback d-block">{{ $errors->first('gst_number') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control erp-filter-input" placeholder="Enter address" value="{{ old('address') }}">
                                @if ($errors->has('address'))
                                    <span class="invalid-feedback d-block">{{ $errors->first('address') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Opening balance ({{ \App\Models\MasterOpeningBalance::getCurrentFinancialYear() }})</label>
                                <input type="number" name="balance" class="form-control erp-filter-input" placeholder="Enter opening balance" value="{{ old('balance', 0) }}">
                                @if ($errors->has('balance'))
                                    <span class="invalid-feedback d-block">{{ $errors->first('balance') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Opening balance type</label>
                                <select name="balance_type" class="form-control select2 erp-filter-select" style="width: 100%;">
                                    <option value="Credit" {{ old('balance_type') == 'Credit' ? 'selected' : '' }}>Credit</option>
                                    <option value="Debit" {{ old('balance_type') == 'Debit' ? 'selected' : '' }}>Debit</option>
                                </select>
                                @if ($errors->has('balance_type'))
                                    <span class="invalid-feedback d-block">{{ $errors->first('balance_type') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Payment term (days)</label>
                                <input type="number" name="payment_term_days" class="form-control erp-filter-input" placeholder="E.g. 120" value="{{ old('payment_term_days', 120) }}">
                                @if ($errors->has('payment_term_days'))
                                    <span class="invalid-feedback d-block">{{ $errors->first('payment_term_days') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Direct Fields (Per Brand Discounts) -->
                <div class="col-md-12" id="direct_fields" style="display: none;">
                    <hr class="my-3">
                    <h6 class="font-weight-bold mb-2">Per brand discounts (%)</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-2">
                            <thead class="thead-light">
                                <tr>
                                    <th>Brand name</th>
                                    <th style="width: 200px;">Discount percentage (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items['brands'] as $brand)
                                    <tr>
                                        <td>{{ $brand->name }}</td>
                                        <td>
                                            <input type="number" step="0.01" min="0" max="100"
                                                name="brand_discounts[{{ $brand->id }}]"
                                                class="form-control form-control-sm erp-filter-input"
                                                value="{{ old('brand_discounts.' . $brand->id, 0) }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <hr class="my-3">
                </div>

                <!-- Agent/Shop Fields -->
                <div class="col-md-6" id="agent_fields" style="display: none;">
                    <div class="form-group mb-3">
                        <label class="form-label">Select parent agent</label>
                        <select name="sales_agent_id" class="form-control select2 erp-filter-select" style="width: 100%;">
                            <option value="">Select agent</option>
                            @foreach($items['agents'] as $agent)
                                <option value="{{ $agent->id }}" {{ old('sales_agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-6" id="agent_comm_fields" style="display: none;">
                    <div class="form-group mb-3">
                        <label class="form-label">Password (for agent login)</label>
                        <input type="password" name="password" class="form-control erp-filter-input" placeholder="Enter password">
                    </div>
                    <div class="form-group custom-control custom-checkbox mb-3">
                        <input class="custom-control-input" type="checkbox" id="see_price" name="see_price" value="1" checked>
                        <label for="see_price" class="custom-control-label">Show pricing info</label>
                    </div>
                </div>

                <!-- Shop Details Section -->
                <div class="col-md-12" id="shop_details_section" style="display: none;">
                    <hr class="my-3">
                    <h6 class="font-weight-bold mb-2">Shop details</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Shop name <span class="text-danger">*</span></label>
                                <input type="text" name="shop_name" class="form-control erp-filter-input" placeholder="Enter shop or company name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Shop phone number <span class="text-danger">*</span></label>
                                <input type="number" name="shop_phone" class="form-control erp-filter-input" placeholder="E.g. 98XXXXXXXX">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Shop email address (optional)</label>
                                <input type="email" name="shop_email" class="form-control erp-filter-input" placeholder="example@gmail.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Shop GST number (optional)</label>
                                <input type="text" name="shop_gst_number" class="form-control erp-filter-input" placeholder="Enter GST number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Shop full address (optional)</label>
                                <textarea name="shop_address" class="form-control erp-filter-input" rows="2" placeholder="Street, landmark, city..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Opening balance ({{ \App\Models\MasterOpeningBalance::getCurrentFinancialYear() }})</label>
                                <input type="number" step="0.01" name="balance" class="form-control erp-filter-input" placeholder="Enter opening balance" value="{{ old('balance', 0) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Opening balance type</label>
                                <select name="balance_type" class="form-control select2 erp-filter-select" style="width: 100%;">
                                    <option value="Credit" {{ old('balance_type') == 'Credit' ? 'selected' : '' }}>Credit</option>
                                    <option value="Debit" {{ old('balance_type') == 'Debit' ? 'selected' : '' }}>Debit</option>
                                </select>
                                @if ($errors->has('balance_type'))
                                    <span class="invalid-feedback d-block">{{ $errors->first('balance_type') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Payment term (days)</label>
                                <input type="number" name="payment_term_days" class="form-control erp-filter-input" placeholder="E.g. 120" value="{{ old('payment_term_days', 120) }}">
                                @if ($errors->has('payment_term_days'))
                                    <span class="invalid-feedback d-block">{{ $errors->first('payment_term_days') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <hr class="my-3">
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control select2 erp-filter-select" style="width: 100%;">
                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-12 d-flex justify-content-end align-items-center mt-3 pt-2 border-top">
                    <a href="{{ route('admin.master.customer.index') }}" class="btn-erp btn-erp-outline mr-2">Cancel</a>
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-check mr-1"></i> Save customer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function () {
        function toggleFields() {
            var type = $('#customer_type').val();
            var subtype = $('#customer_subtype').val();

            if (type === 'domestic') {
                $('#subtype_wrapper').show();
                if (subtype === 'direct') {
                    $('#standard_fields').show().find('input, select, textarea').prop('disabled', false);
                    $('#direct_fields').show().find('input, select, textarea').prop('disabled', false);
                    $('#agent_fields').hide().find('input, select, textarea').prop('disabled', true);
                    $('#agent_comm_fields').hide().find('input, select, textarea').prop('disabled', true);
                    $('#shop_details_section').hide().find('input, select, textarea').prop('disabled', true);
                } else if (subtype === 'agent') {
                    $('#standard_fields').hide().find('input, select, textarea').prop('disabled', true);
                    $('#direct_fields').hide().find('input, select, textarea').prop('disabled', true);
                    $('#agent_fields').show().find('input, select, textarea').prop('disabled', false);
                    $('#agent_comm_fields').hide().find('input, select, textarea').prop('disabled', true);
                    $('#shop_details_section').show().find('input, select, textarea').prop('disabled', false);
                } else {
                    $('#standard_fields').show().find('input, select, textarea').prop('disabled', false);
                    $('#direct_fields').hide().find('input, select, textarea').prop('disabled', true);
                    $('#agent_fields').hide().find('input, select, textarea').prop('disabled', true);
                    $('#agent_comm_fields').hide().find('input, select, textarea').prop('disabled', true);
                    $('#shop_details_section').hide().find('input, select, textarea').prop('disabled', true);
                }
            } else {
                $('#subtype_wrapper').hide();
                $('#standard_fields').show().find('input, select, textarea').prop('disabled', false);
                $('#direct_fields').hide().find('input, select, textarea').prop('disabled', true);
                $('#agent_fields').hide().find('input, select, textarea').prop('disabled', true);
                $('#agent_comm_fields').hide().find('input, select, textarea').prop('disabled', true);
                $('#shop_details_section').hide().find('input, select, textarea').prop('disabled', true);
            }
        }

        $('#customer_type, #customer_subtype').on('change', function () {
            toggleFields();
        });

        // Initial call
        toggleFields();
    });
</script>
@endsection