<?php
$file = 'resources/views/admin/packing/process.blade.php';
$content = file_get_contents($file);

$modals = [
    ['prefix' => 'dead', 'empty_msg' => 'No pieces available at this unit to mark as damage.', 'qty_class' => 'dead-qty-input'],
    ['prefix' => 'sampling', 'empty_msg' => 'No pieces available at this unit for sampling.', 'qty_class' => 'sampling-qty-input'],
    ['prefix' => 'debit', 'empty_msg' => 'No pieces available at this unit to mark for debit.', 'qty_class' => 'debit-qty-input'],
    ['prefix' => 'rework', 'empty_msg' => 'No pieces available at this unit to mark for rework.', 'qty_class' => 'rework-qty-input'],
];

foreach ($modals as $modal) {
    // We will find `let validDesigns = UNIT_LOTS.filter...` and replace the whole block up to `if (\$list.is(':empty'))`
    // Wait, the easiest way is to rewrite the entire `renderGrid` function using preg_replace
    
    $js = <<<JS
            function render{$modal['prefix']}Grid() {
                let selectedLot = $('#{$modal['prefix']}_lot_no').val();
                let \$list = $('#{$modal['prefix']}ItemsList');
                \$list.empty();
                if (!selectedLot) return;

                let validSetIds = UNIT_LOTS.filter(l => l.lot_no == selectedLot).map(l => l.set_id);
                // Also get the remaining quantity for the lot, to limit the max available per piece if needed
                // But the lot's remaining_quantity is for the whole lot, while pieces are per size.
                // We'll just filter ORDER_ITEMS by set_id

                if (ORDER_ITEMS && ORDER_ITEMS.length > 0) {
                    ORDER_ITEMS.forEach(item => {
                        if (!validSetIds.includes(item.order_products_set_id)) return;

                        let avl = item.unit_available_qty || 0;
                        if (avl > 0) {
                            \$list.append(`
                                        <tr>
                                            <td class="align-middle">
                                                <div class="font-weight-bold text-dark">\${item.design_number || 'N/A'}</div>
                                                <div class="small text-muted">\${item.color_name || 'N/A'}</div>
                                            </td>
                                            <td class="align-middle">\${item.size || 'N/A'}</td>
                                            <td class="align-middle text-center">
                                                <span class="badge badge-light border px-2">\${avl} Pcs</span>
                                            </td>
                                            <td class="align-middle">
                                                <input type="number" class="form-control form-control-sm {$modal['qty_class']}" 
                                                       data-id="\${item.id}" data-max="\${avl}" 
                                                       min="0" max="\${avl}" value="0" oninput="validateUnitPackagingStock(this)">
                                            </td>
                                            ${$modal['prefix'] === 'debit' ? `
                                            <td class="align-middle">
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend"><span class="input-group-text px-2">₹</span></div>
                                                    <input type="number" class="form-control debit-rate-input" placeholder="0.00" min="0" step="0.01" value="0">
                                                </div>
                                            </td>
                                            ` : ''}
                                        </tr>
                                    `);
                        }
                    });
                }

                if (\$list.is(':empty')) {
                    \$list.append('<tr><td colspan="4" class="text-center py-4 text-muted">{$modal['empty_msg']}</td></tr>');
                }
            }
JS;

    $regex = '/function render' . $modal['prefix'] . 'Grid\(\)\s*\{.*?\$.list\.is\(\':empty\'\)\)\s*\{.*?\}\s*\}/s';
    $content = preg_replace($regex, $js, $content);
}

file_put_contents($file, $content);
echo "Replaced renderGrid JS\n";
?>
