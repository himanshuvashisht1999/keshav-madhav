@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper erp-page p-2">
        <div class="container-fluid p-0">
            <!-- PAGE HEADER -->
            <div class="erp-header-bar mb-2">
                <div>
                    <h1 class="erp-page-title">Stock Transfer History</h1>
                    <p class="erp-page-subtitle">Recorded history of all warehouse inventory movements</p>
                </div>
                <div class="erp-header-actions">
                    <a href="{{ route('admin.inventory.warehouse_stock') }}" class="btn-erp btn-erp-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Warehouse Stock
                    </a>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="erp-card">
                <div class="erp-card-body p-0">
                    <div class="table-responsive">
                        <table id="historyTable" class="erp-table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                     <th style="width: 45px;" class="text-center">#</th>
                                     <th>Date & time</th>
                                     <th>Product details</th>
                                     <th>From location</th>
                                     <th>Destination</th>
                                     <th class="text-center">Boxes</th>
                                     <th class="text-center" style="width: 70px;">Action</th>
                                 </tr>
                             </thead>
                             <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function () {
            $('#historyTable').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: "{{ route('admin.inventory.warehouse_stock.history.list') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center' },
                    { 
                        data: 'created_at', 
                        name: 'created_at',
                        render: function(data) {
                            if(!data) return '';
                            let date = new Date(data);
                            return date.toLocaleString();
                        }
                    },
                    { data: 'product_details', name: 'product_details' },
                    { data: 'from_location', name: 'from_location' },
                    { data: 'to_location', name: 'to_location' },
                    { data: 'box_quantity', name: 'box_quantity', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                language: {
                    processing: '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>'
                }
            });
        });
    </script>
@endsection
