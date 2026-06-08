@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Customer</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item active">Create Customer</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- SELECT2 EXAMPLE -->
                <div class="card card-default">
                    <!-- <div class="card-header">
                            <h3 class="card-title">Create Customer</h3>
                        </div> -->
                    <form action="{{route('admin.master.customer.store')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Customer Type</label>
                                        <select name="type" id="customer_type" class="form-control select2"
                                            style="width: 100%;">
                                            <option value="corporate" {{old('type') == 'corporate' ? 'selected' : ''}}>
                                                Corporate</option>
                                            <option value="domestic" {{old('type') == 'domestic' ? 'selected' : ''}}>Domestic
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6" id="subtype_wrapper" style="display: none;">
                                    <div class="form-group">
                                        <label>Domestic Type</label>
                                        <select name="subtype" id="customer_subtype" class="form-control select2"
                                            style="width: 100%;">
                                            <option value="">Select Subtype</option>
                                            <option value="direct" {{old('subtype') == 'direct' ? 'selected' : ''}}>Direct
                                            </option>
                                            <option value="agent" {{old('subtype') == 'agent' ? 'selected' : ''}}>Agent
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12 p-0" id="standard_fields">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Name</label>
                                                <input type="text" name="name" class="form-control" placeholder="Enter name"
                                                    value="{{old('name')}}">
                                                @if ($errors->has('name'))
                                                    <span class="invalid-feedback d-block">
                                                        {{ $errors->first('name') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Phone</label>
                                                <input type="number" name="phone" class="form-control"
                                                    placeholder="Enter phone" value="{{old('phone')}}">
                                                @if ($errors->has('phone'))
                                                    <span class="invalid-feedback d-block">
                                                        {{ $errors->first('phone') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Email</label>
                                                <input type="text" name="email" class="form-control"
                                                    placeholder="Enter email" value="{{old('email')}}">
                                                @if ($errors->has('email'))
                                                    <span class="invalid-feedback d-block">
                                                        {{ $errors->first('email') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Address</label>
                                                <input type="text" name="address" class="form-control"
                                                    placeholder="Enter address" value="{{old('address')}}">
                                                @if ($errors->has('address'))
                                                    <span class="invalid-feedback d-block">
                                                        {{ $errors->first('address') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Opening Balance ({{ \App\Models\MasterOpeningBalance::getCurrentFinancialYear() }})</label>
                                                <input type="number" name="balance" class="form-control"
                                                    placeholder="Enter opening balance" value="{{old('balance', 0)}}">
                                                @if ($errors->has('balance'))
                                                    <span class="invalid-feedback d-block">
                                                        {{ $errors->first('balance') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                 <label>Opening Balance Type</label>
                                                 <select name="balance_type" class="form-control select2" style="width: 100%;">
                                                     <option value="Credit" {{old('balance_type') == 'Credit' ? 'selected' : ''}}>Credit</option>
                                                     <option value="Debit" {{old('balance_type') == 'Debit' ? 'selected' : ''}}>Debit</option>
                                                 </select>
                                                 @if ($errors->has('balance_type'))
                                                     <span class="invalid-feedback d-block">{{ $errors->first('balance_type') }}</span>
                                                 @endif
                                             </div>
                                         </div>
                                         <div class="col-md-6">
                                             <div class="form-group">
                                                 <label>Payment Term (Days)</label>
                                                 <input type="number" name="payment_term_days" class="form-control"
                                                     placeholder="E.g. 120" value="{{old('payment_term_days', 120)}}">
                                                 @if ($errors->has('payment_term_days'))
                                                     <span class="invalid-feedback d-block">{{ $errors->first('payment_term_days') }}</span>
                                                 @endif
                                             </div>
                                         </div>
                                    </div>
                                </div>

                                <!-- Direct Fields (Per Brand Discounts) -->
                                <div class="col-md-12" id="direct_fields" style="display: none;">
                                    <hr>
                                    <h5>Per Brand Discounts (%)</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Brand Name</th>
                                                    <th width="200">Discount Percentage (%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($items['brands'] as $brand)
                                                    <tr>
                                                        <td>{{ $brand->name }}</td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" max="100"
                                                                name="brand_discounts[{{ $brand->id }}]"
                                                                class="form-control form-control-sm"
                                                                value="{{ old('brand_discounts.' . $brand->id, 0) }}">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <hr>
                                </div>

                                <!-- Agent/Shop Fields -->
                                <div class="col-md-6" id="agent_fields" style="display: none;">
                                    <div class="form-group">
                                        <label>Select Parent Agent</label>
                                        <select name="sales_agent_id" class="form-control select2" style="width: 100%;">
                                            <option value="">Select Agent</option>
                                            @foreach($items['agents'] as $agent)
                                                <option value="{{$agent->id}}" {{old('sales_agent_id') == $agent->id ? 'selected' : ''}}>{{$agent->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6" id="agent_comm_fields" style="display: none;">
                                    <div class="form-group">
                                        <label>Password (For Agent Login)</label>
                                        <input type="password" name="password" class="form-control"
                                            placeholder="Enter password">
                                    </div>
                                    <div class="form-group custom-control custom-checkbox">
                                        <input class="custom-control-input" type="checkbox" id="see_price" name="see_price"
                                            value="1" checked>
                                        <label for="see_price" class="custom-control-label">Show Pricing Info</label>
                                    </div>
                                </div>

                                <!-- Shop Details Section -->
                                <div class="col-md-12" id="shop_details_section" style="display: none;">
                                    <hr>
                                    <h5>Shop Details</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Shop Name <span class="text-danger">*</span></label>
                                                <input type="text" name="shop_name" class="form-control"
                                                    placeholder="Enter shop or company name">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Shop Phone Number <span class="text-danger">*</span></label>
                                                <input type="number" name="shop_phone" class="form-control"
                                                    placeholder="E.g. 98XXXXXXXX">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Shop Email Address (Optional)</label>
                                                <input type="email" name="shop_email" class="form-control"
                                                    placeholder="example@gmail.com">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Shop Full Address (Optional)</label>
                                                <textarea name="shop_address" class="form-control" rows="2"
                                                    placeholder="Street, landmark, city..."></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Opening Balance ({{ \App\Models\MasterOpeningBalance::getCurrentFinancialYear() }})</label>
                                                <input type="number" step="0.01" name="balance" class="form-control"
                                                    placeholder="Enter opening balance" value="{{old('balance', 0)}}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                 <label>Opening Balance Type</label>
                                                 <select name="balance_type" class="form-control select2" style="width: 100%;">
                                                     <option value="Credit" {{old('balance_type') == 'Credit' ? 'selected' : ''}}>Credit</option>
                                                     <option value="Debit" {{old('balance_type') == 'Debit' ? 'selected' : ''}}>Debit</option>
                                                 </select>
                                                 @if ($errors->has('balance_type'))
                                                     <span class="invalid-feedback d-block">{{ $errors->first('balance_type') }}</span>
                                                 @endif
                                             </div>
                                         </div>
                                         <div class="col-md-6">
                                             <div class="form-group">
                                                 <label>Payment Term (Days)</label>
                                                 <input type="number" name="payment_term_days" class="form-control"
                                                     placeholder="E.g. 120" value="{{old('payment_term_days', 120)}}">
                                                 @if ($errors->has('payment_term_days'))
                                                     <span class="invalid-feedback d-block">{{ $errors->first('payment_term_days') }}</span>
                                                 @endif
                                             </div>
                                         </div>
                                    </div>
                                    <hr>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control select2" style="width: 100%;">
                                            <option value="1" {{old('status') == '1' ? 'selected' : ''}}>Active</option>
                                            <option value="0" {{old('status') == '0' ? 'selected' : ''}}>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="mt-2" style="float:right">
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </section>
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