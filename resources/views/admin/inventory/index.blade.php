@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- PAGE HEADER -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-3 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-dark">Domestic Inventory</h1>
                        <small class="text-muted">Manage and track packed domestic orders in inventory</small>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONTENT -->
        <section class="content">
            <div class="container-fluid">
                <!-- SINGLE CONSOLIDATED FILTER CARD -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-body bg-light rounded p-4">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="small font-weight-bold text-muted mb-1">Design Number</label>
                                <input type="text" id="design_number" class="form-control" placeholder="Search Design...">
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Size Set</label>
                                <select id="size_set_filter" class="form-control select2">
                                    <option value="">All Size Sets</option>
                                    @foreach($size_sets as $set)
                                        <option value="{{ $set->size_set_id }}">{{ $set->size_set_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Product</label>
                                <select id="product_filter" class="form-control select2">
                                    <option value="">All Products</option>
                                    @foreach($products as $prod)
                                        <option value="{{ $prod->product_id }}">{{ $prod->product_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Min MRP</label>
                                <input type="number" id="mrp_filter" class="form-control" placeholder="Min MRP">
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Min Selling Price</label>
                                <input type="number" id="selling_price_filter" class="form-control" placeholder="Min Price">
                            </div>
                            <div class="col-md-1">
                                <button id="reset_filters" class="btn btn-secondary shadow-sm btn-block">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLE CARD -->
                <div class="card shadow border-0" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="inventoryTable" class="table table-hover mb-0">
                                <thead class="bg-light contrast-text">
                                    <tr>
                                        <th width="5%" class="text-center py-3">#</th>
                                        <th class="py-3">Product Name</th>
                                        <th class="py-3">Design Number</th>
                                        <th class="py-3">Size Set</th>
                                        <th class="py-3">MRP</th>
                                        <th class="py-3">Selling Price</th>
                                        <th class="text-center py-3">Total Boxes</th>
                                        <th class="text-center py-3">Total Qty</th>
                                        <th class="text-center py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        .contrast-text th {
            color: #444;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            vertical-align: middle;
            padding: 1rem 0.75rem;
        }

        .badge {
            padding: 0.5em 0.8em;
            border-radius: 6px;
        }

        .form-control {
            border-radius: 8px;
        }
    </style>

    <script>
        $(function () {
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            let table = $('#inventoryTable').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: {
                    url: "{{ route('admin.inventory.list') }}",
                    data: function (d) {
                        d.size_set_id = $('#size_set_filter').val();
                        d.product_id = $('#product_filter').val();
                        d.mrp = $('#mrp_filter').val();
                        d.selling_price = $('#selling_price_filter').val();
                        d.design_number = $('#design_number').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'id', className: 'text-center text-muted' },
                    { data: 'product_name', name: 'product_name' },
                    { data: 'design_number', name: 'design_number' },
                    { data: 'size_set_name', name: 'size_set_name' },
                    { data: 'mrp_display', name: 'mrp' },
                    { data: 'selling_price_display', name: 'selling_price' },
                    { data: 'total_boxes', name: 'total_boxes', className: 'text-center font-weight-bold' },
                    { data: 'total_qty', name: 'total_qty', className: 'text-center font-weight-bold' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                language: {
                    emptyTable: "No inventory records found",
                    zeroRecords: "No matching records found",
                    processing: '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>'
                }
            });

            // Filter events
            $('#design_number, #size_set_filter, #product_filter, #mrp_filter, #selling_price_filter').on('keyup change', function () {
                table.ajax.reload();
            });

            // Reset filter
            $('#reset_filters').on('click', function () {
                $('#design_number, #mrp_filter, #selling_price_filter').val('');
                $('#size_set_filter, #product_filter').val('').trigger('change');
                table.ajax.reload();
            });
        });
    </script>
@endsection