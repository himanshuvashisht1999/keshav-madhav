<?php
$file = 'resources/views/admin/packing/process.blade.php';
$content = file_get_contents($file);

$modals = [
    [
        'name' => 'DeadStock',
        'prefix' => 'dead',
        'qty_class' => 'dead-qty-input',
        'empty_msg' => 'No pieces available at this unit to mark as damage.',
        'open_func' => 'openDeadStockModal',
        'list_id' => 'deadStockItemsList'
    ],
    [
        'name' => 'Sampling',
        'prefix' => 'sampling',
        'qty_class' => 'sampling-qty-input',
        'empty_msg' => 'No pieces available at this unit for sampling.',
        'open_func' => 'openSamplingModal',
        'list_id' => 'samplingItemsList'
    ],
    [
        'name' => 'Debit',
        'prefix' => 'debit',
        'qty_class' => 'debit-qty-input',
        'empty_msg' => 'No pieces available at this unit to mark for debit.',
        'open_func' => 'openDebitModal',
        'list_id' => 'debitItemsList'
    ],
    [
        'name' => 'Rework',
        'prefix' => 'rework',
        'qty_class' => 'rework-qty-input',
        'empty_msg' => 'No pieces available at this unit to mark for rework.',
        'open_func' => 'openReworkModal',
        'list_id' => 'reworkItemsList'
    ]
];

foreach ($modals as $modal) {
    $js = <<<JS
            function {$modal['open_func']}() {
                if (!ORDER_ID) {
                    alert('Please select an order first.');
                    return;
                }

                // Populate Lot Dropdown
                let uniqueLots = [...new Set(UNIT_LOTS.map(l => l.lot_no))];
                let \$lotSelect = $('#{$modal['prefix']}_lot_no');
                \$lotSelect.html('<option value="">-- Select Lot --</option>');
                uniqueLots.forEach(lot => {
                    \$lotSelect.append(`<option value="\${lot}">Lot #\${lot}</option>`);
                });

                $('#{$modal['list_id']}').empty();
                $('#{$modal['prefix']}QueueList').empty(); // clear previous queues
                {$modal['prefix']}Queue = []; // reset array
                $('#{$modal['name']}Modal').modal('show');
            }

            function render{$modal['prefix']}Grid() {
                let selectedLot = $('#{$modal['prefix']}_lot_no').val();
                let \$list = $('#{$modal['list_id']}');
                \$list.empty();
                if (!selectedLot) return;

                let validDesigns = UNIT_LOTS.filter(l => l.lot_no == selectedLot).map(l => l.design_number);

                if (ORDER_ITEMS && ORDER_ITEMS.length > 0) {
                    ORDER_ITEMS.forEach(item => {
                        if (!validDesigns.includes(item.design_number)) return;

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

    // We will extract the current openModal function and replace it with this block
    // We search for `function openDeadStockModal() { ... }` which is bounded by `$('#deadStockModal').modal('show');\n            }`
    // But since regex across multiple lines is tricky, we'll just append it to the script or use strpos.
    // Wait, let's just find `function openDeadStockModal() {` and replace up to `modal('show'); }`
    // Actually, `preg_replace` with `/function openDeadStockModal\(\).*?\$('#deadStockModal'\)\.modal\('show'\);\s*\}/s`
    
    $regexName = $modal['name'];
    if($modal['name'] === 'DeadStock') $regexModalId = 'deadStockModal';
    else if($modal['name'] === 'Sampling') $regexModalId = 'samplingModal';
    else if($modal['name'] === 'Debit') $regexModalId = 'debitModal';
    else if($modal['name'] === 'Rework') $regexModalId = 'reworkModal';
    
    $regex = '/function ' . $modal['open_func'] . '\(\).*?\$\(\'#' . $regexModalId . '\'\)\.modal\(\'show\'\);\s*\}/s';
    
    $content = preg_replace($regex, $js, $content);
}

file_put_contents($file, $content);
echo "Replaced modal JS\n";
?>
