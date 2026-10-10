@extends('admin.layouts.app')
@section('title', 'Fabric Transfer')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- PAGE HEADER -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title m-0">Fabric Transfer</h1>
                <p class="erp-page-subtitle m-0">Transfer fabric rolls between cutting masters and warehouses</p>
            </div>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <a href="{{ route('admin.inventory.fabric_transfer.history') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-history mr-1"></i> Transfer history
                </a>
                <a href="{{ route('admin.inventory.index') }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to inventory
                </a>
            </div>
        </div>

        <form id="transferForm">
            @csrf
            <div class="row">
                {{-- Selection Sidebar --}}
                <div class="col-md-4 mb-2">
                    <div class="erp-card p-3">
                        <div class="border-bottom pb-2 mb-3">
                            <h6 class="m-0 font-weight-bold text-dark" style="font-size: 0.9rem;">
                                <i class="fas fa-sliders-h mr-1 text-primary"></i> Transfer parameters
                            </h6>
                        </div>

                        <div class="form-group mb-2">
                            <label class="erp-label mb-1">From warehouse *</label>
                            <select name="from_warehouse_id" id="from_warehouse" class="form-control form-control-sm select2" required>
                                <option value="">Select warehouse</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->cutting_master_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-2">
                            <label class="erp-label mb-1">Select fabric(s) *</label>
                            <select name="fabric_ids[]" id="fabric_id" class="form-control form-control-sm select2" multiple disabled required>
                                <option value="">Select fabric</option>
                            </select>
                        </div>

                        <div class="form-group mb-2">
                            <label class="erp-label mb-1">To warehouse *</label>
                            <select name="to_warehouse_id" id="to_warehouse" class="form-control form-control-sm select2" required>
                                <option value="">Select destination</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->cutting_master_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-2">
                            <label class="erp-label mb-1">Transfer date *</label>
                            <input type="date" name="transfer_date" class="form-control form-control-sm erp-input" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="form-group mb-0">
                            <label class="erp-label mb-1">Remarks</label>
                            <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                </div>

                {{-- Rolls List --}}
                <div class="col-md-8 mb-2">
                    <div class="erp-card p-0">
                        <div class="p-2 border-bottom d-flex justify-content-between align-items-center" style="background-color: #f8fafc;">
                            <h6 class="m-0 font-weight-bold text-dark" style="font-size: 0.9rem;">
                                <i class="fas fa-boxes mr-1 text-primary"></i> Select fabric rolls
                            </h6>
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" id="selectAll">
                                <label for="selectAll" class="custom-control-label erp-label" style="cursor: pointer;">Select all</label>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                            <table class="erp-table table-bordered table-hover mb-0" id="rollsTable">
                                <thead style="position: sticky; top: 0; z-index: 5;">
                                    <tr>
                                        <th style="width: 40px; text-align: center;">#</th>
                                        <th>Roll no</th>
                                        <th>Remaining qty (Mtr)</th>
                                        <th>Current location</th>
                                    </tr>
                                </thead>
                                <tbody id="rollsBody">
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="fas fa-arrow-left fa-2x mb-2 d-block opacity-50"></i>
                                            Please select Warehouse and Fabric to load rolls
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-2 border-top bg-white text-right">
                            <button type="submit" class="btn-erp btn-erp-primary py-2 px-4 font-weight-bold" id="submitBtn" disabled>
                                <i class="fas fa-exchange-alt mr-1"></i> Transfer selected rolls
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    $('#from_warehouse').change(function() {
        let whId = $(this).val();
        $('#fabric_id').val('').trigger('change').prop('disabled', true);
        $('#rollsBody').html('<tr><td colspan="4" class="text-center py-5 text-muted">Loading fabrics...</td></tr>');
        
        if (whId) {
            $.get("{{ route('admin.inventory.fabric_transfer.get-fabrics') }}", {warehouse_id: whId}, function(data) {
                let options = '<option value="">Select Fabric</option>';
                data.forEach(function(fabric) {
                    options += `<option value="${fabric.id}">${fabric.name}</option>`;
                });
                $('#fabric_id').html(options).prop('disabled', false);
                $('#rollsBody').html('<tr><td colspan="4" class="text-center py-5 text-muted">Please select a fabric</td></tr>');
            });
        } else {
            $('#rollsBody').html('<tr><td colspan="4" class="text-center py-5 text-muted">Please select a warehouse</td></tr>');
        }
    });

    $('#fabric_id').change(function() {
        let fabricIds = $(this).val();
        let whId = $('#from_warehouse').val();
        
        if (fabricIds && fabricIds.length > 0 && whId) {
            $('#rollsBody').html('<tr><td colspan="4" class="text-center py-5 text-muted">Loading rolls...</td></tr>');
            $.get("{{ route('admin.inventory.fabric_transfer.get-rolls') }}", {warehouse_id: whId, fabric_ids: fabricIds}, function(data) {
                let rows = '';
                if (data.length > 0) {
                    data.forEach(function(roll) {
                        rows += `
                            <tr>
                                <td class="text-center">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input roll-check" type="checkbox" name="roll_ids[]" id="roll_${roll.id}" value="${roll.id}">
                                        <label for="roll_${roll.id}" class="custom-control-label"></label>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-light border font-weight-bold text-dark px-2 py-1">${roll.roll_number}</span>
                                    <div class="small text-muted font-weight-bold mt-1">${roll.fabric ? roll.fabric.name : 'Unknown Fabric'}</div>
                                </td>
                                <td><strong style="color: #05421c;">${roll.remaining_quantity}</strong> mtr</td>
                                <td><small class="text-muted"><i class="fas fa-map-marker-alt mr-1"></i> ${$('#from_warehouse option:selected').text()}</small></td>
                            </tr>
                        `;
                    });
                    $('#submitBtn').prop('disabled', false);
                } else {
                    rows = '<tr><td colspan="4" class="text-center py-5 text-warning font-weight-bold">No rolls available for selected fabrics</td></tr>';
                    $('#submitBtn').prop('disabled', true);
                }
                $('#rollsBody').html(rows);
            });
        } else {
            $('#rollsBody').html('<tr><td colspan="4" class="text-center py-5 text-muted">Please select one or more fabrics</td></tr>');
            $('#submitBtn').prop('disabled', true);
        }
    });

    $(document).on('change', '.roll-check', function() {
        updateSubmitButton();
    });

    $('#selectAll').change(function() {
        $('.roll-check').prop('checked', $(this).is(':checked'));
        updateSubmitButton();
    });

    function updateSubmitButton() {
        let checked = $('.roll-check:checked').length;
        $('#submitBtn').prop('disabled', checked === 0);
    }

    $('#transferForm').submit(function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Confirm Transfer?',
            text: "Are you sure you want to transfer the selected rolls?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#05421c',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, transfer them!'
        }).then((result) => {
            if (result.isConfirmed) {
                let formData = $(this).serialize();
                $.post("{{ route('admin.inventory.fabric_transfer.store') }}", formData, function(response) {
                    if (response.status === 'success') {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        setTimeout(() => {
                            window.location.href = response.redirect;
                        }, 1000);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }).fail(function(xhr) {
                    Swal.fire('Error', xhr.responseJSON.message || 'Something went wrong', 'error');
                });
            }
        });
    });
});
</script>
@endsection
