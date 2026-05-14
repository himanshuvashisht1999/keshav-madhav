<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\MasterOpeningBalance;

$type = 'vendor';
$id = 17;
$fy = MasterOpeningBalance::getCurrentFinancialYear();

$openingBalance = MasterOpeningBalance::where('master_type', $type)
    ->where('master_id', $id)
    ->where('financial_year', $fy)
    ->first();

if ($openingBalance) {
    echo "Found record!\n";
    echo "Amount: " . $openingBalance->amount . "\n";
    echo "Type: '" . $openingBalance->balance_type . "'\n";
    $isDebit = (strtolower($openingBalance->balance_type) === 'debit');
    echo "Is Debit: " . ($isDebit ? "Yes" : "No") . "\n";
} else {
    echo "Record not found for $type $id $fy\n";
}
