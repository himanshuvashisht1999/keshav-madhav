<?php
require 'vendor/autoload.php';

use Barryvdh\DomPDF\Facade\Pdf;

// Just testing the DomPDF engine directly
$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Generated Barcodes</title>
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

        .page-wrapper.last-page {
            page-break-after: avoid;
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
            padding: 23mm 2mm 5mm 2mm; /* Top padding pushes content below blank space */
            border: 1px solid black;
        }
    </style>
</head>
<body><div class="page-wrapper">
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
$dompdf->loadHtml($html);
$dompdf->setPaper([0.0, 0.0, 283.46, 255.12], 'landscape');
$dompdf->render();

file_put_contents('test.pdf', $dompdf->output());
echo "PDF Generated\n";
