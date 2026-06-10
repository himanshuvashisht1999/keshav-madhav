@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-4">
                    <h1>Manage Vendor</h1>
                </div>
                <div class="col-sm-8">
                    <div class="d-flex justify-content-end">
                        <div class="info-box bg-light border-0 shadow-none m-0 mr-2" style="min-height: 50px; padding: 5px;">
                            <span class="info-box-icon bg-info" style="width: 40px; font-size: 1rem;"><i class="fas fa-truck"></i></span>
                            <div class="info-box-content" style="padding: 0 10px;">
                                <span class="info-box-text text-muted small">Opening ({{ \App\Models\MasterOpeningBalance::getCurrentFinancialYear() }})</span>
                                <span class="info-box-number" style="font-size: 0.9rem;">₹ {{ number_format(abs($total_opening_balance), 2) }} {{ $total_opening_balance >= 0 ? 'Cr' : 'Dr' }}</span>
                            </div>
                        </div>
                        <div class="info-box bg-light border-0 shadow-none m-0" style="min-height: 50px; padding: 5px;">
                            <span class="info-box-icon bg-success" style="width: 40px; font-size: 1rem;"><i class="fas fa-wallet"></i></span>
                            <div class="info-box-content" style="padding: 0 10px;">
                                <span class="info-box-text text-muted small">Current Balance</span>
                                <span class="info-box-number" style="font-size: 0.9rem;">₹ {{ number_format(abs($total_current_balance), 2) }} {{ $total_current_balance >= 0 ? 'Cr' : 'Dr' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- SELECT2 EXAMPLE -->
            <div class="card card-default ">
                 <!-- <div class="row" >
                    <div class="col-9 card-header">
                        <h3 class="card-title">Manage Vendors</h3>
                    </div>
                    <div class="col-3 card-header">
                        <a href="{{route('admin.master.vendor.create')}}" class="btn btn-primary" style=" float: right;  width: max-content;">Add Vendor</a>
                    </div>
                </div> -->
                
                <div class="card-body table-responsive">
                <table id="customers" class="table table-bordered table-hover">
                  <thead>
                    <tr role="row" class="filter">
                        <td>
                        </td>
                        <td>
                            <input type="text" class="form-control" name="name" id="name" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="phone" id="phone" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="email" id="email" autocomplete="off">
                        </td>
                        <td>
                            <select class="form-control" name="status" id="status" autocomplete="off">
                                <option value="">ALL</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </td>
                        <td>
                        </td>
                        <td>
                        </td>
                        <td>
                        </td>
                    </tr>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Opening Balance</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                  </thead>
                  <tbody>
                  <!-- <tr>
                    <td>1</td>
                    <td>wefds</td>
                    <td>Win 95+</td>
                    <td> 4</td>
                    <td>X</td>
                  </tr> -->
                  
                  </tbody>
                  
                </table>
              </div>
            </div>
        </div>
    </section>
</div>
<script>
    $(function () {
        var i = 1;
        var oTable = $('#customers').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: true,
            ordering:false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            "pageLength":25,
            ajax: {
                url: '{!! route('admin.master.vendor.indexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.name = $('#name').val();
                    d.phone = $('#phone').val();
                    d.email = $('#email').val();
					d.status = $('#status').val();
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
                    
                    $('.info-box-text:contains("Opening")').next('.info-box-number').text('₹ ' + Math.abs(opBal).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ' + opType);
                    $('.info-box-text:contains("Current Balance")').next('.info-box-number').text('₹ ' + Math.abs(currBal).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ' + currType);
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'phone', name: 'phone'},
                {data: 'email', name: 'email'},
                {data: 'opening_balance', name: 'opening_balance'},
                {data: 'balance', name: 'balance'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: [
                {
                    text: 'Add Vendor',
                    className: 'btn-datatable',
                    action: function (e, dt, node, config) {
                        window.location.href = "{{ route('admin.master.vendor.create') }}";
                    }
                }
            ]
        });

        $('#email-queue-search-form').on('submit', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#id').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#name').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#phone').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#email').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
     

        $('#status').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });


    });

    $(document).ready(function () {
        
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
                // If user confirms, trigger the delete route
                window.location.href = "{{ route('admin.master.vendor.delete') }}?id=" + id;
            }
        });
    }
</script>

@endsection
