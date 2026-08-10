<?php
$file = 'resources/views/admin/packing/process.blade.php';
$content = file_get_contents($file);

// 1. Add queue logic JS inside <script> tag before submitReworkAssignment
$queue_js = <<<JS
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
                let \$tbody = $('#reworkQueueList');
                \$tbody.empty();
                reworkQueue.forEach((q, index) => {
                    \$tbody.append(`<tr>
                        <td>\${q.lot_no}</td>
                        <td>\${q.design_number} / \${q.color_name}</td>
                        <td>\${q.size}</td>
                        <td>\${q.qty}</td>
                        <td><button type="button" class="btn btn-sm btn-danger py-0 px-1" onclick="removeReworkQueue(\${index})"><i class="fas fa-trash"></i></button></td>
                    </tr>`);
                });
            }
            function removeReworkQueue(index) {
                reworkQueue.splice(index, 1);
                renderReworkQueue();
            }

            function submitReworkAssignment() {
JS;
$content = str_replace('            function submitReworkAssignment() {', $queue_js, $content);

// Modifying submitReworkAssignment items collection
$old_rework = <<<JS
                let items = [];
                $('.rework-qty-input').each(function () {
                    let qty = parseInt($(this).val()) || 0;
                    if (qty > 0) {
                        items.push({
                            detail_id: $(this).data('id'),
                            qty: qty
                        });
                    }
                });
JS;
$new_rework = <<<JS
                let items = reworkQueue.map(q => ({ detail_id: q.detail_id, qty: q.qty, lot_no: q.lot_no }));
JS;
$content = str_replace($old_rework, $new_rework, $content);

// 2. Dead Stock JS
$queue_dead_js = <<<JS
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
                let \$tbody = $('#deadQueueList');
                \$tbody.empty();
                deadQueue.forEach((q, index) => {
                    \$tbody.append(`<tr>
                        <td>\${q.lot_no}</td>
                        <td>\${q.design_number} / \${q.color_name}</td>
                        <td>\${q.size}</td>
                        <td>\${q.qty}</td>
                        <td><button type="button" class="btn btn-sm btn-danger py-0 px-1" onclick="removeDeadQueue(\${index})"><i class="fas fa-trash"></i></button></td>
                    </tr>`);
                });
            }
            function removeDeadQueue(index) {
                deadQueue.splice(index, 1);
                renderDeadQueue();
            }

            function submitDeadStock() {
JS;
$content = str_replace('            function submitDeadStock() {', $queue_dead_js, $content);

$old_dead = <<<JS
                let items = [];
                $('.dead-qty-input').each(function () {
                    let qty = parseInt($(this).val()) || 0;
                    if (qty > 0) {
                        items.push({
                            detail_id: $(this).data('id'),
                            qty: qty
                        });
                    }
                });
JS;
$new_dead = <<<JS
                let items = deadQueue.map(q => ({ detail_id: q.detail_id, qty: q.qty, lot_no: q.lot_no }));
JS;
$content = str_replace($old_dead, $new_dead, $content);

// 3. Sampling JS
$queue_sampling_js = <<<JS
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
                let \$tbody = $('#samplingQueueList');
                \$tbody.empty();
                samplingQueue.forEach((q, index) => {
                    \$tbody.append(`<tr>
                        <td>\${q.lot_no}</td>
                        <td>\${q.design_number} / \${q.color_name}</td>
                        <td>\${q.size}</td>
                        <td>\${q.qty}</td>
                        <td><button type="button" class="btn btn-sm btn-danger py-0 px-1" onclick="removeSamplingQueue(\${index})"><i class="fas fa-trash"></i></button></td>
                    </tr>`);
                });
            }
            function removeSamplingQueue(index) {
                samplingQueue.splice(index, 1);
                renderSamplingQueue();
            }

            function submitSampling() {
JS;
$content = str_replace('            function submitSampling() {', $queue_sampling_js, $content);

$old_sampling = <<<JS
                let items = [];
                $('.sampling-qty-input').each(function () {
                    let qty = parseInt($(this).val()) || 0;
                    if (qty > 0) {
                        items.push({
                            detail_id: $(this).data('id'),
                            qty: qty
                        });
                    }
                });
JS;
$new_sampling = <<<JS
                let items = samplingQueue.map(q => ({ detail_id: q.detail_id, qty: q.qty, lot_no: q.lot_no }));
JS;
$content = str_replace($old_sampling, $new_sampling, $content);

// 4. Debit JS
$queue_debit_js = <<<JS
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
                    let \$qtyInput = $(this).find('.debit-qty-input');
                    let \$rateInput = $(this).find('.debit-rate-input');
                    if(\$qtyInput.length === 0) return;
                    let qty = parseInt(\$qtyInput.val()) || 0;
                    let rate = parseFloat(\$rateInput.val()) || 0;
                    if (qty > 0) {
                        if (rate <= 0) {
                            hasInvalidRate = true;
                            return false;
                        }
                        let detailId = \$qtyInput.data('id');
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
                        \$qtyInput.val(0);
                        \$rateInput.val(0);
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
                let \$tbody = $('#debitQueueList');
                \$tbody.empty();
                debitQueue.forEach((q, index) => {
                    \$tbody.append(`<tr>
                        <td>\${q.lot_no}</td>
                        <td>\${q.design_number} / \${q.color_name}</td>
                        <td>\${q.size}</td>
                        <td>\${q.qty}</td>
                        <td>\${q.per_piece_amount}</td>
                        <td><button type="button" class="btn btn-sm btn-danger py-0 px-1" onclick="removeDebitQueue(\${index})"><i class="fas fa-trash"></i></button></td>
                    </tr>`);
                });
            }
            function removeDebitQueue(index) {
                debitQueue.splice(index, 1);
                renderDebitQueue();
                calculateDebitTotal();
            }

            function calculateDebitTotal() {
JS;
$content = str_replace('            function calculateDebitTotal() {', $queue_debit_js, $content);

$old_debit_calc = <<<JS
                let subtotal = 0;
                $('#debitItemsList tr').each(function () {
                    let qty = parseInt($(this).find('.debit-qty-input').val()) || 0;
                    let rate = parseFloat($(this).find('.debit-rate-input').val()) || 0;
                    subtotal += (qty * rate);
                });
JS;
$new_debit_calc = <<<JS
                let subtotal = 0;
                debitQueue.forEach(q => {
                    subtotal += (q.qty * q.per_piece_amount);
                });
                $('#debitItemsList tr').each(function () {
                    let qty = parseInt($(this).find('.debit-qty-input').val()) || 0;
                    let rate = parseFloat($(this).find('.debit-rate-input').val()) || 0;
                    subtotal += (qty * rate);
                });
JS;
$content = str_replace($old_debit_calc, $new_debit_calc, $content);

$old_debit_items = <<<JS
                let items = [];
                let hasValidRate = true;
                $('#debitItemsList tr').each(function () {
                    let \$qtyInput = $(this).find('.debit-qty-input');
                    let \$rateInput = $(this).find('.debit-rate-input');
                    let qty = parseInt(\$qtyInput.val()) || 0;
                    let rate = parseFloat(\$rateInput.val()) || 0;

                    if (qty > 0) {
                        if (rate <= 0) {
                            hasValidRate = false;
                        }
                        items.push({
                            detail_id: \$qtyInput.data('id'),
                            qty: qty,
                            per_piece_amount: rate
                        });
                    }
                });
JS;
$new_debit_items = <<<JS
                let items = debitQueue.map(q => ({ detail_id: q.detail_id, qty: q.qty, lot_no: q.lot_no, per_piece_amount: q.per_piece_amount }));
                let hasValidRate = true;
JS;
$content = str_replace($old_debit_items, $new_debit_items, $content);

// 5. Add Dropdowns to modals
$dead_html_old = <<<HTML
                            <h6>Select Pieces to Mark as Dead Stock</h6>
HTML;
$dead_html_new = <<<HTML
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted small">Select Lot Number <span class="text-danger">*</span></label>
                                <select id="dead_lot_no" class="form-control custom-select">
                                    <option value="">-- Select Lot --</option>
                                </select>
                            </div>
                            <h6>Select Pieces to Mark as Dead Stock</h6>
HTML;
$content = str_replace($dead_html_old, $dead_html_new, $content);

$sampling_html_old = <<<HTML
                            <h6>Select Pieces for Sampling</h6>
HTML;
$sampling_html_new = <<<HTML
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted small">Select Lot Number <span class="text-danger">*</span></label>
                                <select id="sampling_lot_no" class="form-control custom-select">
                                    <option value="">-- Select Lot --</option>
                                </select>
                            </div>
                            <h6>Select Pieces for Sampling</h6>
HTML;
$content = str_replace($sampling_html_old, $sampling_html_new, $content);

$debit_html_old = <<<HTML
                            <h6>2. Select Damaged Pieces</h6>
HTML;
$debit_html_new = <<<HTML
                            <h6>2. Select Damaged Pieces</h6>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted small">Select Lot Number <span class="text-danger">*</span></label>
                                <select id="debit_lot_no" class="form-control custom-select">
                                    <option value="">-- Select Lot --</option>
                                </select>
                            </div>
HTML;
$content = str_replace($debit_html_old, $debit_html_new, $content);

// 6. Add Queue Tables
// For Rework
$rework_table_old = <<<HTML
                                    <tbody id="reworkItemsList">
                                        <!-- Dynamic rows -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-5 border-left">
HTML;
$rework_table_new = <<<HTML
                                    <tbody id="reworkItemsList">
                                        <!-- Dynamic rows -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                                <h6>Processing Queue</h6>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addReworkToQueue()"><i class="fas fa-plus"></i> Add to List</button>
                            </div>
                            <div class="table-responsive border rounded mb-3" style="max-height: 250px;">
                                <table class="table table-sm mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Lot No</th>
                                            <th>Design / Color</th>
                                            <th>Size</th>
                                            <th>Qty</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="reworkQueueList"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-5 border-left">
HTML;
$content = str_replace($rework_table_old, $rework_table_new, $content);

// For Dead Stock
$dead_table_old = <<<HTML
                                    <tbody id="deadStockItemsList">
                                        <!-- Dynamic rows -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-4 border-left">
HTML;
$dead_table_new = <<<HTML
                                    <tbody id="deadStockItemsList">
                                        <!-- Dynamic rows -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                                <h6>Processing Queue</h6>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addDeadToQueue()"><i class="fas fa-plus"></i> Add to List</button>
                            </div>
                            <div class="table-responsive border rounded mb-3" style="max-height: 250px;">
                                <table class="table table-sm mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Lot No</th>
                                            <th>Design / Color</th>
                                            <th>Size</th>
                                            <th>Qty</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="deadQueueList"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-4 border-left">
HTML;
$content = str_replace($dead_table_old, $dead_table_new, $content);

// For Sampling
$sampling_table_old = <<<HTML
                                    <tbody id="samplingItemsList">
                                        <!-- Dynamic rows -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-4 border-left">
HTML;
$sampling_table_new = <<<HTML
                                    <tbody id="samplingItemsList">
                                        <!-- Dynamic rows -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                                <h6>Processing Queue</h6>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addSamplingToQueue()"><i class="fas fa-plus"></i> Add to List</button>
                            </div>
                            <div class="table-responsive border rounded mb-3" style="max-height: 250px;">
                                <table class="table table-sm mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Lot No</th>
                                            <th>Design / Color</th>
                                            <th>Size</th>
                                            <th>Qty</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="samplingQueueList"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-4 border-left">
HTML;
$content = str_replace($sampling_table_old, $sampling_table_new, $content);

// For Debit
$debit_table_old = <<<HTML
                                    <tbody id="debitItemsList"></tbody>
                                </table>
                            </div>

                            <h6>3. Storage Location</h6>
HTML;
$debit_table_new = <<<HTML
                                    <tbody id="debitItemsList"></tbody>
                                </table>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                                <h6>Processing Queue</h6>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addDebitToQueue()"><i class="fas fa-plus"></i> Add to List</button>
                            </div>
                            <div class="table-responsive border rounded mb-4" style="max-height: 250px;">
                                <table class="table table-sm mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Lot No</th>
                                            <th>Design / Color</th>
                                            <th>Size</th>
                                            <th>Qty</th>
                                            <th>Rate(₹)</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="debitQueueList"></tbody>
                                </table>
                            </div>

                            <h6>3. Storage Location</h6>
HTML;
$content = str_replace($debit_table_old, $debit_table_new, $content);

file_put_contents($file, $content);
echo "All JS and HTML changes applied.\n";
?>
