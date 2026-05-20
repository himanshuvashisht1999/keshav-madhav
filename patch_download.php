<?php

$file_path = 'resources/views/admin/ledger/party/download.blade.php';
$content = file_get_contents($file_path);

$orig_tables = <<<EOT
    <table class="balance-summary">
        <tr>
            <td style="border-right: 1px solid #ddd;">
                <span class="label">Opening Balance</span>
                <span class="value">₹ {{ number_format(abs(\$openingBalAmount), 2) }} {{ \$openingBalAmount >= 0 ? 'DR' : 'CR' }}</span>
            </td>
            <td>
                <span class="label">Closing Balance</span>
                <span class="value">₹ {{ number_format(abs(\$party->balance), 2) }} 
                    @if(\$type === 'customer')
                        {{ \$party->balance <= 0 ? 'DR' : 'CR' }}
                    @else
                        {{ \$party->balance >= 0 ? 'CR' : 'DR' }}
                    @endif
                </span>
            </td>
        </tr>
    </table>

    <table class="ledger-table">
        <thead>
            <tr>
                <th width="10%">Date</th>
                <th width="12%">Type</th>
                <th width="15%">Ref</th>
                <th>Description</th>
                <th width="12%" class="text-right">Debit</th>
                <th width="12%" class="text-right">Credit</th>
                <th width="15%" class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @php \$currentBalance = 0; @endphp
            @forelse(\$transactions as \$tx)
                @php \$currentBalance = \$tx->running_balance; @endphp
                <tr>
                    <td>{{ date('d-M-y', strtotime(\$tx->date)) }}</td>
                    <td>{{ \$tx->type }}</td>
                    <td>{{ \$tx->ref }}</td>
                    <td>{{ \$tx->description }}</td>
                    <td class="text-right">
                        {{ \$tx->debit > 0 ? '₹ ' . number_format(\$tx->debit, 2) : '-' }}
                    </td>
                    <td class="text-right">
                        {{ \$tx->credit > 0 ? '₹ ' . number_format(\$tx->credit, 2) : '-' }}
                    </td>
                    <td class="text-right text-bold">
                        ₹ {{ number_format(abs(\$currentBalance), 2) }} {{ \$currentBalance >= 0 ? 'DR' : 'CR' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">No transactions found.</td>
                </tr>
            @endforelse
        </tbody>
        @if(!\$transactions->isEmpty())
        <tfoot>
            <tr style="background: #f0f0f0;">
                <td colspan="4" class="text-right text-bold" style="padding: 10px;">TOTALS:</td>
                <td class="text-right text-bold" style="padding: 10px;">₹ {{ number_format(\$transactions->sum('debit'), 2) }}</td>
                <td class="text-right text-bold" style="padding: 10px;">₹ {{ number_format(\$transactions->sum('credit'), 2) }}</td>
                <td class="text-right text-bold" style="padding: 10px; color: #1e3c72;">
                    ₹ {{ number_format(abs(\$currentBalance), 2) }} {{ \$currentBalance >= 0 ? 'DR' : 'CR' }}
                </td>
            </tr>
        </tfoot>
        @endif
    </table>
EOT;

$new_tables = <<<EOT
    @if(isset(\$viewMode) && \$viewMode === 'party_wise' && isset(\$groupedLedgers))
        @forelse(\$groupedLedgers as \$ledger)
            <div style="margin-top: 30px; margin-bottom: 10px; font-size: 14px; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 5px; color: #1e3c72;">
                Customer: {{ \$ledger->shop->name }}
            </div>
            <table class="balance-summary">
                <tr>
                    <td style="border-right: 1px solid #ddd;">
                        <span class="label">Opening Balance</span>
                        <span class="value">₹ {{ number_format(abs(\$ledger->opening_balance), 2) }} {{ \$ledger->opening_balance >= 0 ? 'DR' : 'CR' }}</span>
                    </td>
                    <td>
                        <span class="label">Closing Balance</span>
                        <span class="value">₹ {{ number_format(abs(\$ledger->closing_balance), 2) }} 
                            {{ \$ledger->closing_balance >= 0 ? 'DR' : 'CR' }}
                        </span>
                    </td>
                </tr>
            </table>

            <table class="ledger-table">
                <thead>
                    <tr>
                        <th width="10%">Date</th>
                        <th width="12%">Type</th>
                        <th width="15%">Ref</th>
                        <th>Description</th>
                        <th width="12%" class="text-right">Debit</th>
                        <th width="12%" class="text-right">Credit</th>
                        <th width="15%" class="text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @php \$currentBalance = \$ledger->opening_balance; @endphp
                    @forelse(\$ledger->transactions as \$tx)
                        @php \$currentBalance = \$tx->running_balance; @endphp
                        <tr>
                            <td>{{ date('d-M-y', strtotime(\$tx->date)) }}</td>
                            <td>{{ \$tx->type }}</td>
                            <td>{{ \$tx->ref }}</td>
                            <td>{{ \$tx->description }}</td>
                            <td class="text-right">
                                {{ \$tx->debit > 0 ? '₹ ' . number_format(\$tx->debit, 2) : '-' }}
                            </td>
                            <td class="text-right">
                                {{ \$tx->credit > 0 ? '₹ ' . number_format(\$tx->credit, 2) : '-' }}
                            </td>
                            <td class="text-right text-bold">
                                ₹ {{ number_format(abs(\$currentBalance), 2) }} {{ \$currentBalance >= 0 ? 'DR' : 'CR' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px;">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if(!\$ledger->transactions->isEmpty())
                <tfoot>
                    <tr style="background: #f0f0f0;">
                        <td colspan="4" class="text-right text-bold" style="padding: 10px;">TOTALS:</td>
                        <td class="text-right text-bold" style="padding: 10px;">₹ {{ number_format(\$ledger->transactions->sum('debit'), 2) }}</td>
                        <td class="text-right text-bold" style="padding: 10px;">₹ {{ number_format(\$ledger->transactions->sum('credit'), 2) }}</td>
                        <td class="text-right text-bold" style="padding: 10px; color: #1e3c72;">
                            ₹ {{ number_format(abs(\$currentBalance), 2) }} {{ \$currentBalance >= 0 ? 'DR' : 'CR' }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        @empty
            <div style="text-align: center; padding: 20px;">No customers found.</div>
        @endforelse
    @else
{$orig_tables}
    @endif
EOT;

$content = str_replace($orig_tables, $new_tables, $content);

file_put_contents($file_path, $content);
echo "Patch applied successfully\n";
