<?php
$c = file_get_contents('resources/views/admin/packing/process.blade.php');

// 1. Insert Lot Dropdown into reworkModal HTML (because regex failed earlier)
$rew_dd = <<<HTML
<div class="form-group mb-3 px-3">
    <label class="font-weight-bold text-muted small">Select Lot Number <span class="text-danger">*</span></label>
    <select id="rework_lot_no" class="form-control custom-select">
        <option value="">-- Auto (Most Recent) --</option>
    </select>
    <small class="text-muted">Choosing a specific lot ensures exact deduction.</small>
</div>
HTML;
if (strpos($c, 'id="rework_lot_no"') === false) {
    $c = preg_replace('/(<div class="alert alert-warning py-2 mb-3">.*?<\/div>)\s*(<div class="row mb-3">)/s', "$1\n                    $rew_dd\n                    $2", $c, 1);
}

// 2. Add LOT_AVAILABLE variable
if (strpos($c, 'let LOT_AVAILABLE =') === false) {
    $c = preg_replace('/(let UNIT_LOTS = @json\(.*?\);)/', "$1\n            let LOT_AVAILABLE = @json(\$unit_available_per_lot ?? []);", $c, 1);
}

// 3. Inject populateAuxTable function and bind events
$populateFunc = <<<'JS'
            function populateAuxTable($tbody, inputClass, selectedLot = '') {
                $tbody.empty();
                let validDesigns = UNIT_LOTS ? UNIT_LOTS.map(l => l.design_number) : [];
                
                if (ORDER_ITEMS && ORDER_ITEMS.length > 0) {
                    ORDER_ITEMS.forEach(item => {
                        if (!validDesigns.includes(item.design_number)) return;
                        
                        let avl = 0;
                        if (selectedLot && LOT_AVAILABLE && LOT_AVAILABLE[selectedLot]) {
                            avl = LOT_AVAILABLE[selectedLot][item.id] || 0;
                        } else if (!selectedLot) {
                            avl = item.unit_available_qty || 0;
                        }

                        if (avl > 0) {
                            $tbody.append(`
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
                                        <input type="number" class="form-control form-control-sm ${inputClass}" 
                                               data-id="${item.id}" data-max="${avl}" 
                                               min="0" max="${avl}" value="0" oninput="validateUnitPackagingStock(this)">
                                    </td>
                                </tr>
                            `);
                        }
                    });
                }

                if ($tbody.is(':empty')) {
                    $tbody.append('<tr><td colspan="4" class="text-center py-4 text-muted">No pieces available for this selection.</td></tr>');
                }
            }

            $(document).ready(function() {
                $('#rework_lot_no').on('change', function() { populateAuxTable($('#reworkItemsList'), 'rework-qty-input', $(this).val()); });
                $('#dead_lot_no').on('change', function() { populateAuxTable($('#deadStockItemsList'), 'dead-qty-input', $(this).val()); });
                $('#sampling_lot_no').on('change', function() { populateAuxTable($('#samplingItemsList'), 'sampling-qty-input', $(this).val()); });
                $('#debit_lot_no').on('change', function() { populateAuxTable($('#debitItemsList'), 'debit-qty-input', $(this).val()); });
            });
JS;

if (strpos($c, 'function populateAuxTable') === false) {
    $c = preg_replace('/(let LOT_AVAILABLE =.*?;)/', "$1\n$populateFunc", $c, 1);
}

// 4. Update the openModal functions to use populateAuxTable
// openReworkModal
$c = preg_replace(
    '/(let validDesigns = UNIT_LOTS \? UNIT_LOTS\.map\(l => l\.design_number\) : \[\];.*?if \(\$list\.is\(\':empty\'\)\) \{.*?\})/s',
    "$('#rework_lot_no').val(''); populateAuxTable(\$list, 'rework-qty-input', '');",
    $c,
    1
);

// openDeadStockModal
$c = preg_replace(
    '/(let validDesigns = UNIT_LOTS \? UNIT_LOTS\.map\(l => l\.design_number\) : \[\];.*?if \(\$list\.is\(\':empty\'\)\) \{.*?\})/s',
    "$('#dead_lot_no').val(''); populateAuxTable(\$list, 'dead-qty-input', '');",
    $c,
    1
);

// openSamplingModal
$c = preg_replace(
    '/(let validDesigns = UNIT_LOTS \? UNIT_LOTS\.map\(l => l\.design_number\) : \[\];.*?if \(\$list\.is\(\':empty\'\)\) \{.*?\})/s',
    "$('#sampling_lot_no').val(''); populateAuxTable(\$list, 'sampling-qty-input', '');",
    $c,
    1
);

// openDebitModal
$c = preg_replace(
    '/(let validDesigns = UNIT_LOTS \? UNIT_LOTS\.map\(l => l\.design_number\) : \[\];.*?if \(\$list\.is\(\':empty\'\)\) \{.*?\})/s',
    "$('#debit_lot_no').val(''); populateAuxTable(\$list, 'debit-qty-input', '');",
    $c,
    1
);

file_put_contents('resources/views/admin/packing/process.blade.php', $c);
echo "Refactored UI Modals JS";
