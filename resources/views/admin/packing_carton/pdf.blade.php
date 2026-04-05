<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Packing Details #{{ $data['cartons_session_data']['id'] }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            color: #007bff;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-section {
            margin-bottom: 25px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }

        .info-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .info-grid td {
            padding: 5px 0;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            color: #555;
            width: 150px;
        }

        .value {
            color: #000;
            font-weight: bold;
        }

        .carton-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
            border: 1px solid #007bff;
            border-radius: 5px;
            overflow: hidden;
        }

        .carton-header {
            background: #007bff;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            font-size: 13px;
        }

        .carton-body {
            padding: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #e9ecef;
            color: #333;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            border: 1px solid #dee2e6;
            font-size: 10px;
            text-transform: uppercase;
        }

        td {
            padding: 8px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .pcs-badge {
            background: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 10px;
        }

        .footer {
            position: fixed;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Packing Carton Details</h2>
    </div>

    <div class="info-section">
        <table class="info-grid">
            <tr>
                <td class="label">Session No:</td>
                <td class="value">{{ $data['cartons_session_data']['carton_packing_session_no'] }}</td>
                <td class="label">Total Cartons:</td>
                <td class="value">{{ $data['cartons_session_data']['total_cartons'] }}</td>
            </tr>
            <tr>
                <td class="label">Order No:</td>
                <td class="value">{{ $data['cartons_session_data']['order_no'] }}</td>
                <td class="label">Total Boxes:</td>
                <td class="value">{{ $data['cartons_session_data']['total_boxes_session'] }}</td>
            </tr>
            <tr>
                <td class="label">Customer:</td>
                <td class="value" colspan="3">{{ $data['cartons_session_data']['customer'] }}</td>
            </tr>
        </table>
    </div>

    @foreach($data['cartonsDetails'] as $carton)
        <div class="carton-section">
            <div class="carton-header">
                CARTON #{{ $carton['id'] }} | {{ $carton['total_boxes'] }} BOXES
            </div>
            <div class="carton-body">
                <table>
                    <thead>
                        <tr>
                            <th>Bar Code</th>
                            <th>Design</th>
                            <th>Size Set</th>
                            <th>Color</th>
                            <th style="text-align: center;">Pcs/Set</th>
                            <th style="text-align: center;">Boxes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($carton['car_data'] as $box)
                            <tr>
                                <td>{{ $box['bar_code'] }}</td>
                                <td style="font-weight: bold;">{{ $box['design_number'] }}</td>
                                <td>{{ $box['set_size'] }} ({{ $box['size_group'] }})</td>
                                <td>{{ $box['color'] }}</td>
                                <td style="text-align: center;">{{ $box['no_of_pcs'] }}</td>
                                <td style="text-align: center;">
                                    <span class="pcs-badge">{{ $box['set_quantity'] }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    <div class="footer">
        Generated on {{ now()->format('d M Y, h:i A') }} | Production Management System
    </div>
</body>

</html>