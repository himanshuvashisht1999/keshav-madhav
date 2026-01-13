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
                                <th>ID</th>
                                <!-- <th>Slip ID</th> -->
                                <th>Image</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($slips as $slip)
                            <tr>
                                <td>{{ $slip->created_at->format('d-m-Y') }}</td>
                                <td>#{{ $slip->id }}</td>
                                <!-- <td>{{ $slip->id ?? 'N/A' }}</td> {{-- Assuming slip_id is a field or just use ID --}} -->
                                <td>
                                    @if($slip->slip_file)
                                        <a href="{{ asset('assets/production_slips/'.$slip->slip_file) }}" target="_blank">
                                            <img src="{{ asset('assets/production_slips/'.$slip->slip_file) }}" alt="Slip" style="height: 50px; width: 50px; object-fit: cover;">
                                        </a>
                                    @else
                                        <span class="text-muted">No Image</span>
                                    @endif
                                </td>
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
