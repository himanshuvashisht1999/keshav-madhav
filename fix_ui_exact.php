<?php
$c = file_get_contents('resources/views/admin/packing/process.blade.php');

$reworkOld = <<<'JS'
                let validDesigns = UNIT_LOTS ? UNIT_LOTS.map(l => l.design_number) : [];

                if (ORDER_ITEMS && ORDER_ITEMS.length > 0) {
                    ORDER_ITEMS.forEach(item => {
                        if (!validDesigns.includes(item.design_number)) return;
                        
                        let avl = item.unit_available_qty || 0;
                        if (avl > 0) {
                            $list.append(`
                                        <tr>
                                            <td class="align-middle">
                                                <div class="font-weight-bold">${item.design_number || 'N/A'}</div>
                                                <div class="small text-muted">${item.color_name || 'N/A'}</div>
                                            </td>
                                            <td class="align-middle">${item.size || 'N/A'}</td>
                                            <td class="align-middle text-center">
                                                <span class="badge badge-light border px-2">${avl} Pcs</span>
                                            </td>
                                            <td class="align-middle">
                                                <input type="number" class="form-control form-control-sm rework-qty-input" 
                                                       data-id="${item.id}" data-max="${avl}" 
                                                       min="0" max="${avl}" value="0" oninput="validateUnitPackagingStock(this)">
                                            </td>
                                        </tr>
                                    `);
                        }
                    });
                }

                if ($list.is(':empty')) {
                    $list.append('<tr><td colspan="4" class="text-center py-4 text-muted">No pieces available at this unit to return.</td></tr>');
                }
JS;

$debitOld = <<<'JS'
                let validDesigns = UNIT_LOTS ? UNIT_LOTS.map(l => l.design_number) : [];

                if (ORDER_ITEMS && ORDER_ITEMS.length > 0) {
                    ORDER_ITEMS.forEach(item => {
                        if (!validDesigns.includes(item.design_number)) return;

                        let avl = item.unit_available_qty || 0;
                        if (avl > 0) {
                            $list.append(`
                                        <tr>
                                            <td class="align-middle small">
                                                <div class="font-weight-bold">${item.design_number || 'N/A'}</div>
                                                <div class="text-muted">${item.color_name || 'N/A'}</div>
                                            </td>
                                            <td class="align-middle small">${item.size || 'N/A'}</td>
                                            <td class="align-middle text-center small">${avl}</td>
                                            <td class="align-middle">
                                                <input type="number" class="form-control form-control-sm debit-qty-input" 
                                                       data-id="${item.id}" data-max="${avl}" 
                                                       min="0" max="${avl}" value="0" oninput="validateUnitPackagingStock(this)">
                                            </td>
                                            <td class="align-middle">
                                                <input type="number" class="form-control form-control-sm debit-rate-input" 
                                                       placeholder="0" value="0" oninput="calculateDebitTotal()">
                                            </td>
                                        </tr>
                                    `);
                        }
                    });
                }

                if ($list.is(':empty')) {
                    $list.append('<tr><td colspan="5" class="text-center py-4 text-muted">No pieces available at this unit for debit.</td></tr>');
                }
JS;

$samplingOld = <<<'JS'
                let validDesigns = UNIT_LOTS ? UNIT_LOTS.map(l => l.design_number) : [];

                if (ORDER_ITEMS && ORDER_ITEMS.length > 0) {
                    ORDER_ITEMS.forEach(item => {
                        if (!validDesigns.includes(item.design_number)) return;

                        let avl = item.unit_available_qty || 0;
                        if (avl > 0) {
                            $list.append(`
                                        <tr>
                                            <td class="align-middle">
                                                <div class="font-weight-bold text-dark">${item.design_number || 'N/A'}</div>
                                                <div class="small text-muted">${item.color_name || 'N/A'}</div>
                                            </td>
                                            <td class="align-middle">${item.size || 'N/A'}</td>
                                            <td class="align-middle text-center">
                                                <span class="badge badge-light border px-2">${avl} Pcs</span>
                                            </td>
                                            <td class="align-middle">
                                                <input type="number" class="form-control form-control-sm sampling-qty-input" 
                                                       data-id="${item.id}" data-max="${avl}" 
                                                       min="0" max="${avl}" value="0" oninput="validateUnitPackagingStock(this)">
                                            </td>
                                        </tr>
                                    `);
                        }
                    });
                }

                if ($list.is(':empty')) {
                    $list.append('<tr><td colspan="4" class="text-center py-4 text-muted">No pieces available at this unit for sampling.</td></tr>');
                }
JS;

$deadOld = <<<'JS'
                let validDesigns = UNIT_LOTS ? UNIT_LOTS.map(l => l.design_number) : [];

                if (ORDER_ITEMS && ORDER_ITEMS.length > 0) {
                    ORDER_ITEMS.forEach(item => {
                        if (!validDesigns.includes(item.design_number)) return;

                        let avl = item.unit_available_qty || 0;
                        if (avl > 0) {
                            $list.append(`
                                        <tr>
                                            <td class="align-middle">
                                                <div class="font-weight-bold text-dark">${item.design_number || 'N/A'}</div>
                                                <div class="small text-muted">${item.color_name || 'N/A'}</div>
                                            </td>
                                            <td class="align-middle">${item.size || 'N/A'}</td>
                                            <td class="align-middle text-center">
                                                <span class="badge badge-light border px-2">${avl} Pcs</span>
                                            </td>
                                            <td class="align-middle">
                                                <input type="number" class="form-control form-control-sm dead-qty-input" 
                                                       data-id="${item.id}" data-max="${avl}" 
                                                       min="0" max="${avl}" value="0" oninput="validateUnitPackagingStock(this)">
                                            </td>
                                        </tr>
                                    `);
                        }
                    });
                }

                if ($list.is(':empty')) {
                    $list.append('<tr><td colspan="4" class="text-center py-4 text-muted">No pieces available at this unit to mark as damage.</td></tr>');
                }
JS;

$c = str_replace($reworkOld, "$('#rework_lot_no').val('');\n                populateAuxTable(\$list, 'rework-qty-input');", $c);
$c = str_replace($debitOld, "$('#debit_lot_no').val('');\n                populateAuxTable(\$list, 'debit-qty-input', '', true);", $c);
$c = str_replace($samplingOld, "$('#sampling_lot_no').val('');\n                populateAuxTable(\$list, 'sampling-qty-input');", $c);
$c = str_replace($deadOld, "$('#dead_lot_no').val('');\n                populateAuxTable(\$list, 'dead-qty-input');", $c);

file_put_contents('resources/views/admin/packing/process.blade.php', $c);
echo "Exact replacement done.";
