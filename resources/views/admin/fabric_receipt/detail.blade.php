@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12 text-center">
                    <h1 class="mb-0">Fabric Receipt Detail</h1>
                    <small class="text-muted">Fill fabric details while viewing challan & fabric images</small>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <div class="card shadow-sm">
                <form action="{{ route('admin.fabric_receipt.storeDetail') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $data->id }}">

                    <div class="card-body">
                        <div class="row">

                            <!-- LEFT: COMPLETE FORM -->
                            <div class="col-lg-8 mb-3">
                                <div class="card border-0 h-100">
                                    <div class="card-header bg-light py-2">
                                        <strong>Fabric Receipt Form</strong>
                                    </div>
                                    <div class="card-body">

                                        {{-- Purchase Order --}}
                                        <div class="form-group mb-4">
                                            <label class="font-weight-bold">Purchase Order</label>
                                            <select name="purchase_order_id" id="purchase_order_id" class="form-control select2">
                                                <option value="">NILL</option>
                                                @foreach ($purchase_orders as $single_data)
                                                    <option value="{{ $single_data->id }}">{{ $single_data->sku }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Fabric Table --}}
                                        <div class="mb-2 d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">Fabric Rolls</h5>
                                            <small class="text-muted">Select fabric, enter roll & meters. Image will auto-show.</small>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped text-center align-middle mb-2" id="fabric-table">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th style="width:35%;">Fabric</th>
                                                        <th style="width:25%;">Roll No</th>
                                                        <th style="width:15%;">Meter</th>
                                                        <th style="width:25%;">Fabric Image</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @for ($i = 1; $i <= $data->roll; $i++)
                                                        <tr>
                                                            <td>
                                                                <select name="rolls[{{ $i }}][fabric_sku]" 
                                                                        class="form-control select2 fabric-sku" 
                                                                        data-row="{{ $i }}" required>
                                                                    <option value="">Select Fabric</option>
                                                                    @foreach ($fabrics as $single_data)
                                                                        <option value="{{ $single_data->id }}">{{ $single_data->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="number" class="form-control" name="rolls[{{ $i }}][roll]" required>
                                                            </td>
                                                            <td>
                                                                <input type="number" class="form-control meter" name="rolls[{{ $i }}][meter]" data-row="{{ $i }}" min="0" step="0.01" required>
                                                            </td>
                                                            <td class="fabric-image-cell" data-row="{{ $i }}">
                                                                <span class="text-muted no-image-text">No image</span>
                                                                <img src="" class="img-thumbnail fabric-thumb d-none" style="max-height:60px; cursor:pointer;">
                                                            </td>
                                                        </tr>
                                                    @endfor
                                                </tbody>
                                            </table>
                                        </div>

                                        <button type="button" class="btn btn-primary btn-sm mt-1" id="add-row">
                                            + Add More
                                        </button>

                                        {{-- Save button --}}
                                        <div class="text-right mt-4">
                                            <button type="submit" class="btn btn-success btn-lg px-4">
                                                Save
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- RIGHT: CHALLAN IMAGE -->
                            <div class="col-lg-4 mb-3">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header bg-light py-2">
                                        <strong>Challan Image</strong>
                                    </div>
                                    <div class="card-body text-center" style="max-height: 600px; overflow-y: auto;">
                                        @if(!empty($data->challan_photo))
                                            <img src="{{ $data->challan_photo }}" 
                                                class="img-fluid rounded shadow-sm challan-thumb"
                                                style="max-height: 550px; object-fit: contain; cursor:pointer;">
                                        @else
                                            <p class="text-muted">No challan image uploaded.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </div> <!-- /row -->
                    </div> <!-- /card-body -->
                </form>
            </div>

        </div>
    </section>
</div>

{{-- Map fabric id => image url --}}
@php
    $fabricImageMap = [];
    foreach ($fabrics as $f) {
        // If you store only file name, change this to: asset('storage/fabrics/'.$f->image)
        $fabricImageMap[$f->id] = $f->image;
    }
@endphp

<script>
    const fabricImages = @json($fabricImageMap); // { fabricId: "image_url" }
</script>

{{-- Modal for big fabric image --}}
<div class="modal fade" id="fabricImageModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Fabric Image</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <img id="fabricImageModalImg" src="" class="img-fluid">
      </div>
    </div>
  </div>
</div>

<style>
    .table td, .table th {
        vertical-align: middle !important;
    }
</style>

<script>
$(document).ready(function () {
    // init select2
    $('.select2').select2({ width: '100%' });

    let rowCount = {{ $data->roll }};

    window._propagatingFabric = false;
    window._propagatingMeter  = false;
    window._propagatingBatch  = false;

    function updateFabricImage(row, fabricId) {
        const imageUrl = fabricImages[fabricId] || null;
        const $cell   = $('.fabric-image-cell[data-row="' + row + '"]');
        const $img    = $cell.find('img.fabric-thumb');
        const $text   = $cell.find('.no-image-text');

        if (imageUrl) {
            $img.attr('src', imageUrl).removeClass('d-none');
            $text.addClass('d-none');
        } else {
            $img.attr('src', '').addClass('d-none');
            $text.removeClass('d-none');
        }
    }

    // Fabric selection -> copy down + show images
    $(document).on('change', '.fabric-sku', function () {
        if (window._propagatingFabric) return;
        const row      = parseInt($(this).data('row'));
        const fabricId = $(this).val();

        updateFabricImage(row, fabricId);

        if (!fabricId) return;

        window._propagatingFabric = true;
        $('.fabric-sku').each(function () {
            const currentRow = parseInt($(this).data('row'));
            if (currentRow > row) {
                $(this).val(fabricId).trigger('change.select2');
                updateFabricImage(currentRow, fabricId);
            }
        });
        window._propagatingFabric = false;
    });

    // Meter copy down
    $(document).on('input', '.meter', function () {
        if (window._propagatingMeter) return;
        const row = parseInt($(this).data('row'));
        const value = $(this).val();
        if (value === '') return;

        window._propagatingMeter = true;
        $('.meter').each(function () {
            const currentRow = parseInt($(this).data('row'));
            if (currentRow > row) {
                $(this).val(value).trigger('input');
            }
        });
        window._propagatingMeter = false;
    });

    // Add More Row
    $('#add-row').on('click', function () {
        rowCount++;

        const newRow = `
            <tr>
                <td>
                    <select name="rolls[${rowCount}][fabric_sku]"
                            class="form-control select2 fabric-sku"
                            data-row="${rowCount}" required>
                        <option value="">Select Fabric</option>
                        @foreach ($fabrics as $single_data)
                            <option value="{{ $single_data->id }}">{{ $single_data->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" name="rolls[${rowCount}][roll]" class="form-control" required>
                </td>
                <td>
                    <input type="number"
                           name="rolls[${rowCount}][meter]"
                           class="form-control meter"
                           data-row="${rowCount}"
                           min="0" step="0.01" required>
                </td>
                <td class="fabric-image-cell" data-row="${rowCount}">
                    <span class="text-muted no-image-text">No image</span>
                    <img src="" class="img-thumbnail fabric-thumb d-none" style="max-height:60px; cursor:pointer;">
                </td>
            </tr>
        `;

        const $tbody = $('#fabric-table tbody');
        $tbody.append(newRow);

        $tbody.find('tr:last .select2').select2({ width: '100%' });
    });

    // Click fabric thumbnail -> big modal
    $(document).on('click', '.fabric-thumb, .challan-thumb', function () {
        const src = $(this).attr('src');
        if (!src) return;
        $('#fabricImageModalImg').attr('src', src);
        $('#fabricImageModal').modal('show');
    });

});
</script>

@endsection
