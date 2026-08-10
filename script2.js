const fs = require('fs');
let file = fs.readFileSync('c:/xampp/htdocs/keshav-madhav/resources/views/admin/packing/process.blade.php', 'utf8');

// 1. Rework Queue Logic
const reworkJs = `let reworkQueue = [];
            function addReworkToQueue() {
                let lotNo = $('#rework_lot_no').val();
                if (!lotNo) {
                    alert("Please select a lot first.");
                    return;
                }
                let added = false;
                $('.rework-qty-input').each(function () {
                    let qty = parseInt($(this).val()) || 0;
                    if (qty > 0) {
                        let detailId = $(this).data('id');
                        let item = ORDER_ITEMS.find(i => i.id == detailId);
                        reworkQueue.push({
                            lot_no: lotNo,
                            detail_id: detailId,
                            qty: qty,
                            design_number: item ? item.design_number : '',
                            color_name: item ? item.color_name : '',
                            size: item ? item.size : ''
                        });
                        $(this).val(0); // clear input
                        added = true;
                    }
                });
                if (!added) {
                    alert("Please enter quantity for at least one item.");
                } else {
                    renderReworkQueue();
                }
            }
            function renderReworkQueue() {
                let $tbody = $('#reworkQueueList');
                $tbody.empty();
                reworkQueue.forEach((q, index) => {
                    $tbody.append(\`<tr>
                        <td>\${q.lot_no}</td>
                        <td>\${q.design_number} / \${q.color_name}</td>
                        <td>\${q.size}</td>
                        <td>\${q.qty}</td>
                        <td><button type="button" class="btn btn-sm btn-danger py-0 px-1" onclick="removeReworkQueue(\${index})"><i class="fas fa-trash"></i></button></td>
                    </tr>\`);
                });
            }
            function removeReworkQueue(index) {
                reworkQueue.splice(index, 1);
                renderReworkQueue();
            }`;

// 2. Dead Stock Queue Logic
const deadJs = `let deadQueue = [];
            function addDeadToQueue() {
                let lotNo = $('#dead_lot_no').val();
                if (!lotNo) {
                    alert("Please select a lot first.");
                    return;
                }
                let added = false;
                $('.dead-qty-input').each(function () {
                    let qty = parseInt($(this).val()) || 0;
                    if (qty > 0) {
                        let detailId = $(this).data('id');
                        let item = ORDER_ITEMS.find(i => i.id == detailId);
                        deadQueue.push({
                            lot_no: lotNo,
                            detail_id: detailId,
                            qty: qty,
                            design_number: item ? item.design_number : '',
                            color_name: item ? item.color_name : '',
                            size: item ? item.size : ''
                        });
                        $(this).val(0); // clear input
                        added = true;
                    }
                });
                if (!added) {
                    alert("Please enter quantity for at least one item.");
                } else {
                    renderDeadQueue();
                }
            }
            function renderDeadQueue() {
                let $tbody = $('#deadQueueList');
                $tbody.empty();
                deadQueue.forEach((q, index) => {
                    $tbody.append(\`<tr>
                        <td>\${q.lot_no}</td>
                        <td>\${q.design_number} / \${q.color_name}</td>
                        <td>\${q.size}</td>
                        <td>\${q.qty}</td>
                        <td><button type="button" class="btn btn-sm btn-danger py-0 px-1" onclick="removeDeadQueue(\${index})"><i class="fas fa-trash"></i></button></td>
                    </tr>\`);
                });
            }
            function removeDeadQueue(index) {
                deadQueue.splice(index, 1);
                renderDeadQueue();
            }`;

// 3. Sampling Queue Logic
const samplingJs = `let samplingQueue = [];
            function addSamplingToQueue() {
                let lotNo = $('#sampling_lot_no').val();
                if (!lotNo) {
                    alert("Please select a lot first.");
                    return;
                }
                let added = false;
                $('.sampling-qty-input').each(function () {
                    let qty = parseInt($(this).val()) || 0;
                    if (qty > 0) {
                        let detailId = $(this).data('id');
                        let item = ORDER_ITEMS.find(i => i.id == detailId);
                        samplingQueue.push({
                            lot_no: lotNo,
                            detail_id: detailId,
                            qty: qty,
                            design_number: item ? item.design_number : '',
                            color_name: item ? item.color_name : '',
                            size: item ? item.size : ''
                        });
                        $(this).val(0); // clear input
                        added = true;
                    }
                });
                if (!added) {
                    alert("Please enter quantity for at least one item.");
                } else {
                    renderSamplingQueue();
                }
            }
            function renderSamplingQueue() {
                let $tbody = $('#samplingQueueList');
                $tbody.empty();
                samplingQueue.forEach((q, index) => {
                    $tbody.append(\`<tr>
                        <td>\${q.lot_no}</td>
                        <td>\${q.design_number} / \${q.color_name}</td>
                        <td>\${q.size}</td>
                        <td>\${q.qty}</td>
                        <td><button type="button" class="btn btn-sm btn-danger py-0 px-1" onclick="removeSamplingQueue(\${index})"><i class="fas fa-trash"></i></button></td>
                    </tr>\`);
                });
            }
            function removeSamplingQueue(index) {
                samplingQueue.splice(index, 1);
                renderSamplingQueue();
            }`;

// 4. Debit Queue Logic
const debitJs = `let debitQueue = [];
            function addDebitToQueue() {
                let lotNo = $('#debit_lot_no').val();
                if (!lotNo) {
                    alert("Please select a lot first.");
                    return;
                }
                let added = false;
                let hasInvalidRate = false;
                $('#debitItemsList tr').each(function () {
                    let $qtyInput = $(this).find('.debit-qty-input');
                    let $rateInput = $(this).find('.debit-rate-input');
                    if($qtyInput.length === 0) return;
                    let qty = parseInt($qtyInput.val()) || 0;
                    let rate = parseFloat($rateInput.val()) || 0;
                    if (qty > 0) {
                        if (rate <= 0) {
                            hasInvalidRate = true;
                            return false;
                        }
                        let detailId = $qtyInput.data('id');
                        let item = ORDER_ITEMS.find(i => i.id == detailId);
                        debitQueue.push({
                            lot_no: lotNo,
                            detail_id: detailId,
                            qty: qty,
                            per_piece_amount: rate,
                            design_number: item ? item.design_number : '',
                            color_name: item ? item.color_name : '',
                            size: item ? item.size : ''
                        });
                        $qtyInput.val(0);
                        $rateInput.val(0);
                        added = true;
                    }
                });
                if (hasInvalidRate) {
                    alert("Please enter a valid rate for all selected pieces.");
                    return;
                }
                if (!added) {
                    alert("Please enter quantity for at least one item.");
                } else {
                    renderDebitQueue();
                    calculateDebitTotal();
                }
            }
            function renderDebitQueue() {
                let $tbody = $('#debitQueueList');
                $tbody.empty();
                debitQueue.forEach((q, index) => {
                    $tbody.append(\`<tr>
                        <td>\${q.lot_no}</td>
                        <td>\${q.design_number} / \${q.color_name}</td>
                        <td>\${q.size}</td>
                        <td>\${q.qty}</td>
                        <td>\${q.per_piece_amount}</td>
                        <td><button type="button" class="btn btn-sm btn-danger py-0 px-1" onclick="removeDebitQueue(\${index})"><i class="fas fa-trash"></i></button></td>
                    </tr>\`);
                });
            }
            function removeDebitQueue(index) {
                debitQueue.splice(index, 1);
                renderDebitQueue();
                calculateDebitTotal();
            }`;

// We will insert these functions before the submit methods.
file = file.replace(
    'function submitReworkAssignment() {',
    reworkJs + '\n\n            function submitReworkAssignment() {'
);
file = file.replace(
    'function submitDeadStock() {',
    deadJs + '\n\n            function submitDeadStock() {'
);
file = file.replace(
    'function submitSampling() {',
    samplingJs + '\n\n            function submitSampling() {'
);
file = file.replace(
    'function submitDebit() {',
    debitJs + '\n\n            function submitDebit() {'
);

fs.writeFileSync('c:/xampp/htdocs/keshav-madhav/resources/views/admin/packing/process.blade.php', file);
