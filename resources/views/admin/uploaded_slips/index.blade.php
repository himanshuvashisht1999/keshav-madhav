@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- PAGE HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">Uploaded Slips</h1>
                    <small class="text-muted">Manage and view all uploaded production slips</small>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <!-- FILTER CARD -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body bg-light rounded">
                    <form method="GET" action="{{ route('admin.uploaded-slips.index') }}">
                        <div class="row">
                            <div class="col-md-2 mb-2">
                                <label class="small font-weight-bold text-muted">From Stage</label>
                                <select name="from_stage_id" class="form-control select2">
                                    <option value="">-- All Stages --</option>
                                    @foreach($stages as $stage)
                                        <option value="{{ $stage->id }}" {{ request('from_stage_id') == $stage->id ? 'selected' : '' }}>
                                            {{ $stage->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small font-weight-bold text-muted">Unit</label>
                                <select name="stage_master_unit_id" class="form-control select2">
                                    <option value="">-- All Units --</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ request('stage_master_unit_id') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small font-weight-bold text-muted">Status</label>
                                <select name="status" class="form-control select2">
                                    <option value="">-- All Status --</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pending</option>
                                    <option value="1" {{ request('status') == 1 ? 'selected' : '' }}>Digitized</option>
                                    <option value="2" {{ request('status') == 2 ? 'selected' : '' }}>Skipped</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small font-weight-bold text-muted">Date</label>
                                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                            </div>
                            <div class="col-md-4 mb-2 d-flex justify-content-end align-items-end">
                                <button type="submit" class="btn btn-primary px-4 shadow-sm mr-2">Filter</button>
                                <a href="{{ route('admin.uploaded-slips.index') }}" class="btn btn-secondary px-3 shadow-sm">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="card shadow border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>Date</th>
                                    <th>From Stage</th>
                                    <th>Unit</th>
                                    <th>ID</th>

                                    <th class="text-center">Status</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($slips as $slip)
                                <tr>
                                    <td>{{ $slip->created_at->format('d M Y') }}</td>
                                    <td>{{ $slip->fromStage->name ?? '-' }}</td>
                                    <td>{{ $slip->getUnitMaster->name ?? '-' }}</td>
                                    <td class="font-weight-bold">{{ $slip->id }}</td>

                                    <td class="text-center">
                                        @if($slip->status == 0)
                                            <span class="badge badge-warning px-2 py-1">Pending</span>
                                        @elseif($slip->status == 2)
                                            <span class="badge badge-danger px-2 py-1">Skipped</span>
                                        @else
                                            <span class="badge badge-success px-2 py-1">Digitized</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <?php
                                            $from_stage_id = $slip->from_stage_id;


                                        ?>
                                        @if($slip->status == 1)
                                            <a href="{{ route('admin.uploaded-slips.show', $slip->id) }}" class="btn btn-success btn-sm shadow-sm">View</a>
                                        @else
                                            <?php
                                                $actionRoute = '#';
                                                if($from_stage_id == 3) {
                                                    $actionRoute = route('admin.order_digitalization.cutting-master', ['slip_id' => $slip->id]);
                                                } elseif($from_stage_id == 11) {
                                                    $actionRoute = route('admin.packing.process', [$slip->id]);
                                                } else {
                                                    $actionRoute = route('admin.order_digitalization.create-slips-production', ['slip_id' => $slip->id]);
                                                }
                                            ?>
                                            <a href="{{ $actionRoute }}" class="btn btn-primary btn-sm shadow-sm">Action</a>
                                        @endif

                                        @if($slip->status == 0)
                                        <form action="{{ route('admin.uploaded-slips.destroy', $slip->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this slip?');" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm shadow-sm ml-1" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif

                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No slips found matching your criteria.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- PAGINATION -->
                <div class="card-footer bg-white d-flex justify-content-end py-3">
                    {{ $slips->withQueryString()->links() }}
                </div>
            </div>

        </div>
    </section>
</div>

<style>
    .card { border-radius: 8px; }
    .form-control { border-radius: 6px; }
    .table thead th { border: none; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
    .table tbody td { vertical-align: middle; font-size: 0.95rem; }
    .select2-container .select2-selection--single { height: 38px; border: 1px solid #ced4da; border-radius: 6px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
</style>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'default', // Using default to match custom CSS overrides
            width: '100%'
        });
    });
</script>
@endsection
