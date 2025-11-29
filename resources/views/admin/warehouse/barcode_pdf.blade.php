<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: sans-serif;
            text-align: center;
            padding-top: 30px;
        }
        .barcode-box {
            border: 1px solid #444;
            padding: 20px;
            margin: auto;
            width: 90%;
        }
        .barcode-text {
            font-size: 16px;
            margin-top: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="barcode-box">
    <h4>Package Barcode</h4>
    <img src="data:image/png;base64,{{ $barcodeImage }}" alt="Barcode" />
    <p class="barcode-text">{{ $barcodeValue }}</p>

    <hr>
    <p><strong>Box ID:</strong> {{ $box->id }}</p>
    <p><strong>Quantity:</strong> {{ $box->quantity }}</p>
</div>

</body>
</html>
