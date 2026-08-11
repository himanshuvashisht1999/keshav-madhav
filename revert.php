<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FabricReceiptDetail;

$log = <<<LOG
Fixing Roll ID 106 (Roll No: 318651) | DB: 30.00 -> Corrected: 0
Fixing Roll ID 107 (Roll No: 303097) | DB: 98.00 -> Corrected: 0
Fixing Roll ID 108 (Roll No: 303100) | DB: 150.00 -> Corrected: 0
Fixing Roll ID 109 (Roll No: 303101) | DB: 150.00 -> Corrected: 0
Fixing Roll ID 110 (Roll No: 303103) | DB: 126.00 -> Corrected: 0
Fixing Roll ID 111 (Roll No: 403127) | DB: 33.00 -> Corrected: 0
Fixing Roll ID 112 (Roll No: 403635) | DB: 27.00 -> Corrected: 0
Fixing Roll ID 113 (Roll No: 603519) | DB: 88.00 -> Corrected: 0
Fixing Roll ID 866 (Roll No: 6) | DB: 8.00 -> Corrected: 0
Fixing Roll ID 1535 (Roll No: 5689) | DB: 0.00 -> Corrected: 662
Fixing Roll ID 1536 (Roll No: 5881) | DB: 0.00 -> Corrected: 669
Fixing Roll ID 1931 (Roll No: 5936) | DB: 0.00 -> Corrected: 674
Fixing Roll ID 1932 (Roll No: 1004.) | DB: 0.00 -> Corrected: 649
Fixing Roll ID 1933 (Roll No: 206) | DB: 0.00 -> Corrected: 733
Fixing Roll ID 1934 (Roll No: 223) | DB: 0.00 -> Corrected: 740
Fixing Roll ID 2017 (Roll No: 12842) | DB: 0.00 -> Corrected: 813
Fixing Roll ID 2145 (Roll No: 311) | DB: 0.00 -> Corrected: 753
Fixing Roll ID 2146 (Roll No: 317) | DB: 0.00 -> Corrected: 748
Fixing Roll ID 2147 (Roll No: 258) | DB: 0.00 -> Corrected: 749
Fixing Roll ID 2148 (Roll No: 266) | DB: 0.00 -> Corrected: 758
Fixing Roll ID 2417 (Roll No: 8000115) | DB: -100.00 -> Corrected: 0
Fixing Roll ID 2595 (Roll No: 2788) | DB: 3.00 -> Corrected: 0
Fixing Roll ID 2707 (Roll No: 379) | DB: 0.00 -> Corrected: 751
Fixing Roll ID 2708 (Roll No: 383) | DB: 0.00 -> Corrected: 752
Fixing Roll ID 3158 (Roll No: 1542) | DB: 0.00 -> Corrected: 665
Fixing Roll ID 3159 (Roll No: 297) | DB: 0.00 -> Corrected: 763
Fixing Roll ID 3160 (Roll No: 307) | DB: 0.00 -> Corrected: 764
Fixing Roll ID 3161 (Roll No: 327) | DB: 0.00 -> Corrected: 740
Fixing Roll ID 3162 (Roll No: 359) | DB: 0.00 -> Corrected: 749
Fixing Roll ID 3163 (Roll No: 1491) | DB: 0.00 -> Corrected: 929
Fixing Roll ID 3164 (Roll No: 435) | DB: 0.00 -> Corrected: 768
Fixing Roll ID 3165 (Roll No: 1777) | DB: 0.00 -> Corrected: 913
Fixing Roll ID 3339 (Roll No: 1864) | DB: 0.00 -> Corrected: 759
Fixing Roll ID 3340 (Roll No: 1865) | DB: 0.00 -> Corrected: 776
Fixing Roll ID 3341 (Roll No: 1832) | DB: 0.00 -> Corrected: 758
Fixing Roll ID 3342 (Roll No: 1844) | DB: 0.00 -> Corrected: 759
Fixing Roll ID 3343 (Roll No: 1845) | DB: 0.00 -> Corrected: 765
Fixing Roll ID 3344 (Roll No: 443) | DB: 0.00 -> Corrected: 751
Fixing Roll ID 3541 (Roll No: 98) | DB: 0.00 -> Corrected: 763
Fixing Roll ID 3559 (Roll No: 141) | DB: 0.00 -> Corrected: 429.2
Fixing Roll ID 3560 (Roll No: 142) | DB: 0.00 -> Corrected: 420.5
Fixing Roll ID 3561 (Roll No: 143) | DB: 0.00 -> Corrected: 383.9
Fixing Roll ID 3562 (Roll No: 144) | DB: 0.00 -> Corrected: 419.4
Fixing Roll ID 3563 (Roll No: 145) | DB: 0.00 -> Corrected: 334.1
Fixing Roll ID 3564 (Roll No: 153) | DB: 0.00 -> Corrected: 308.9
Fixing Roll ID 3859 (Roll No: CONSUME) | DB: 0.00 -> Corrected: 299
Fixing Roll ID 3941 (Roll No: CONSUME-3) | DB: 0.00 -> Corrected: 142
Fixing Roll ID 4023 (Roll No: CONSUME-4) | DB: 0.00 -> Corrected: 249.5
Fixing Roll ID 4026 (Roll No: CONSUME-2) | DB: 0.00 -> Corrected: 94.5
Fixing Roll ID 4071 (Roll No: CONSUME-5) | DB: 0.00 -> Corrected: 85
LOG;

$lines = explode("\n", trim($log));
foreach ($lines as $line) {
    if (preg_match('/Fixing Roll ID (\d+).*DB: ([\-\d\.]+) -> Corrected: ([\-\d\.]+)/', $line, $matches)) {
        $id = $matches[1];
        $original_db = $matches[2];
        $corrected = $matches[3];
        
        // If it was modified from 0 (or a manual adjustment) to a higher number, we should revert it.
        // We ONLY wanted to fix ones that failed to deduct (so they were high and became low).
        if ((float)$original_db < (float)$corrected) {
            $roll = FabricReceiptDetail::find($id);
            if ($roll) {
                echo "Reverting Roll ID {$id}: {$corrected} back to {$original_db}\n";
                $roll->update(['remaining_quantity' => $original_db]);
            }
        }
    }
}
