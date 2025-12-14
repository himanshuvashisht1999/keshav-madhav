@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <h4 class="text-center">Stage Units</h4>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <form action="{{ route('admin.master.stage_unit.update') }}" method="post">
                @csrf

                {{-- Warehouse --}}
                
                <div class="row mb-2">
                    <div class="col-md-6">
                        <label>Fabric Warehouse</label>
                        <select class="form-control select2"
                                id="warehouseSelect"
                                onchange="changeWarehouse(this.value)">
                            <option value="">Select</option>
                            @foreach($master_warehouse_fabrics as $index => $warehouse)
                                <option value="{{ $warehouse->id }}" {{ $index === 0 ? 'selected' : '' }}>
                                    {{ $warehouse->cutting_master_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="text-right mr-4">
                            <button type="submit" class="btn btn-primary btn-sm">
                                Save Stage Units
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th width="25%">Stage</th>
                                <th width="30%">Unit Name</th>
                                <th width="25%">Phone</th>
                                <th width="20%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="stageUnitContainer">
                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    Select warehouse
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                

            </form>

        </div>
    </section>
</div>

{{-- Get Link Modal --}}
<div class="modal fade" id="getLinkModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">

            <div class="modal-header py-2">
                <h6 class="modal-title">Stage Upload Link</h6>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <input type="text"
                       id="stageLinkInput"
                       class="form-control form-control-sm"
                       readonly>
            </div>

            <div class="modal-footer py-2">
                <button type="button"
                        class="btn btn-secondary btn-sm"
                        data-dismiss="modal">
                    Close
                </button>
                <button type="button"
                        class="btn btn-primary btn-sm"
                        onclick="copyStageLink()">
                    Copy
                </button>
                <button type="button"
                        class="btn btn-success btn-sm"
                        onclick="downloadStageLink()">
                    Download Link
                </button>
            </div>

        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    let firstWarehouse = $('#warehouseSelect').val();
    if (firstWarehouse) {
        changeWarehouse(firstWarehouse);
    }
});
</script>
<script>
function changeWarehouse(master_fabric_warehouse_id) {

    if (!master_fabric_warehouse_id) {
        $('#stageUnitContainer').html(`
            <tr>
                <td colspan="3" class="text-center text-muted">
                    Select warehouse
                </td>
            </tr>
        `);
        return;
    }

    $.ajax({
        url: "{{ route('admin.master.stage_unit.stageUnit', '') }}/" + master_fabric_warehouse_id,
        type: "GET",
        success: function (res) {

            let html = '';

            if (res.length === 0) {
                html = `
                <tr>
                    <td colspan="3" class="text-center text-muted">
                        No stages found
                    </td>
                </tr>`;
            }

            res.forEach((row, index) => {
                html += `
                <tr>
                    <td>
                        ${row.stage_name}
                        <input type="hidden" name="rows[${index}][master_stage_id]" value="${row.master_stage_id}">
                        <input type="hidden" name="rows[${index}][master_fabric_warehouse_id]" value="${master_fabric_warehouse_id}">
                    </td>
                    <td>
                        <input type="text"
                            name="rows[${index}][name]"
                            class="form-control form-control-sm"
                            value="${row.name ?? ''}">
                    </td>
                    <td>
                        <input type="text"
                            name="rows[${index}][phone]"
                            class="form-control form-control-sm"
                            value="${row.phone ?? ''}">
                    </td>
                    <td>
                        <button type="button"
                                class="btn btn-info btn-sm"
                                onclick="getStageLink('${row.encrypted_id}')">
                            Get Link
                        </button>
                    </td>
                </tr>`;
            });


            $('#stageUnitContainer').html(html);
        }
    });
}
</script>
<script>
function getStageLink(encryptedId) {
    const baseUrl = "{{ url('/') }}";
    const finalUrl = baseUrl + "/upload-production-slip/" + encryptedId;

    $('#stageLinkInput').val(finalUrl);
    $('#getLinkModal').modal('show');
}
</script>
<script>
function copyStageLink() {
    const input = document.getElementById('stageLinkInput');
    input.select();
    input.setSelectionRange(0, 99999); // mobile support

    document.execCommand('copy');

    // Optional feedback
    alert('Link copied to clipboard');
}
</script>
<script>
function downloadStageLink() {
    const url = document.getElementById('stageLinkInput').value;

    const content = `[InternetShortcut]\nURL=${url}`;
    const blob = new Blob([content], { type: 'application/octet-stream' });

    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'Stage-Unit-Upload.url';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
</script>

@endsection
