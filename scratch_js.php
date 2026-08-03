            function showColorSelection(data) {
                $('#scanProductName').text(data.product.name);

                // For multiple size sets, we don't just show one text. We can clear it or show all.
                $('#scanSizeSet').text(''); // We will show size sets in tabs now

                const list = $('#colorSelectionList');
                list.empty();

                if (!data.size_sets || data.size_sets.length === 0) {
                    list.append('<div class="alert alert-warning">No variations found.</div>');
                    $('#scanSelectionModal').modal('show');
                    return;
                }

                // Create Nav Tabs
                let navHtml = '<ul class="nav nav-pills mb-3" id="sizeSetTabs" role="tablist">';
                let contentHtml = '<div class="tab-content" id="sizeSetTabsContent">';

                data.size_sets.forEach((sizeSet, index) => {
                    const isActive = sizeSet.size_set_id === data.scanned_size_set_id ? 'active' : '';
                    const isSelected = sizeSet.size_set_id === data.scanned_size_set_id ? 'true' : 'false';
                    
                    // Tab Link
                    navHtml += `
                        <li class="nav-item" role="presentation">
                            <button class="nav-link ${isActive}" id="tab-ss-${sizeSet.size_set_id}" data-toggle="pill" data-target="#pane-ss-${sizeSet.size_set_id}" type="button" role="tab" aria-controls="pane-ss-${sizeSet.size_set_id}" aria-selected="${isSelected}">
                                ${sizeSet.size_set_name}
                            </button>
                        </li>
                    `;

                    // Tab Pane
                    const showClass = isActive ? 'show active' : '';
                    contentHtml += `<div class="tab-pane fade ${showClass}" id="pane-ss-${sizeSet.size_set_id}" role="tabpanel" aria-labelledby="tab-ss-${sizeSet.size_set_id}">`;

                    let maxGlobalQty = 0;
                    sizeSet.colors.forEach(color => {
                        if (parseInt(color.available_boxes) > maxGlobalQty) {
                            maxGlobalQty = parseInt(color.available_boxes);
                        }
                    });

                    // Global Apply for this Size Set
                    contentHtml += `
                        <div class="card border-primary shadow-sm mb-3 rounded-lg overflow-hidden" style="background-color: #f8faff;">
                            ${data.product.image ? `<div style="background-color: #f8f9fa; text-align: center; border-bottom: 1px solid #dee2e6;"><img src="${data.product.image}" class="zoom-image" style="max-height: 250px; width: auto; max-width: 100%; object-fit: contain; cursor: pointer;"></div>` : `<div class="bg-light d-flex align-items-center justify-content-center" style="height: 150px; border-bottom: 1px solid #dee2e6;"><i class="fas fa-image fa-3x text-muted opacity-25"></i></div>`}
                            <div class="card-body p-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="font-weight-bold text-primary mb-0"><i class="fas fa-layer-group mr-2"></i>Apply to All Colors</h6>
                                    <small class="text-muted">Set quantity for every color in ${sizeSet.size_set_name}</small>
                                </div>
                                <div class="quantity-control-app d-flex align-items-center p-1 border-primary">
                                    <button class="btn-q btn-minus-global text-primary" data-size-set="${sizeSet.size_set_id}">-</button>
                                    <input type="number" class="box-qty-global-input text-primary font-weight-bold" 
                                        data-size-set="${sizeSet.size_set_id}"
                                        min="0"
                                        ${(data.is_advance_sample || allowOverStock) ? '' : `max="${maxGlobalQty}"`}
                                        value="0">
                                    <button class="btn-q btn-plus-global text-primary" data-size-set="${sizeSet.size_set_id}">+</button>
                                </div>
                            </div>
                        </div>
                    `;

                    // Colors for this Size Set
                    contentHtml += `<div class="size-set-colors-container" id="colors-container-${sizeSet.size_set_id}">`;
                    sizeSet.colors.forEach(color => {
                        const vKey = data.product.id + '_' + color.id + '_' + sizeSet.size_set_id;
                        const item = cart.get(vKey);
                        const currentQty = item ? item.qty : 0;

                        contentHtml += `
                            <div class="card border-0 shadow-sm mb-3 rounded-lg overflow-hidden" data-key="${vKey}">
                                ${color.image ? `<div style="background-color: #f8f9fa; text-align: center; border-bottom: 1px solid #dee2e6;"><img src="${color.image}" class="zoom-image" style="max-height: 250px; width: auto; max-width: 100%; object-fit: contain; cursor: pointer;"></div>` : `<div class="bg-light d-flex align-items-center justify-content-center" style="height: 150px; border-bottom: 1px solid #dee2e6;"><i class="fas fa-image fa-3x text-muted opacity-25"></i></div>`}
                                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="font-weight-bold text-dark mb-0">${color.name}</h6>
                                                ${showStock && !data.is_advance_sample ? `<small class="text-muted">${color.available_boxes} Boxes available</small>` : (data.is_advance_sample ? '<small class="text-success font-weight-bold">Advance Sample (Unlimited)</small>' : '')}
                                    </div>
                                    <div class="quantity-control-app d-flex align-items-center p-1">
                                        <button class="btn-q btn-minus-scan" data-key="${vKey}">-</button>
                                        <input type="number" class="box-qty-scan-input" 
                                            data-product-id="${data.product.id}"
                                            data-color-id="${color.id}"
                                            data-size-set-id="${sizeSet.size_set_id}"
                                            data-pcs="${color.pcs_per_box}"
                                            data-price="${sizeSet.unit_price}"
                                            ${(data.is_advance_sample || allowOverStock) ? '' : `max="${color.available_boxes}"`}
                                            value="${currentQty}">
                                        <button class="btn-q btn-plus-scan" data-key="${vKey}">+</button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    contentHtml += `</div>`; // End colors container
                    
                    contentHtml += `</div>`; // End tab pane
                });

                navHtml += '</ul>';
                contentHtml += '</div>';

                list.append(navHtml);
                list.append(contentHtml);

                $('#scanSelectionModal').modal('show');
            }
