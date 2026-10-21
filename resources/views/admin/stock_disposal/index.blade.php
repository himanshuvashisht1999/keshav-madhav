@extends('admin.layouts.app')

@section('title', 'Stock Disposal History')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title">
                <i class="fas fa-trash-alt text-primary mr-2"></i> Stock Disposal History
            </div>
            <div class="erp-header-actions">
                <div class="hud-stat-box">
                    <span class="hud-stat-label">Total disposals</span>
                    <span class="hud-stat-value text-success" id="header_total_disposals">-</span>
                </div>
                <div class="hud-stat-box">
                    <span class="hud-stat-label">Total items</span>
                    <span class="hud-stat-value text-primary" id="header_total_items">-</span>
                </div>
                <a href="{{ route('admin.inventory.stock_disposal.create') }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-plus"></i> New Disposal
                </a>
            </div>
        </div>

        <!-- Table Card -->
        <div class="erp-card">
            <div class="erp-card-body p-2 table-responsive">
                <table id="disposalTable" class="table erp-table table-bordered table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 45px;">#</th>
                            <th>Disposal no.</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th class="text-center">Total items</th>
                            <th>Reason</th>
                            <th class="text-center" style="width: 90px;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        let table = $('#disposalTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.inventory.stock_disposal.list') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center text-muted font-weight-bold'},
                {data: 'disposal_no', name: 'disposal_no', className: 'font-weight-bold'},
                {
                    data: 'created_at',
                    name: 'created_at',
                    render: function(data) {
                        return moment(data).format('D MMM YYYY');
                    }
                },
                {
                    data: 'item_type',
                    name: 'item_type',
                    render: function(data) {
                        return data === 'fabric' 
                            ? '<span class="badge-erp-status badge-erp-info"><i class="fas fa-scroll mr-1"></i> Fabric</span>' 
                            : '<span class="badge-erp-status badge-erp-primary"><i class="fas fa-box mr-1"></i> Domestic</span>';
                    }
                },
                {
                    data: 'items_count',
                    name: 'items_count',
                    className: 'text-center',
                    render: function(data) {
                        return '<span class="badge-erp-status badge-erp-secondary font-weight-bold">' + data + ' Items</span>';
                    }
                },
                {
                    data: 'reason', 
                    name: 'reason',
                    render: function(data) {
                        return '<span class="text-capitalize font-weight-bold">' + data + '</span>';
                    }
                },
                {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'}
            ],
            order: [[2, 'desc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search history...",
                processing: '<i class="fas fa-spinner fa-spin fa-2x text-success"></i>'
            }
        });

        table.on('xhr.dt', function(e, settings, json, xhr) {
            if (json) {
                if (json.total_disposals !== undefined) $('#header_total_disposals').text(Number(json.total_disposals).toLocaleString());
                if (json.total_items !== undefined) $('#header_total_items').text(Number(json.total_items).toLocaleString());
            }
        });

        // Delete Disposal
        $(document).on('click', '.delete-disposal', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Delete Disposal?',
                text: "This will permanently delete the record and RESTORE the stock to inventory.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Yes, Restore Stock & Delete'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('admin/inventory/stock-disposal/delete') }}/" + id,
                        type: "DELETE",
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire('Deleted', res.message, 'success');
                                table.ajax.reload();
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
