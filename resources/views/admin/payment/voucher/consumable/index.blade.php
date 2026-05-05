@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Consumable Voucher</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item active">Consumable Voucher</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card card-outline card-primary shadow-sm">
                            <div class="card-body">
                                <table id="consumableVoucherTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Voucher Date</th>
                                            <th>Voucher No</th>
                                            <th>Master Name</th>
                                            <th>Total Amount</th>
                                            <th>Doc</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            $('#consumableVoucherTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                lengthChange: true,
                autoWidth: false,
                dom: 'lBfrtip',
                buttons: [
                    {
                        text: 'Add Consumable Voucher',
                        className: 'btn btn-primary',
                        action: function (e, dt, node, config) {
                            window.location.href = "{{ route('admin.payment.voucher.consumable.create') }}";
                        }
                    }
                ],
                ajax: {
                    url: '{!! route('admin.payment.voucher.consumable.indexList') !!}',
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'voucher_date', name: 'voucher_date' },
                    { data: 'voucher_number', name: 'voucher_number' },
                    { data: 'consumable_good.name', name: 'consumableGood.name' },
                    { data: 'total_amount', name: 'total_amount' },
                    { data: 'document', name: 'document', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });
        });
    </script>
@endsection
