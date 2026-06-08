@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Racks for Storeroom: {{ $storeroom->name }}</h1>
                </div>
                <div class="col-sm-6 text-end">
                    <a href="{{ route('admin.master.storeroom.index') }}" class="btn btn-secondary">Back to Storerooms</a>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <!-- Racks Management -->
            <div class="card mt-4">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0 font-weight-bold text-dark">Racks Management</h3>
                    <button class="btn btn-primary btn-sm px-3 shadow-sm" onclick="openRackModal()">
                        <i class="fas fa-plus-circle mr-1"></i> Add New Rack
                    </button>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Rack Name</th>
                                <th>Capacity</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="racksTableBody">
                            @forelse($storeroom->racks as $rack)
                            <tr id="rack-row-{{ $rack->id }}">
                                <td>{{ $rack->name }}</td>
                                <td>{{ $rack->capacity }}</td>
                                <td>
                                    <button class="btn btn-warning btn-sm" onclick="editRack({{ $rack->id }}, '{{ $rack->name }}', '{{ $rack->capacity }}')">Edit</button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteRack({{ $rack->id }})">Delete</button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted">No Racks found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Add/Edit Rack Modal -->
<div class="modal fade" id="rackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rackModalLabel">Add Rack</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="storeroom_id" value="{{ $storeroom->id }}">
                <input type="hidden" id="rack_id" value="">
                <div class="mb-3">
                    <label>Rack Name</label>
                    <input type="text" id="rack_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Capacity (Optional)</label>
                    <input type="number" id="rack_capacity" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitRack()">Save Rack</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openRackModal() {
        $('#rackModalLabel').text('Add Rack');
        $('#rack_id').val('');
        $('#rack_name').val('');
        $('#rack_capacity').val('');
        $('#rackModal').modal('show');
    }

    function editRack(id, name, capacity) {
        $('#rackModalLabel').text('Edit Rack');
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
