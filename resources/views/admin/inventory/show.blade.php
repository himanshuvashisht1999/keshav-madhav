@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper erp-page p-2">
        <div class="container-fluid p-0">
            <div class="erp-header-bar mb-2">
                <div>
                    <h1 class="erp-page-title">Product Group Details</h1>
                    <p class="erp-page-subtitle">Viewing details for: {{ $group_info->product_name }} ({{ $group_info->design_number }})</p>
                </div>
                <div class="erp-header-actions">
                    <a href="{{ route('admin.inventory.index') }}" class="btn-erp btn-erp-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Inventory
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- LEFT COLUMN: INFO -->
                <div class="col-md-4">
                    <div class="erp-card mb-2">
                        <div class="erp-card-header">
                            <h3 class="erp-card-title"><i class="fas fa-info-circle mr-1"></i> General information</h3>
                        </div>
                        <div class="erp-card-body p-0">
                            <table class="erp-table table-bordered mb-0">
                                <tr>
                                    <th style="width: 130px;">Image</th>
                                    <td>
                                        @php
                                            $imgSrc = $group_info->product_image ? asset('assets/products/' . $group_info->product_image) : asset('images/image-placeholder.png');
                                        @endphp
                                        <a href="javascript:void(0)" onclick="openVariantImageModal({{ $group_info->variant_id ?? 'null' }}, '{{ $imgSrc }}')">
                                            <img src="{{ $imgSrc }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;" onerror="this.src='{{ asset('images/image-placeholder.png') }}'">
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Product name</th>
                                    <td class="font-weight-bold">{{ $group_info->product_name }}</td>
                                </tr>
                                <tr>
                                    <th>Design number</th>
                                    <td class="font-weight-bold text-success">{{ $group_info->design_number }}</td>
                                </tr>
                                <tr>
                                    <th>Size set</th>
                                    <td><span class="badge badge-light border">{{ $group_info->size_set_name }}</span></td>
                                </tr>
                                <tr>
                                    <th>Fitting</th>
                                    <td class="font-weight-bold">{{ $group_info->fitting_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Pattern</th>
                                    <td class="font-weight-bold">{{ $group_info->pattern_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>MRP</th>
                                    <td class="font-weight-bold text-dark">₹{{ number_format($group_info->mrp, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Total boxes</th>
                                    <td class="font-weight-bold text-success">{{ $items->sum('total_boxes') }}</td>
                                </tr>
                                <tr>
                                    <th>Total order</th>
                                    <td class="font-weight-bold text-danger">{{ $total_order }} Boxes</td>
                                </tr>
                                <tr>
                                    <th>Total quantity</th>
                                    <td class="font-weight-bold text-primary" style="font-size: 1.1rem;">
                                        {{ $items->sum(function($item) { return $item->quantity * $item->total_boxes; }) }} <small>Pcs</small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: TABLE -->
                <div class="col-md-8">
                    <div class="erp-card mb-2">
                        <div class="erp-card-header">
                            <h3 class="erp-card-title"><i class="fas fa-boxes mr-1"></i> Content details</h3>
                            <select id="colorFilter" class="form-control form-control-sm w-auto" style="height: 28px !important; font-size: 12px;">
                                <option value="">All Colors</option>
                                @php
                                    $unique_colors = $items->pluck('color_name')->unique()->filter();
                                @endphp
                                @foreach($unique_colors as $color)
                                    <option value="{{ $color }}">{{ $color }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="erp-card-body p-0">
                            <div class="table-responsive">
                                <table class="erp-table table-bordered table-striped table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Warehouse</th>
                                            <th>Rack</th>
                                            <th>Barcode</th>
                                            <th>Color</th>
                                            <th class="text-center">Pcs/box</th>
                                            <th class="text-center">Total boxes</th>
                                            <th class="text-center">Total order</th>
                                            <th class="text-center">Total qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $item)
                                            @php
                                                $total_qty = $item->quantity * $item->total_boxes;
                                            @endphp
                                            @if($item->total_boxes == 0 && $item->color_total_order == 0 && $total_qty == 0)
                                                @continue
                                            @endif
                                            <tr class="item-row" data-color="{{ $item->color_name ?? 'N/A' }}">
                                                <td>
                                                    @if($item->rack && $item->rack->storeroom)
                                                        <span class="badge badge-secondary">{{ $item->rack->storeroom->name }}</span>
                                                    @else
                                                        <span class="text-muted">Unassigned</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($item->rack)
                                                        <span class="badge badge-info">{{ $item->rack->name }}</span>
                                                    @else
                                                        <span class="text-muted">Unassigned</span>
                                                    @endif
                                                </td>
                                                <td class="font-weight-bold text-dark">{{ $item->barcode }}</td>
                                                <td class="font-weight-bold">{{ $item->color_name ?? 'N/A' }}</td>
                                                <td class="text-center">{{ $item->quantity }}</td>
                                                <td class="text-center font-weight-bold text-success">{{ $item->total_boxes }}</td>
                                                <td class="text-center font-weight-bold text-danger">{{ $item->color_total_order }}</td>
                                                <td class="text-center font-weight-bold text-success">{{ $total_qty }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#colorFilter').on('change', function() {
                var selectedColor = $(this).val();
                if (selectedColor) {
                    $('.item-row').hide();
                    $('.item-row').filter(function() {
                        return $(this).data('color') == selectedColor;
                    }).show();
                } else {
                    $('.item-row').show();
                }
            });
        });
    </script>

    <!-- Variant Images Modal -->
    <div class="modal fade" id="variantImagesModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border-radius: 12px; border: none; overflow: hidden;">
                <div class="modal-header bg-dark text-white border-0">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-images mr-2"></i>Product Images
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-light text-center">
                    <div id="modal_big_image_container" class="mb-4" style="background: #fff; padding: 10px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <img id="modal_big_image" src="" style="max-height: 400px; max-width: 100%; object-fit: contain; border-radius: 8px;">
                        <div id="modal_big_image_caption" class="mt-2 font-weight-bold text-muted"></div>
                    </div>
                    
                    <div id="modal_thumbnails_container" class="d-flex justify-content-center flex-wrap" style="gap: 15px;">
                        <!-- Thumbnails will be injected here via JS -->
                    </div>
                    <div id="modal_images_loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openVariantImageModal(variantId, defaultImageSrc) {
            $('#variantImagesModal').modal('show');
            $('#modal_big_image').attr('src', defaultImageSrc);
            $('#modal_big_image_caption').text('Size Set');
            $('#modal_thumbnails_container').empty();
            $('#modal_images_loading').show();

            if (!variantId) {
                $('#modal_images_loading').hide();
                return;
            }

            $.ajax({
                url: "{{ route('admin.inventory.get_variant_images', '') }}/" + variantId,
                type: 'GET',
                success: function(res) {
                    $('#modal_images_loading').hide();
                    if (res.success && res.images.length > 0) {
                        let html = '';
                        res.images.forEach(function(img) {
                            html += `
                                <div class="thumbnail-wrapper" style="cursor: pointer; text-align: center; width: 80px;" onclick="updateModalBigImage('${img.url}', '${img.color}')">
                                    <img src="${img.url}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px; border: 2px solid #ddd; padding: 2px; transition: 0.2s;" onerror="this.src='{{ asset('images/image-placeholder.png') }}'" class="modal-thumbnail">
                                    <small class="d-block mt-1 text-muted text-truncate" title="${img.color}">${img.color}</small>
                                </div>
                            `;
                        });
                        $('#modal_thumbnails_container').html(html);
                    } else {
                        $('#modal_thumbnails_container').html('<p class="text-muted w-100">No additional color images found.</p>');
                    }
                },
                error: function() {
                    $('#modal_images_loading').hide();
                    $('#modal_thumbnails_container').html('<p class="text-danger w-100">Failed to load images.</p>');
                }
            });
        }

        function updateModalBigImage(url, caption) {
            $('#modal_big_image').attr('src', url);
            $('#modal_big_image_caption').text(caption);
            $('.modal-thumbnail').css('border-color', '#ddd');
            event.currentTarget.querySelector('img').style.borderColor = '#007bff';
        }
    </script>
@endsection