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

                {{-- hidden warehouse --}}
                <input type="hidden" name="warehouse_id" id="warehouse_id">

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

                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn btn-primary btn-sm mt-4">
                            Save Stage Units
                        </button>
                    </div>
                </div>

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
                                <td colspan="4" class="text-center text-muted">
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

{{-- Modal --}}
<div class="modal fade" id="getLinkModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">

            <div class="modal-header py-2">
                <h6 class="modal-title">Stage Upload Link</h6>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <input type="text" id="stageLinkInput" class="form-control form-control-sm" readonly>
            </div>

            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="copyStageLink()">Copy</button>
                <button type="button" class="btn btn-success btn-sm" onclick="downloadStageLink()">Download</button>
            </div>

        </div>
    </div>
</div>

<script>
$(document).ready(function () {

    let first = $('#warehouseSelect').val();

    $('#warehouse_id').val(first);     // sync hidden field

    if (first) changeWarehouse(first);
});

function changeWarehouse(master_fabric_warehouse_id) {

    $('#warehouse_id').val(master_fabric_warehouse_id);

    if (!master_fabric_warehouse_id) {
        $('#stageUnitContainer').html(`
            <tr><td colspan="4" class="text-center text-muted">Select warehouse</td></tr>
        `);
        return;
    }

    $.ajax({
        url: "{{ route('admin.master.stage_unit.stageUnit','') }}/" + master_fabric_warehouse_id,
        type: "GET",

        success: function (res) {

            let html = '';
            let rowIndex = 0;

            // group by stage
            let grouped = {};
            res.forEach(r => {
                if (!grouped[r.master_stage_id]) grouped[r.master_stage_id] = {
                    stage_name: r.stage_name,
                    rows: []
                };
                grouped[r.master_stage_id].rows.push(r);
            });

            Object.keys(grouped).forEach(stageId => {

                let stage = grouped[stageId];

                // header
                html += `
                <tr class="table-primary">
                    <td colspan="3"><strong>${stage.stage_name}</strong></td>
                    <td class="text-right">
                        <button type="button"
                                class="btn btn-success btn-sm add-unit-btn"
                                data-stage-id="${stageId}">
                            + Add New
                        </button>
                    </td>
                </tr>`;

                // if no units yet → show ONE blank editable row
                if (stage.rows.length === 0) {

                    html += `
                    <tr>
                        <td></td>

                        <td>
                            <input type="hidden" name="rows[${rowIndex}][id]" value="">
                            <input type="hidden" name="rows[${rowIndex}][master_stage_id]" value="${stageId}">
                            <input type="text" name="rows[${rowIndex}][name]" class="form-control form-control-sm">
                        </td>

                        <td>
                            <input type="text" name="rows[${rowIndex}][phone]" class="form-control form-control-sm">
                        </td>

                        <td><span class="text-muted">New</span></td>
                    </tr>`;
                    rowIndex++;
                }

                // existing units
                stage.rows.forEach(row => {

                    html += `
                    <tr>
                        <td></td>

                        <td>
                            <input type="hidden" name="rows[${rowIndex}][id]" value="${row.id ?? ''}">
                            <input type="hidden" name="rows[${rowIndex}][master_stage_id]" value="${row.master_stage_id}">
                            <input type="text" name="rows[${rowIndex}][name]" class="form-control form-control-sm"
                                   value="${row.name ?? ''}">
                        </td>

                        <td>
                            <input type="text" name="rows[${rowIndex}][phone]" class="form-control form-control-sm"
                                   value="${row.phone ?? ''}">
                        </td>

                        <td>
                            ${row.encrypted_id
                                ? `<button type="button" class="btn btn-info btn-sm"
                                           onclick="getStageLink('${row.encrypted_id}')">Get Link</button>`
                                : `<span class="text-muted">New</span>`}
                        </td>
                    </tr>`;

                    rowIndex++;
                });
            });

            $('#stageUnitContainer').html(html);
        }
    });
}

$(document).on('click', '.add-unit-btn', function () {

    let stageId = $(this).data('stage-id');

    let rows = $('#stageUnitContainer tr').filter(function () {
        return $(this).find('input[name*="[master_stage_id]"]').val() == stageId;
    });

    let index = $('input[name^="rows"]').length;

    $(rows.last()).after(`
        <tr>
            <td></td>

            <td>
                <input type="hidden" name="rows[${index}][id]" value="">
                <input type="hidden" name="rows[${index}][master_stage_id]" value="${stageId}">
                <input type="text" name="rows[${index}][name]" class="form-control form-control-sm">
            </td>

            <td>
                <input type="text" name="rows[${index}][phone]" class="form-control form-control-sm">
            </td>

            <td><span class="text-muted">New</span></td>
        </tr>
    `);
});

function getStageLink(id) {
    $('#stageLinkInput').val("{{ url('/') }}/upload-production-slip/" + id);
    $('#getLinkModal').modal('show');
}

function copyStageLink() {
    document.getElementById('stageLinkInput').select();
    document.execCommand('copy');
}

function downloadStageLink() {
    const url = document.getElementById('stageLinkInput').value;
    const blob = new Blob([`[InternetShortcut]\nURL=${url}`], {type:'application/octet-stream'});
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'Stage-Unit-Upload.url';
    a.click();
}
</script>
@endsection
