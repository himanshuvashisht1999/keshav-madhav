@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Fabric Ledger</h1>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Fabrics</h3>
                        <div class="card-tools">
                            <form action="{{ route('admin.ledger.fabric.index') }}" method="GET">
                                <div class="input-group input-group-sm" style="width: 250px;">
                                    <input type="text" name="search" class="form-control float-right" placeholder="Search Fabric..." value="{{ request('search') }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-default">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fabric Name</th>
                                    <th class="text-end">Total Inward (Mtrs)</th>
                                    <th class="text-end">Total Outward (Mtrs)</th>
                                    <th class="text-end">Current Balance (Mtrs)</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($fabrics as $fabric)
                                    <tr>
                                        <td>{{ ($fabrics->currentPage() - 1) * $fabrics->perPage() + $loop->iteration }}</td>
                                        <td class="fw-bold">{{ $fabric->name }}</td>
                                        <td class="text-end text-success">{{ number_format($fabric->total_inward, 2) }}</td>
                                        <td class="text-end text-danger">{{ number_format($fabric->total_outward, 2) }}</td>
                                        <td class="text-end fw-bold">{{ number_format($fabric->current_balance, 2) }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.ledger.fabric.show', $fabric->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-book-open"></i> View Ledger
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No fabrics found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer clearfix">
                        {{ $fabrics->links() }}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
