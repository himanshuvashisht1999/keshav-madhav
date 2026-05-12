@extends('admin.layouts.app')

@section('title', 'Stock Disposal History')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Stock Disposal History</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.inventory.stock_disposal.create') }}" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus mr-1"></i> New Disposal
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary card-outline shadow-sm border-0">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="disposalTable" class="table table-bordered table-striped table-hover w-100">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="40">#</th>
                                            <th>Disposal No</th>
                                            <th>Date</th>
                                            <th>User</th>
                                            <th>Type</th>
                                            <th>Total Items</th>
                                            <th>Reason</th>
                                            <th width="60">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold">Disposal Details: <span id="modal_disp_no"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3 pb-3 border-bottom">
                    <div class="col-md-6">
                        <label class="text-muted small text-uppercase mb-1">Reason</label>
                        <p class="font-weight-bold text-dark mb-0" id="modal_reason"></p>
                    </div>
                    <div class="col-md-6 text-md-right">
                        <label class="text-muted small text-uppercase mb-1">Remarks</label>
                        <p class="text-dark mb-0" id="modal_remarks"></p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Item / Barcode</th>
                                <th width="150" class="text-right">Quantity</th>
                            </tr>
                        </thead>
                        <tbody id="modal_items_body"></tbody>
                    </table>
                </div>
            </div>
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
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'disposal_no', name: 'disposal_no'},
                {
                    data: 'created_at',
                    name: 'created_at',
                    render: function(data) {
                        return moment(data).format('D MMM YYYY, h:mm A');
                    }
                },
                {data: 'user.name', name: 'user.name', defaultContent: '<span class="text-muted">N/A</span>'},
                {
                    data: 'item_type',
                    name: 'item_type',
                    render: function(data) {
                        return data === 'fabric' 
                            ? '<span class="badge badge-info px-2 py-1"><i class="fas fa-scroll mr-1"></i> Fabric</span>' 
                            : '<span class="badge badge-primary px-2 py-1"><i class="fas fa-box mr-1"></i> Domestic</span>';
                    }
                },
                {
                    data: 'items_count',
                    name: 'items_count',
                    render: function(data) {
                        return '<span class="badge badge-secondary px-2 py-1">' + data + ' Items</span>';
                    }
                },
                {
                    data: 'reason', 
                    name: 'reason',
                    render: function(data) {
                        return '<span class="text-capitalize font-weight-bold">' + data + '</span>';
                    }
                },
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            order: [[2, 'desc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search history..."
            }
        });

        // View Details
        $(document).on('click', '.view-details', function() {
            let id = $(this).data('id');
            $.get("{{ url('admin/inventory/stock-disposal/show') }}/" + id, function(res) {
                $('#modal_disp_no').text(res.disposal_no);
                $('#modal_reason').text(res.reason);
                $('#modal_remarks').text(res.remarks || 'No remarks provided.');
                
                let rows = '';
                res.items.forEach(item => {
                    let itemName = '-';
                    if (res.item_type === 'fabric') {
                        itemName = `Roll: <span class="badge badge-info">${item.fabric_receipt_detail?.roll_number}</span> 
                                   <div class="small text-muted">${item.fabric_receipt_detail?.fabric?.name || 'Fabric'}</div>`;
                    } else {
                        itemName = `<span class="font-weight-bold">${item.domestic_inventory?.product?.design_number || 'Product'}</span>
                                   <div class="small text-muted">${item.domestic_inventory?.color?.name} / ${item.domestic_inventory?.size_set?.name}</div>`;
                    }
                    rows += `<tr>
                        <td>${itemName} <br><small class="text-muted">Barcode: ${item.barcode || 'N/A'}</small></td>
                        <td class="text-right"><strong>${item.quantity}</strong> ${res.item_type === 'fabric' ? 'Mtr' : 'Boxes'}</td>
                    </tr>`;
                });
                $('#modal_items_body').html(rows);
                $('#detailsModal').modal('show');
            });
        });

        // Delete Disposal
        $(document).on('click', '.delete-disposal', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Delete Disposal?',
                text: "This will permanently delete the record and RESTORE the stock to inventory.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
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
<style>
    .badge { font-weight: 500; font-size: 85%; }
    code { background: #f8f9fa; padding: 2px 5px; border-radius: 4px; border: 1px solid #eee; }
</style>
@endsection
