@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">

    <!-- Page Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="mb-0">Packing Carton Details</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.packing-carton.download-pdf', ['id' => $data['cartons_session_data']['id']]) }}" class="btn btn-primary">
                        <i class="fas fa-file-pdf mr-1"></i> Download PDF
                    </a>
                    <a href="{{ route('admin.packing-carton.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <!-- PAGE SUMMARY -->
            <div class="card shadow-sm mb-3">
                <div class="card-body">

                    <div class="row">

                        <div class="col-md-3 col-sm-6 mb-2">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-boxes"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Carton Packing Session No</span>
                                    <span class="info-box-number">{{$data['cartons_session_data']['carton_packing_session_no'] ?? ''}}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 mb-2">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-file-invoice"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Order No</span>
                                    <span class="info-box-number">{{$data['cartons_session_data']['order_no'] ?? ''}}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-6 mb-2">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-user-tie"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Customer Name</span>
                                    <span class="info-box-number">{{$data['cartons_session_data']['customer'] ?? ''}}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-6 mb-2">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-cubes"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Cartons</span>
                                    <span class="info-box-number">{{$data['cartons_session_data']['total_cartons'] ?? '0'}}</span>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-0">

                    <!-- MAIN TABLE -->
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>S. No</th>
                                <th>Carton No</th>
                                <th>Total Boxes</th>
                                <th width="5%">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php $index = 1; @endphp
                            @foreach($data['cartonsDetails'] as $carton)
                           
                            <!-- MAIN ROW -->
                            <tr class="main-row">
                                <td><strong>{{$index++;}}</strong></td>
                                <td>
                                    <span class="badge badge-primary px-3 py-2">Carton- {{$carton['id']}}</span>
                                </td>
                                <td>
                                    <span class="badge badge-success px-3 py-2">{{$carton['total_boxes']}} Boxes</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary toggle-row">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            
                            <!-- SUB ROW -->
                            <tr class="sub-row d-none">
                                <td colspan="4" class="bg-soft">
                                    
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm mb-0">
                                            <thead class="bg-primary text-white">
                                                <tr>
                                                    <th>Bar Code</th>
                                                    <th>Design No</th>
                                                    <th>Size Set</th>
                                                    <th>Size Group</th>
                                                    <th>Colour</th>
                                                    <th>No of Pcs (per Set)</th>
                                                    <th>No of Boxes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($carton['car_data'] as $box)
                                                <tr>
                                                    <td>{{$box['bar_code']}}</td>
                                                    <td>{{$box['design_number']}}</td>
                                                    <td>{{$box['set_size']}}</td>
                                                    <td>{{$box['size_group']}}</td>
                                                    <td>{{$box['color']}}</td>
                                                    <td>{{$box['no_of_pcs']}}</td>
                                                    <td>{{$box['set_quantity']}}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </section>
</div>

<style>
    .bg-soft {
        background-color: #f8f9fa;
    }
    .main-row td {
        vertical-align: middle;
    }
    .toggle-row {
        border-radius: 50%;
    }
</style>
<script>
    document.querySelectorAll('.toggle-row').forEach(button => {
        button.addEventListener('click', function () {
            let icon = this.querySelector('i');
            let subRow = this.closest('tr').nextElementSibling;

            subRow.classList.toggle('d-none');

            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    });
</script>
@endsection

