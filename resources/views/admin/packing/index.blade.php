@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Packing Module</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pending Packing Slips</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Slip ID</th>
                                <th>Order SKU</th>
                                <th>Lot No</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($slips as $slip)
                            <tr>
                                <td>{{ $slip->created_at->format('d-m-Y') }}</td>
                                <td>#{{ $slip->id }}</td>
                                <td>{{ $slip->sku }}</td>
                                <td>{{ $slip->lot_no }}</td>
                                <td>
                                    <a href="{{ route('admin.packing.process', $slip->id) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-box-open"></i> Process Packing
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No pending slips for packing.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
