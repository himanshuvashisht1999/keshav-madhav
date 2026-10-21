@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Slim ERP Header Bar -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title">
                <i class="fas fa-shopping-cart text-primary"></i> Purchase History
            </div>
            <div class="erp-header-actions">
                <div class="hud-stat-box">
                    <span class="hud-stat-label">Total purchases</span>
                    <span class="hud-stat-value text-success" id="header_total_purchases">-</span>
                </div>
                <div class="hud-stat-box">
                    <span class="hud-stat-label">Total boxes</span>
                    <span class="hud-stat-value text-primary" id="header_total_boxes">-</span>
                </div>
                <div class="hud-stat-box">
                    <span class="hud-stat-label">Total amount</span>
                    <span class="hud-stat-value text-dark" id="header_total_amount">-</span>
                </div>
                <a href="{{ route('admin.inventory.purchase') }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-plus"></i> New Purchase
                </a>
                <a href="{{ route('admin.inventory.index') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-arrow-left"></i> Back to Inventory
                </a>
            </div>
        </div>

        <!-- Compact ERP Filter Bar -->
        <div class="erp-filter-bar mb-2">
            <div class="row align-items-end">
                <div class="col-md-2 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> Start Date</label>
                    <input type="date" id="start_date" class="form-control erp-input">
                </div>
                <div class="col-md-2 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-check mr-1"></i> End Date</label>
                    <input type="date" id="end_date" class="form-control erp-input">
                </div>
                <div class="col-md-2 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-user-tie mr-1"></i> Vendor</label>
                    <select id="vendor_filter" class="form-control select2 erp-input" style="width: 100%;">
                        <option value="">-- ALL VENDORS --</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->company_name ?? $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-building mr-1"></i> Customer</label>
                    <select id="customer_filter" class="form-control select2 erp-input" style="width: 100%;">
                        <option value="">-- ALL CUSTOMERS --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->company_name ?? $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-file-invoice mr-1"></i> Bill No.</label>
                    <input type="text" id="bill_no_filter" class="form-control erp-input" placeholder="Search bill..." autocomplete="off">
                </div>
                <div class="col-md-1 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-barcode mr-1"></i> PO No.</label>
                    <input type="text" id="po_number_filter" class="form-control erp-input" placeholder="PO..." autocomplete="off">
                </div>
                <div class="col-auto mb-1 ml-auto">
                    <div class="erp-filter-actions">
                        <button id="reset_filters" class="btn-erp btn-erp-outline" title="Reset Filters">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="erp-card">
            <div class="erp-card-body p-2 table-responsive">
                <table id="purchaseHistoryTable" class="erp-table table table-bordered table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">#</th>
                            <th>Purchase date</th>
                            <th>Bill no.</th>
                            <th>Source</th>
                            <th>Production PO</th>
                            <th style="text-align: center;">Total boxes</th>
                            <th style="text-align: right;">Total amount</th>
                            <th style="width: 130px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(function () {
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        const table = $('#purchaseHistoryTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.inventory.purchase_history.list') }}",
                data: function(d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.vendor_id = $('#vendor_filter').val();
                    d.customer_id = $('#customer_filter').val();
                    d.po_number = $('#po_number_filter').val();
                    d.bill_no = $('#bill_no_filter').val();
                }
            },
            order: [[1, 'desc']],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center font-weight-bold', orderable: false, searchable: false },
                { 
                    data: 'purchase_date', 
                    name: 'purchase_date', 
                    render: function(data, type, row) {
                        let dateStr = data ? data : row.created_at;
                        return `<div class="font-weight-bold text-dark">${moment(dateStr).format('DD MMM YYYY')}</div>`;
                    }
                },
                { 
                    data: 'bill_no', 
                    name: 'bill_no',
                    render: function(data) {
                        return data ? `<span class="font-weight-bold text-dark">${data}</span>` : '<span class="text-muted">-</span>';
                    }
                },
                { 
                    data: 'source', 
                    name: 'source',
                    orderable: false, 
                    searchable: false,
                    render: function(data) {
                        return `<span class="badge badge-light border px-2 py-1 text-uppercase">${data}</span>`;
                    }
                },
                { 
                    data: 'production_po', 
                    name: 'production_po',
                    render: function(data) {
                        return data.includes('Manual') ? `<span class="text-muted">${data}</span>` : `<span class="badge badge-light border text-primary font-weight-bold px-2 py-1">${data}</span>`;
                    }
                },
                { 
                    data: 'total_boxes', 
                    name: 'total_boxes',
                    searchable: false,
                    className: 'text-center',
                    render: (d) => `<span class="badge badge-light border font-weight-bold px-2 py-1">${d || 0} Boxes</span>`
                },
                { 
                    data: 'total_amount', 
                    name: 'total_amount', 
                    className: 'text-right',
                    render: (d) => `<span class="font-weight-bold" style="color: #05421c; font-size: 0.95rem;">₹${parseFloat(d).toLocaleString()}</span>` 
                },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search records...",
                paginate: {
                    previous: '<i class="fas fa-chevron-left"></i>',
                    next: '<i class="fas fa-chevron-right"></i>'
                }
            }
        });

        table.on('xhr.dt', function(e, settings, json, xhr) {
            if (json) {
                if (json.total_purchases !== undefined) $('#header_total_purchases').text(Number(json.total_purchases).toLocaleString());
                if (json.total_boxes !== undefined) $('#header_total_boxes').text(Number(json.total_boxes).toLocaleString());
                if (json.total_amount !== undefined) $('#header_total_amount').text('₹ ' + Number(json.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            }
        });

        // Filter triggers
        $('#start_date, #end_date, #po_number_filter, #bill_no_filter').on('change keyup', function () {
            table.draw();
        });

        $('#vendor_filter, #customer_filter').on('change', function () {
            table.draw();
        });

        // Reset filter
        $('#reset_filters').on('click', function () {
            $('#start_date, #end_date, #po_number_filter, #bill_no_filter').val('');
            $('#vendor_filter, #customer_filter').val('').trigger('change');
            table.draw();
        });

        // Delete functionality
        $(document).on('click', '.btn-delete-purchase', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete the purchase and revert the added stock levels!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('admin/inventory/purchase-history') }}/${id}/delete`,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                table.ajax.reload();
                            } else {
                                toastr.error(response.message);
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
@endsection
