<?php

$file_path = 'resources/views/admin/ledger/party/show.blade.php';
$content = file_get_contents($file_path);

// 1. Update Filters Row
$orig_filters = <<<EOT
                                @if(\$type === 'sales_agent')
                                    <div class="col-md-3">
                                        <label class="small font-weight-bold text-muted">Filter by Customer</label>
                                        <select name="customer_id" class="form-control" style="border-radius: 10px;">
                                            <option value="">All Customers (Mix Parties)</option>
                                            @foreach(\$shops as \$shop)
                                                <option value="{{ \$shop->id }}" {{ request('customer_id') == \$shop->id ? 'selected' : '' }}>
                                                    {{ \$shop->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="col-md-{{ \$type === 'sales_agent' ? '3' : '6' }} mt-2 mt-md-0">
                                    <button type="submit" class="btn btn-primary px-3" style="border-radius: 10px;">
                                        <i class="fas fa-filter mr-1"></i> Filter
                                    </button>
                                    <a href="{{ route('admin.ledger.party.show', ['type' => \$type, 'id' => \$party->id]) }}"
                                        class="btn btn-outline-secondary ml-1" style="border-radius: 10px;">Clear</a>
                                    <a href="{{ route('admin.ledger.party.download', ['type' => \$type, 'id' => \$party->id, 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'customer_id' => request('customer_id')]) }}"
                                        class="btn btn-danger px-3 ml-1" style="border-radius: 10px;">
                                        <i class="fas fa-file-pdf mr-1"></i> PDF
                                    </a>
                                </div>
EOT;

$new_filters = <<<EOT
                                @if(\$type === 'sales_agent')
                                    <div class="col-md-3">
                                        <label class="small font-weight-bold text-muted">Filter by Customer</label>
                                        <select name="customer_id" class="form-control" style="border-radius: 10px;" onchange="this.form.submit()">
                                            <option value="">All Customers (Mix Parties)</option>
                                            @foreach(\$shops as \$shop)
                                                <option value="{{ \$shop->id }}" {{ request('customer_id') == \$shop->id ? 'selected' : '' }}>
                                                    {{ \$shop->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @if(!request('customer_id'))
                                    <div class="col-md-3 mt-2 mt-md-0">
                                        <label class="small font-weight-bold text-muted">View Mode</label>
                                        <select name="view_mode" class="form-control" style="border-radius: 10px;" onchange="this.form.submit()">
                                            <option value="mix" {{ request('view_mode', 'mix') === 'mix' ? 'selected' : '' }}>Mix (Consolidated)</option>
                                            <option value="party_wise" {{ request('view_mode') === 'party_wise' ? 'selected' : '' }}>Party-wise (Grouped)</option>
                                        </select>
                                    </div>
                                    @endif
                                @endif
                                <div class="col-md-{{ \$type === 'sales_agent' ? '12 mt-3 text-right' : '6 mt-2 mt-md-0' }}">
                                    <button type="submit" class="btn btn-primary px-3" style="border-radius: 10px;">
                                        <i class="fas fa-filter mr-1"></i> Filter
                                    </button>
                                    <a href="{{ route('admin.ledger.party.show', ['type' => \$type, 'id' => \$party->id]) }}"
                                        class="btn btn-outline-secondary ml-1" style="border-radius: 10px;">Clear</a>
                                    <a href="{{ route('admin.ledger.party.download', ['type' => \$type, 'id' => \$party->id, 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'customer_id' => request('customer_id'), 'view_mode' => request('view_mode', 'mix')]) }}"
                                        class="btn btn-danger px-3 ml-1" style="border-radius: 10px;">
                                        <i class="fas fa-file-pdf mr-1"></i> PDF
                                    </a>
                                </div>
EOT;

$content = str_replace($orig_filters, $new_filters, $content);

// 2. Wrap Existing Table in if condition and add party_wise rendering
$orig_table_start = "{{-- LEDGER TABLE --}}";
$new_table_start = <<<EOT
                {{-- LEDGER TABLE --}}
                @if(isset(\$viewMode) && \$viewMode === 'party_wise' && isset(\$groupedLedgers))
                    @forelse(\$groupedLedgers as \$ledger)
                        <div class="card card-detail mb-4">
                            <div class="card-header bg-light">
                                <div class="row align-items-center">
                                    <div class="col-sm-6">
                                        <h5 class="font-weight-bold mb-0 text-primary">{{ \$ledger->shop->name }}</h5>
                                        <small class="text-muted"><i class="fas fa-phone mr-1"></i>{{ \$ledger->shop->phone ?? '-' }} | <i class="fas fa-map-marker-alt mr-1"></i>{{ \$ledger->shop->address ?? 'No Address' }}</small>
                                    </div>
                                    <div class="col-sm-6 text-md-right mt-2 mt-md-0">
                                        <span class="mr-3">Opening: <strong>₹ {{ number_format(abs(\$ledger->opening_balance), 2) }} <span class="badge {{ \$ledger->opening_balance >= 0 ? 'badge-success' : 'badge-danger' }}">{{ \$ledger->opening_balance >= 0 ? 'CR' : 'DR' }}</span></strong></span>
                                        <span>Closing: <strong>₹ {{ number_format(abs(\$ledger->closing_balance), 2) }} <span class="badge {{ \$ledger->closing_balance >= 0 ? 'badge-success' : 'badge-danger' }}">{{ \$ledger->closing_balance >= 0 ? 'CR' : 'DR' }}</span></strong></span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-ledger mb-0">
                                        <thead>
                                            <tr>
                                                <th width="12%" class="pl-4">Date</th>
                                                <th width="10%">Type</th>
                                                <th width="12%">Reference</th>
                                                <th>Particulars</th>
                                                <th width="12%" class="text-right">Debit (DR)</th>
                                                <th width="12%" class="text-right">Credit (CR)</th>
                                                <th width="12%" class="text-right">Balance</th>
                                                <th width="8%" class="text-center pr-4">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php \$currentBalance = \$ledger->opening_balance; @endphp
                                            @forelse(\$ledger->transactions as \$tx)
                                                @php \$currentBalance = \$tx->running_balance; @endphp
                                                <tr>
                                                    <td class="pl-4 align-middle">
                                                        <span class="date-badge">
                                                            {{ \Carbon\Carbon::parse(\$tx->date)->format('d M Y') }}
                                                        </span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <span class="type-label bg-light border">
                                                            {{ \$tx->type }}
                                                        </span>
                                                    </td>
                                                    <td class="align-middle text-muted small font-weight-bold">
                                                        {{ \$tx->ref }}
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="particulars-text">{{ \$tx->description }}</div>
                                                    </td>
                                                    <td class="align-middle text-right text-debit">
                                                        {{ \$tx->debit > 0 ? '₹ ' . number_format(\$tx->debit, 2) : '-' }}
                                                    </td>
                                                    <td class="align-middle text-right text-credit">
                                                        {{ \$tx->credit > 0 ? '₹ ' . number_format(\$tx->credit, 2) : '-' }}
                                                    </td>
                                                    <td class="align-middle text-right text-balance">
                                                        ₹ {{ number_format(abs(\$currentBalance), 2) }}
                                                        <small class="text-muted ml-1">{{ \$currentBalance >= 0 ? 'CR' : 'DR' }}</small>
                                                    </td>
                                                    <td class="align-middle text-center pr-4">
                                                        @if(isset(\$tx->view_url) && \$tx->view_url !== '#')
                                                            <a href="{{ \$tx->view_url }}" class="btn btn-xs btn-outline-primary" title="View Transaction Details" target="_blank">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-4 text-muted">
                                                        No transactions recorded for this customer in the selected period.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        @if(!\$ledger->transactions->isEmpty())
                                            <tfoot class="bg-light">
                                                <tr class="font-weight-bold">
                                                    <td colspan="4" class="text-right py-3 pr-4">Totals:</td>
                                                    <td class="text-right py-3 text-debit">₹ {{ number_format(\$ledger->transactions->sum('debit'), 2) }}</td>
                                                    <td class="text-right py-3 text-credit">₹ {{ number_format(\$ledger->transactions->sum('credit'), 2) }}</td>
                                                    <td class="text-right py-3 text-primary">
                                                        ₹ {{ number_format(abs(\$currentBalance), 2) }}
                                                        <small>{{ \$currentBalance >= 0 ? 'CR' : 'DR' }}</small>
                                                    </td>
                                                    <td class="bg-light"></td>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="card card-detail">
                            <div class="card-body text-center py-5 text-muted">
                                No customers found for this agent.
                            </div>
                        </div>
                    @endforelse
                @else
EOT;

$content = str_replace($orig_table_start, $new_table_start, $content);

// 3. Add @endif at the end of the table
$orig_end = <<<EOT
                    </div>
                </div>
            </div>
        </section>
EOT;
$new_end = <<<EOT
                    </div>
                </div>
                @endif
            </div>
        </section>
EOT;
$content = str_replace($orig_end, $new_end, $content);

file_put_contents($file_path, $content);
echo "Patch applied successfully\n";
