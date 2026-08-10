const fs = require('fs');
let file = fs.readFileSync('c:/xampp/htdocs/keshav-madhav/resources/views/admin/packing/process.blade.php', 'utf8');

// 1. Modals HTML
file = file.replace(
    '<tbody id="reworkItemsList">\n                                    <!-- Populated by JS -->\n                                </tbody>\n                            </table>\n                        </div>\n                    </div>',
    `<tbody id="reworkItemsList">
                                    <!-- Populated by JS -->
                                </tbody>
                            </table>
                        </div>
                        <div class="text-right mt-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addReworkToQueue()">Add to List</button>
                        </div>
                        <div class="mt-3">
                            <h6>Rework List</h6>
                            <table class="table table-sm table-bordered">
                                <thead class="bg-light">
                                    <tr><th>Lot</th><th>Design/Color</th><th>Size</th><th>Qty</th><th>Action</th></tr>
                                </thead>
                                <tbody id="reworkQueueList"></tbody>
                            </table>
                        </div>
                    </div>`
);

file = file.replace(
    '<h6>Select Pieces to Mark as Dead Stock</h6>',
    `<div class="form-group mb-3">
                                <label class="font-weight-bold text-muted small">Select Lot Number <span class="text-danger">*</span></label>
                                <select id="dead_lot_no" class="form-control custom-select">
                                    <option value="">-- Select Lot --</option>
                                </select>
                            </div>
                            <h6>Select Pieces to Mark as Dead Stock</h6>`
);

file = file.replace(
    '<tbody id="deadStockItemsList">\n                                        <!-- Dynamic rows -->\n                                    </tbody>\n                                </table>\n                            </div>\n                        </div>',
    `<tbody id="deadStockItemsList">
                                        <!-- Dynamic rows -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-right mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addDeadToQueue()">Add to List</button>
                            </div>
                            <div class="mt-3">
                                <h6>Dead Stock List</h6>
                                <table class="table table-sm table-bordered">
                                    <thead class="bg-light">
                                        <tr><th>Lot</th><th>Design/Color</th><th>Size</th><th>Qty</th><th>Action</th></tr>
                                    </thead>
                                    <tbody id="deadQueueList"></tbody>
                                </table>
                            </div>
                        </div>`
);

file = file.replace(
    '<h6>Select Pieces for Sampling</h6>',
    `<div class="form-group mb-3">
                                <label class="font-weight-bold text-muted small">Select Lot Number <span class="text-danger">*</span></label>
                                <select id="sampling_lot_no" class="form-control custom-select">
                                    <option value="">-- Select Lot --</option>
                                </select>
                            </div>
                            <h6>Select Pieces for Sampling</h6>`
);

file = file.replace(
    '<tbody id="samplingItemsList">\n                                        <!-- Dynamic rows -->\n                                    </tbody>\n                                </table>\n                            </div>\n                        </div>',
    `<tbody id="samplingItemsList">
                                        <!-- Dynamic rows -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-right mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addSamplingToQueue()">Add to List</button>
                            </div>
                            <div class="mt-3">
                                <h6>Sampling List</h6>
                                <table class="table table-sm table-bordered">
                                    <thead class="bg-light">
                                        <tr><th>Lot</th><th>Design/Color</th><th>Size</th><th>Qty</th><th>Action</th></tr>
                                    </thead>
                                    <tbody id="samplingQueueList"></tbody>
                                </table>
                            </div>
                        </div>`
);

file = file.replace(
    '<h6>2. Select Damaged Pieces</h6>',
    `<h6>2. Select Damaged Pieces</h6>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted small">Select Lot Number <span class="text-danger">*</span></label>
                                <select id="debit_lot_no" class="form-control custom-select">
                                    <option value="">-- Select Lot --</option>
                                </select>
                            </div>`
);

file = file.replace(
    '<tbody id="debitItemsList"></tbody>\n                                </table>\n                            </div>\n\n                            <h6>3. Storage Location</h6>',
    `<tbody id="debitItemsList"></tbody>
                                </table>
                            </div>
                            <div class="text-right mt-2 mb-3">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addDebitToQueue()">Add to List</button>
                            </div>
                            <div class="mt-3 mb-3">
                                <h6>Debit List</h6>
                                <table class="table table-sm table-bordered">
                                    <thead class="bg-light">
                                        <tr><th>Lot</th><th>Design/Color</th><th>Size</th><th>Qty</th><th>Rate(₹)</th><th>Action</th></tr>
                                    </thead>
                                    <tbody id="debitQueueList"></tbody>
                                </table>
                            </div>

                            <h6>3. Storage Location</h6>`
);

// 2. Queue Logic Functions
const queueLogic = `
            let reworkQueue = [];
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
                        $(this).val(0);
                        added = true;
                    }
                });
                if (!added) alert("Please enter quantity for at least one item.");
                else renderReworkQueue();
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
            }

            let deadQueue = [];
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
                        $(this).val(0);
                        added = true;
                    }
                });
                if (!added) alert("Please enter quantity for at least one item.");
                else renderDeadQueue();
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
            }

            let samplingQueue = [];
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
                        $(this).val(0);
                        added = true;
                    }
                });
                if (!added) alert("Please enter quantity for at least one item.");
                else renderSamplingQueue();
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
            }

            let debitQueue = [];
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
                if (!added) alert("Please enter quantity for at least one item.");
                else {
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
            }

            function calculateDebitTotal() {
                let subtotal = 0;
                debitQueue.forEach(q => {
                    subtotal += (q.qty * q.per_piece_amount);
                });
                $('#debitItemsList tr').each(function () {
                    let qty = parseInt($(this).find('.debit-qty-input').val()) || 0;
                    let rate = parseFloat($(this).find('.debit-rate-input').val()) || 0;
                    subtotal += (qty * rate);
                });

                let discount = parseFloat($('#debitDiscount').val()) || 0;
                let finalTotal = Math.max(0, subtotal - discount);

                $('#debitAmount').val(finalTotal.toFixed(2));
                $('#debitTotalDisplay').text(finalTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            }
`;

file = file.replace(
    'function submitReworkAssignment() {',
    queueLogic + '\n            function submitReworkAssignment() {'
);


// 3. Replace the submit logic inside functions
// A) submitReworkAssignment
file = file.replace(
    /let items = \[\];[\s\S]*?if \(items\.length === 0\) \{/m,
    `let items = reworkQueue.map(q => ({ detail_id: q.detail_id, qty: q.qty, lot_no: q.lot_no }));\n\n                if (items.length === 0) {`
);

// B) submitDeadStock
file = file.replace(
    /let items = \[\];\s*\$\('\.dead-qty-input'\)\.each\(function \(\) \{[\s\S]*?\}\);\s*if \(items\.length === 0\) \{/m,
    `let items = deadQueue.map(q => ({ detail_id: q.detail_id, qty: q.qty, lot_no: q.lot_no }));\n\n                if (items.length === 0) {`
);

// C) submitSampling
file = file.replace(
    /let items = \[\];\s*\$\('\.sampling-qty-input'\)\.each\(function \(\) \{[\s\S]*?\}\);\s*if \(items\.length === 0\) \{/m,
    `let items = samplingQueue.map(q => ({ detail_id: q.detail_id, qty: q.qty, lot_no: q.lot_no }));\n\n                if (items.length === 0) {`
);

// D) submitDebit
file = file.replace(
    /let items = \[\];\s*let hasValidRate = true;\s*\$\('#debitItemsList tr'\)\.each\(function \(\) \{[\s\S]*?if \(!hasValidRate\) \{[\s\S]*?return;\s*\}/m,
    `let items = debitQueue.map(q => ({ detail_id: q.detail_id, qty: q.qty, per_piece_amount: q.per_piece_amount, lot_no: q.lot_no }));\n\n                if (items.length === 0) {\n                    alert('Please select at least one damaged item to debit.');\n                    return;\n                }`
);

// Remove existing calculateDebitTotal to avoid duplicate since we included it in queueLogic
file = file.replace(/function calculateDebitTotal\(\) \{[\s\S]*?\}\n/, '');


// Clean success responses
file = file.replace(
    /if \(response\.status === 'success'\) \{\s*toastr\.success\(response\.message\);\s*setTimeout\(\(\) => location\.reload\(\), 800\);\s*\}/g,
    `if (response.status === 'success') {
                            toastr.success(response.message);
                            setTimeout(() => location.reload(), 800);
                        }`
);

fs.writeFileSync('c:/xampp/htdocs/keshav-madhav/resources/views/admin/packing/process.blade.php', file);
