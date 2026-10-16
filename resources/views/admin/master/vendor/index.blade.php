@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-truck text-primary"></i> Manage Vendors
        </div>
        <div class="erp-header-actions d-flex align-items-center" style="gap: 6px;">
            <select class="form-control form-control-sm erp-filter-select" name="balance_type" id="balance_type" style="width: 125px; height: 31px; display: inline-block;">
                <option value="">All Balance</option>
                <option value="credit">Credit (Cr)</option>
                <option value="debit">Debit (Dr)</option>
            </select>
            <a href="javascript:void(0)" id="download-pdf-btn" class="btn-erp btn-erp-outline" title="Download PDF">
                <i class="fas fa-file-pdf text-danger"></i> PDF
            </a>
            <a href="javascript:void(0)" id="download-excel-btn" class="btn-erp btn-erp-outline" title="Download Excel">
                <i class="fas fa-file-excel text-success"></i> Excel
            </a>
            <a href="{{ route('admin.master.vendor.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Add Vendor
            </a>
        </div>
    </div>

    <!-- Summary Metrics Strip -->
    <div class="row mb-2">
        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Opening</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;">
                            <span id="opening-balance-text">₹ {{ number_format(abs($total_opening_balance), 2) }} {{ $total_opening_balance >= 0 ? 'Cr' : 'Dr' }}</span>
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Opening
                </span>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Current Balance</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;">
                            <span id="current-balance-text">₹ {{ number_format(abs($total_current_balance), 2) }} {{ $total_current_balance >= 0 ? 'Cr' : 'Dr' }}</span>
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Balance
                </span>
            </div>
        </div>
    </div>

    <!-- Main Data Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table id="customers" class="erp-table table table-bordered table-hover">
                <thead>
                    <tr role="row" class="filter">
                        <td></td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="name" id="name" placeholder="Search name..." autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="phone" id="phone" placeholder="Search phone..." autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="email" id="email" placeholder="Search email..." autocomplete="off">
                        </td>
                        <td data-label="From Date">
                            <input type="date" class="form-control erp-filter-input" name="start_date" id="start_date" data-label="From Date" autocomplete="off" title="Opening Date / Start Date">
                        </td>
                        <td data-label="To Date">
                            <input type="date" class="form-control erp-filter-input" name="end_date" id="end_date" data-label="To Date" autocomplete="off" title="Closing Date / End Date">
                        </td>
                        <td>
                            <select class="form-control erp-filter-select" name="status" id="status" autocomplete="off">
                                <option value="">ALL</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Opening Balance</th>
                        <th>Balance</th>
                        <th style="width: 90px;" class="text-center">Status</th>
                        <th style="width: 110px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(function () {
        var oTable = $('#customers').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: false,
            ordering: false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            pageLength: 25,
            ajax: {
                url: '{!! route('admin.master.vendor.indexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.name = $('#name').val();
                    d.phone = $('#phone').val();
                    d.email = $('#email').val();
                    d.status = $('#status').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.balance_type = $('#balance_type').val();
                },
                orderable: false
            },
            drawCallback: function(settings) {
                var json = settings.json;
                if (json) {
                    let opBal = json.total_opening_balance || 0;
                    let currBal = json.total_current_balance || 0;
                    
                    let opType = opBal >= 0 ? 'Cr' : 'Dr';
                    let currType = currBal >= 0 ? 'Cr' : 'Dr';
                    
                    $('#opening-balance-text').text('₹ ' + Math.abs(opBal).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ' + opType);
                    $('#current-balance-text').text('₹ ' + Math.abs(currBal).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ' + currType);
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'phone', name: 'phone'},
                {data: 'email', name: 'email'},
                {data: 'opening_balance', name: 'opening_balance'},
                {data: 'balance', name: 'balance'},
                {data: 'status', name: 'status', className: 'text-center'},
                {data: 'action', name: 'action', searchable: false, className: 'text-center'}
            ]
        });

        $('#download-pdf-btn').on('click', function (e) {
            e.preventDefault();
            var name = $('#name').val() || '';
            var phone = $('#phone').val() || '';
            var email = $('#email').val() || '';
            var status = $('#status').val() || '';
            var start_date = $('#start_date').val() || '';
            var end_date = $('#end_date').val() || '';
            var balance_type = $('#balance_type').val() || '';
            
            var url = "{{ route('admin.master.vendor.downloadPdf') }}?" + 
                "name=" + encodeURIComponent(name) + 
                "&phone=" + encodeURIComponent(phone) + 
                "&email=" + encodeURIComponent(email) + 
                "&status=" + encodeURIComponent(status) +
                "&start_date=" + encodeURIComponent(start_date) +
                "&end_date=" + encodeURIComponent(end_date) +
                "&balance_type=" + encodeURIComponent(balance_type);
                
            window.location.href = url;
        });

        $('#download-excel-btn').on('click', function (e) {
            e.preventDefault();
            var name = $('#name').val() || '';
            var phone = $('#phone').val() || '';
            var email = $('#email').val() || '';
            var status = $('#status').val() || '';
            var start_date = $('#start_date').val() || '';
            var end_date = $('#end_date').val() || '';
            var balance_type = $('#balance_type').val() || '';
            
            var url = "{{ route('admin.master.vendor.downloadExcel') }}?" + 
                "name=" + encodeURIComponent(name) + 
                "&phone=" + encodeURIComponent(phone) + 
                "&email=" + encodeURIComponent(email) + 
                "&status=" + encodeURIComponent(status) +
                "&start_date=" + encodeURIComponent(start_date) +
                "&end_date=" + encodeURIComponent(end_date) +
                "&balance_type=" + encodeURIComponent(balance_type);
                
            window.location.href = url;
        });

        $('#name, #phone, #email').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#status, #start_date, #end_date, #balance_type').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
    });

    function deleteData(id){
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('admin.master.vendor.delete') }}?id=" + id;
            }
        });
    }
</script>
@endsection
