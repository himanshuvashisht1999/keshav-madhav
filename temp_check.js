
        let isSampleSet = 0;
        let allowOverStockNormal = 0;
        let allowOverStockSample = 0;
        let allowOverStock = isSampleSet ? allowOverStockSample : allowOverStockNormal;
        let showStock = 0;
        $(document).ready(function () {

            // Image Zoom functionality
            let pz = null;
            const elem = document.getElementById('zoomedImage');
            
            $(document).on('click', '.zoom-image, .btn-preview-photo', function(e) {
                e.stopPropagation();
                var src = $(this).attr('src') || $(this).data('src');
                if (!src) return;
                $('#zoomedImage').attr('src', src);
                $('#imageZoomModal').modal('show');
                
                if(pz) {
                    pz.destroy();
                }
                pz = Panzoom(elem, {
                    maxScale: 5,
                    minScale: 1
                });
                elem.parentElement.addEventListener('wheel', pz.zoomWithWheel);
            });
            
            $(document).on('click', '#btnZoomIn', function() {
                if(pz) pz.zoomIn();
            });
            
            $(document).on('click', '#btnZoomOut', function() {
                if(pz) pz.zoomOut();
            });
            
            $('#imageZoomModal').on('hidden.bs.modal', function () {
                if(pz) {
                    pz.reset();
                }
                if ($('#scanSelectionModal').hasClass('show')) {
                    $('body').addClass('modal-open');
                }
            });
            if ($.fn.select2) {
                $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
            }

            let cart = new Map();
            const storageKey = 'agent_order_cart_0_0';
            const seePrice = 0;

            // --- SCANNER LOGIC ---
            let html5QrcodeScanner = null;

            $('#btnScanQR').click(function() {
                $('#scannerModal').modal('show');
            });

            $('#scannerModal').on('shown.bs.modal', function () {
                startScanner();
            });

            $('#scannerModal').on('hidden.bs.modal', function () {
                stopScanner();
            });

            function startScanner() {
                if (html5QrcodeScanner) return;
                
                html5QrcodeScanner = new Html5Qrcode("reader");
                const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                html5QrcodeScanner.start(
                    { facingMode: "environment" }, 
                    config, 
                    onScanSuccess
                ).catch(err => {
                    console.error("Scanner error:", err);
                    Swal.fire('Camera Error', 'Could not start camera scanner. Please ensure you have given camera permissions.', 'error');
                });
            }

            function stopScanner() {
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.stop().then(() => {
                        html5QrcodeScanner = null;
                    }).catch(err => console.error(err));
                }
            }

            function onScanSuccess(decodedText) {
                stopScanner();
                $('#scannerModal').modal('hide');
                handleBarcode(decodedText);
            }

            $('#btnManualSubmit').click(function() {
                const bc = $('#manual_barcode').val().trim();
                if (bc) {
                    $('#scannerModal').modal('hide');
                    handleBarcode(bc);
                }
            });

            function handleBarcode(barcode) {
                // If the barcode is a URL, extract the last segment (the actual barcode)
                if (barcode.includes('/fc/')) {
                    const parts = barcode.split('/');
                    barcode = parts[parts.length - 1];
                } else if (barcode.startsWith('http')) {
                    // Fallback for any other URL format
                    const parts = barcode.split('/');
                    barcode = parts[parts.length - 1];
                }

                Swal.fire({
                    title: 'Processing...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: "0",
                    data: { barcode: barcode },
                    success: function(res) {
                        Swal.close();
                        if (res.success) {
                            setTimeout(() => {
                                showColorSelection(res);
                            }, 400);
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error', 'Failed to fetch product details.', 'error');
                    }
                });
            }

            function showColorSelection(data) {
                const list = $('#colorSelectionList');
                list.empty();

                if (!data.size_sets || data.size_sets.length === 0) {
                    if (data.product && data.colors) {
                        data.size_sets = [{
                            size_set_id: data.product.size_set_id || (data.product.variants && data.product.variants[0] ? data.product.variants[0].master_size_measurement_id : 1),
                            size_set_name: data.product.size_set_name || 'Standard',
                            mrp: data.product.mrp || 0,
                            unit_price: data.product.unit_price || 0,
                            image: data.product.image || null,
                            colors: data.colors
                        }];
                    } else {
                        list.append('<div class="alert alert-warning">No variations found.</div>');
                        $('#scanSelectionModal').modal('show');
                        return;
                    }
                }

                // Find initial Hero Image (first size set image or product image or first color image)
                let initialHeroImage = '';
                for (let ss of data.size_sets) {
                    if (ss.image) { initialHeroImage = ss.image; break; }
                    if (ss.colors && ss.colors.length > 0) {
                        for (let c of ss.colors) {
                            if (c.image) { initialHeroImage = c.image; break; }
                        }
                    }
                    if (initialHeroImage) break;
                }
                if (!initialHeroImage && data.product && data.product.image) {
                    initialHeroImage = data.product.image;
                }

                let maxColors = 0;
                data.size_sets.forEach(ss => {
                    const count = (ss.colors || []).length;
                    if (count > maxColors) maxColors = count;
                });
                if (maxColors === 0) maxColors = 1;

                let html = '';

                // 1 & 2: Top Header Section (Left: Design Info, Right: Larger Image)
                // 1. Centered Design Number at Starting
                html += `
                    <div class="text-center mb-2">
                        <span class="badge badge-primary px-3 py-2 font-weight-bold shadow-sm d-inline-block" style="font-size: 16px; border-radius: 8px; letter-spacing: 1px;">
                            DESIGN NO: ${data.product.design_number || data.product.name || 'N/A'}
                        </span>
                        ${(data.product.name && data.product.name !== data.product.design_number) ? `
                            <div class="font-weight-bold text-dark mt-1" style="font-size: 15px;">${data.product.name}</div>
                        ` : ''}
                        ${data.product.series_name ? `
                            <div class="small text-muted font-weight-bold"><i class="fas fa-layer-group mr-1"></i> ${data.product.series_name}</div>
                        ` : ''}
                    </div>
                `;

                // 2. Centered Large Preview Image
                html += `
                    <div class="text-center mb-3">
                        <div class="d-inline-block position-relative rounded-xl shadow-sm border overflow-hidden bg-white" style="max-width: 100%;">
                            <img id="scanHeroImage" 
                                 src="${initialHeroImage || ''}" 
                                 class="img-fluid zoom-image" 
                                 style="max-height: 340px; min-height: 200px; width: auto; max-width: 100%; object-fit: contain; cursor: pointer; ${!initialHeroImage ? 'display:none;' : ''}" 
                                 data-src="${initialHeroImage || ''}"
                                 alt="Product Preview">
                            <div id="scanHeroPlaceholder" style="width: 260px; height: 180px; display: ${initialHeroImage ? 'none' : 'flex'}; align-items: center; justify-content: center; background: #f8f9fa;">
                                <div class="text-muted text-center"><i class="fas fa-image fa-3x opacity-50 mb-1"></i><div class="small font-weight-bold">No Image</div></div>
                            </div>
                            <span class="badge badge-dark position-absolute" style="bottom: 8px; right: 8px; opacity: 0.85; pointer-events: none; font-size: 11px; padding: 4px 8px; border-radius: 6px;">
                                <i class="fas fa-search-plus mr-1"></i>Tap to Zoom
                            </span>
                        </div>
                    </div>
                `;

                // 3. Master Global Apply Bar
                html += `
                    <div class="card border-primary mb-3 shadow-sm" style="background: #eef5ff; border-radius: 12px;">
                        <div class="card-body p-2 d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                            <div>
                                <span class="font-weight-bold text-primary"><i class="fas fa-bolt mr-1"></i> Apply Quantity to All</span>
                                <div class="small text-muted">Set quantity for all size sets and colors</div>
                            </div>
                            <div class="quantity-control-app d-flex align-items-center p-1 border-primary bg-white shadow-sm" style="border-radius: 8px;">
                                <button type="button" class="btn-q btn-minus-master text-primary font-weight-bold">-</button>
                                <input type="number" id="masterQtyInput" class="text-primary font-weight-bold" min="0" value="0" style="width: 45px; text-align: center; border: 0; background: transparent;">
                                <button type="button" class="btn-q btn-plus-master text-primary font-weight-bold">+</button>
                            </div>
                        </div>
                    </div>
                `;

                // 4. Excel Structure Table (Size Sets = Vertical, Colors = Horizontal)
                html += `
                    <div class="table-responsive border rounded-lg mb-3 shadow-sm bg-white" style="max-height: 440px; overflow-y: auto;">
                        <table class="table table-bordered table-sm mb-0 matrix-table text-center align-middle" style="font-size: 13px;">
                            <thead>
                                <tr>
                                    <th class="align-middle text-center font-weight-bold text-white px-2 py-2" style="min-width: 140px; width: 140px; background-color: #007bff;">
                                        SIZE SET
                                    </th>
                                    ${Array.from({ length: maxColors }).map((_, idx) => {
                                        let colColorObj = null;
                                        for (let ss of data.size_sets) {
                                            if (ss.colors && ss.colors[idx]) {
                                                colColorObj = ss.colors[idx];
                                                break;
                                            }
                                        }
                                        const colName = colColorObj ? (colColorObj.name ? `${colColorObj.name}(${colColorObj.id})` : `COLOR ${colColorObj.id}`) : `COL ${idx + 1}`;

                                        return `
                                            <th class="align-middle text-center font-weight-bold text-white p-2 matrix-col-header matrix-col-${idx}" data-col-index="${idx}" style="min-width: 145px; background-color: #007bff; color: #fff;">
                                                <div class="custom-control custom-checkbox d-inline-block text-left" style="max-width: 100%;">
                                                    <input type="checkbox" class="custom-control-input toggle-col-checkbox" id="toggle_col_${idx}" data-col-index="${idx}" checked>
                                                    <label class="custom-control-label font-weight-bold text-white" for="toggle_col_${idx}" style="font-size: 13px; cursor: pointer; user-select: none; line-height: 1.5;" title="${colName}">
                                                        <span class="d-inline-block text-truncate" style="max-width: 105px; vertical-align: middle;">${colName}</span>
                                                    </label>
                                                </div>
                                                <!-- Color Column batch stepper -->
                                                <div class="mt-1">
                                                    <div class="quantity-control-app d-inline-flex align-items-center p-0 border bg-white shadow-xs" style="border-radius: 6px; height: 26px;">
                                                        <button type="button" class="btn-q btn-minus-col px-2 py-0 text-primary font-weight-bold" data-col-index="${idx}" style="line-height: 1;">-</button>
                                                        <input type="number" class="col-batch-qty-input font-weight-bold text-center text-primary" data-col-index="${idx}" min="0" value="0" style="width: 32px; border: 0; background: transparent; font-size: 12px; padding: 0;">
                                                        <button type="button" class="btn-q btn-plus-col px-2 py-0 text-primary font-weight-bold" data-col-index="${idx}" style="line-height: 1;">+</button>
                                                    </div>
                                                </div>
                                            </th>
                                        `;
                                    }).join('')}
                                </tr>
                            </thead>
                            <tbody>
                `;

                data.size_sets.forEach(ss => {
                    html += `
                        <tr class="matrix-row-${ss.size_set_id}">
                            <td class="align-middle p-2 text-center matrix-ss-header-cell" style="min-width: 140px; width: 140px; background-color: #f1f7ff; border-right: 2px solid #b8daff;">
                                <div class="custom-control custom-checkbox d-inline-block mb-1">
                                    <input type="checkbox" class="custom-control-input toggle-ss-checkbox" id="toggle_ss_${ss.size_set_id}" data-size-set="${ss.size_set_id}" checked>
                                    <label class="custom-control-label font-weight-bold text-primary" for="toggle_ss_${ss.size_set_id}" style="font-size: 14px; cursor: pointer; user-select: none;">
                                        ${ss.size_set_name}
                                    </label>
                                </div>
                                <!-- Size Set Row batch stepper -->
                                <div>
                                    <div class="quantity-control-app d-inline-flex align-items-center p-0 border bg-white shadow-xs" style="border-radius: 6px; height: 26px;">
                                        <button type="button" class="btn-q btn-minus-ss px-2 py-0 text-primary" data-size-set="${ss.size_set_id}" style="line-height: 1;">-</button>
                                        <input type="number" class="ss-batch-qty-input font-weight-bold text-center text-primary" data-size-set="${ss.size_set_id}" min="0" value="0" style="width: 32px; border: 0; background: transparent; font-size: 12px; padding: 0;">
                                        <button type="button" class="btn-q btn-plus-ss px-2 py-0 text-primary" data-size-set="${ss.size_set_id}" style="line-height: 1;">+</button>
                                    </div>
                                </div>
                            </td>
                    `;

                    for (let c = 0; c < maxColors; c++) {
                        const colorObj = (ss.colors && ss.colors[c]) ? ss.colors[c] : null;
                        if (!colorObj) {
                            html += `<td class="matrix-cell align-middle text-muted bg-light p-2" data-size-set="${ss.size_set_id}" data-col-index="${c}">-</td>`;
                        } else {
                            const vKey = data.product.id + '_' + colorObj.id + '_' + ss.size_set_id;
                            const item = cart.get(vKey);
                            const currentQty = item ? item.qty : 0;
                            const isAdv = data.is_advance_sample;
                            const maxAttr = (isAdv || allowOverStock) ? '' : `max="${colorObj.available_boxes}"`;
                            const cImg = colorObj.image || ss.image || '';

                            html += `
                                <td class="matrix-cell matrix-cell-ss-${ss.size_set_id} matrix-cell-col-${c} align-middle p-2" data-size-set="${ss.size_set_id}" data-col-index="${c}" style="background: #fff; min-width: 145px;">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="font-weight-bold text-dark text-truncate text-left flex-grow-1" style="font-size: 13px;" title="${colorObj.name}(${colorObj.id})">
                                            <i class="fas fa-palette text-secondary mr-1" style="font-size: 11px;"></i>${colorObj.name}(${colorObj.id})
                                        </span>
                                        ${colorObj.image ? `
                                            <button type="button" class="btn btn-outline-info btn-xs btn-preview-photo rounded-circle p-0 ml-1 d-inline-flex align-items-center justify-content-center" data-src="${colorObj.image}" title="View Photo" style="width: 22px; height: 22px; flex-shrink: 0;">
                                                <i class="fas fa-eye" style="font-size: 10px;"></i>
                                            </button>
                                        ` : ''}
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="quantity-control-app d-inline-flex align-items-center p-0 border mx-auto shadow-xs" style="border-radius: 6px;">
                                            <button type="button" class="btn-q btn-minus-scan px-2 py-1" data-key="${vKey}">-</button>
                                            <input type="number" class="box-qty-scan-input font-weight-bold" 
                                                data-product-id="${data.product.id}"
                                                data-product-name="${(data.product.name || '').replace(/"/g, '&quot;')}"
                                                data-size-set-id="${ss.size_set_id}"
                                                data-size-set-name="${(ss.size_set_name || '').replace(/"/g, '&quot;')}"
                                                data-color-id="${colorObj.id}"
                                                data-color-name="${(colorObj.name || '').replace(/"/g, '&quot;')}"
                                                data-pcs="${colorObj.pcs_per_box}"
                                                data-price="${ss.unit_price}"
                                                ${maxAttr}
                                                value="${currentQty}"
                                                style="width: 38px; border: 0; background: transparent; text-align: center; font-size: 13px;">
                                            <button type="button" class="btn-q btn-plus-scan px-2 py-1" data-key="${vKey}">+</button>
                                        </div>
                                    </div>
                                    ${showStock && !isAdv ? `<div class="text-muted text-center mt-1" style="font-size: 10px; line-height: 1;">${colorObj.available_boxes} avl</div>` : ''}
                                </td>
                            `;
                        }
                    }

                    html += `</tr>`;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;

                // 5. Size Set Remarks
                html += `
                    <div class="card border-0 bg-light rounded-lg p-2 mb-2">
                        <span class="font-weight-bold text-muted small mb-2 d-block"><i class="fas fa-comment-alt mr-1"></i> Size Set Remarks (Optional)</span>
                        <div class="row">
                            ${data.size_sets.map(ss => `
                                <div class="col-sm-6 mb-2">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white font-weight-bold" style="font-size: 11px;">${ss.size_set_name}</span>
                                        </div>
                                        <input type="text" class="form-control size-set-remark-input" data-size-set="${ss.size_set_id}" placeholder="Remark...">
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;

                list.append(html);
                $('#scanSelectionModal').modal('show');
            }

            // Master Stepper Handlers
            $(document).on('click', '.btn-plus-master', function() {
                let input = $('#masterQtyInput');
                let val = parseInt(input.val()) || 0;
                input.val(val + 1).trigger('change');
            });

            $(document).on('click', '.btn-minus-master', function() {
                let input = $('#masterQtyInput');
                let val = parseInt(input.val()) || 0;
                if (val > 0) {
                    input.val(val - 1).trigger('change');
                }
            });

            $(document).on('change', '#masterQtyInput', function() {
                let masterQty = parseInt($(this).val()) || 0;
                if (masterQty < 0) {
                    masterQty = 0;
                    $(this).val(0);
                }

                // Sync active Size Set row and Color column steppers
                $('.matrix-table .ss-batch-qty-input:not(:disabled)').val(masterQty);
                $('.matrix-table .col-batch-qty-input:not(:disabled)').val(masterQty);

                $('.matrix-table .box-qty-scan-input:not(:disabled)').each(function() {
                    const max = parseInt($(this).attr('max'));
                    let targetQty = masterQty;
                    if (!allowOverStock && !isNaN(max) && targetQty > max) targetQty = max;

                    if (parseInt($(this).val()) !== targetQty) {
                        $(this).val(targetQty).trigger('change');
                    }
                });
            });

            // Size Set Row Quick Stepper Handlers
            $(document).on('click', '.btn-plus-ss', function() {
                let input = $(this).siblings('.ss-batch-qty-input');
                if (input.prop('disabled')) return;
                let val = parseInt(input.val()) || 0;
                input.val(val + 1).trigger('change');
            });

            $(document).on('click', '.btn-minus-ss', function() {
                let input = $(this).siblings('.ss-batch-qty-input');
                if (input.prop('disabled')) return;
                let val = parseInt(input.val()) || 0;
                if (val > 0) {
                    input.val(val - 1).trigger('change');
                }
            });

            $(document).on('change', '.ss-batch-qty-input', function() {
                const ssId = $(this).data('size-set');
                let rowQty = parseInt($(this).val()) || 0;
                if (rowQty < 0) {
                    rowQty = 0;
                    $(this).val(0);
                }

                $(`.matrix-row-${ssId} .box-qty-scan-input:not(:disabled)`).each(function() {
                    const max = parseInt($(this).attr('max'));
                    let targetQty = rowQty;
                    if (!allowOverStock && !isNaN(max) && targetQty > max) targetQty = max;

                    if (parseInt($(this).val()) !== targetQty) {
                        $(this).val(targetQty).trigger('change');
                    }
                });
            });

            // Color Column Quick Stepper Handlers
            $(document).on('click', '.btn-plus-col', function() {
                let input = $(this).siblings('.col-batch-qty-input');
                if (input.prop('disabled')) return;
                let val = parseInt(input.val()) || 0;
                input.val(val + 1).trigger('change');
            });

            $(document).on('click', '.btn-minus-col', function() {
                let input = $(this).siblings('.col-batch-qty-input');
                if (input.prop('disabled')) return;
                let val = parseInt(input.val()) || 0;
                if (val > 0) {
                    input.val(val - 1).trigger('change');
                }
            });

            $(document).on('change', '.col-batch-qty-input', function() {
                const colIndex = $(this).data('col-index');
                let colQty = parseInt($(this).val()) || 0;
                if (colQty < 0) {
                    colQty = 0;
                    $(this).val(0);
                }

                $(`.matrix-cell-col-${colIndex} .box-qty-scan-input:not(:disabled)`).each(function() {
                    const max = parseInt($(this).attr('max'));
                    let targetQty = colQty;
                    if (!allowOverStock && !isNaN(max) && targetQty > max) targetQty = max;

                    if (parseInt($(this).val()) !== targetQty) {
                        $(this).val(targetQty).trigger('change');
                    }
                });
            });

            // Toggle Size Set Row Selection
            $(document).on('change', '.toggle-ss-checkbox', function() {
                const ssId = $(this).data('size-set');
                const isChecked = $(this).is(':checked');
                const row = $(this).closest('tr');
                
                if (!isChecked) {
                    row.addClass('matrix-row-deselected');
                    row.find('.ss-batch-qty-input').prop('disabled', true).val(0);
                    row.find('.btn-minus-ss, .btn-plus-ss').prop('disabled', true);
                    
                    row.find('.box-qty-scan-input').each(function() {
                        $(this).prop('disabled', true);
                        if (parseInt($(this).val()) > 0) {
                            $(this).val(0).trigger('change');
                        }
                    });
                    row.find('.btn-minus-scan, .btn-plus-scan').prop('disabled', true);
                } else {
                    row.removeClass('matrix-row-deselected');
                    row.find('.ss-batch-qty-input').prop('disabled', false);
                    row.find('.btn-minus-ss, .btn-plus-ss').prop('disabled', false);
                    
                    row.find('.matrix-cell').each(function() {
                        const colIndex = $(this).data('col-index');
                        const isColChecked = $(`.toggle-col-checkbox[data-col-index="${colIndex}"]`).is(':checked');
                        if (isColChecked) {
                            $(this).removeClass('cell-disabled');
                            $(this).find('.box-qty-scan-input').prop('disabled', false);
                            $(this).find('.btn-minus-scan, .btn-plus-scan').prop('disabled', false);
                        }
                    });
                }
            });

            // Toggle Color Column Selection
            $(document).on('change', '.toggle-col-checkbox', function() {
                const colIndex = $(this).data('col-index');
                const isChecked = $(this).is(':checked');
                const th = $(this).closest('th');
                
                if (!isChecked) {
                    th.addClass('matrix-col-deselected');
                    th.find('.col-batch-qty-input').prop('disabled', true).val(0);
                    th.find('.btn-minus-col, .btn-plus-col').prop('disabled', true);
                    
                    $(`.matrix-cell-col-${colIndex}`).each(function() {
                        $(this).addClass('cell-disabled');
                        const input = $(this).find('.box-qty-scan-input');
                        input.prop('disabled', true);
                        if (parseInt(input.val()) > 0) {
                            input.val(0).trigger('change');
                        }
                        $(this).find('.btn-minus-scan, .btn-plus-scan').prop('disabled', true);
                    });
                } else {
                    th.removeClass('matrix-col-deselected');
                    th.find('.col-batch-qty-input').prop('disabled', false);
                    th.find('.btn-minus-col, .btn-plus-col').prop('disabled', false);
                    
                    $(`.matrix-cell-col-${colIndex}`).each(function() {
                        const ssId = $(this).data('size-set');
                        const isRowChecked = $(`.toggle-ss-checkbox[data-size-set="${ssId}"]`).is(':checked');
                        if (isRowChecked) {
                            $(this).removeClass('cell-disabled');
                            $(this).find('.box-qty-scan-input').prop('disabled', false);
                            $(this).find('.btn-minus-scan, .btn-plus-scan').prop('disabled', false);
                        }
                    });
                }
            });

            // Clear Order Handler
            $(document).on('click', '.btn-clear-order', function() {
                if (cart.size === 0) {
                    Swal.fire('Info', 'Your cart is already empty.', 'info');
                    return;
                }

                Swal.fire({
                    title: 'Clear Order?',
                    text: 'Are you sure you want to remove all selected items from this order?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Clear All',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        cart.clear();
                        localStorage.removeItem(storageKey);
                        $('.box-qty-input').val(0);
                        $('.variation-card').removeClass('has-qty');
                        $('.has-qty-top').remove();
                        $('.box-qty-scan-input').val(0);
                        $('#masterQtyInput').val(0);
                        $('.ss-batch-qty-input').val(0);
                        updateUI();
                        Swal.fire({
                            icon: 'success',
                            title: 'Order Cleared',
                            text: 'All items have been removed.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
            });

            // Individual Cell Stepper Handlers
            $(document).on('click', '.btn-plus-scan', function() {
                const input = $(this).closest('.quantity-control-app').find('.box-qty-scan-input');
                const max = parseInt(input.attr('max'));
                let val = parseInt(input.val()) || 0;
                if (allowOverStock || isNaN(max) || val < max) {
                    val++;
                    input.val(val).trigger('change');
                }
            });

            $(document).on('click', '.btn-minus-scan', function() {
                const input = $(this).closest('.quantity-control-app').find('.box-qty-scan-input');
                let val = parseInt(input.val()) || 0;
                if (val > 0) {
                    val--;
                    input.val(val).trigger('change');
                }
            });

            $(document).on('change', '.box-qty-scan-input', function() {
                const pId = $(this).data('product-id');
                const cId = $(this).data('color-id');
                const ssId = $(this).data('size-set-id');
                const key = pId + '_' + cId + '_' + ssId;
                let qty = parseInt($(this).val()) || 0;
                const max = parseInt($(this).attr('max'));
                
                if (qty < 0) qty = 0;
                if (!allowOverStock && !isNaN(max) && qty > max) qty = max;
                $(this).val(qty);

                if (qty > 0) {
                    let item = cart.get(key) || {
                        product_id: pId,
                        color_id: cId,
                        size_set_id: ssId,
                        pcs_per_box: parseFloat($(this).data('pcs')),
                        unit_price: parseFloat($(this).data('price')),
                        remark: $('.size-set-remark-input[data-size-set="'+ssId+'"]').val() || ''
                    };
                    item.qty = qty;
                    item.remark = $('.size-set-remark-input[data-size-set="'+ssId+'"]').val() || item.remark || '';
                    cart.set(key, item);

                    // Append to DOM if not exists
                    if ($('.variation-card[data-key="' + key + '"]').length === 0) {
                        const productName = $(this).data('product-name') || '';
                        const sizeSetName = $(this).data('size-set-name') || '';
                        const colorName = $(this).data('color-name') || '';
                        
                        const cardHtml = `
                            <div class="col-md-4 col-lg-3 mb-3 variation-row-container has-qty-top">
                                <div class="card variation-card shadow-sm h-100 has-qty"
                                    data-key="${key}"
                                    data-product-id="${pId}"
                                    data-color-id="${cId}"
                                    data-size-set-id="${ssId}"
                                    data-price="${$(this).data('price')}"
                                    data-pcs="${$(this).data('pcs')}">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <div>
                                                <h6 class="font-weight-bold text-dark mb-0">${productName}</h6>
                                                <small class="text-muted"><i class="fas fa-palette mr-1"></i> ${colorName}</small>
                                            </div>
                                            <div class="text-right">
                                                <span class="badge badge-light border mb-1"><i class="fas fa-ruler-horizontal mr-1"></i> ${sizeSetName}</span>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                            ${seePrice ? `
                                            <div class="font-weight-bold text-primary">
                                                ₹${$(this).data('price')}
                                            </div>
                                            ` : ''}
                                            <div class="quantity-control-app d-flex align-items-center p-1">
                                                <button type="button" class="btn-q btn-minus">-</button>
                                                <input type="number" class="box-qty-input" value="${qty}" max="${$(this).attr('max')}">
                                                <button type="button" class="btn-q btn-plus">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        $('#variation-container').prepend(cardHtml);
                    }
                } else {
                    cart.delete(key);
                }
                updateUI();
            });

            $(document).on('change', '.size-set-remark-input', function() {
                const sizeSetId = $(this).data('size-set');
                const remark = $(this).val();
                cart.forEach((item, key) => {
                    if (item.size_set_id == sizeSetId) {
                        item.remark = remark;
                        cart.set(key, item);
                    }
                });
                
                // Update localStorage right away
                const cartData = {};
                cart.forEach((v, k) => { if (v.qty > 0) cartData[k] = v; });
                localStorage.setItem(storageKey, JSON.stringify(cartData));
            });

            // Load from local storage
            const saved = localStorage.getItem(storageKey);
            let hasCartItems = false;
            
            if (saved) {
                const data = JSON.parse(saved);
                Object.keys(data).forEach(key => {
                    cart.set(key, data[key]);
                    if (data[key].qty > 0) hasCartItems = true;
                });
            }
            
            if (hasCartItems) {
                const missingKeys = [];
                cart.forEach((v, k) => {
                    if (v.qty > 0) {
                        // Check if this key exists in the DOM
                        let exists = false;
                        $('.variation-card').each(function() {
                            if ($(this).data('key') == k) exists = true;
                        });
                        if (!exists) missingKeys.push(k);
                    }
                });

                if (missingKeys.length > 0) {
                    fetchCartItemsHtml(missingKeys);
                } else {
                    updateUI();
                }
            } else {
                updateUI();
            }

            function fetchCartItemsHtml(keysToFetch = null) {
                $('#empty-state').hide();
                $('#variation-container').append('<div class="col-12 text-center py-5" id="loading-cart"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2 text-muted font-weight-bold">Loading your selected products...</p></div>');
                
                const keys = keysToFetch || Array.from(cart.keys());
                
                $.ajax({
                    url: window.location.pathname,
                    method: 'GET',
                    data: {
                        shop_id: '0',
                        party_type: '0',
                        load_more: 1, 
                        cart_keys: keys,
                        sample_set: $('#sampleSetToggle').is(':checked') ? 1 : 0
                    },
                    success: function(res) {
                        $('#loading-cart').remove();
                        if (res.html) {
                            $('#variation-container').append(res.html);
                            updateUI(); 
                        } else {
                            $('#empty-state').show();
                        }
                    },
                    error: function() {
                        $('#loading-cart').remove();
                        $('#empty-state').show();
                    }
                });
            }

            $('#toggleFilters').click(function () {
                $('#filterContainer').slideToggle();
            });

            $('#btnShowDetails, #btnNextSummary').click(function () {
                $('#detailsModal').modal('show');
            });

            // --- INFINITE SCROLL / LOAD MORE ---
            let nextPage = 0;
            let loading = false;
            const container = $('#variation-container');

            $('#loadMoreBtn').click(function () {
                loadMore();
            });

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                loadMore(true);
                $('#filtersCollapse').collapse('hide');
            });

            $('#resetFiltersBtn').click(function(e) {
                e.preventDefault();
                $('#filterForm')[0].reset();
                $('.select2').val(null).trigger('change');
                $('#filterForm').submit();
            });

            function loadMore(reset = false) {
                if (reset) {
                    nextPage = 1;
                    loading = false;
                    container.empty();
                    $('#loadMoreBtn').show();
                }

                if (loading || !nextPage) return;
                loading = true;
                $('#loading-spinner').show();
                $('#loadMoreBtn').prop('disabled', true).text('Loading...');

                let formData = $('#filterForm').serialize();
                let requestData = formData + '&load_more=1&page=' + nextPage;

                $.ajax({
                    url: window.location.pathname,
                    method: 'GET',
                    data: requestData,
                    success: function (response) {
                        if (reset) {
                            container.empty();
                        }
                        container.append(response.html);
                        nextPage = response.next_page;
                        loading = false;
                        $('#loading-spinner').hide();

                        if (!nextPage) {
                            $('#loadMoreBtn').hide();
                        } else {
                            $('#loadMoreBtn').prop('disabled', false).text('Load More Variations');
                        }
                        updateUI();
                    },
                    error: function () {
                        loading = false;
                        $('#loading-spinner').hide();
                        $('#loadMoreBtn').prop('disabled', false).text('Load More Variations');
                    }
                });
            }

            function updateUI() {
                // Sync prices from DOM to cart (handles sample set toggle changes)
                $('.variation-card').each(function () {
                    const key = $(this).data('key');
                    if (cart.has(key)) {
                        const item = cart.get(key);
                        item.unit_price = parseFloat($(this).data('price'));
                        
                        const availableText = $(this).find('.available-text');
                        if (availableText.length) {
                            const base = parseInt(availableText.data('base-available')) || 0;
                            availableText.text((base - item.qty) + ' Boxes');
                        }
                    } else {
                        const availableText = $(this).find('.available-text');
                        if (availableText.length) {
                            const base = parseInt(availableText.data('base-available')) || 0;
                            availableText.text(base + ' Boxes');
                        }
                    }
                });

                let totalBoxes = 0;
                let subTotal = 0;

                cart.forEach((item) => {
                    if (item.qty > 0) {
                        totalBoxes += item.qty;
                        subTotal += (item.qty * item.pcs_per_box * item.unit_price);
                    }
                });

                subTotal = Math.ceil(subTotal);

                const otherCharges = Math.ceil(parseFloat($('#other_charges').val()) || 0);
                const discountAmount = Math.ceil(parseFloat($('#discountAmountInput').val()) || 0);
                const taxableAmount = Math.ceil(subTotal - discountAmount);

                let gstAmount = Math.ceil(parseFloat($('#gstAmountInput').val()) || 0);

                const grandTotal = Math.ceil(taxableAmount + gstAmount + otherCharges);

                $('#selectedCount').text(totalBoxes);
                $('#subTotalAmount').text(subTotal.toFixed(2));
                $('#taxableAmount').text(taxableAmount.toFixed(2));
                $('#grandTotalAmount').text(grandTotal.toFixed(2));

                if (totalBoxes > 0) {
                    $('#summaryBar').fadeIn();
                } else {
                    $('#summaryBar').fadeOut();
                }

                $('.variation-card').each(function () {
                    const key = $(this).data('key');
                    const item = cart.get(key);
                    const input = $(this).find('.box-qty-input');
                    if (item && item.qty > 0) {
                        input.val(item.qty);
                        $(this).addClass('has-qty');
                    } else {
                        input.val(0);
                        $(this).removeClass('has-qty');
                    }
                });

                const storageObj = {};
                cart.forEach((val, key) => { if (val.qty > 0) storageObj[key] = val; });
                localStorage.setItem(storageKey, JSON.stringify(storageObj));
            }

            $('#gstAmountInput').on('input', function() {
                $(this).data('manual', true);
                updateUI();
            });

            $(document).on('change', '.box-qty-input', function () {
                const card = $(this).closest('.variation-card');
                const key = card.data('key');
                let qty = parseInt($(this).val()) || 0;
                const max = parseInt($(this).attr('max'));

                if (qty < 0) qty = 0;
                if (!allowOverStock && qty > max) { qty = max; $(this).val(qty); }

                if (qty > 0) {
                    let item = cart.get(key) || {
                        product_id: card.data('product-id'),
                        color_id: card.data('color-id'),
                        size_set_id: card.data('size-set-id'),
                        pcs_per_box: parseFloat(card.data('pcs')),
                        unit_price: parseFloat(card.data('price')),
                        remark: ''
                    };
                    item.qty = qty;
                    cart.set(key, item);
                    // Move the PREVIOUSLY edited card to the top when selecting a new product
                    const row = card.closest('.variation-row-container');
                    
                    if (window.lastEditedCard && window.lastEditedCard.data('key') !== key) {
                        const prevRow = window.lastEditedCard.closest('.variation-row-container');
                        if (!prevRow.hasClass('has-qty-top')) {
                            prevRow.addClass('has-qty-top').prependTo('#variation-container');
                        }
                    }
                    window.lastEditedCard = card;
                } else {
                    cart.delete(key);
                    card.closest('.variation-row-container').removeClass('has-qty-top');
                }
                updateUI();
            });

            $(document).on('input', '#discountAmountInput, #other_charges', function () {
                updateUI();
            });

            $(document).on('click', '.btn-plus', function () {
                const input = $(this).closest('.variation-card').find('.box-qty-input');
                const max = parseInt(input.attr('max'));
                const current = parseInt(input.val()) || 0;
                if (allowOverStock || isNaN(max) || current < max) input.val(current + 1).trigger('change');
            });

            $(document).on('click', '.btn-minus', function () {
                const input = $(this).closest('.variation-card').find('.box-qty-input');
                const current = parseInt(input.val()) || 0;
                if (current > 0) input.val(current - 1).trigger('change');
            });

            $('.place-order-btn').click(function () {
                const btn = $(this);
                let variations = [];
                cart.forEach((item) => { if (item.qty > 0) variations.push(item); });

                if (variations.length === 0) return;

                Swal.fire({
                    title: 'Confirm Order',
                    text: "Create order for " + variations.reduce((a, b) => a + b.qty, 0) + " boxes?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Confirm',
                    confirmButtonColor: '#007bff'
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                        $.ajax({
                            url: "0",
                            method: "POST",
                            data: {
                                _token: "0",
                                shop_id: "0",
                                party_type: "0",
                                order_date: "0",
                                order_type: 'normal',
                                sale_type: 'item',
                                is_sample_set: "0",
                                variations: variations,
                                sales_man_id: $('#sales_man_id').val(),
                                expected_dispatch_date: $('#expectedDispatchDate').val(),
                                discount_amount: $('#discountAmountInput').val(),
                                gst_amount: $('#gstAmountInput').val(),
                                other_charges: $('#other_charges').val(),
                                remark: $('#remark').val(),
                                booking_station: $('#booking_station').val(),
                                transport: $('#transport').val()
                            },
                            success: function (response) {
                                if (response.success) {
                                    localStorage.removeItem(storageKey);
                                    Swal.fire('Ordered!', 'Your order has been placed successfully.', 'success').then(() => {
                                        window.location.href = response.redirect_url;
                                    });
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                    btn.prop('disabled', false).html('Confirm Order <i class="fas fa-arrow-right ml-2"></i>');
                                }
                            }
                        });
                    }
                });
            });

            updateUI();
        });
    