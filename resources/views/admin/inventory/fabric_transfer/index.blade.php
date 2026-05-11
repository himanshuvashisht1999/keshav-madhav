@extends('admin.layouts.app')
@section('title', 'Fabric Transfer')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Fabric Transfer</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-sm-right">
                        <a href="{{ route('admin.inventory.fabric_transfer.history') }}" class="btn btn-dark">
                            <i class="fas fa-history"></i> Transfer History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <form id="transferForm">
                @csrf
                <div class="row">
                    {{-- Selection Sidebar --}}
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title">Transfer Details</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>From Warehouse <span class="text-danger">*</span></label>
                                    <select name="from_warehouse_id" id="from_warehouse" class="form-control select2" required>
                                        <option value="">Select Warehouse</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->cutting_master_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mt-3">
                                    <label>Select Fabric(s) <span class="text-danger">*</span></label>
                                    <select name="fabric_ids[]" id="fabric_id" class="form-control select2" multiple disabled required>
                                        <option value="">Select Fabric</option>
                                    </select>
                                </div>

                                <div class="form-group mt-3">
                                    <label>To Warehouse <span class="text-danger">*</span></label>
                                    <select name="to_warehouse_id" id="to_warehouse" class="form-control select2" required>
                                        <option value="">Select Destination</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->cutting_master_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mt-3">
                                    <label>Transfer Date <span class="text-danger">*</span></label>
                                    <input type="date" name="transfer_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>

                                <div class="form-group mt-3">
                                    <label>Remarks</label>
                                    <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Rolls List --}}
                    <div class="col-md-8">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">Select Rolls</h3>
                                <div class="card-tools">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" type="checkbox" id="selectAll">
                                        <label for="selectAll" class="custom-control-label">Select All</label>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                    <table class="table table-hover table-striped mb-0" id="rollsTable">
                                        <thead class="sticky-top bg-light">
                                            <tr>
                                                <th width="50">#</th>
                                                <th>Roll No</th>
                                                <th>Batch</th>
                                                <th>Remaining Qty (Mtr)</th>
                                                <th>Current Location</th>
                                            </tr>
                                        </thead>
                                        <tbody id="rollsBody">
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted">
                                                    <i class="fas fa-arrow-left fa-2x mb-2 d-block"></i>
                                                    Please select Warehouse and Fabric to load rolls
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white text-right">
                                <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm" id="submitBtn" disabled>
                                    <i class="fas fa-exchange-alt"></i> Transfer Selected Rolls
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    $('#from_warehouse').change(function() {
        let whId = $(this).val();
        $('#fabric_id').val('').trigger('change').prop('disabled', true);
        $('#rollsBody').html('<tr><td colspan="5" class="text-center py-5 text-muted">Loading fabrics...</td></tr>');
        
        if (whId) {
            $.get("{{ route('admin.inventory.fabric_transfer.get-fabrics') }}", {warehouse_id: whId}, function(data) {
                let options = '<option value="">Select Fabric</option>';
                data.forEach(function(fabric) {
                    options += `<option value="${fabric.id}">${fabric.name}</option>`;
                });
                $('#fabric_id').html(options).prop('disabled', false);
                $('#rollsBody').html('<tr><td colspan="5" class="text-center py-5 text-muted">Please select a fabric</td></tr>');
            });
        } else {
            $('#rollsBody').html('<tr><td colspan="5" class="text-center py-5 text-muted">Please select a warehouse</td></tr>');
        }
    });

    $('#fabric_id').change(function() {
        let fabricIds = $(this).val();
        let whId = $('#from_warehouse').val();
        
        if (fabricIds && fabricIds.length > 0 && whId) {
            $('#rollsBody').html('<tr><td colspan="5" class="text-center py-5 text-muted">Loading rolls...</td></tr>');
            $.get("{{ route('admin.inventory.fabric_transfer.get-rolls') }}", {warehouse_id: whId, fabric_ids: fabricIds}, function(data) {
                let rows = '';
                if (data.length > 0) {
                    data.forEach(function(roll) {
                        rows += `
                            <tr>
                                <td>
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input roll-check" type="checkbox" name="roll_ids[]" id="roll_${roll.id}" value="${roll.id}">
                                        <label for="roll_${roll.id}" class="custom-control-label"></label>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-info">${roll.roll_number}</span>
                                    <div class="small text-muted">${roll.fabric ? roll.fabric.name : 'Unknown Fabric'}</div>
                                </td>
                                <td>${roll.batch_no || 'N/A'}</td>
                                <td><strong>${roll.remaining_quantity}</strong> mtr</td>
                                <td><small class="text-muted"><i class="fas fa-map-marker-alt"></i> ${$('#from_warehouse option:selected').text()}</small></td>
                            </tr>
                        `;
                    });
                    $('#submitBtn').prop('disabled', false);
                } else {
                    rows = '<tr><td colspan="5" class="text-center py-5 text-warning">No rolls available for selected fabrics</td></tr>';
                    $('#submitBtn').prop('disabled', true);
                }
                $('#rollsBody').html(rows);
            });
        } else {
            $('#rollsBody').html('<tr><td colspan="5" class="text-center py-5 text-muted">Please select one or more fabrics</td></tr>');
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
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
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
<style>
    .sticky-top {
        z-index: 10;
    }
    .table thead th {
        border-top: 0;
    }
    .card-title {
        font-weight: 600;
    }
</style>
@endsection
