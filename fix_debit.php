<?php
$c = file_get_contents('resources/views/admin/packing/process.blade.php');

$c = preg_replace(
    '/(function populateAuxTable.*?)\)/',
    "$1, isDebit = false)",
    $c,
    1
);

$newRow = <<<'JS'
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
                        ${isDebit ? `<td class="align-middle">
                            <input type="number" class="form-control form-control-sm debit-rate-input" 
                                   placeholder="0" value="0" oninput="calculateDebitTotal()">
                        </td>` : ''}
                    </tr>
JS;

$c = preg_replace(
    '/<tr>.*?<\/tr>/s',
    $newRow,
    $c,
    1
);

// Now update debit modal
$c = preg_replace(
    '/(let validDesigns = UNIT_LOTS \? UNIT_LOTS\.map\(l => l\.design_number\) : \[\];.*?if \(\$list\.is\(\':empty\'\)\) \{.*?\})/s',
    "$('#debit_lot_no').val('');\n                populateAuxTable(\$list, 'debit-qty-input', '', true);",
    $c,
    1
);

// Also update the document.ready binding for debit_lot_no to pass true
$c = str_replace(
    "$('#debit_lot_no').on('change', function() { populateAuxTable($('#debitItemsList'), 'debit-qty-input', $(this).val()); });",
    "$('#debit_lot_no').on('change', function() { populateAuxTable($('#debitItemsList'), 'debit-qty-input', $(this).val(), true); });",
    $c
);

file_put_contents('resources/views/admin/packing/process.blade.php', $c);
echo "Updated Debit Modal and populateAuxTable";
