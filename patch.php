<?php

$file_path = 'app/Http/Controllers/Admin/Ledger/PartyLedgerController.php';
$content = file_get_contents($file_path);

// 1. Start of getLedgerData
$content = str_replace(
    "\$endDate = \$request->query('end_date');",
    "\$endDate = \$request->query('end_date');\n        \$viewMode = \$request->query('view_mode', 'mix');\n        \$groupedLedgers = [];",
    $content
);

// 2. Selected customer ID
$content = str_replace(
    "\$party->balance = \$selectedCustomer ? \$selectedCustomer->balance : 0;",
    "\$party->balance = \$selectedCustomer ? \$selectedCustomer->balance : 0;\n                \$viewMode = 'mix';",
    $content
);

// 3. Add customer_id to Dispatches
$content = preg_replace(
    "/'date' => \\\$d->dispatch_date,/",
    "'customer_id' => \$d->master_customer_id,\n                    'date' => \$d->dispatch_date,",
    $content,
    1
);

// 4. Add customer_id to Order Dispatches
$content = preg_replace(
    "/'date' => \\\$od->dispatch_date,/",
    "'customer_id' => \$od->customer_id,\n                    'date' => \$od->dispatch_date,",
    $content,
    1
);

// 5. Add customer_id to Sales Returns
$content = preg_replace(
    "/'date' => \\\$r->return_date,/",
    "'customer_id' => \$r->dispatch->master_customer_id ?? null,\n                    'date' => \$r->return_date,",
    $content,
    1
);

// 6. Add customer_id to Payments
$content = preg_replace(
    "/'date' => \\\$p->payment_date,/",
    "'customer_id' => \$p->party_id,\n                    'date' => \$p->payment_date,",
    $content,
    1
);

// 7. Add customer_id to Inventory Purchases
$content = preg_replace(
    "/'date' => \\\$ip->created_at,/",
    "'customer_id' => \$ip->customer_id,\n                    'date' => \$ip->created_at,",
    $content,
    1
);

// 8. Add customer_id to Journal Vouchers
$content = preg_replace(
    "/'date' => \\\$v->voucher->date,/",
    "'customer_id' => \$v->master_id,\n                    'date' => \$v->voucher->date,",
    $content,
    1
);

// 9. Opening Balance
$orig_ob = <<<EOT
            // 7. Opening Balance
            \$openingBalAmount = 0;
            \$openingBalances = \App\Models\MasterOpeningBalance::where('master_type', 'customer')
                ->whereIn('master_id', \$customerIds)
                ->where('financial_year', \App\Models\MasterOpeningBalance::getCurrentFinancialYear())
                ->get();

            foreach (\$openingBalances as \$ob) {
                \$balanceType = strtolower(trim(\$ob->balance_type));
                \$obAmount = (float) \$ob->amount;
                if (\$balanceType === 'debit') {
                    \$openingBalAmount -= \$obAmount;
                } else {
                    \$openingBalAmount += \$obAmount;
                }
            }
EOT;

$new_ob = <<<EOT
            \$openingBalancesMap = [];
            foreach (\$customerIds as \$cId) {
                \$openingBalancesMap[\$cId] = 0;
            }

            // 7. Opening Balance
            \$openingBalAmount = 0;
            \$openingBalances = \App\Models\MasterOpeningBalance::where('master_type', 'customer')
                ->whereIn('master_id', \$customerIds)
                ->where('financial_year', \App\Models\MasterOpeningBalance::getCurrentFinancialYear())
                ->get();

            foreach (\$openingBalances as \$ob) {
                \$balanceType = strtolower(trim(\$ob->balance_type));
                \$obAmount = (float) \$ob->amount;
                if (\$balanceType === 'debit') {
                    \$openingBalAmount -= \$obAmount;
                    \$openingBalancesMap[\$ob->master_id] -= \$obAmount;
                } else {
                    \$openingBalAmount += \$obAmount;
                    \$openingBalancesMap[\$ob->master_id] += \$obAmount;
                }
            }
EOT;
$content = str_replace($orig_ob, $new_ob, $content);

// 10. Sort and Calculate Balance
$orig_calc = <<<EOT
            \$balance = \$openingBalAmount;
            foreach (\$transactions as \$tx) {
                \$balance += (\$tx->credit - \$tx->debit);
                \$tx->running_balance = \$balance;
            }

            return compact('party', 'transactions', 'type', 'startDate', 'endDate', 'openingBalAmount', 'shops');
EOT;

$new_calc = <<<EOT
            if (\$viewMode === 'party_wise') {
                foreach (\$customerIds as \$cId) {
                    \$shop = \$shops->firstWhere('id', \$cId);
                    if (!\$shop) continue;

                    \$shopTransactions = \$transactions->where('customer_id', \$cId)->values();

                    \$opBalance = \$openingBalancesMap[\$cId] ?? 0;
                    \$runningBal = \$opBalance;
                    foreach (\$shopTransactions as \$tx) {
                        \$runningBal += (\$tx->credit - \$tx->debit);
                        \$tx->running_balance = \$runningBal;
                    }

                    \$groupedLedgers[] = (object) [
                        'shop' => \$shop,
                        'opening_balance' => \$opBalance,
                        'transactions' => \$shopTransactions,
                        'closing_balance' => \$runningBal
                    ];
                }
            } else {
                \$balance = \$openingBalAmount;
                foreach (\$transactions as \$tx) {
                    \$balance += (\$tx->credit - \$tx->debit);
                    \$tx->running_balance = \$balance;
                }
            }

            return compact('party', 'transactions', 'type', 'startDate', 'endDate', 'openingBalAmount', 'shops', 'viewMode', 'groupedLedgers');
EOT;
$content = str_replace($orig_calc, $new_calc, $content);

// 11. Final compact return
$orig_ret = "        return compact('party', 'transactions', 'type', 'startDate', 'endDate', 'openingBalAmount');";
$new_ret = "        return compact('party', 'transactions', 'type', 'startDate', 'endDate', 'openingBalAmount', 'viewMode', 'groupedLedgers');";
$content = str_replace($orig_ret, $new_ret, $content);

file_put_contents($file_path, $content);

echo "Patch applied successfully\n";
