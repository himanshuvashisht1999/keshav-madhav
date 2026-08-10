const fs = require('fs');
let file = fs.readFileSync('c:/xampp/htdocs/keshav-madhav/resources/views/admin/packing/process.blade.php', 'utf8');

// 1. Rework Modal
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

// 2. Dead Stock Modal
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

// 3. Sampling Modal
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

// 4. Debit Modal
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

fs.writeFileSync('c:/xampp/htdocs/keshav-madhav/resources/views/admin/packing/process.blade.php', file);
