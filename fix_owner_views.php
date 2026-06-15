<?php
$f1 = 'app/Http/Controllers/Owner/Ledger/PartyLedgerController.php';
$content1 = file_get_contents($f1);
$content1 = str_replace("view('owner.ledger.party.", "view('owner.party-ledger.", $content1);
$content1 = str_replace("loadView('owner.ledger.party.", "loadView('owner.party-ledger.", $content1);
file_put_contents($f1, $content1);

$f2 = 'app/Http/Controllers/Owner/Ledger/BankCashLedgerController.php';
$content2 = file_get_contents($f2);
$content2 = str_replace("view('owner.ledger.bank_cash.", "view('owner.bank-cash-ledger.", $content2);
$content2 = str_replace("loadView('owner.ledger.bank_cash.", "loadView('owner.bank-cash-ledger.", $content2);
file_put_contents($f2, $content2);

echo "Done fixing owner view paths.";
