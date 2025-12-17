@extends('admin.layouts.app')

@section('content')
<style>
    h5{
        color: #007bff !important;
    }
</style>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-3 text-start">
                    <h5 class="">No. RJ 1</h5>
                </div>
                <div class="col-sm-6 text-center">
                    <h1 class="text-center">Status of Sales Orders</h1>
                </div>
                <div class="col-sm-3 text-end">
                    <h5 class="">Date : 12 Dec 2025 3:00 PM</h5>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card card-default">
                

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="adjustmentTable" class="table table-striped table-bordered" data-ajax-url="{{ route('admin.purchase_order.adjustmentShipment') }}">
                            <thead>
                                <tr>
                                    <th>Sr No.</th>
                                    <th>Date of Order</th>
                                    <th>Customer</th>
                                    <th>Order Number</th>
                                    <th>No. of Pcs in order</th>
                                    <th>Lot Numbers</th>
                                    <th>No. of Pcs in each Lot</th>
                                    <th>Status</th>
                                    <th>Is it delayed?</th>                                
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $sr_no = 1; ?>
                                @foreach($data as $single_data)
                                <tr>
                                    <td>{{$sr_no}}</td>
                                    <td>{{$single_data['order_date']}}</td>
                                    <td>{{$single_data['customer']}}</td>
                                    <td>{{$single_data['order_no']}}</td>
                                    <td>{{$single_data['total_pcs_in_order']}}</td>
                                    <td>{{$single_data['lot_no']}}</td>
                                    <td>100</td>
                                    <td>Pending</td>
                                    <td>No</td>
                                    <td>Action</td>
                                </tr>
                                <?php $sr_no++ ?>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection