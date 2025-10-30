@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Production Order</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Manage Production Order</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- SELECT2 EXAMPLE -->
            <div class="card card-default ">
                 <div class="row" >
                    <div class="col-9 card-header">
                        <h3 class="card-title">Manage Production Order</h3>
                    </div>
                    <div class="col-3 card-header">
                        <!-- <a href="{{route('admin.sales_order.create')}}" class="btn btn-primary" style =" float: right;  width: max-content;">Add Sales Order</a> -->
                    </div>
                </div>
                
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
                            <select name="master_customer_id" id="master_customer_id" class="form-control">
                                <option value="">All</option>
                                @foreach($customers as $customer)
                                <option value="{{$customer->id}}">{{$customer->name}}</option>
                                @endforeach
                            </select>
                            
                        </td>
                       
                        <td>
                            <input type="date" class="form-control" name="created_at" id="created_at" autocomplete="off">
                        </td>
                        <td>
                            <input type="date" class="form-control" name="expected_delivery_date" id="expected_delivery_date" autocomplete="off">
                        </td>

                        <td></td>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Created Date</th>
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
<style >
    #hoverBox {
        position: absolute;
        display: none;
        background: #fff;
        border-radius: 10px;
        padding: 10px 15px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        min-width: 220px;
        z-index: 9999;
        transition: all 0.2s ease-in-out;
    }

    #hoverBox h6 {
        margin: 0 0 5px;
        font-weight: 600;
        color: #007bff;
    }

    #hoverBox p {
        margin: 0;
        font-size: 14px;
        color: #444;
    }

    #productTable tbody tr:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }

</style>
<div id="hoverBox"></div>
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
                url: '{!! route('admin.product_order.indexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.sku = $('#sku').val();
                    d.master_customer_id = $('#master_customer_id').val();
                    d.created_at = $('#created_at').val();
                    d.expected_delivery_date = $('#expected_delivery_date').val();
                  
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'sku', name: 'sku'},
                {data: 'master_customer_id', name: 'master_customer_id'},                
                {data: 'created_at', name: 'created_at'},                
                {data: 'expected_delivery_date', name: 'expected_delivery_date'},                
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: ['excel', 'csv', 'pdf', 'copy']
        });

        $('#email-queue-search-form').on('submit', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#id').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#sku').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#master_customer_id').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#expected_delivery_date').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#created_at').on('change', function (e) {
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
                window.location.href = "{{ route('admin.product_order.delete', ['id' => '']) }}" + id;
            }
        });
    }


    $(document).ready(function() {
        let table = $('#customers').DataTable();
        const hoverBox = $('#hoverBox');

        // When mouse enters a row
        $('#customers tbody').on('mouseenter', 'tr', function(e) {
            const row = $(this);
            const name = row.find('td:eq(0)').text();
            const price = row.find('td:eq(1)').text();

            // Static data (for now)
            const desc = "This is a high-quality product with great performance.";

            // Set box content
            hoverBox.html(`
                <h6>${name}</h6>
                <p><strong>Price:</strong> ${price}</p>
                <p>${desc}</p>
            `);

            // Position near cursor and show
            hoverBox.css({
                top: e.pageY + 10 + 'px',
                left: e.pageX + 10 + 'px'
            }).fadeIn(150);
        });

        // Move box with cursor
        $('#productTable tbody').on('mousemove', 'tr', function(e) {
            hoverBox.css({
                top: e.pageY + 10 + 'px',
                left: e.pageX + 10 + 'px'
            });
        });

        // Hide box when mouse leaves row
        $('#productTable tbody').on('mouseleave', 'tr', function() {
            hoverBox.hide();
        });
    });
</script>

@endsection
