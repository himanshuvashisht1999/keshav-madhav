<?php
require 'vendor/autoload.php';

try {
    $pdf = new \setasign\Fpdi\Fpdi();
    $pageCount = $pdf->setSourceFile('test.pdf');
    echo "Total Pages: " . $pageCount . "\n";
} catch (\Exception $e) {
    echo "Error counting pages: " . $e->getMessage() . "\n";
}
