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
            padding: 20px;
            color: #333;
            background-color: #fff;
        }

        .company-header {
            text-align: center;
            border-bottom: 3px solid #1b4332;
            padding-bottom: 10px;
            margin-bottom: 15px;
            padding-top: 5px;
        }

        .logo {
            max-height: 60px;
            margin-bottom: 5px;
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
            font-weight: bold;
            margin-bottom: 15px;
            color: #1b4332;
            border: 2px solid #1b4332;
            padding: 5px;
            border-radius: 8px;
        }

        /* ... existing styles ... */
        .price-section {
            margin-top: 8px;
            border-top: 1px dashed #1b4332;
            padding-top: 5px;
        }

        .price-row {
            margin-bottom: 2px;
        }

        .price-label {
            font-size: 11px;
            color: #64748b;
            width: 70px;
            display: inline-block;
        }

        .price-value {
            font-size: 13px;
            font-weight: bold;
            color: #1b4332;
        }

        .net-price {
            font-size: 16px;
            color: #d97706;
            /* Yellowish orange for focus */
        }

        .sample-item {
            border: 2px solid #1b4332;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 12px;
            page-break-inside: avoid;
            background-color: #fff;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
        }

        .image-cell {
            width: 150px;
            vertical-align: top;
        }

        .product-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #fdf001;
        }

        .details-cell {
            padding: 0 30px;
            vertical-align: top;
        }

        .product-name {
            font-size: 20px;
            font-weight: bold;
            margin: 0 0 10px 0;
            color: #1b4332;
        }

        .design-no {
            font-size: 15px;
            color: #1b4332;
            font-weight: bold;
            margin-bottom: 10px;
            background: #fdf001;
            display: inline-block;
            padding: 2px 10px;
            border-radius: 4px;
        }

        .size-badge {
            display: block;
            margin-top: 10px;
            color: #1b4332;
            font-size: 14px;
            font-weight: bold;
        }

        .qr-cell {
            width: 120px;
            text-align: center;
            vertical-align: middle;
        }

        .qr-box {
            background: #fdf001;
            padding: 10px;
            border: 2px solid #1b4332;
            border-radius: 8px;
            display: inline-block;
        }

        .qr-label {
            font-size: 11px;
            font-weight: bold;
            color: #1b4332;
            margin-top: 8px;
            text-transform: uppercase;
        }

        .footer {
            position: fixed;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 11px;
            color: #1b4332;
            font-weight: bold;
            border-top: 2px solid #fdf001;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="company-header">

        @if($settings && $settings->getRawOriginal('logo'))
            <img src="{{ public_path('assets/general-settings-image/' . $settings->getRawOriginal('logo')) }}" class="logo">
        @endif
        <p class="company-name">{{ $settings->website_name ?? 'Snapkid' }}</p>
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
                                style="width: 150px; height: 150px; background: #f1f5f9; border-radius: 8px; text-align: center; line-height: 150px; color: #94a3b8; font-size: 12px; border: 1px solid #ddd;">
                                No Image</div>
                        @endif
                    </td>
                    <td class="details-cell">
                        @php
                            $seriesName = $sample->product->series ? $sample->product->series->name : '';
                            $fullName = $seriesName . ' ' . $sample->product->name_of_garment;
                        @endphp
                        <h2 class="product-name">{{ $fullName }}</h2>

                        <div style="margin-top: 5px;">
                            <span style="font-size: 11px; font-weight: bold; color: #1b4332;">Size Set:
                                {{ $sample->sizeSet->name }}</span>
                            @if($sample->product->fitting)
                                <span style="font-size: 11px; color: #1b4332; margin-left: 10px;">| Fitting:
                                    {{ $sample->product->fitting->name }}</span>
                            @endif
                            @if($sample->product->pattern)
                                <span style="font-size: 11px; color: #1b4332; margin-left: 10px;">| Pattern:
                                    {{ $sample->product->pattern->name }}</span>
                            @endif
                        </div>

                        @if($showWsp)
                            <div class="price-section">
                                <div class="price-row" style="margin-top: 5px;">
                                    <span class="price-label"
                                        style="font-size: 14px; font-weight: bold; color: #1b4332;">WSP:</span>
                                    <span class="price-value net-price">Rs. {{ number_format($sample->final_price, 2) }}</span>
                                </div>
                            </div>
                        @endif
                    </td>
                    <td class="qr-cell">
                        <div class="qr-box">
                            @php
                                $url = route('fair-product.color-chart', ['barcode' => $sample->barcode]);
                            @endphp
                            <img src="data:image/svg+xml;base64, {!! base64_encode(QrCode::format('svg')->size(100)->generate($url)) !!} "
                                width="100">
                            <div class="qr-label">Scan for Details</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    @endforeach

    <div class="footer">
        Generated on {{ date('d M Y, h:i A') }} | {{ $settings->company_name ?? 'Keshav Madhav' }}
    </div>
</body>

</html>