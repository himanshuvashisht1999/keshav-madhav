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
                            <label>Production Warehouse</label>

                            <select class="form-control select2" id="warehouseSelect"
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
                                    <th width="20%">Stage</th>
                                    <th width="20%">Unit Name</th>
                                    <th width="15%">Phone</th>
                                    <th width="15%">Employee ID</th>
                                    <th width="15%">Password</th>
                                    <th width="10%">Time (Days)</th>
                                    <th width="10%">Action</th>
                                </tr>
                            </thead>

                            <tbody id="stageUnitContainer">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
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

    {{-- Upload Slip Modal --}}
    <div class="modal fade" id="uploadSlipModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="uploadSlipForm" action="{{ route('submitProductionSlip') }}" method="POST">
                    @csrf
                    <div class="modal-header py-2">
                        <h6 class="modal-title">Upload Production Slip - <span id="modalUnitName"></span></h6>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="stage_master_unit_id" id="upload_encrypted_id">
                        <input type="hidden" name="photo_data" id="upload_photo_data">
                        <input type="hidden" name="type" value="2">

                        <div class="text-center mb-3">
                            <div id="camera-container" style="display:none;">
                                <video id="admin-video" autoplay playsinline style="width:100%; border-radius:8px;"></video>
                                <button type="button" class="btn btn-info btn-sm mt-2" id="admin-capture-btn">Capture Photo</button>
                            </div>
                            <div id="preview-container" style="display:none;">
                                <img id="admin-preview" style="width:100%; border-radius:8px; border:1px solid #ddd;">
                                <button type="button" class="btn btn-warning btn-sm mt-2" id="admin-retake-btn">Retake</button>
                            </div>
                            <canvas id="admin-canvas" style="display:none;"></canvas>
                        </div>

                        <div class="form-group">
                            <label>Select File or Use Camera</label>
                            <div class="d-flex justify-content-between">
                                <input type="file" id="admin-file-input" accept="image/*" class="form-control-file w-auto">
                                <button type="button" class="btn btn-primary btn-sm" id="admin-open-camera-btn">
                                    <i class="fa fa-camera"></i> Camera
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success btn-sm" id="admin-submit-btn" disabled>Submit Slip</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let adminStream = null;

        function openUploadModal(encryptedId, unitName) {
            $('#upload_encrypted_id').val(encryptedId);
            $('#modalUnitName').text(unitName);
            $('#uploadSlipModal').modal('show');
            resetUploadModal();
        }

        function resetUploadModal() {
            stopCamera();
            $('#camera-container').hide();
            $('#preview-container').hide();
            $('#upload_photo_data').val('');
            $('#admin-file-input').val('');
            $('#admin-submit-btn').prop('disabled', true);
        }

        function stopCamera() {
            if (adminStream) {
                adminStream.getTracks().forEach(track => track.stop());
                adminStream = null;
            }
        }

        $('#admin-open-camera-btn').on('click', function() {
            $('#preview-container').hide();
            $('#admin-file-input').val('');
            
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function (s) {
                    adminStream = s;
                    $('#admin-video')[0].srcObject = adminStream;
                    $('#camera-container').show();
                })
                .catch(() => alert('Camera permission denied'));
        });

        $('#admin-capture-btn').on('click', function() {
            const video = $('#admin-video')[0];
            const canvas = $('#admin-canvas')[0];
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            
            const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
            $('#upload_photo_data').val(dataUrl);
            $('#admin-preview').attr('src', dataUrl);
            
            $('#camera-container').hide();
            $('#preview-container').show();
            $('#admin-submit-btn').prop('disabled', false);
            stopCamera();
        });

        $('#admin-retake-btn').on('click', function() {
            $('#admin-open-camera-btn').trigger('click');
        });

        $('#admin-file-input').on('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            stopCamera();
            $('#camera-container').hide();
            
            const reader = new FileReader();
            reader.onload = function (ev) {
                $('#upload_photo_data').val(ev.target.result);
                $('#admin-preview').attr('src', ev.target.result);
                $('#preview-container').show();
                $('#admin-submit-btn').prop('disabled', false);
            };
            reader.readAsDataURL(file);
        });

        $('#uploadSlipModal').on('hidden.bs.modal', function () {
            stopCamera();
        });

        $(document).ready(function () {

            let first = $('#warehouseSelect').val();

            $('#warehouse_id').val(first);     // sync hidden field

            if (first) changeWarehouse(first);
        });

        function changeWarehouse(master_fabric_warehouse_id) {

            $('#warehouse_id').val(master_fabric_warehouse_id);

            if (!master_fabric_warehouse_id) {
                $('#stageUnitContainer').html(`
                                    <tr><td colspan="7" class="text-center text-muted">Select warehouse</td></tr>
                                `);
                return;
            }

            $.ajax({
                url: "{{ route('admin.master.stage_unit.stageUnit', '') }}/" + master_fabric_warehouse_id,
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
                                    <td colspan="6"><strong>${stage.stage_name}</strong></td>
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
                                                <td>
                                                    <input type="text" name="rows[${rowIndex}][employee_id]" class="form-control form-control-sm">
                                                </td>
                                                <td>
                                                    <input type="text" name="rows[${rowIndex}][password]" class="form-control form-control-sm">
                                                </td>
                                                <td>
                                                    <input type="number" name="rows[${rowIndex}][lot_time_in_days]" class="form-control form-control-sm" value="1">
                                                </td>
                                                <td>-</td>
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
                                                    <input type="text" name="rows[${rowIndex}][employee_id]" class="form-control form-control-sm"
                                                           value="${row.employee_id ?? ''}">
                                                </td>
                                                 <td>
                                                    <input type="text" name="rows[${rowIndex}][password]" class="form-control form-control-sm"
                                                           value="${row.password ?? ''}">
                                                </td>
                                                <td>
                                                    <input type="number" name="rows[${rowIndex}][lot_time_in_days]" class="form-control form-control-sm"
                                                           value="${row.lot_time_in_days ?? 1}">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-info btn-xs" onclick="openUploadModal('${row.encrypted_id}', '${row.name}')">
                                                        <i class="fa fa-upload"></i> Upload
                                                    </button>
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
                                    <td>
                                        <input type="text" name="rows[${index}][employee_id]" class="form-control form-control-sm">
                                    </td>
                                     <td>
                                        <input type="text" name="rows[${index}][password]" class="form-control form-control-sm">
                                    </td>
                                    <td>
                                        <input type="number" name="rows[${index}][lot_time_in_days]" class="form-control form-control-sm" value="1">
                                    </td>
                                    <td>-</td>
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
            const blob = new Blob([`[InternetShortcut]\nURL=${url}`], { type: 'application/octet-stream' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'Stage-Unit-Upload.url';
            a.click();
        }
    </script>
@endsection