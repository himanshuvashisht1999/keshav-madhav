const fs = require('fs');
let file = fs.readFileSync('c:/xampp/htdocs/keshav-madhav/resources/views/admin/packing/process.blade.php', 'utf8');

file = file.replace(
    /                        order_id: ORDER_ID,[\s\S]*?toastr\.error\('Something went wrong on the server\.'\);\s*\}/,
    `            function submitDebit() {
                let stageId = $('#debitStage').val();
                let unitId = $('#debitUnit').val();
                let rackId = $('#debitRack').val();
                let discount = $('#debitDiscount').val();
                let totalAmount = $('#debitAmount').val();
                let remarks = $('#debitRemarks').val();

                if (!stageId || !unitId || !rackId) {
                    alert('Please select stage, unit and storage rack.');
                    return;
                }

                let items = debitQueue.map(q => ({ detail_id: q.detail_id, qty: q.qty, per_piece_amount: q.per_piece_amount, lot_no: q.lot_no }));

                if (items.length === 0) {
                    alert('Please select at least one damaged item to debit.');
                    return;
                }

                if (!confirm(\`Confirm debit of ₹\${totalAmount} to the selected unit? This will also remove the items and move them to warehouse.\`)) {
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.packing.recordUnitDebit') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        order_id: ORDER_ID,
                        slip_id: SLIP_ID,
                        stage_id: stageId,
                        unit_id: unitId,
                        rack_id: rackId,
                        discount: discount,
                        total_amount: totalAmount,
                        items: items,
                        remarks: remarks
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            toastr.success(response.message);
                            setTimeout(() => location.reload(), 800);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function () {
                        toastr.error('Something went wrong on the server.');
                    }
                });
            }`
);

file = file.replace(/                \}\s+            \}\s+\}\s+            \/\/\s+---\s+DOMESTIC PACKING LOGIC/, '            }\n\n            // --- DOMESTIC PACKING LOGIC');

fs.writeFileSync('c:/xampp/htdocs/keshav-madhav/resources/views/admin/packing/process.blade.php', file);
