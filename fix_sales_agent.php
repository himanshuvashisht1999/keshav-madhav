<?php
function updateController($f) {
    $c = file_get_contents($f);
    
    // Replace the SalesAgent logic block
    $searchSales = <<<'EOD'
            // Sort and Calculate Balance
            $transactions = $transactions->sort(function ($a, $b) {
                $dateA = \Carbon\Carbon::parse($a->date)->format('Y-m-d');
                $dateB = \Carbon\Carbon::parse($b->date)->format('Y-m-d');

                if ($dateA != $dateB) {
                    return $dateA <=> $dateB;
                }

                return ($a->created_at ?? 0) <=> ($b->created_at ?? 0);
            })->values();

            $balance = $openingBalAmount;
            foreach ($transactions as $tx) {
                $balance += ($tx->credit - $tx->debit);
                $tx->running_balance = $balance;
            }

            if ($viewMode === 'party_wise') {
                foreach ($shops as $shop) {
                    // Filter transactions for this specific customer
                    $shopTx = $transactions->where('customer_id', $shop->id)->values();
                    
                    // Calculate opening balance for this customer
                    $shopOpeningAmt = 0;
                    $shopOpening = \App\Models\MasterOpeningBalance::where('master_type', 'customer')
                        ->where('master_id', $shop->id)
                        ->where('financial_year', \App\Models\MasterOpeningBalance::getCurrentFinancialYear())
                        ->first();

                    if ($shopOpening) {
                        $balanceType = strtolower(trim($shopOpening->balance_type));
                        $obAmount = (float) $shopOpening->amount;
                        if ($balanceType === 'debit') {
                            $shopOpeningAmt -= $obAmount;
                        } else {
                            $shopOpeningAmt += $obAmount;
                        }
                    }

                    $shopBal = $shopOpeningAmt;
                    foreach ($shopTx as $tx) {
                        $shopBal += ($tx->credit - $tx->debit);
                        $tx->running_balance = $shopBal;
                    }

                    $groupedLedgers[] = (object)[
                        'shop' => $shop,
                        'opening_balance' => $shopOpeningAmt,
                        'closing_balance' => $shopBal,
                        'transactions' => $shopTx
                    ];
                }
            }
EOD;

    $replaceSales = <<<'EOD'
            // Sort and Calculate Balance
            $transactions = $transactions->sort(function ($a, $b) {
                $dateA = \Carbon\Carbon::parse($a->date)->format('Y-m-d');
                $dateB = \Carbon\Carbon::parse($b->date)->format('Y-m-d');

                if ($dateA != $dateB) {
                    return $dateA <=> $dateB;
                }

                return ($a->created_at ?? 0) <=> ($b->created_at ?? 0);
            })->values();

            $balance = $openingBalAmount;
            $finalTransactions = collect();

            $shopPreBalances = [];
            if ($viewMode === 'party_wise') {
                foreach ($shops as $shop) {
                    $shopOpeningAmt = 0;
                    $shopOpening = \App\Models\MasterOpeningBalance::where('master_type', 'customer')
                        ->where('master_id', $shop->id)
                        ->where('financial_year', \App\Models\MasterOpeningBalance::getCurrentFinancialYear())
                        ->first();
                    if ($shopOpening) {
                        $balanceType = strtolower(trim($shopOpening->balance_type));
                        if ($balanceType === 'debit') {
                            $shopOpeningAmt -= (float) $shopOpening->amount;
                        } else {
                            $shopOpeningAmt += (float) $shopOpening->amount;
                        }
                    }
                    $shopPreBalances[$shop->id] = $shopOpeningAmt;
                }
            }

            foreach ($transactions as $tx) {
                $dateTx = \Carbon\Carbon::parse($tx->date)->format('Y-m-d');
                $isPre = $startDate && $dateTx < $startDate;
                
                if ($isPre) {
                    $openingBalAmount += ($tx->credit - $tx->debit);
                    $balance = $openingBalAmount;
                    if ($viewMode === 'party_wise' && isset($tx->customer_id)) {
                        if(isset($shopPreBalances[$tx->customer_id])) {
                            $shopPreBalances[$tx->customer_id] += ($tx->credit - $tx->debit);
                        }
                    }
                } else {
                    $balance += ($tx->credit - $tx->debit);
                    $tx->running_balance = $balance;
                    
                    if (!$endDate || $dateTx <= $endDate) {
                        $finalTransactions->push($tx);
                    }
                }
            }
            
            if ($viewMode === 'party_wise') {
                foreach ($shops as $shop) {
                    $shopTx = $finalTransactions->where('customer_id', $shop->id)->values();
                    $shopOpeningAmt = $shopPreBalances[$shop->id];
                    $shopBal = $shopOpeningAmt;
                    
                    foreach ($shopTx as $tx) {
                        $shopBal += ($tx->credit - $tx->debit);
                        $tx->running_balance = $shopBal;
                    }

                    $groupedLedgers[] = (object)[
                        'shop' => $shop,
                        'opening_balance' => $shopOpeningAmt,
                        'closing_balance' => $shopBal,
                        'transactions' => $shopTx
                    ];
                }
            }

            $transactions = $finalTransactions;
            if (isset($party)) {
                $party->balance = $balance;
            }
EOD;

    // Normalizing newlines to avoid mismatch on Windows
    $searchSales = str_replace("\r\n", "\n", $searchSales);
    $replaceSales = str_replace("\r\n", "\n", $replaceSales);
    $c = str_replace("\r\n", "\n", $c);
    
    $c = str_replace($searchSales, $replaceSales, $c);
    
    file_put_contents($f, $c);
}

updateController('app/Http/Controllers/Admin/Ledger/PartyLedgerController.php');
updateController('app/Http/Controllers/Owner/Ledger/PartyLedgerController.php');
echo "Script completed.";
