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
                                <div class="col-md-4 mb-2">
                                    <input type="text" name="search" class="form-control" placeholder="Search by Design No or Name" value="{{ $search ?? '' }}">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <select name="warehouse_ids[]" class="form-control select2" multiple data-placeholder="All Warehouses">
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}" {{ (in_array($wh->id, (array)($warehouseIds ?? []))) ? 'selected' : '' }}>{{ $wh->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
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
                                    <tr class="bg-light">
                                        <th colspan="3" class="text-end text-right"><strong>Overall Totals:</strong></th>
                                        <th class="text-success fs-5">{{ number_format($totalInwardOverall ?? 0, 0) }}</th>
                                        <th class="text-danger fs-5">{{ number_format($totalOutwardOverall ?? 0, 0) }}</th>
                                        <th class="text-primary fs-5">{{ number_format($totalBalanceOverall ?? 0, 0) }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($goods as $good)
                                        @foreach($good->variants as $variant)
                                        <tr>
                                            <td>{{ $good->id }}</td>
                                            <td>{{ $good->design_number }}</td>
                                            <td>
                                                {{ $good->series?->name }} {{ $good->name_of_garment }}<br>
                                                <small class="text-muted">Size Set: {{ $variant->sizeSet?->name }}</small>
                                            </td>
                                            <td class="text-success">{{ number_format($variant->total_inward, 0) }}</td>
                                            <td class="text-danger">{{ number_format($variant->total_outward, 0) }}</td>
                                            <td class="text-primary fw-bold">{{ number_format($variant->current_balance, 0) }}</td>
                                            <td>
                                                <a href="{{ route('admin.ledger.production-goods.show', ['id' => $good->id, 'size_set_id' => $variant->master_size_measurement_id, 'warehouse_ids' => $warehouseIds]) }}" class="btn btn-sm btn-info">
                                                    <i class="mdi mdi-eye"></i> View Ledger
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
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
