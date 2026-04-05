@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- PAGE HEADER -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-3 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-dark">Warehouse Stock</h1>
                        <small class="text-muted">Manage inventory physical locations and transfers</small>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('admin.inventory.warehouse_stock.history') }}" class="btn btn-outline-primary shadow-sm mr-2">
                            <i class="fas fa-history mr-1"></i> Transfer History
                        </a>
                        <button id="btn-transfer" class="btn btn-primary shadow-sm" style="display:none;" data-toggle="modal" data-target="#transferModal">
                            <i class="fas fa-exchange-alt mr-1"></i> Transfer Selected (<span id="sel-count">0</span>)
                        </button>
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
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Warehouse</label>
                                <select id="storeroom_filter" class="form-control select2">
                                    <option value="">All Warehouses</option>
                                    @foreach($storerooms as $storeroom)
                                        <option value="{{ $storeroom->id }}">{{ $storeroom->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Rack</label>
                                <select id="rack_filter" class="form-control select2">
                                    <option value="">All Racks</option>
                                    <!-- Populated via JS -->
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Design No.</label>
                                <select id="design_filter" class="form-control select2">
                                    <option value="">All Design Nos.</option>
                                    @foreach($designs as $design)
                                        <option value="{{ $design->id }}">{{ $design->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Product</label>
                                <select id="product_filter" class="form-control select2">
                                    <option value="">All Products</option>
                                    @foreach($products as $prod)
                                        <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Color</label>
                                <select id="color_filter" class="form-control select2">
                                    <option value="">All Colors</option>
                                    @foreach($colors as $color)
                                        <option value="{{ $color->id }}">{{ $color->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Size Set</label>
                                <select id="size_set_filter" class="form-control select2">
                                    <option value="">All Size Sets</option>
                                    @foreach($size_sets as $set)
                                        <option value="{{ $set->id }}">{{ $set->name }}</option>
                                    @endforeach
                                </select>
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
                                        <th width="5%" class="text-center py-3">
                                            <input type="checkbox" id="checkAll">
                                        </th>
                                        <th class="py-3">Product Name</th>
                                        <th class="py-3">Design No.</th>
                                        <th class="py-3">Size Set</th>
                                        <th class="py-3">Color</th>
                                        <th class="py-3">Location (WH / Rack)</th>
                                        <th class="py-3 text-center">Total Boxes</th>
                                        <th class="py-3 text-center">Total Qty</th>
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

    <!-- Transfer Modal -->
    <div class="modal fade" id="transferModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-shipping-fast mr-2 text-primary"></i> Stock Transfer</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div style="max-height: 300px; overflow-y: auto; padding: 15px; background: #fafafa; border-bottom: 1px solid #eee;">
                        <h6 class="font-weight-bold small text-muted">Confirm selection and quantities:</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Item Details</th>
                                    <th width="100px" class="text-right">Boxes to Move</th>
                                </tr>
                            </thead>
                            <tbody id="transfer-item-list">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3">
                        <form id="form-transfer">
                            @csrf
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Destination Warehouse</label>
                                <select id="dest_storeroom" class="form-control" required>
                                    <option value="">Select Warehouse</option>
                                    @foreach($storerooms as $storeroom)
                                        <option value="{{ $storeroom->id }}">{{ $storeroom->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Destination Rack</label>
                                <select id="dest_rack" name="rack_id" class="form-control" required>
                                    <option value="">Select Rack</option>
                                </select>
                            </div>
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold text-muted mb-1">Transfer Notes (Optional)</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Any additional details..."></textarea>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" id="submit-transfer" class="btn btn-primary shadow-sm px-4">
                        <i class="fas fa-check mr-1"></i> Proceed Transfer
                    </button>
                </div>
            </div>
        </div>
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
                    url: "{{ route('admin.inventory.warehouse_stock.list') }}",
                    data: function (d) {
                        d.storeroom_id = $('#storeroom_filter').val();
                        d.rack_id = $('#rack_filter').val();
                        d.size_set_id = $('#size_set_filter').val();
                        d.design_filter = $('#design_filter').val();
                        d.product_id = $('#product_filter').val();
                        d.color_id = $('#color_filter').val();
                    }
                },
                columns: [
                    { data: 'checkbox', name: 'checkbox', className: 'text-center' },
                    { data: 'product_name', name: 'product_name' },
                    { data: 'design_number', name: 'design_number' },
                    { data: 'size_set_name', name: 'size_set_name' },
                    { data: 'color_name', name: 'color_name' },
                    { data: 'location', name: 'location' },
                    { data: 'total_boxes', name: 'total_boxes', className: 'text-center' },
                    { data: 'total_quantity', name: 'total_quantity', className: 'text-center font-weight-bold' }
                ],
                language: {
                    emptyTable: "No inventory records found",
                    zeroRecords: "No matching records found",
                    processing: '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>'
                }
            });

            // Filter events
            $('#storeroom_filter, #rack_filter, #size_set_filter, #design_filter, #product_filter, #color_filter').on('change', function () {
                table.ajax.reload();
            });

            // Reset filter
            $('#reset_filters').on('click', function () {
                $('#storeroom_filter, #rack_filter, #size_set_filter, #design_filter, #product_filter, #color_filter').val('').trigger('change');
                table.ajax.reload();
            });

            // Dynamic Racks logic for Filters
            $('#storeroom_filter').on('change', function() {
                let wh_id = $(this).val();
                let rack_filter = $('#rack_filter');
                rack_filter.html('<option value="">All Racks</option>');
                if(wh_id) {
                    $.get('{{ url("admin/inventory/warehouse-stock/racks") }}/' + wh_id, function(data) {
                        $.each(data, function(i, rack) {
                            rack_filter.append('<option value="'+rack.id+'">'+rack.name+'</option>');
                        });
                    });
                }
            });

            // Dynamic Racks logic for Transfer Modal
            $('#dest_storeroom').on('change', function() {
                let wh_id = $(this).val();
                let dest_rack = $('#dest_rack');
                dest_rack.html('<option value="">Select Rack</option>');
                if(wh_id) {
                    $.get('{{ url("admin/inventory/warehouse-stock/racks") }}/' + wh_id, function(data) {
                        $.each(data, function(i, rack) {
                            dest_rack.append('<option value="'+rack.id+'">'+rack.name+'</option>');
                        });
                    });
                }
            });

            // Checkbox multi-select logic
            let selectedCartons = new Set();
            
            $(document).on('change', '.carton-checkbox', function() {
                let val = $(this).val();
                if(val === "-") return;
                
                if($(this).is(':checked')) {
                    selectedCartons.add(val);
                } else {
                    selectedCartons.delete(val);
                    $('#checkAll').prop('checked', false);
                }
                updateTransferBtn();
            });

            $('#checkAll').on('change', function() {
                let isChecked = $(this).is(':checked');
                $('.carton-checkbox').each(function() {
                    let val = $(this).val();
                    if(val !== "-") {
                        $(this).prop('checked', isChecked);
                        if(isChecked) selectedCartons.add(val);
                        else selectedCartons.delete(val);
                    }
                });
                updateTransferBtn();
            });

            table.on('draw', function() {
                // Restore selections on pagination/draw
                $('.carton-checkbox').each(function() {
                    if(selectedCartons.has($(this).val())) {
                        $(this).prop('checked', true);
                    }
                });
                // Uncheck checkAll just in case
                $('#checkAll').prop('checked', false);
            });

            function updateTransferBtn() {
                let count = selectedCartons.size;
                $('#sel-count').text(count);
                if(count > 0) {
                    $('#btn-transfer').show();
                    
                    // Update Modal List
                    let list = $('#transfer-item-list').empty();
                    $('.carton-checkbox:checked').each(function() {
                        let $data = $(this).data();
                        list.append(`
                            <tr class="border-bottom">
                                <td class="py-2">
                                    <div class="font-weight-bold text-dark small">${$data.designNo} - ${$data.productName}</div>
                                    <div class="text-muted" style="font-size: 11px;">Color: ${$data.colorName} | Size: ${$data.sizeSetName}</div>
                                </td>
                                <td class="py-2">
                                    <input type="number" class="form-control form-control-sm transfer-qty text-right" 
                                        data-cartons="${$(this).val()}"
                                        value="${$data.totalBoxes}" min="1" max="${$data.totalBoxes}"
                                        style="background: white; border: 1px solid #ddd;">
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    $('#btn-transfer').hide();
                }
            }

            // Submit Transfer
            $('#submit-transfer').on('click', function() {
                let dest_rack = $('#dest_rack').val();
                if(!dest_rack) {
                    toastr.error('Please select a destination rack.');
                    return;
                }
                
                let btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing');

                let transfers = [];
                $('.transfer-qty').each(function() {
                    let fullList = $(this).data('cartons').split(',');
                    let qty = parseInt($(this).val());
                    // Sub-list of cartons based on quantity
                    let selectedForTransfer = fullList.slice(0, qty);
                    transfers.push(...selectedForTransfer);
                });

                if(transfers.length === 0) {
                    toastr.error('No items to transfer.');
                    btn.prop('disabled', false).text('Confirm Transfer');
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.inventory.warehouse_stock.transfer') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        carton_ids: transfers.join(','),
                        rack_id: dest_rack,
                        notes: $('textarea[name="notes"]').val()
                    },
                    success: function(res) {
                        btn.prop('disabled', false).text('Proceed Transfer');
                        if(res.success) {
                            toastr.success(res.message);
                            $('#transferModal').modal('hide');
                            $('textarea[name="notes"]').val('');
                            selectedCartons.clear();
                            updateTransferBtn();
                            table.ajax.reload();
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function(err) {
                        btn.prop('disabled', false).text('Proceed Transfer');
                        toastr.error('An error occurred during transfer.');
                    }
                });
            });
        });
    </script>
@endsection