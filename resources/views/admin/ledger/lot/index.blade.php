@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Lot Ledger</h1>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.ledger.lot.index') }}" class="mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control" placeholder="Search by Lot No or Order SKU..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary"><i class="mdi mdi-magnify"></i> Search</button>
                                    <a href="{{ route('admin.ledger.lot.index') }}" class="btn btn-secondary">Reset</a>
                                </div>
                            </div>
                        </form>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Lot No</th>
                                        <th>Order SKU</th>
                                        <th>Customer</th>
                                        <th>Fabric</th>
                                        <th>Total Assigned (Pcs)</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($lots as $lot)
                                        <tr>
                                            <td class="font-weight-bold">{{ $lot->lot_no }}</td>
                                            <td>{{ $lot->orderMain->sku ?? '-' }}</td>
                                            <td>{{ $lot->orderMain->customer->name ?? '-' }}</td>
                                            <td>{{ $lot->orderProductSet->fabric->name ?? '-' }}</td>
                                            <td class="text-success font-weight-bold">{{ number_format($lot->lot_quantity, 0) }}</td>
                                            <td>
                                                @if($lot->status == 1)
                                                    <span class="badge badge-warning">Processing</span>
                                                @elseif($lot->status == 2)
                                                    <span class="badge badge-success">Completed</span>
                                                @else
                                                    <span class="badge badge-secondary">Status {{ $lot->status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.ledger.lot.show', $lot->lot_no) }}" class="btn btn-sm btn-info text-white">
                                                    <i class="fas fa-book"></i> View Ledger
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No Lots Found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer clearfix">
                        {{ $lots->links() }}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
