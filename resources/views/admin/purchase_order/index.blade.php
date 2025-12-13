@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="text-center">List of Available Purchase Orders</h1>
                </div>
                <!-- <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Manage Purchase Order</li>
                    </ol>
                </div> -->
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
                        <h3 class="card-title">Manage Purchase Order</h3>
                    </div>
                    <div class="col-3 card-header">
                        <a href="{{route('admin.purchase_order.create')}}" class="btn btn-primary" style =" float: right;  width: max-content;">Add Purchase Order</a>
                    </div>
                </div> -->
                
                <div class="card-body table-responsive">
                <table id="customers" class="table table-bordered table-hover">
                  <thead>
                    <tr role="row" class="filter">
                        <td>
                            <!-- <input type="text" class="form-control" name="id" id="id" autocomplete="off"> -->
                        </td>
                        <td>
                            <input type="text" class="form-control" name="sku" id="sku" autocomplete="off">
                        </td>
                        <td>
                            <input type="date" class="form-control" name="date" id="date" autocomplete="off">
                        </td>
                        <td>
                            <select class="form-control select2" name="vendor_id" id="vendor_id" style="width: 100%;">
                                <option value="">ALL</option>
                                @foreach($vendors as $single_data)
                                    <option value="{{$single_data->id}}" >{{$single_data->name}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="date" class="form-control" name="delivery_date" id="delivery_date" autocomplete="off">
                        </td>
                        
                        <td> </td>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>PO No.</th>
                        <th>Purchase Order Date</th>
                        <th>Vendor</th>
                        <th>Expected Delivery Date</th>
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

<!-- Send Email Modal -->
<div class="modal fade" id="sendEmailModal" tabindex="-1" aria-labelledby="sendEmailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form id="sendEmailForm">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="sendEmailModalLabel">Resend Purchase Order</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="id" id="emailPurchaseId" value="">

          <div class="mb-2">
            <label for="emailTo" class="form-label">Recipient Email</label>
            <input type="email" name="email" id="emailTo" class="form-control" placeholder="email@example.com" required>
          </div>

          <div id="sendEmailError" class="text-danger small" style="display:none;"></div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Send</button>
        </div>
      </form>
    </div>
  </div>
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
                url: '{!! route('admin.purchase_order.indexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.sku = $('#sku').val();
                    d.date = $('#date').val();
                    d.vendor_id = $('#vendor_id').val();
                    d.delivery_date = $('#delivery_date').val();
                  
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'sku', name: 'sku'},
                {data: 'date', name: 'date'},
                {data: 'vendor_id', name: 'vendor_id'},
                {data: 'delivery_date', name: 'delivery_date'},
                
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: [
                // {
                //     text: 'Add Purchase Order',
                //     className: 'btn-datatable',
                //     action: function (e, dt, node, config) {
                //         window.location.href = "{{ route('admin.purchase_order.create') }}";
                //     }
                // }
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

        $('#date').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#vendor_id').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#delivery_date').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#sku').on('keyup', function (e) {
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
                window.location.href = "{{ route('admin.purchase_order.delete', ['id' => '']) }}" + id;
            }
        });
    }
</script>

<script>
    $(function() {
    // open modal when send-email button clicked
    $(document).on('click', '.btn-send-email', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $('#emailPurchaseId').val(id);
        $('#emailTo').val(''); // clear previous
        $('#sendEmailError').hide().text('');
        // show modal (Bootstrap 4/5 safe)
        $('#sendEmailModal').modal ? $('#sendEmailModal').modal('show') : $('#sendEmailModal').modal('show');
    });

    // submit send email form via AJAX
    $('#sendEmailForm').on('submit', function(e) {
        e.preventDefault();
        var $btn = $(this).find('button[type="submit"]');
        var id = $('#emailPurchaseId').val();
        var email = $('#emailTo').val().trim();

        if (!email) {
            $('#sendEmailError').show().text('Please enter a recipient email.');
            return;
        }

        $btn.prop('disabled', true).text('Sending...');

        $.ajax({
            url: '{{ route("admin.purchase_order.resend") }}', // create this POST route in web.php/controller
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id,
                email: email
            },
            success: function(response) {
                // example success handling — adjust based on your JSON response
                if (response.success) {
                    // close modal
                    $('#sendEmailModal').modal('hide');
                    // show success (SweetAlert if you use it)
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Sent', response.message || 'Purchase order resent successfully.', 'success');
                    } else {
                        alert(response.message || 'Purchase order resent successfully.');
                    }
                    // reload datatable
                    if ($.fn.DataTable && $('#customers').length) {
                        $('#customers').DataTable().ajax.reload(null, false);
                    }
                } else {
                    $('#sendEmailError').show().text(response.message || 'Failed to send email.');
                }
            },
            error: function(xhr) {
                var msg = 'Something went wrong.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    // show first validation error
                    var errors = xhr.responseJSON.errors;
                    var first = Object.keys(errors)[0];
                    msg = errors[first][0];
                }
                $('#sendEmailError').show().text(msg);
            },
            complete: function() {
                $btn.prop('disabled', false).text('Send');
            }
        });
    });
});

</script>

@endsection
