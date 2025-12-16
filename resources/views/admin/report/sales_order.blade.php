@extends('admin.layouts.app')

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-2 text-start">
                    <h1 class="text-center">No. RJ 1</h1>
                </div>
                <div class="col-sm-5 text-center">
                    <h1 class="text-center">Status of Sales Orders</h1>
                </div>
                <div class="col-sm-5 text-end">
                    <h1 class="text-center">Date : 12 Dec 2025 3:00 PM</h1>
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
                                    <th>Status</th>
                                    <th>Is it delayed?</th>                                
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>22 May 2025</td>
                                    <td>Himanshu</td>
                                    <td>1234</td>
                                    <td>1232</td>
                                    <td>2000</td>
                                    <td>Pending</td>
                                    <td>No</td>
                                    <td>Action</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection