@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Design-wise WIP Report</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Filter Section -->
                <div class="card">
                    <div class="card-header bg-primary">
                        <h3 class="card-title text-white">Filter Report</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.reports.design-wip') }}">
                            <div class="row align-items-end">
                                <div class="col-md-2 mb-3">
                                    <label for="design_no">Design / Lot No</label>
                                    <input type="text" name="design_no" id="design_no" class="form-control"
                                        value="{{ request('design_no') }}" placeholder="Design No">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="color_id">Color</label>
                                    <select name="color_id" id="color_id" class="form-control select2">
                                        <option value="">All Colors</option>
                                        @foreach($colors as $color)
                                            <option value="{{ $color->id }}" {{ request('color_id') == $color->id ? 'selected' : '' }}>
                                                {{ $color->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="pattern_id">Pattern</label>
                                    <select name="pattern_id" id="pattern_id" class="form-control select2">
                                        <option value="">All Patterns</option>
                                        @foreach($patterns as $pattern)
                                            <option value="{{ $pattern->id }}" {{ request('pattern_id') == $pattern->id ? 'selected' : '' }}>
                                                {{ $pattern->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="fitting_id">Fitting</label>
                                    <select name="fitting_id" id="fitting_id" class="form-control select2">
                                        <option value="">All Fittings</option>
                                        @foreach($fittings as $fitting)
                                            <option value="{{ $fitting->id }}" {{ request('fitting_id') == $fitting->id ? 'selected' : '' }}>
                                                {{ $fitting->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="stage">Stage</label>
                                    <select name="stage" id="stage" class="form-control select2">
                                        <option value="">All Stages</option>
                                        @foreach($stages as $stage)
                                            <option value="{{ $stage }}" {{ request('stage') == $stage ? 'selected' : '' }}>
                                                {{ $stage }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3 d-flex align-items-end">
                                    <div class="btn-group w-100">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <a href="{{ route('admin.reports.design-wip') }}" class="btn btn-secondary">
                                            <i class="fas fa-sync"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Report Data -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">WIP Results (Total grouped sets:
                            {{ $reportData->groupBy('design_no')->count() }})</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive p-0">
                        <table class="table table-bordered table-striped table-hover text-nowrap">
                            <thead class="bg-light">
                                <tr>
                                    <th>Design No</th>
                                    <th>Attributes</th>
                                    <th>Stage / Current Location</th>
                                    <th>Unit Person / Warehouse</th>
                                    <th class="text-center">Quantity (Pieces)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportData as $row)
                                    <tr>
                                        <td><strong>{{ $row['design_no'] }}</strong></td>
                                        <td>
                                            <span class="badge badge-info">{{ $row['color'] }}</span>
                                            <span class="badge badge-secondary">{{ $row['pattern'] }}</span>
                                            <span class="badge badge-dark">{{ $row['fitting'] }}</span>
                                        </td>
                                        <td>
                                            @if($row['stage'] === 'Inventory')
                                                <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle"></i>
                                                    {{ $row['stage'] }}</span>
                                            @else
                                                <span class="badge badge-warning px-2 py-1"><i class="fas fa-cogs"></i>
                                                    {{ $row['stage'] }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $row['location'] }}</td>
                                        <td class="text-center">
                                            <span
                                                class="font-weight-bold {{ $row['stage'] === 'Inventory' ? 'text-success' : 'text-primary' }}"
                                                style="font-size: 1.1rem;">
                                                {{ $row['quantity'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No pending quantities or inventory
                                            found for the selected criteria.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer clearfix">
                        {{ $reportData->links() }}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('.select2').select2({
                theme: 'bootstrap4'
            });
        });
    </script>
@endsection