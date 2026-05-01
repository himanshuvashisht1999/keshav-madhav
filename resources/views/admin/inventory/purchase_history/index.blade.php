@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Inventory Purchase History</h3>
                    </div>
                    <div class="card-body">
                        <table id="purchaseHistoryTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Source</th>
                                    <th>Sub Total</th>
                                    <th>GST</th>
                                    <th>Other</th>
                                    <th>Discount</th>
                                    <th>Total Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(function () {
        $('#purchaseHistoryTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.inventory.purchase_history.list') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                { data: 'created_at', name: 'created_at', render: function(data) {
                    return moment(data).format('DD-MM-YYYY HH:mm');
                }},
                { data: 'source', name: 'source' },
                { data: 'sub_total', name: 'sub_total' },
                { data: 'gst', name: 'gst' },
                { data: 'other_amount', name: 'other_amount' },
                { data: 'discount', name: 'discount' },
                { data: 'total_amount', name: 'total_amount' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endpush
@endsection
