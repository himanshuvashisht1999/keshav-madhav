<?php
$f = 'app/Http/Controllers/Owner/Ledger/PartyLedgerController.php';
$content = file_get_contents($f);
$content = str_replace("route('admin.", "route('owner.", $content);
file_put_contents($f, $content);
echo "Done replacing routes.";
