const fs = require('fs');
let file = fs.readFileSync('c:/xampp/htdocs/keshav-madhav/resources/views/admin/packing/process.blade.php', 'utf8');

// We need to update calculateDebitTotal() to use debitQueue
file = file.replace(
    /function calculateDebitTotal\(\) \{[\s\S]*?let discount = parseFloat\(\$\('#debitDiscount'\)\.val\(\)\) \|\| 0;/g,
    `function calculateDebitTotal() {
                let subtotal = 0;
                debitQueue.forEach(q => {
                    subtotal += (q.qty * q.per_piece_amount);
                });
                $('#debitItemsList tr').each(function () {
                    let qty = parseInt($(this).find('.debit-qty-input').val()) || 0;
                    let rate = parseFloat($(this).find('.debit-rate-input').val()) || 0;
                    subtotal += (qty * rate);
                });

                let discount = parseFloat($('#debitDiscount').val()) || 0;`
);

// Remove the hasValidRate check in submitDebit
file = file.replace(
    /let hasValidRate = true;[\s\S]*?if \(!hasValidRate\) \{[\s\S]*?return;\s*\}/,
    '' // remove the valid rate checking loop and condition entirely
);

// But submitDebit also had its items loop!
// Let's just ensure we replaced it properly in the previous script. Wait, previous script did:
// let items = debitQueue.map(q => ({ detail_id: q.detail_id, qty: q.qty, per_piece_amount: q.per_piece_amount, lot_no: q.lot_no }));
// It matched: `let items = \[\];[\s\S]*?\}\);`
// In submitDebit, the items loop was:
// let items = [];
// $('#debitItemsList tr').each(function () { ... });
// Since we used `/let items = \[\];[\s\S]*?\}\);/g`, this might have replaced too much or too little. Let's verify submitDebit

fs.writeFileSync('c:/xampp/htdocs/keshav-madhav/resources/views/admin/packing/process.blade.php', file);
