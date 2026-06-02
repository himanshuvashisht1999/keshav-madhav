@extends('sales_agent.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Create Order (Master View)</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-primary text-white text-center py-3">
                            <h4 class="mb-0 font-weight-bold"><i class="fas fa-shopping-cart mr-2"></i> Select Order Details</h4>
                        </div>
                        <div class="card-body p-4 bg-light">
                            <form method="GET" action="{{ route('agent.orders.create') }}">

                                <div class="form-group mb-4">
                                    <label class="font-weight-bold text-dark"><i class="fas fa-users mr-1 text-primary"></i> Order For <span class="text-danger">*</span></label>
                                    <div class="d-flex bg-white p-2 border rounded" style="gap: 15px;">
                                        <div class="custom-control custom-radio">
                                            <input class="custom-control-input" type="radio" id="partyCustomer" name="party_type" value="customer" {{ $party_type == 'customer' ? 'checked' : '' }}>
                                            <label for="partyCustomer" class="custom-control-label">Customer</label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                            <input class="custom-control-input" type="radio" id="partyVendor" name="party_type" value="vendor" {{ $party_type == 'vendor' ? 'checked' : '' }}>
                                            <label for="partyVendor" class="custom-control-label">Vendor</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-4" id="customerDiv" style="display: {{ $party_type == 'customer' ? 'block' : 'none' }};">
                                    <label class="font-weight-bold text-dark"><i class="fas fa-store mr-1 text-primary"></i> Select Customer <span class="text-danger">*</span></label>
                                    <select name="shop_id" class="form-control select2" style="border-radius: 8px;" id="customerSelect">
                                        <option value="">-- Select Customer --</option>
                                        @foreach($shops as $shop)
                                            <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mb-4" id="vendorDiv" style="display: {{ $party_type == 'vendor' ? 'block' : 'none' }};">
                                    <label class="font-weight-bold text-dark"><i class="fas fa-industry mr-1 text-primary"></i> Select Vendor <span class="text-danger">*</span></label>
                                    <select name="vendor_id" class="form-control select2" style="border-radius: 8px;" id="vendorSelect">
                                        <option value="">-- Select Vendor --</option>
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="border-top pt-4 d-flex justify-content-between">
                                    <a href="{{ route('agent.dashboard') }}" class="btn btn-outline-secondary px-4" style="border-radius: 8px;">
                                        <i class="fas fa-times mr-2"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary px-5 shadow-sm" style="border-radius: 8px;">
                                        Proceed <i class="fas fa-arrow-right ml-2"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    $(function() {
        $('.select2').select2({ theme: 'bootstrap4' });
        
        $('input[name="party_type"]').change(function() {
            if ($(this).val() === 'customer') {
                $('#customerDiv').show();
                $('#vendorDiv').hide();
                $('#customerSelect').attr('name', 'shop_id');
                $('#vendorSelect').removeAttr('name');
            } else {
                $('#customerDiv').hide();
                $('#vendorDiv').show();
                $('#vendorSelect').attr('name', 'shop_id');
                $('#customerSelect').removeAttr('name');
            }
        });
        
        // Trigger on load
        $('input[name="party_type"]:checked').trigger('change');
    });
</script>
@endpush
@endsection
