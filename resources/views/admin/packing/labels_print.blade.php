<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Box Labels PDF</title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }

        body {
            margin: 0;
            padding: 10mm;
            font-family: 'Helvetica', 'Arial', sans-serif;
            background: #fff;
        }

        .labels-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .label-cell {
            width: 50%;
            padding: 5mm;
            vertical-align: top;
        }

        .label-card {
            border: 1px dashed #000;
            height: 80mm;
            padding: 5mm;
            box-sizing: border-box;
            position: relative;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            margin-bottom: 4mm;
        }

        .header-table td {
            font-weight: bold;
            font-size: 14pt;
            padding-bottom: 2mm;
        }

        .header-box {
            text-align: left;
        }

        .header-carton {
            text-align: right;
        }

        .clear {
            clear: both;
        }

        .details {
            font-size: 11pt;
            line-height: 1.6;
            margin-bottom: 5mm;
        }

        .details strong {
            display: inline-block;
            width: 25mm;
        }

        .qty-box {
            font-size: 18pt;
            font-weight: bold;
            margin-top: 5mm;
        }

        .codes-section {
            position: absolute;
            bottom: 5mm;
            left: 5mm;
            right: 5mm;
            border-top: 1px solid #ddd;
            padding-top: 4mm;
        }

        .barcode-container {
            float: left;
            width: 70%;
            text-align: center;
        }

        .qrcode-container {
            float: right;
            width: 25%;
            text-align: right;
        }

        .barcode-img {
            max-width: 100%;
            height: 12mm;
        }

        .qrcode-img {
            width: 15mm;
            height: 15mm;
        }

        .barcode-text {
            font-size: 9pt;
            font-weight: bold;
            display: block;
            margin-top: 1mm;
        }
    </style>
</head>

<body>

    <table class="labels-grid">
        @php $i = 0; @endphp
        @foreach($labels as $label)
            @if($i % 2 == 0)
                <tr>
            @endif

                <td class="label-cell">
                    <div class="label-card">
                        <table class="header-table">
                            <tr>
                                <td class="header-box">BOX #{{ $label->box_no }}</td>
                                <td class="header-carton">CTN #{{ $label->carton_no }}</td>
                            </tr>
                        </table>

                        <div class="details">
                            <div><strong>ITEM:</strong> {{ $label->product_name ?? 'N/A' }}</div>
                            <div><strong>DESIGN:</strong> {{ $label->design_number }}</div>
                            <div><strong>COLOR:</strong> {{ $label->color_name }}</div>
                            <div><strong>SIZE:</strong> {{ $label->size_set_name }}</div>
                        </div>

                        <div class="qty-box">
                            QTY: {{ $label->quantity }} PCS
                        </div>

                        <div class="codes-section">
                            <div class="barcode-container">
                                <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($label->barcode, 'C128', 2, 40) }}"
                                    class="barcode-img">
                                <span class="barcode-text">{{ $label->barcode }}</span>
                            </div>
                            @if(!empty($label->qrcode))
                                <div class="qrcode-container">
                                    <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($label->qrcode, 'QRCODE', 3, 3) }}"
                                        class="qrcode-img">
                                </div>
                            @endif
                            <div class="clear"></div>
                        </div>
                    </div>
                </td>

                @if($i % 2 == 1 || $loop->last)
                    </tr>
                @endif
            @php $i++; @endphp
        @endforeach
    </table>

</body>

</html>