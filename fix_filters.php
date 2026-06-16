<?php
$f = 'app/Http/Controllers/Admin/Ledger/PartyLedgerController.php';
$c = file_get_contents($f);
$c = preg_replace('/->when\(\$startDate,\s*fn\(\$q\)\s*=>\s*\$q->whereDate\([\'"]date[\'"],\s*[\'"]>=[\'"],\s*\$startDate\)\)/i', '', $c);
$c = preg_replace('/->when\(\$endDate,\s*fn\(\$q\)\s*=>\s*\$q->whereDate\([\'"]date[\'"],\s*[\'"]<=[\'"],\s*\$endDate\)\)/i', '', $c);
$c = preg_replace('/->when\(\$startDate,\s*fn\(\$q\)\s*=>\s*\$q->whereDate\([\'"]payment_date[\'"],\s*[\'"]>=[\'"],\s*\$startDate\)\)/i', '', $c);
$c = preg_replace('/->when\(\$endDate,\s*fn\(\$q\)\s*=>\s*\$q->whereDate\([\'"]payment_date[\'"],\s*[\'"]<=[\'"],\s*\$endDate\)\)/i', '', $c);

// Also the Journal Voucher complex condition
// ->when($startDate, fn($q2) => $q2->whereDate('date', '>=', $startDate))
$c = preg_replace('/->when\(\$startDate,\s*fn\(\$q2\)\s*=>\s*\$q2->whereDate\([\'"]date[\'"],\s*[\'"]>=[\'"],\s*\$startDate\)\)/i', '', $c);
$c = preg_replace('/->when\(\$endDate,\s*fn\(\$q2\)\s*=>\s*\$q2->whereDate\([\'"]date[\'"],\s*[\'"]<=[\'"],\s*\$endDate\)\)/i', '', $c);

file_put_contents($f, $c);
echo "Replaced in Admin.";

$f2 = 'app/Http/Controllers/Owner/Ledger/PartyLedgerController.php';
$c2 = file_get_contents($f2);
$c2 = preg_replace('/->when\(\$startDate,\s*fn\(\$q\)\s*=>\s*\$q->whereDate\([\'"]date[\'"],\s*[\'"]>=[\'"],\s*\$startDate\)\)/i', '', $c2);
$c2 = preg_replace('/->when\(\$endDate,\s*fn\(\$q\)\s*=>\s*\$q->whereDate\([\'"]date[\'"],\s*[\'"]<=[\'"],\s*\$endDate\)\)/i', '', $c2);
$c2 = preg_replace('/->when\(\$startDate,\s*fn\(\$q\)\s*=>\s*\$q->whereDate\([\'"]payment_date[\'"],\s*[\'"]>=[\'"],\s*\$startDate\)\)/i', '', $c2);
$c2 = preg_replace('/->when\(\$endDate,\s*fn\(\$q\)\s*=>\s*\$q->whereDate\([\'"]payment_date[\'"],\s*[\'"]<=[\'"],\s*\$endDate\)\)/i', '', $c2);

$c2 = preg_replace('/->when\(\$startDate,\s*fn\(\$q2\)\s*=>\s*\$q2->whereDate\([\'"]date[\'"],\s*[\'"]>=[\'"],\s*\$startDate\)\)/i', '', $c2);
$c2 = preg_replace('/->when\(\$endDate,\s*fn\(\$q2\)\s*=>\s*\$q2->whereDate\([\'"]date[\'"],\s*[\'"]<=[\'"],\s*\$endDate\)\)/i', '', $c2);

file_put_contents($f2, $c2);
echo "\nReplaced in Owner.";
