<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\MasterProductFitting;
use App\Models\MasterDesignPattern;
use App\Models\ProductionGoods;
use App\Models\DomesticInventory;
use App\Models\MasterSizeMeasurement;
use App\Models\FairProduct;
use App\Models\FairBatch;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;

class FairProductController extends Controller
{
    public function index()
    {
        $batches = FairBatch::withCount('products')
            ->orderBy('id', 'desc')
            ->paginate(20);
            
        return view('admin.inventory.fair_product.index', compact('batches'));
    }

    public function create()
    {
        $brands = Brand::where('status', 1)->get();
        $fittings = MasterProductFitting::where('status', 1)->get();
        $patterns = MasterDesignPattern::where('status', 1)->get();
        $series = \App\Models\MasterSeries::where('status', 1)->get();
        $sizeSets = MasterSizeMeasurement::where('status', 1)->get();
        $designNumbers = ProductionGoods::select('design_number')->distinct()->pluck('design_number');
        
        return view('admin.inventory.fair_product.create', compact('brands', 'fittings', 'patterns', 'series', 'designNumbers', 'sizeSets'));
    }

    public function edit($id)
    {
        $batch = FairBatch::with(['products.product.series', 'products.sizeSet'])->findOrFail($id);
        $brands = Brand::where('status', 1)->get();
        $fittings = MasterProductFitting::where('status', 1)->get();
        $patterns = MasterDesignPattern::where('status', 1)->get();
        $series = \App\Models\MasterSeries::where('status', 1)->get();
        $sizeSets = MasterSizeMeasurement::where('status', 1)->get();
        $designNumbers = ProductionGoods::select('design_number')->distinct()->pluck('design_number');

        // Prepare existing items for JS
        $existingItems = $batch->products->map(function($p) {
            $seriesName = $p->product->series ? $p->product->series->name : '';
            $fullName = $seriesName . ' ' . $p->product->name_of_garment;
            
            // Find MRP
            $variant = \App\Models\ProductionGoodVariant::where('production_goods_id', $p->product_id)
                ->where('master_size_measurement_id', $p->size_set_id)
                ->first();

            return [
                'productId' => $p->product_id,
                'sizeId' => $p->size_set_id,
                'discount' => $p->discount_percent,
                'mrp' => $variant->mrp ?? 0,
                'designNo' => $p->product->design_number,
                'garment' => $fullName,
                'sizeName' => $p->sizeSet->name
            ];
        });

        return view('admin.inventory.fair_product.create', compact('brands', 'fittings', 'patterns', 'series', 'designNumbers', 'sizeSets', 'batch', 'existingItems'));
    }

    public function getProducts(Request $request)
    {
        $query = ProductionGoods::with(['series', 'mainImage', 'variants.items']);

        if ($request->brand_id) $query->where('brand_id', $request->brand_id);
        if ($request->fitting_id) $query->where('master_product_fitting_id', $request->fitting_id);
        if ($request->pattern_id) $query->where('master_pattern_id', $request->pattern_id);
        if ($request->series_id) $query->where('master_series_id', $request->series_id);
        if ($request->design_number) $query->where('design_number', $request->design_number);

        if ($request->size_set_id) {
            $query->whereHas('inventory', function($q) use ($request) {
                $q->where('size_set_id', $request->size_set_id)->where('quantity', '>', 0);
            });
        }

        if ($request->mrp_from || $request->mrp_to) {
            $query->whereHas('variants', function($q) use ($request) {
                if ($request->mrp_from) $q->where('mrp', '>=', $request->mrp_from);
                if ($request->mrp_to) $q->where('mrp', '<=', $request->mrp_to);
                if ($request->size_set_id) $q->where('master_size_measurement_id', $request->size_set_id);
            });
        }

        $products = $query->get();

        $products = $products->filter(function($product) use ($request) {
            // Image selection priority: Variant -> Color -> Main Image
            $displayImage = null;
            foreach ($product->variants as $variant) {
                if ($variant->image) {
                    $displayImage = $variant->image;
                    break;
                }
            }

            if (!$displayImage) {
                foreach ($product->variants as $variant) {
                    foreach ($variant->items as $item) {
                        if ($item->image) {
                            $displayImage = $item->image;
                            break 2;
                        }
                    }
                }
            }

            if (!$displayImage && $product->mainImage) {
                $displayImage = $product->mainImage->image;
            }

            $product->display_image = $displayImage;

            $stockQuery = DomesticInventory::where('product_id', $product->id)->where('quantity', '>', 0);
            if ($request->size_set_id) {
                $stockQuery->where('size_set_id', $request->size_set_id);
            }

            $product->color_stock = $stockQuery->select('color_id', \DB::raw('SUM(total_boxes) as total_boxes'))
                ->with('color')
                ->groupBy('color_id')
                ->get();

            $sizeQuery = DomesticInventory::where('product_id', $product->id)->where('quantity', '>', 0);
            if ($request->size_set_id) {
                $sizeQuery->where('size_set_id', $request->size_set_id);
            }

            $product->available_sizes = $sizeQuery->with('sizeSet')
                ->get()
                ->map(function($inventory) use ($product) {
                    $variant = \App\Models\ProductionGoodVariant::where('production_goods_id', $product->id)
                        ->where('master_size_measurement_id', $inventory->size_set_id)
                        ->first();
                    
                    return [
                        'id' => $inventory->sizeSet->id,
                        'name' => $inventory->sizeSet->name,
                        'mrp' => $variant->mrp ?? 0
                    ];
                })
                ->unique('id')
                ->values();
            
            $totalBoxes = $product->color_stock->sum('total_boxes');
            return $product->available_sizes->count() > 0 && $totalBoxes > 0;
        })->values();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
        ]);

        $batch = FairBatch::create([
            'batch_no' => 'FAIR-' . strtoupper(uniqid())
        ]);

        foreach ($request->items as $item) {
            $productId = $item['product_id'];
            $sizeSetId = $item['size_set_id'];
            $discountPercent = $item['discount_percent'] ?? 0;
            
            $barcode = 'FAIR-' . $productId . '-' . $sizeSetId . '-' . time() . rand(10, 99);
            
            FairProduct::create([
                'fair_batch_id' => $batch->id,
                'product_id' => $productId,
                'size_set_id' => $sizeSetId,
                'barcode' => $barcode,
                'discount_percent' => $discountPercent
            ]);
        }

        return redirect()->route('admin.inventory.fair-product.index')->with('success', 'Fair batch generated successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'items' => 'required|array',
        ]);

        $batch = FairBatch::findOrFail($id);
        
        // Simple approach: delete existing products and recreate
        $batch->products()->delete();

        foreach ($request->items as $item) {
            $productId = $item['product_id'];
            $sizeSetId = $item['size_set_id'];
            $discountPercent = $item['discount_percent'] ?? 0;
            
            $barcode = 'FAIR-' . $productId . '-' . $sizeSetId . '-' . time() . rand(10, 99);
            
            FairProduct::create([
                'fair_batch_id' => $batch->id,
                'product_id' => $productId,
                'size_set_id' => $sizeSetId,
                'barcode' => $barcode,
                'discount_percent' => $discountPercent
            ]);
        }

        return redirect()->route('admin.inventory.fair-product.index')->with('success', 'Fair batch updated successfully');
    }

    public function generatePdfFromBatch(Request $request, $id)
    {
        $batch = FairBatch::findOrFail($id);
        $showWsp = $request->query('show_wsp', 'no') === 'yes';

        $samples = FairProduct::where('fair_batch_id', $batch->id)
            ->with(['product.series', 'product.fitting', 'product.pattern', 'product.mainImage', 'product.variants.items', 'sizeSet'])
            ->get();

        foreach ($samples as $sample) {
            // Find MRP from ProductionGoodVariant
            $variant = \App\Models\ProductionGoodVariant::where('production_goods_id', $sample->product_id)
                ->where('master_size_measurement_id', $sample->size_set_id)
                ->first();
            
            $mrp = $variant->mrp ?? 0;
            $sample->final_price = $mrp - ($mrp * ($sample->discount_percent / 100));

            // Image selection priority: Variant -> Color -> Main Image
            $displayImage = null;
            foreach ($sample->product->variants as $variant) {
                if ($variant->image) {
                    $displayImage = $variant->image;
                    break;
                }
            }

            if (!$displayImage) {
                foreach ($sample->product->variants as $variant) {
                    foreach ($variant->items as $item) {
                        if ($item->image) {
                            $displayImage = $item->image;
                            break 2;
                        }
                    }
                }
            }

            if (!$displayImage && $sample->product->mainImage) {
                $displayImage = $sample->product->mainImage->image;
            }

            $sample->product->display_image = $displayImage;
        }

        $settings = \App\Models\GeneralSettings::first();
        $pdf = Pdf::loadView('admin.inventory.fair_product.pdf', compact('samples', 'settings', 'showWsp'));
        return $pdf->download('fair-catalog-' . $batch->batch_no . ($showWsp ? '-with-wsp' : '') . '.pdf');
    }

    public function destroy($id)
    {
        $batch = FairBatch::findOrFail($id);
        $batch->delete(); // Cascades to products
        return redirect()->back()->with('success', 'Fair batch deleted successfully');
    }

    public function showColorChart($barcode)
    {
        $sample = FairProduct::where('barcode', $barcode)
            ->with(['product.series', 'product.fitting', 'product.pattern', 'product.variants.items', 'product.mainImage', 'sizeSet'])
            ->firstOrFail();
        
        // Image selection priority: Variant -> Color -> Main Image
        $displayImage = null;
        foreach ($sample->product->variants as $v) {
            if ($v->image) {
                $displayImage = $v->image;
                break;
            }
        }

        if (!$displayImage) {
            foreach ($sample->product->variants as $v) {
                foreach ($v->items as $item) {
                    if ($item->image) {
                        $displayImage = $item->image;
                        break 2;
                    }
                }
            }
        }

        if (!$displayImage && $sample->product->mainImage) {
            $displayImage = $sample->product->mainImage->image;
        }

        $sample->product->display_image = $displayImage;

        // Color chart is the list of colors available for this product and size set
        $variant = \App\Models\ProductionGoodVariant::where('production_goods_id', $sample->product_id)
            ->where('master_size_measurement_id', $sample->size_set_id)
            ->with('items.color')
            ->first();

        // Calculate WSP (Net Price)
        $mrp = $variant->mrp ?? 0;
        $sample->final_price = $mrp - ($mrp * ($sample->discount_percent / 100));

        return view('admin.inventory.fair_product.color_chart', compact('sample', 'variant'));
    }
}
