@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- ERP Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="erp-header-title">Racks for storeroom: {{ $storeroom->name }}</h1>
        </div>
        <div class="erp-header-actions">
            <button class="btn-erp btn-erp-primary mr-1" onclick="openRackModal()">
                <i class="fas fa-plus mr-1"></i> Add new rack
            </button>
            <a href="{{ route('admin.master.storeroom.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back to storerooms
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="erp-card p-3">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Rack name</th>
                        <th>Capacity</th>
                        <th style="width: 140px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="racksTableBody">
                    @forelse($storeroom->racks as $rack)
                    <tr id="rack-row-{{ $rack->id }}">
                        <td class="font-weight-bold" style="color: var(--erp-green-primary);">{{ $rack->name }}</td>
                        <td>{{ $rack->capacity ?? '-' }}</td>
                        <td class="text-center">
                            <button class="btn-erp btn-erp-outline py-1 px-2 mr-1" onclick="editRack({{ $rack->id }}, '{{ $rack->name }}', '{{ $rack->capacity }}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-erp btn-erp-outline py-1 px-2 text-danger" onclick="deleteRack({{ $rack->id }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted p-4">No racks found for this storeroom.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Rack Modal -->
<div class="modal fade" id="rackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="rackModalLabel">Add rack</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="storeroom_id" value="{{ $storeroom->id }}">
                <input type="hidden" id="rack_id" value="">
                <div class="form-group mb-3">
                    <label class="form-label">Rack name <span class="text-danger">*</span></label>
                    <input type="text" id="rack_name" class="form-control erp-filter-input" required placeholder="Enter rack name">
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Capacity <small class="text-muted">(Optional)</small></label>
                    <input type="number" id="rack_capacity" class="form-control erp-filter-input" placeholder="Enter capacity">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal">Close</button>
                <button type="button" class="btn-erp btn-erp-primary" onclick="submitRack()">
                    <i class="fas fa-check mr-1"></i> Save rack
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openRackModal() {
        $('#rackModalLabel').text('Add rack');
        $('#rack_id').val('');
        $('#rack_name').val('');
        $('#rack_capacity').val('');
        $('#rackModal').modal('show');
    }

    function editRack(id, name, capacity) {
        $('#rackModalLabel').text('Edit rack');
        $('#rack_id').val(id);
        $('#rack_name').val(name);
        $('#rack_capacity').val(capacity);
        $('#rackModal').modal('show');
    }

    function submitRack() {
        let id = $('#rack_id').val();
        let name = $('#rack_name').val();
        let capacity = $('#rack_capacity').val();
        let storeroom_id = $('#storeroom_id').val();

        if(!name) { alert("Name is required"); return; }

        let url = id ? "{{ route('admin.master.storeroom.rack.update') }}" : "{{ route('admin.master.storeroom.rack.store') }}";
        let data = {
            _token: "{{ csrf_token() }}",
            storeroom_id: storeroom_id,
            name: name,
            capacity: capacity
        };

        if(id) { data.id = id; }

        $.post(url, data, function(res) {
            if(res.status === 'success') {
                location.reload();
            } else {
                alert("Error: " + res.message);
            }
        });
    }

    function deleteRack(id) {
        if(confirm("Are you sure? This will affect unpacked cartons associated with this rack.")) {
            $.get("/admin/master/storerooms/rack/delete/" + id, function(res) {
                if(res.status === 'success') {
                    $('#rack-row-' + id).remove();
                } else {
                    alert("Error: " + res.message || "Failed to delete");
                }
            });
        }
    }
</script>
@endpush
