<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Color Chart - {{ $sample->product->design_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            color: #1e293b;
        }
        .header {
            background: #fff;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .container {
            padding: 1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .product-info {
            background: #fff;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .design-no {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            color: #6366f1;
        }
        .product-name {
            color: #64748b;
            margin-top: 0.25rem;
        }
        .size-badge {
            display: inline-block;
            background: #eef2ff;
            color: #4f46e5;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.875rem;
            margin-top: 0.75rem;
        }
        .color-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        .color-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .color-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            background: #eee;
        }
        .color-info {
            padding: 1rem;
            text-align: center;
        }
        .color-name {
            font-weight: 600;
            margin: 0;
        }
        .no-colors {
            text-align: center;
            padding: 3rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ App\Models\GeneralSettings::first()->logo }}" alt="Logo" style="height: 40px; margin-bottom: 0.5rem;">
        <h2 style="margin: 0; font-size: 1.25rem;">Color Chart</h2>
    </div>

    <div class="container">
        <div class="product-info" style="display: flex; align-items: center; gap: 1.5rem;">
            @if($sample->product->display_image)
                <a href="{{ asset('assets/products/' . $sample->product->display_image) }}" target="_blank">
                    <img src="{{ asset('assets/products/' . $sample->product->display_image) }}" 
                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                </a>
            @endif
            <div>
                <h1 class="product-name" style="font-weight: 700; font-size: 1.4rem; color: #1e293b; margin: 0; line-height: 1.2;">
                    {{ $sample->product->series ? $sample->product->series->name : '' }} {{ $sample->product->name_of_garment }}
                </h1>
                <div style="margin-top: 0.5rem; display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    <div class="size-badge" style="margin-top: 0;">Size: {{ $sample->sizeSet->name }}</div>
                    @if($sample->product->fitting)
                        <div class="size-badge" style="margin-top: 0; background: #f0fdf4; color: #166534;">Fitting: {{ $sample->product->fitting->name }}</div>
                    @endif
                    @if($sample->product->pattern)
                        <div class="size-badge" style="margin-top: 0; background: #fff7ed; color: #9a3412;">Pattern: {{ $sample->product->pattern->name }}</div>
                    @endif
                    <!-- <div class="size-badge" style="margin-top: 0; background: #fdf2f8; color: #9d174d; border-color: #fbcfe8;">
                        WSP: Rs. {{ number_format($sample->final_price, 2) }}
                    </div> -->
                </div>
            </div>
        </div>

        <h3 style="margin-bottom: 1rem; padding-left: 0.5rem; font-size: 1.1rem;">Available Colors</h3>
        
        @if($variant && $variant->items->count() > 0)
            <div class="color-grid">
                @foreach($variant->items as $item)
                    <div class="color-card">
                        @if($item->image)
                            <a href="{{ asset('assets/products/' . $item->image) }}" target="_blank">
                                <img src="{{ asset('assets/products/' . $item->image) }}" class="color-img" alt="{{ $item->color->name }}">
                            </a>
                        @else
                            <div class="color-img" style="display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-image fa-2x text-muted"></i>
                            </div>
                        @endif
                        <div class="color-info">
                            <p class="color-name">{{ $item->color->name }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="product-info">
                <div class="no-colors">
                    <i class="fas fa-palette fa-3x mb-3"></i>
                    <p>No color chart items found for this variant.</p>
                </div>
            </div>
        @endif
    </div>
</body>
</html>
