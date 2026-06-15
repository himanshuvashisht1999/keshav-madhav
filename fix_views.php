<?php
function replaceInFiles($dir, $search, $replace) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($files as $file) {
        if ($file->isFile() && pathinfo($file->getFilename(), PATHINFO_EXTENSION) == 'php') {
            $path = $file->getRealPath();
            $content = file_get_contents($path);
            if (strpos($content, $search) !== false) {
                $content = str_replace($search, $replace, $content);
                file_put_contents($path, $content);
                echo "Updated: $path\n";
            }
        }
    }
}

// Admin
replaceInFiles('resources/views/admin/ledger/bank_cash', 'admin.ledger.party', 'admin.ledger.bank-cash-ledger');
replaceInFiles('resources/views/admin/ledger/bank_cash', 'Party Ledger', 'Bank & Cash Ledger');
replaceInFiles('resources/views/admin/ledger/bank_cash', 'Party Ledger Report', 'Bank & Cash Ledger Report');

// Owner
replaceInFiles('resources/views/owner/bank-cash-ledger', 'owner.party-ledger', 'owner.bank-cash-ledger');
replaceInFiles('resources/views/owner/bank-cash-ledger', 'Party Ledger', 'Bank & Cash Ledger');
replaceInFiles('resources/views/owner/bank-cash-ledger', 'Party Ledger Report', 'Bank & Cash Ledger Report');

echo "Done replacing view strings.";
