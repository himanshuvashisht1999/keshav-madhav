@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Production Goods Ledger</h1>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.ledger.production-goods.index') }}" class="mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control" placeholder="Search by Design No or Name" value="{{ $search ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                    <a href="{{ route('admin.ledger.production-goods.index') }}" class="btn btn-secondary">Reset</a>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Design Number</th>
                                        <th>Series / Name</th>
                                        <th>Total Inward (Boxes)</th>
                                        <th>Total Outward (Boxes)</th>
                                        <th>Current Balance (Boxes)</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($goods as $good)
                                        <tr>
                                            <td>{{ $good->id }}</td>
                                            <td>{{ $good->design_number }}</td>
                                            <td>{{ $good->series?->name }} {{ $good->name_of_garment }}</td>
                                            <td class="text-success">{{ number_format($good->total_inward, 0) }}</td>
                                            <td class="text-danger">{{ number_format($good->total_outward, 0) }}</td>
                                            <td class="text-primary fw-bold">{{ number_format($good->current_balance, 0) }}</td>
                                            <td>
                                                <a href="{{ route('admin.ledger.production-goods.show', $good->id) }}" class="btn btn-sm btn-info">
                                                    <i class="mdi mdi-eye"></i> View Ledger
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No production goods found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            {{ $goods->links('pagination::bootstrap-4') }}
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
