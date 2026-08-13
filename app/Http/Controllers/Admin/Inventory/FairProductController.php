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
    public function index(Request $request)
    {
        $query = FairBatch::withCount('products')
            ->with(['products.sizeSet']);

        if ($request->filled('batch_no')) {
            $query->where('batch_no', 'like', '%' . $request->batch_no . '%');
        }

        if ($request->filled('design_number')) {
            $designNo = $request->design_number;
            $query->whereHas('products.product', function ($q) use ($designNo) {
                $q->where('design_number', 'like', '%' . $designNo . '%');
            });
        }

        if ($request->filled('size_set_id')) {
            $sizeSetId = $request->size_set_id;
            $query->whereHas('products', function ($q) use ($sizeSetId) {
                $q->where('size_set_id', $sizeSetId);
            });
        }

        if ($request->filled('sales_agent_ids')) {
            $query->where(function($q) use ($request) {
                foreach($request->sales_agent_ids as $agent_id) {
                    $q->orWhereJsonContains('sales_agent_ids', $agent_id)
                      ->orWhereJsonContains('sales_agent_ids', (string) $agent_id)
                      ->orWhereJsonContains('sales_agent_ids', (int) $agent_id);
                }
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $batches = $query->orderBy('id', 'desc')->paginate(20);
        $salesAgents = \App\Models\SalesAgent::where('status', 1)->get();
        $designNumbers = ProductionGoods::select('design_number')
            ->distinct()
            ->whereNotNull('design_number')
            ->orderBy('design_number')
            ->pluck('design_number');
        $sizeSets = MasterSizeMeasurement::where('status', 1)->orderBy('name')->get();
        
        $productsList = ProductionGoods::select(
            DB::raw('DISTINCT(TRIM(CONCAT(COALESCE((SELECT name FROM master_series WHERE id = production_goods.master_series_id), ""), " ", COALESCE(production_goods.name_of_garment, "")))) as full_name')
        )
        ->having('full_name', '!=', '')
        ->orderBy('full_name')
        ->pluck('full_name')
        ->filter()
        ->values();

        return view('admin.inventory.fair_product.index', compact('batches', 'salesAgents', 'designNumbers', 'sizeSets', 'productsList'));
    }

    public function searchByDesign(Request $request)
    {
        $designNo     = trim($request->get('design_number', ''));
        $sizeSetId    = $request->get('size_set_id');
        $salesAgentId = $request->get('sales_agent_id');
        $productName  = $request->get('product_name');

        if (!$designNo && !$sizeSetId && !$salesAgentId && !$productName) {
            return response()->json([]);
        }

        // Build query
        $query = FairProduct::with([
            'product.series', // Preload series for full name formatting
            'batch',          // FairBatch → sales_agent_ids, batch_no
            'sizeSet',        // MasterSizeMeasurement → name
        ]);

        if ($designNo) {
            $query->whereHas('product', function ($q) use ($designNo) {
                $q->where('design_number', 'like', '%' . $designNo . '%');
            });
        }

        if ($sizeSetId) {
            $query->where('size_set_id', $sizeSetId);
        }

        if ($salesAgentId) {
            $query->whereHas('batch', function ($q) use ($salesAgentId) {
                $q->whereJsonContains('sales_agent_ids', $salesAgentId)
                  ->orWhereJsonContains('sales_agent_ids', (string) $salesAgentId)
                  ->orWhereJsonContains('sales_agent_ids', (int) $salesAgentId);
            });
        }

        if ($productName) {
            $query->whereHas('product', function ($q) use ($productName) {
                $q->where(DB::raw('TRIM(CONCAT(COALESCE((SELECT name FROM master_series WHERE id = production_goods.master_series_id), ""), " ", COALESCE(production_goods.name_of_garment, "")))'), $productName);
            });
        }

        $products = $query->get();

        // Pre-load all agents once
        $allAgents = \App\Models\SalesAgent::pluck('name', 'id');

        $results = $products->map(function ($fp) use ($allAgents) {
            $agentIds   = $fp->batch ? (is_array($fp->batch->sales_agent_ids) ? $fp->batch->sales_agent_ids : []) : [];
            $agentNames = collect($agentIds)
                ->map(fn($id) => $allAgents[$id] ?? null)
                ->filter()
                ->values()
                ->implode(', ');

            $seriesName = $fp->product->series->name ?? '';
            $garmentName = $fp->product->name_of_garment ?? '';
            $fullName = trim($seriesName . ' ' . $garmentName);

            return [
                'design_number' => $fp->product->design_number ?? '-',
                'product_name'  => $fullName ?: '-',
                'batch_no'      => $fp->batch->batch_no ?? '-',
                'size_set'      => $fp->sizeSet->name ?? '-',
                'barcode'       => $fp->barcode,
                'sales_agents'  => $agentNames ?: 'N/A',
            ];
        });

        return response()->json($results->values());
    }

    public function create()
    {
        $brands = Brand::where('status', 1)->get();
        $fittings = MasterProductFitting::where('status', 1)->get();
        $patterns = MasterDesignPattern::where('status', 1)->get();
        $series = \App\Models\MasterSeries::where('status', 1)->get();
        $sizeSets = MasterSizeMeasurement::where('status', 1)->get();
        $designNumbers = ProductionGoods::select('design_number')->distinct()->pluck('design_number');
        $salesAgents = \App\Models\SalesAgent::where('status', 1)->get();
        $productNatures = \App\Models\ProductNature::where('status', 1)->get();
        $fabricTypes = \App\Models\FabricType::where('status', 1)->get();
        
        return view('admin.inventory.fair_product.create', compact('brands', 'fittings', 'patterns', 'series', 'designNumbers', 'sizeSets', 'salesAgents', 'productNatures', 'fabricTypes'));
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
        $salesAgents = \App\Models\SalesAgent::where('status', 1)->get();
        $productNatures = \App\Models\ProductNature::where('status', 1)->get();
        $fabricTypes = \App\Models\FabricType::where('status', 1)->get();

        // Prepare existing items for JS
        $existingItems = $batch->products->map(function($p) {
            $seriesName = $p->product->series ? $p->product->series->name : '';
            $fullName = $seriesName . ' ' . $p->product->name_of_garment;
            
            // Find MRP
            $variant = \App\Models\ProductionGoodVariant::where('production_goods_id', $p->product_id)
                ->where('master_size_measurement_id', $p->size_set_id)
                ->first();

            $colorIds = is_array($p->color_ids) ? $p->color_ids : json_decode($p->color_ids, true);
            $colorIds = $colorIds ?: [];
            $colorNames = [];
            if (!empty($colorIds)) {
                $colorNames = \App\Models\MasterColor::whereIn('id', $colorIds)->pluck('name')->toArray();
            }

            return [
                'productId' => $p->product_id,
                'sizeId' => $p->size_set_id,
                'colorIds' => $colorIds,
                'colorNames' => $colorNames,
                'discount' => $p->discount_percent,
                'barcodeCount' => $p->barcode_count,
                'mrp' => $variant->mrp ?? 0,
                'designNo' => $p->product->design_number,
                'garment' => $fullName,
                'sizeName' => $p->sizeSet->name,
                'brand_id' => $p->product->brand_id
            ];
        });

        return view('admin.inventory.fair_product.create', compact('brands', 'fittings', 'patterns', 'series', 'designNumbers', 'sizeSets', 'batch', 'existingItems', 'salesAgents', 'productNatures', 'fabricTypes'));
    }

    public function show($id)
    {
        $batch = FairBatch::with(['products.product.series', 'products.product.fitting', 'products.product.pattern', 'products.product.variants.items', 'products.product.mainImage', 'products.sizeSet'])->findOrFail($id);

        foreach ($batch->products as $sample) {
            // Find MRP from ProductionGoodVariant
            $variant = \App\Models\ProductionGoodVariant::where('production_goods_id', $sample->product_id)
                ->where('master_size_measurement_id', $sample->size_set_id)
                ->first();
            
            $mrp = $variant->mrp ?? 0;
            $sample->final_price = ceil($mrp - ($mrp * ($sample->discount_percent / 100)));

            // Image selection priority: Specific Variant -> Specific Variant's color -> Any Variant -> Any Variant's color -> Main Image
            $displayImage = null;
            if ($variant && $variant->image) {
                $displayImage = $variant->image;
            }

            if (!$displayImage && $variant) {
                foreach ($variant->items as $item) {
                    if ($item->image) {
                        $displayImage = $item->image;
                        break;
                    }
                }
            }

            if (!$displayImage) {
                foreach ($sample->product->variants as $v) {
                    if ($v->image) {
                        $displayImage = $v->image;
                        break;
                    }
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
                $displayImage = $sample->product->mainImage->getRawOriginal('image');
            }

            $sample->product->display_image = $displayImage;

            $colorIds = is_array($sample->color_ids) ? $sample->color_ids : json_decode($sample->color_ids, true);
            $colorIds = $colorIds ?: [];
            $colorNames = [];
            if (!empty($colorIds)) {
                $colorNames = \App\Models\MasterColor::whereIn('id', $colorIds)->pluck('name')->toArray();
            }
            $sample->color_names = $colorNames;
        }

        return view('admin.inventory.fair_product.show', compact('batch'));
    }

    public function getProducts(Request $request)
    {
        $query = ProductionGoods::with(['series', 'mainImage', 'variants.items']);

        if ($request->brand_id) $query->where('brand_id', $request->brand_id);
        if ($request->fitting_id) $query->where('master_product_fitting_id', $request->fitting_id);
        if ($request->pattern_id) $query->where('master_pattern_id', $request->pattern_id);
        if ($request->series_id) $query->where('master_series_id', $request->series_id);
        if ($request->design_number) $query->where('design_number', $request->design_number);
        if ($request->product_nature_id) $query->where('product_nature_id', $request->product_nature_id);
        if ($request->fabric_type_id) $query->where('fabric_type_id', $request->fabric_type_id);

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

        $productIds = $products->pluck('id')->toArray();
        $allocatedBoxes = collect();
        if (!empty($productIds)) {
            $allocatedBoxes = \DB::table('agent_order_items')
                ->join('agent_orders', 'agent_order_items.agent_order_id', '=', 'agent_orders.id')
                ->where('agent_orders.status', 'pending')
                ->whereIn('product_id', $productIds)
                ->select('product_id', 'color_id', 'size_set_id', \DB::raw('SUM(box_qty) as total_allocated'))
                ->groupBy('product_id', 'color_id', 'size_set_id')
                ->get();
        }

        $products = $products->filter(function($product) use ($request, $allocatedBoxes) {
            // Find specific variant for requested size set if provided
            $specificVariant = null;
            if ($request->size_set_id) {
                $specificVariant = $product->variants->where('master_size_measurement_id', $request->size_set_id)->first();
            }

            // Image selection priority: Specific Variant -> Specific Variant's color -> Any Variant -> Any Variant's color -> Main Image
            $displayImage = null;
            if ($specificVariant && $specificVariant->image) {
                $displayImage = $specificVariant->image;
            }

            if (!$displayImage && $specificVariant) {
                foreach ($specificVariant->items as $item) {
                    if ($item->image) {
                        $displayImage = $item->image;
                        break;
                    }
                }
            }

            if (!$displayImage) {
                foreach ($product->variants as $v) {
                    if ($v->image) {
                        $displayImage = $v->image;
                        break;
                    }
                }
            }

            if (!$displayImage) {
                foreach ($product->variants as $v) {
                    foreach ($v->items as $item) {
                        if ($item->image) {
                            $displayImage = $item->image;
                            break 2;
                        }
                    }
                }
            }

            if (!$displayImage && $product->mainImage) {
                $displayImage = $product->mainImage->getRawOriginal('image');
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

            $sampleRackIds = \App\Models\Storeroom::where('name', 'ADVANCE SAMPLE')->with('racks')->get()->pluck('racks')->flatten()->pluck('id')->toArray();

            $sizeQuery = DomesticInventory::where('product_id', $product->id)->where('quantity', '>', 0);
            if ($request->size_set_id) {
                $sizeQuery->where('size_set_id', $request->size_set_id);
            }

            $product->available_sizes = $sizeQuery->with(['sizeSet', 'color'])
                ->get()
                ->groupBy('size_set_id')
                ->map(function($inventories, $sizeSetId) use ($product, $allocatedBoxes, $sampleRackIds) {
                    $firstInv = $inventories->first();
                    $variant = \App\Models\ProductionGoodVariant::where('production_goods_id', $product->id)
                        ->where('master_size_measurement_id', $sizeSetId)
                        ->first();
                    
                    $colors = $inventories->groupBy('color_id')->map(function($colorInvs) use ($product, $sizeSetId, $allocatedBoxes, $sampleRackIds) {
                        $firstColorInv = $colorInvs->first();
                        if (!$firstColorInv->color) return null;
                        
                        $isSample = $colorInvs->whereIn('rack_id', $sampleRackIds)->isNotEmpty();
                        $totalBoxes = $colorInvs->sum('total_boxes');
                        
                        $allocated = $allocatedBoxes->where('product_id', $product->id)
                                                    ->where('size_set_id', $sizeSetId)
                                                    ->where('color_id', $firstColorInv->color->id)
                                                    ->first();
                        if ($allocated) {
                            $totalBoxes -= $allocated->total_allocated;
                        }
                        
                        // If not in sample warehouse, strictly enforce stock > 0
                        if ($totalBoxes <= 0 && !$isSample) return null;

                        return [
                            'id' => $firstColorInv->color->id,
                            'name' => $firstColorInv->color->name,
                            'total_boxes' => $isSample ? '-' : $totalBoxes,
                        ];
                    })->filter()->values()->toArray();

                    if (empty($colors)) return null;

                    return [
                        'id' => $sizeSetId,
                        'name' => $firstInv->sizeSet->name,
                        'mrp' => $variant->mrp ?? 0,
                        'colors' => $colors
                    ];
                })
                ->filter()
                ->values();
            
            return $product->available_sizes->count() > 0;
        })->values();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
        ]);

        $batch = FairBatch::create([
            'batch_no' => 'FAIR-' . strtoupper(uniqid()),
            'sales_agent_ids' => $request->sales_agent_ids ?? []
        ]);

        foreach ($request->items as $item) {
            $productId = $item['product_id'];
            $sizeSetId = $item['size_set_id'];
            $colorIds = isset($item['color_ids']) ? $item['color_ids'] : null;
            $discountPercent = $item['discount_percent'] ?? 0;
            $barcodeCount = $item['barcode_count'] ?? 1;
            
            $fairProduct = FairProduct::create([
                'fair_batch_id' => $batch->id,
                'product_id' => $productId,
                'size_set_id' => $sizeSetId,
                'color_ids' => $colorIds,
                'barcode' => 'TEMP',
                'discount_percent' => $discountPercent,
                'barcode_count' => $barcodeCount
            ]);

            $fairProduct->update([
                'barcode' => 'F' . strtoupper(base_convert($fairProduct->id, 10, 36))
            ]);
        }

        return redirect()->route('admin.inventory.fair-product.index')->with('success', 'Sample set generated successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'items' => 'required|array',
        ]);

        $batch = FairBatch::findOrFail($id);
        
        $batch->update([
            'sales_agent_ids' => $request->sales_agent_ids ?? []
        ]);

        // Simple approach: delete existing products and recreate
        $existingProducts = $batch->products->keyBy(function($item) {
            return $item->product_id . '-' . $item->size_set_id;
        });

        $submittedKeys = [];

        foreach ($request->items as $item) {
            $productId = $item['product_id'];
            $sizeSetId = $item['size_set_id'];
            $colorIds = isset($item['color_ids']) ? $item['color_ids'] : null;
            
            // Clean up colorIds array if it has empty strings
            if (is_array($colorIds)) {
                $colorIds = array_filter($colorIds, function($value) {
                    return $value !== null && $value !== '';
                });
                $colorIds = empty($colorIds) ? null : array_values($colorIds);
            }

            $discountPercent = $item['discount_percent'] ?? 0;
            $barcodeCount = $item['barcode_count'] ?? 1;
            $key = $productId . '-' . $sizeSetId;
            $submittedKeys[] = $key;
            
            if ($existingProducts->has($key)) {
                // Update existing product to keep the same barcode
                $fairProduct = $existingProducts->get($key);
                $fairProduct->update([
                    'color_ids' => $colorIds,
                    'discount_percent' => $discountPercent,
                    'barcode_count' => $barcodeCount
                ]);
            } else {
                // Create new product if added
                $fairProduct = FairProduct::create([
                    'fair_batch_id' => $batch->id,
                    'product_id' => $productId,
                    'size_set_id' => $sizeSetId,
                    'color_ids' => $colorIds,
                    'barcode' => 'TEMP',
                    'discount_percent' => $discountPercent,
                    'barcode_count' => $barcodeCount
                ]);

                $fairProduct->update([
                    'barcode' => 'F' . strtoupper(base_convert($fairProduct->id, 10, 36))
                ]);
            }
        }
        
        // Delete any products that were removed from the UI
        foreach ($existingProducts as $key => $product) {
            if (!in_array($key, $submittedKeys)) {
                $product->delete();
            }
        }

        return redirect()->route('admin.inventory.fair-product.index')->with('success', 'Sample set updated successfully');
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
            $sample->final_price = ceil($mrp - ($mrp * ($sample->discount_percent / 100)));

            // Image selection priority: Specific Variant -> Specific Variant's color -> Any Variant -> Any Variant's color -> Main Image
            $displayImage = null;
            if ($variant && $variant->image) {
                $displayImage = $variant->image;
            }

            if (!$displayImage && $variant) {
                foreach ($variant->items as $item) {
                    if ($item->image) {
                        $displayImage = $item->image;
                        break;
                    }
                }
            }

            if (!$displayImage) {
                foreach ($sample->product->variants as $v) {
                    if ($v->image) {
                        $displayImage = $v->image;
                        break;
                    }
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
                $displayImage = $sample->product->mainImage->getRawOriginal('image');
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
        return redirect()->back()->with('success', 'Sample set deleted successfully');
    }

    public function showColorChart($barcode)
    {
        $sample = FairProduct::where('barcode', $barcode)
            ->with(['product.series', 'product.fitting', 'product.pattern', 'product.variants.items', 'product.mainImage', 'sizeSet'])
            ->firstOrFail();
        
        // Color chart is the list of colors available for this product and size set
        $variant = \App\Models\ProductionGoodVariant::where('production_goods_id', $sample->product_id)
            ->where('master_size_measurement_id', $sample->size_set_id)
            ->with(['items' => function($q) use ($sample) {
                $q->with('color');
                if (!empty($sample->color_ids)) {
                    $q->whereIn('master_color_id', $sample->color_ids);
                }
            }])
            ->first();

        // Image selection priority: Specific Variant -> Specific Variant's color -> Any Variant -> Any Variant's color -> Main Image
        $displayImage = null;
        if ($variant && $variant->image) {
            $displayImage = $variant->image;
        }

        if (!$displayImage && $variant) {
            foreach ($variant->items as $item) {
                if ($item->image) {
                    $displayImage = $item->image;
                    break;
                }
            }
        }

        if (!$displayImage) {
            foreach ($sample->product->variants as $v) {
                if ($v->image) {
                    $displayImage = $v->image;
                    break;
                }
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
            $displayImage = $sample->product->mainImage->getRawOriginal('image');
        }

        $sample->product->display_image = $displayImage;

        // Calculate WSP (Net Price)
        $mrp = $variant->mrp ?? 0;
        $sample->final_price = ceil($mrp - ($mrp * ($sample->discount_percent / 100)));

        return view('admin.inventory.fair_product.color_chart', compact('sample', 'variant'));
    }

    public function downloadPrn(Request $request)
    {
        $batchId = $request->get('batch_id');
        if (!$batchId) return back()->with('error', 'No batch selected.');

        $samples = FairProduct::with(['product.series', 'product.fitting', 'product.pattern', 'sizeSet'])
            ->where('fair_batch_id', $batchId)
            ->get();

        if ($samples->isEmpty()) return back()->with('error', 'No products found in this batch.');

        $tspl = generateFairBulkTspl($samples);
        
        $filename = "Fair_Batch_" . $batchId . "_" . date('Ymd_His') . ".prn";
        
        return response($tspl)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    public function downloadPrnByBarcodes(Request $request)
    {
        $request->validate([
            'barcodes' => 'required|string',
        ]);

        $barcodes = array_map('trim', explode("\n", $request->barcodes));
        $barcodes = array_filter($barcodes);

        if (empty($barcodes)) {
            return back()->with('error', 'No valid barcodes provided.');
        }

        $samples = FairProduct::with(['product.series', 'product.fitting', 'product.pattern', 'sizeSet'])
            ->whereIn('barcode', $barcodes)
            ->get()
            ->keyBy('barcode');

        $finalSamples = collect();
        foreach ($barcodes as $code) {
            if (isset($samples[$code])) {
                // Do not override barcode_count, respect the database value
                $finalSamples->push(clone $samples[$code]);
            }
        }

        if ($finalSamples->isEmpty()) return back()->with('error', 'No products found for the provided barcodes.');

        $tspl = generateFairBulkTspl($finalSamples);
        
        $filename = "Fair_Custom_" . date('Ymd_His') . ".prn";
        
        return response($tspl)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    public function toggleStatus($id)
    {
        $batch = FairBatch::findOrFail($id);
        $batch->status = !$batch->status;
        $batch->save();
        return redirect()->back()->with('success', 'Sample set status updated successfully');
    }
}
