@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- PAGE HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">View Slip</h1>
                    <small class="text-muted">Details for Slip ID: {{ $slip->id }}</small>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.uploaded-slips.index') }}" class="btn btn-secondary px-4 shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTENT -->
    <section class="content">
        <div class="container-fluid">
            <div class="card shadow border-0">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="border rounded p-2 text-center bg-light">
                                <a href="{{ asset('assets/production_slips/' . $slip->slip_file) }}" target="_blank">
                                    <img src="{{ asset('assets/production_slips/' . $slip->slip_file) }}" class="img-fluid rounded" style="max-height: 600px;" alt="Slip Image">
                                </a>
                                <div class="mt-2 text-muted small">Click image to view full size</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h5 class="font-weight-bold border-bottom pb-2 mb-3">Slip Information</h5>
                            
                            <div class="mb-3">
                                <label class="small text-muted font-weight-bold d-block mb-0">Status</label>
                                @if($slip->status == 0)
                                    <span class="badge badge-warning px-3 py-2">Pending</span>
                                @elseif($slip->status == 2)
                                    <span class="badge badge-danger px-3 py-2">Skipped</span>
                                @elseif($slip->status == 1)
                                    <span class="badge badge-success px-3 py-2">Digitised</span>
                                @else
                                    <span class="badge badge-secondary px-3 py-2">Unknown</span>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label class="small text-muted font-weight-bold d-block mb-0">Slip ID</label>
                                <div class="font-weight-bold">{{ $slip->id }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="small text-muted font-weight-bold d-block mb-0">Date</label>
                                <div>{{ $slip->created_at->format('d M Y, h:i A') }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="small text-muted font-weight-bold d-block mb-0">From Stage</label>
                                <div>{{ $slip->fromStage->name ?? '-' }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="small text-muted font-weight-bold d-block mb-0">Unit</label>
                                <div>{{ $slip->getUnitMaster->name ?? '-' }}</div>
                            </div>
                            
                            <!-- Add more fields if necessary, e.g. Lot No, SKU if they exist in the model -->
                            @if($slip->lot_no)
                            <div class="mb-3">
                                <label class="small text-muted font-weight-bold d-block mb-0">Lot No</label>
                                <div>{{ $slip->lot_no }}</div>
                            </div>
                            @endif
                            
                            @if($slip->sku)
                            <div class="mb-3">
                                <label class="small text-muted font-weight-bold d-block mb-0">SKU</label>
                                <div>{{ $slip->sku }}</div>
                            </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    .card { border-radius: 8px; }
</style>
@endsection
