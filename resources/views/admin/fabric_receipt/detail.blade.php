@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Fabric Receipt Detail</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Fabric Receipt Detail</li>
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
                <div class="card-header">
                    <h3 class="card-title">Fabric Receipt Detail</h3>
                </div>
                <form action="{{route('admin.fabric_receipt.storeDetail')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{$data->id}}" >
                    <div class="card-body">
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Purchase Order</label>
                                    <select name="purchase_order_id" id="purchase_order_id" class="form-control select2" style="width: 100%;">
                                        @foreach($purchase_orders as $single_data)
                                        <option value="{{$single_data->id}}">{{$single_data->sku}}</option>
                                        @endforeach
                                        
                                    </select>
                                    @if ($errors->has('purchase_order_id'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('purchase_order_id') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fabric SKU</label>
                                    <select name="fabric_sku" id="fabric_sku" class="form-control select2" style="width: 100%;">
                                        @foreach($purchase_order_items as $single_data)
                                        <option value="{{$single_data->id}}" >{{$single_data->fabric_sku}}</option>
                                        @endforeach
                                        
                                    </select>
                                    @if ($errors->has('vendor_id'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('vendor_id') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="meter">Meter</label>
                                    <input type="number" name="meter" id="meter" class="form-control" placeholder="Enter meters" >
                                    @if ($errors->has('meter'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('meter') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="roll">Rolls / Boxes</label>
                                    <input type="number" name="roll" id="roll" class="form-control" placeholder="Enter rolls" >
                                    @if ($errors->has('roll'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('roll') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mt-2" style="float:right">
                                    <button type="submit" class="btn btn-success">
                                        Save & Add New
                                    </button>
                                    <a href="{{ route('admin.fabric_receipt.index') }}" class="btn btn-danger">
                                        Exit Without Save
                                    </a>
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
    $('#purchase_order_id').on('change', function () {
        var purchaseOrderId = $(this).val();

        if (purchaseOrderId) {
            var url = "{{ route('admin.fabric_receipt.items', ':id') }}";
            url = url.replace(':id', purchaseOrderId);
            $.ajax({
                url: url,
                type: "GET",
                dataType: "json",
                success: function (data) {
                    $('#fabric_sku').empty(); // clear old options
                    $('#fabric_sku').append('<option value="">Select Fabric SKU</option>');
                    $.each(data, function (key, value) {
                        $('#fabric_sku').append('<option value="'+ value.id +'">'+ value.fabric_sku +'</option>');
                    });
                    $('#fabric_sku').trigger('change'); // refresh select2 if used
                }
            });
        } else {
            $('#fabric_sku').empty();
        }
    });
});
</script>

@endsection
