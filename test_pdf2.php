<?php
require 'vendor/autoload.php';

use Barryvdh\DomPDF\Facade\Pdf;

$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            margin: 0;
            size: 100mm 90mm landscape;
        }
        html, body {
            margin: 0;
            padding: 0;
            font-family: Helvetica, Arial, sans-serif;
            width: 100%;
            height: 100%;
            background: #eee;
        }
        .page-wrapper {
            width: 100%;
            height: 100%;
            page-break-after: always;
            box-sizing: border-box;
        }
        .main-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0;
            padding: 0;
        }
        .label-cell {
            width: 50%;
            height: 100%;
            vertical-align: top;
            padding: 23mm 2mm 5mm 2mm;
            border: 1px solid black;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <table class="main-table">
            <tr>
                <td class="label-cell">TEST 1</td>
                <td class="label-cell">TEST 2</td>
            </tr>
        </table>
    </div>
</body>
</html>
HTML;

$dompdf = new \Dompdf\Dompdf();
$dompdf->setOptions(['defaultPaperSize' => array(0.0, 0.0, 283.46, 255.12), 'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
$dompdf->loadHtml($html);
$dompdf->setPaper([0.0, 0.0, 283.46, 255.12], 'landscape');
$dompdf->render();

$canvas = $dompdf->getCanvas();
echo "Total Pages Generated: " . $canvas->get_page_number() . "\n";
