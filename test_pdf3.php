<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Barryvdh\DomPDF\Facade\Pdf;

$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        @page { margin: 0; }
        html, body { margin: 0; padding: 0; width: 100%; }
        .page-wrapper { width: 100%; page-break-after: always; }
        .page-wrapper.last-page { page-break-after: avoid; }
        .main-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0; padding: 0; }
        .label-cell { width: 50%; vertical-align: top; padding: 0; margin: 0; }
        .inner-content { padding: 22mm 2mm 5mm 2mm; box-sizing: border-box; }
    </style>
</head>
<body>
    <div class="page-wrapper last-page">
        <table class="main-table">
            <tr>
                <td class="label-cell"><div class="inner-content">TEST 1</div></td>
                <td class="label-cell"><div class="inner-content">TEST 2</div></td>
            </tr>
        </table>
    </div>
</body>
</html>
HTML;

$pdf = Pdf::loadHtml($html);
$pdf->setPaper([0.0, 0.0, 283.46, 255.12]);
$pdf->render();

$canvas = $pdf->getCanvas();
echo "Total Pages Generated: " . $canvas->get_page_number() . "\n";
file_put_contents('test3.pdf', $pdf->output());
