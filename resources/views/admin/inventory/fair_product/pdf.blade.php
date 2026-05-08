<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            margin: 0.5cm;
        }

        body {
            font-family: 'Helvetica', sans-serif;
            margin: 0;
            padding: 10px;
            color: #1b4332;
            background-color: #fff;
        }

        .company-header {
            text-align: center;
            border-bottom: 2px solid #1b4332;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .logo {
            max-height: 70px;
            margin-bottom: 2px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1b4332;
            margin: 0;
        }

        .company-info {
            font-size: 11px;
            color: #1b4332;
            font-weight: bold;
            margin-top: 2px;
        }

        .catalog-title {
            text-align: center;
            font-size: 18px;
            font-weight: 800;
            margin: 5px 0 15px 0;
            color: #1b4332;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #fdf001;
            padding-bottom: 5px;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }

        /* Main Item Card */
        .sample-item {
            border: 2px solid #1b4332;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            page-break-inside: avoid;
            background-color: #fff;
            height: 420px;
            box-sizing: border-box;
        }

        .content-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }

        .image-cell {
            width: 440px;
            vertical-align: middle;
            text-align: left;
            padding-right: 15px;
        }

        .product-image {
            max-width: 100%;
            max-height: 390px;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 10px;
            border: 3px solid #fdf001;
        }

        .details-cell {
            vertical-align: top;
            padding-top: 5px;
        }

        .product-header {
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .product-name {
            font-size: 28px;
            font-weight: 800;
            margin: 0;
            color: #1b4332;
            letter-spacing: -0.5px;
        }

        .specs-container {
            margin-top: 15px;
        }

        .spec-item {
            margin-bottom: 8px;
            width: 100%;
            display: block;
            overflow: hidden;
        }

        .spec-label {
            font-size: 11px;
            color: #64748b;
            font-weight: bold;
            text-transform: uppercase;
            width: 80px;
            float: left;
        }

        .spec-value {
            font-size: 14px;
            color: #1b4332;
            font-weight: bold;
            margin-left: 80px;
            display: block;
            word-wrap: break-word;
        }

        .rupee {
            font-family: 'DejaVu Sans', sans-serif;
            font-weight: normal;
        }

        .footer-details-table {
            width: 100%;
            margin-top: 15px;
        }

        .qr-box-wrapper {
            text-align: center;
            vertical-align: middle;
            padding-top: 15px;
        }

        .qr-box {
            background: #fdf001;
            padding: 12px;
            border: 2px solid #1b4332;
            border-radius: 12px;
            display: inline-block;
        }

        .qr-scan-text {
            font-size: 11px;
            font-weight: 800;
            color: #1b4332;
            margin-top: 6px;
            text-transform: uppercase;
        }

        .footer {
            position: fixed;
            bottom: 10px;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 8px;
        }
    </style>
</head>

<body>
    <div class="company-header">
        @if($settings && $settings->getRawOriginal('logo'))
            <img src="{{ public_path('assets/general-settings-image/' . $settings->getRawOriginal('logo')) }}" class="logo">
        @endif
        <!-- <p class="company-name">{{ $settings->website_name ?? 'Snapkid' }}</p> -->
        <p class="company-info">
            {{ $settings->address ?? '' }} | Email: {{ $settings->email ?? '' }} | Phone: {{ $settings->phone ?? '' }}
        </p>
    </div>

    <div class="catalog-title">FAIR PRODUCT CATALOG</div>

    @foreach($samples as $sample)
        <div class="sample-item">
            <table class="content-table">
                <tr>
                    <td class="image-cell">
                        @php
                            $imagePath = $sample->product->display_image;
                            $fullImagePath = $imagePath ? public_path('assets/products/' . $imagePath) : null;
                        @endphp
                        @if($fullImagePath)
                            <img src="{{ $fullImagePath }}" class="product-image">
                        @else
                            <div
                                style="width: 100%; height: 380px; background: #f8fafc; border-radius: 12px; display: table; border: 2px dashed #e2e8f0;">
                                <div
                                    style="display: table-cell; vertical-align: middle; text-align: center; color: #94a3b8; font-size: 14px;">
                                    No Image Available</div>
                            </div>
                        @endif
                    </td>
                    <td class="details-cell">
                        <table style="width: 100%; height: 380px;">
                            <tr>
                                <td style="vertical-align: top;">
                                    <div class="product-header">
                                        @php
                                            $seriesName = $sample->product->series ? $sample->product->series->name : '';
                                            $fullName = $seriesName . ' ' . $sample->product->name_of_garment;
                                        @endphp
                                        <h2 class="product-name">{{ $fullName }}</h2>
                                    </div>

                                    <div class="specs-container">
                                        <div class="spec-item">
                                            <span class="spec-label">Size Set</span>
                                            <span class="spec-value">{{ $sample->sizeSet->name }}</span>
                                        </div>
                                        @if($sample->product->fitting)
                                            <div class="spec-item">
                                                <span class="spec-label">Fitting</span>
                                                <span class="spec-value">{{ $sample->product->fitting->name }}</span>
                                            </div>
                                        @endif
                                        @if($sample->product->pattern)
                                            <div class="spec-item">
                                                <span class="spec-label">Pattern</span>
                                                <span class="spec-value">{{ $sample->product->pattern->name }}</span>
                                            </div>
                                        @endif

                                        @if($showWsp)
                                            <div class="spec-item">
                                                <span class="spec-label">WSP</span>
                                                <span class="spec-value"><span class="rupee">&#8377;</span>
                                                    {{ number_format($sample->final_price, 2) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align: bottom;">
                                    <table class="footer-details-table">
                                        <tr>
                                            <td class="qr-box-wrapper">
                                                <div class="qr-box">
                                                    @php
                                                        $url = route('fair-product.color-chart', ['barcode' => $sample->barcode]);
                                                    @endphp
                                                    <img src="data:image/svg+xml;base64, {!! base64_encode(QrCode::format('svg')->size(120)->generate($url)) !!} "
                                                        width="120" style="display: block; margin: 0 auto;">
                                                    <div class="qr-scan-text">Scan for Details</div>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    @endforeach

    <div class="footer">
        Generated on {{ date('j M Y, h:i A') }} | {{ $settings->website_name ?? 'Snapkid' }}
    </div>
</body>

</html>