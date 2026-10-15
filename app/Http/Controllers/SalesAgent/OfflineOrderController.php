<?php

namespace App\Http\Controllers\SalesAgent;

use App\Http\Controllers\Controller;
use App\Models\AgentOrder;
use App\Models\AgentOrderItem;
use App\Models\DomesticInventory;
use App\Models\MasterCustomer;
use App\Models\ProductionGoods;
use App\Models\SalesMan;
use App\Models\Storeroom;
use App\Services\SalesAgent\AgentOrderException;
use App\Services\SalesAgent\AgentOrderRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OfflineOrderController extends Controller
{
    public function index()
    {
        $agent = Auth::guard('sales_agent')->user();
        $shops_count = MasterCustomer::where('status', 1)
            ->when(!$agent->is_master_agent, fn($q) => $q->where('sales_agent_id', $agent->id))
            ->count();
        return view('sales_agent.offline.index', compact('agent', 'shops_count'));
    }

    public function create(Request $request)
    {
        $agent = Auth::guard('sales_agent')->user();
        $pre_selected_shop_id = $request->get('shop_id', null);
        return view('sales_agent.offline.create', compact('agent', 'pre_selected_shop_id'));
    }

    /** Boxes all open orders still have to take, per item "product_colour_size" (picked boxes have already left the stock). */
    private static function openOrderBoxes(): array
    {
        return DB::table('agent_order_items')
            ->join('agent_orders', 'agent_order_items.agent_order_id', '=', 'agent_orders.id')
            ->where('agent_orders.status', '!=', 'dispatched')
            ->whereNull('agent_order_items.dispatched_at')
            ->groupBy('agent_order_items.product_id', 'agent_order_items.color_id', 'agent_order_items.size_set_id')
            ->get([
                'agent_order_items.product_id', 'agent_order_items.color_id', 'agent_order_items.size_set_id',
                DB::raw('SUM(GREATEST(agent_order_items.box_qty - agent_order_items.scanned_box_qty, 0)) as boxes'),
            ])
            ->mapWithKeys(fn($r) => [$r->product_id . '_' . $r->color_id . '_' . $r->size_set_id => (int) $r->boxes])
            ->all();
    }

    public function catalogData(Request $request)
    {
        try {
            $agent = Auth::guard('sales_agent')->user();
            $agent_id = $agent->id;

            // 1. Fetch Shops
            $shopsQuery = MasterCustomer::where('status', 1);
            if (!$agent->is_master_agent) {
                $shopsQuery->where('sales_agent_id', $agent_id);
            }
            // the shop's balance is not sent to the phone (agents do not see party balances)
            $shops = $shopsQuery->orderBy('name')->get([
                'id', 'name', 'phone', 'address', 'gst_number'
            ]);

            // Each shop's own per-brand discounts (the app prices orders with the selected shop's discount)
            $shopDiscountRows = DB::table('customer_brand_discounts')
                ->whereIn('customer_id', $shops->pluck('id'))
                ->get(['customer_id', 'brand_id', 'discount_percentage'])
                ->groupBy('customer_id');
            $shops = $shops->map(function ($shop) use ($shopDiscountRows) {
                $row = $shop->toArray();
                $row['brand_discounts'] = ($shopDiscountRows[$shop->id] ?? collect())
                    ->pluck('discount_percentage', 'brand_id')->map(function ($v) { return (float) $v; })->all();
                return $row;
            });

            // 2. Fetch Active Products / Variations
            // available = boxes in storerooms that take orders, less what open orders still have to take
            $openBoxes = self::openOrderBoxes();
            $inventories = DB::table('domestic_inventories')
                ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
                ->leftJoin('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
                ->leftJoin('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
                ->leftJoin('master_series', 'production_goods.master_series_id', '=', 'master_series.id')
                ->leftJoin('brands', 'production_goods.brand_id', '=', 'brands.id')
                ->leftJoin('master_product_fittings', 'production_goods.master_product_fitting_id', '=', 'master_product_fittings.id')
                ->leftJoin('master_design_patterns', 'production_goods.master_pattern_id', '=', 'master_design_patterns.id')
                ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
                ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
                ->where('domestic_inventories.status', 1)
                ->select(
                    'domestic_inventories.product_id',
                    'domestic_inventories.color_id',
                    'domestic_inventories.size_set_id',
                    'production_goods.design_number',
                    'production_goods.name_of_garment',
                    'production_goods.brand_id',
                    'master_series.name as series_name',
                    'brands.name as brand_name',
                    'master_colors.name as color_name',
                    'master_size_measurements.name as size_set_name',
                    'master_size_measurements.size_group',
                    'master_size_measurements.no_of_pcs',
                    'master_product_fittings.name as fitting_name',
                    'master_design_patterns.name as pattern_name',
                    DB::raw("SUM(CASE WHEN storerooms.id IS NULL OR storerooms.order_taken = 'Yes' THEN domestic_inventories.total_boxes ELSE 0 END) as available_boxes"),
                    DB::raw('MAX(domestic_inventories.quantity) as pcs_per_box'),
                    DB::raw('MAX(CASE WHEN ' . Storeroom::overStockSql() . ' THEN 1 ELSE 0 END) as is_advance_sample')
                )
                ->groupBy(
                    'domestic_inventories.product_id',
                    'domestic_inventories.color_id',
                    'domestic_inventories.size_set_id',
                    'production_goods.design_number',
                    'production_goods.name_of_garment',
                    'production_goods.brand_id',
                    'master_series.name',
                    'brands.name',
                    'master_colors.name',
                    'master_size_measurements.name',
                    'master_size_measurements.size_group',
                    'master_size_measurements.no_of_pcs',
                    'master_product_fittings.name',
                    'master_design_patterns.name'
                )
                // items with boxes in stock are listed; empty rows still count for the "no stock limit" setting
                ->havingRaw('SUM(domestic_inventories.total_boxes) > 0')
                ->get();

            // Preload variant info and images in bulk for blazing fast performance
            $variantsByProductSize = DB::table('production_goods_variants')
                ->select('id', 'production_goods_id', 'master_size_measurement_id', 'mrp', 'image')
                ->get()
                ->groupBy(function($item) {
                    return $item->production_goods_id . '_' . $item->master_size_measurement_id;
                });

            $anyVariantImgByProduct = DB::table('production_goods_variants')
                ->whereNotNull('image')
                ->where('image', '!=', '')
                ->pluck('image', 'production_goods_id');

            $colorImgsByVariantColor = DB::table('production_goods_variant_colors')
                ->whereNotNull('image')
                ->where('image', '!=', '')
                ->select('variant_id', 'master_color_id', 'image')
                ->get()
                ->groupBy(function($item) {
                    return $item->variant_id . '_' . $item->master_color_id;
                });

            $anyColorImgByProductColor = DB::table('production_goods_variant_colors')
                ->join('production_goods_variants', 'production_goods_variant_colors.variant_id', '=', 'production_goods_variants.id')
                ->whereNotNull('production_goods_variant_colors.image')
                ->where('production_goods_variant_colors.image', '!=', '')
                ->select('production_goods_variants.production_goods_id', 'production_goods_variant_colors.master_color_id', 'production_goods_variant_colors.image')
                ->get()
                ->groupBy(function($item) {
                    return $item->production_goods_id . '_' . $item->master_color_id;
                });

            $products = [];
            $imageUrls = [];
            $imageFiles = [];

            foreach ($inventories as $inv) {
                $vKey = $inv->product_id . '_' . $inv->color_id . '_' . $inv->size_set_id;

                // Variant MRP & Price
                $varGroup = $variantsByProductSize[$inv->product_id . '_' . $inv->size_set_id] ?? null;
                $variant = $varGroup ? $varGroup->first() : null;

                $mrp = $variant->mrp ?? 0;
                // Undiscounted base price; the app applies the selected shop's brand discount
                $selling_price = ceil($mrp > 0 ? $mrp : 0);

                // Variant Color Image with intelligent fallbacks
                $image = null;
                if ($variant) {
                    $vcGroup = $colorImgsByVariantColor[$variant->id . '_' . $inv->color_id] ?? null;
                    if ($vcGroup && $vcGroup->first()) {
                        $image = $vcGroup->first()->image;
                    }
                    if (!$image) {
                        $image = $variant->image;
                    }
                }

                if (!$image) {
                    // Fallback 1: Any variant color for this product and color
                    $pcGroup = $anyColorImgByProductColor[$inv->product_id . '_' . $inv->color_id] ?? null;
                    if ($pcGroup && $pcGroup->first()) {
                        $image = $pcGroup->first()->image;
                    }
                }

                if (!$image) {
                    // Fallback 2: Any variant image for this product
                    $image = $anyVariantImgByProduct[$inv->product_id] ?? null;
                }

                $imageUrl = $image ? asset('assets/products/' . $image) : null;
                if ($imageUrl && !in_array($imageUrl, $imageUrls)) {
                    $imageUrls[] = $imageUrl;
                    if (!in_array($image, $imageFiles)) {
                        $imageFiles[] = $image;
                    }
                }

                $barcode = 'D' . $inv->product_id . 'S' . $inv->size_set_id . 'C' . $inv->color_id;

                $pcs = (float) $inv->pcs_per_box;
                if ($pcs <= 0) {
                    $pcs = (float) ($inv->no_of_pcs ?? 1);
                }

                $products[] = [
                    'key' => $vKey,
                    'product_id' => $inv->product_id,
                    'brand_id' => $inv->brand_id,
                    'color_id' => $inv->color_id,
                    'size_set_id' => $inv->size_set_id,
                    'design_number' => $inv->design_number ?? '-',
                    'name_of_garment' => $inv->name_of_garment ?? '',
                    'series_name' => $inv->series_name ?? '',
                    'brand_name' => $inv->brand_name ?? '',
                    'color_name' => $inv->color_name ?? '-',
                    'size_set_name' => $inv->size_set_name ?? '-',
                    'size_group' => $inv->size_group ?? '',
                    'fitting_name' => $inv->fitting_name ?? '',
                    'pattern_name' => $inv->pattern_name ?? '',
                    'available_boxes' => max(0, (int) $inv->available_boxes - ($openBoxes[$vKey] ?? 0)),
                    'is_advance_sample' => (bool) $inv->is_advance_sample,
                    'pcs_per_box' => $pcs,
                    'mrp' => $mrp,
                    'unit_price' => $selling_price,
                    'barcode' => $barcode,
                    'image' => $image,
                    'image_url' => $imageUrl
                ];
            }

            // 3. Build comprehensive barcode mapping for offline barcode scanner
            $barcodeMap = [];

            // 3a. Variant color barcodes (e.g. D2S3C4, D3S3C4P1F1)
            if (Schema::hasTable('production_goods_variant_colors') && Schema::hasTable('production_goods_variants')) {
                $variantColorRows = DB::table('production_goods_variant_colors')
                    ->join('production_goods_variants', 'production_goods_variant_colors.variant_id', '=', 'production_goods_variants.id')
                    ->whereNotNull('production_goods_variant_colors.barcode')
                    ->where('production_goods_variant_colors.barcode', '!=', '')
                    ->select(
                        'production_goods_variant_colors.barcode',
                        'production_goods_variants.production_goods_id as product_id',
                        'production_goods_variants.master_size_measurement_id as size_set_id',
                        'production_goods_variant_colors.master_color_id as color_id'
                    )
                    ->get();

                foreach ($variantColorRows as $vc) {
                    $bc = trim($vc->barcode);
                    if ($bc !== '') {
                        $barcodeMap[$bc] = [
                            'product_id' => (int) $vc->product_id,
                            'size_set_id' => (int) $vc->size_set_id,
                            'color_id' => (int) $vc->color_id,
                        ];
                    }
                }
            }

            // Shared lookups for the scan payloads below (loaded once instead of once per colour)
            $ctx = [
                'open' => $openBoxes,
                'sizes' => DB::table('master_size_measurements')->get(['id', 'name', 'no_of_pcs'])->keyBy('id'),
                'variant_color_img' => $colorImgsByVariantColor,
                'product_color_img' => $anyColorImgByProductColor,
            ];

            // 3b. Fair / sample-set products assigned to this agent (admin: inventory/fair-product).
            // Full online-style scan payloads are built here so scanning works fully offline.
            $scanPayloads = [];
            $scanImageFiles = [];

            if (Schema::hasTable('fair_products') && Schema::hasTable('fair_batches')) {
                $batches = DB::table('fair_batches')->where('status', 1)->get();
                foreach ($batches as $batch) {
                    if (!$agent->is_master_agent && !AgentOrderRules::batchIsForAgent($batch->sales_agent_ids, $agent_id)) {
                        continue;
                    }

                    $byProduct = DB::table('fair_products')->where('fair_batch_id', $batch->id)->get()->groupBy('product_id');
                    foreach ($byProduct as $pid => $group) {
                        $discounts = [];
                        $colorFilter = [];
                        foreach ($group as $fp) {
                            $discounts[(int) $fp->size_set_id] = (float) $fp->discount_percent;
                            $cids = is_array($fp->color_ids) ? $fp->color_ids : (json_decode($fp->color_ids, true) ?: []);
                            $colorFilter[(int) $fp->size_set_id] = array_map('intval', $cids);
                        }

                        $payload = $this->buildScanPayload(
                            (int) $pid,
                            array_keys($discounts),
                            function ($ssId) use ($discounts) { return $discounts[$ssId] ?? 0; },
                            $colorFilter,
                            'F' . $batch->id . '_',
                            $scanImageFiles,
                            $ctx
                        );
                        if (!$payload) continue;

                        $payloadKey = 'F' . $batch->id . '_' . $pid;
                        $scanPayloads[$payloadKey] = $payload;

                        foreach ($group as $fp) {
                            foreach ([$fp->barcode ?? null, $fp->qrcode ?? null] as $code) {
                                $seg = self::lastSegment($code);
                                if ($seg !== '') {
                                    $barcodeMap[$seg] = [
                                        'product_id' => (int) $fp->product_id,
                                        'size_set_id' => (int) $fp->size_set_id,
                                        'payload_key' => $payloadKey,
                                    ];
                                }
                            }
                        }
                    }
                }
            }

            // 3c. Sample Products barcodes (normal agent pricing)
            if (Schema::hasTable('sample_products')) {
                $sampleRows = DB::table('sample_products')
                    ->whereNotNull('barcode')->where('barcode', '!=', '')
                    ->select('barcode', 'product_id', 'size_set_id')
                    ->get();
                $stockSizes = DB::table('domestic_inventories')->where('status', 1)
                    ->whereIn('product_id', $sampleRows->pluck('product_id')->unique())
                    ->select('product_id', 'size_set_id')->distinct()->get()->groupBy('product_id');

                foreach ($sampleRows->groupBy('product_id') as $pid => $group) {
                    $sizeIds = $group->pluck('size_set_id')->map(function ($v) { return (int) $v; })->all();
                    $sizeIds = array_values(array_unique(array_merge(
                        $sizeIds,
                        ($stockSizes[$pid] ?? collect())->pluck('size_set_id')->map(function ($v) { return (int) $v; })->all()
                    )));

                    $payload = $this->buildScanPayload((int) $pid, $sizeIds, function () { return 0; }, [], '', $scanImageFiles, $ctx, false);
                    if (!$payload) continue;

                    $payloadKey = 'D' . $pid;
                    $scanPayloads[$payloadKey] = $payload;
                    foreach ($group as $sp) {
                        $seg = self::lastSegment($sp->barcode);
                        if ($seg !== '') {
                            $barcodeMap[$seg] = [
                                'product_id' => (int) $sp->product_id,
                                'size_set_id' => (int) $sp->size_set_id,
                                'payload_key' => $payloadKey,
                            ];
                        }
                    }
                }
            }

            // 3d. Box numbers printed on stock box labels (BX-...)
            $boxRows = DB::table('domestic_inventories')->where('box_no', 'like', 'BX-%')
                ->orderBy('total_boxes')->get(['box_no', 'product_id', 'size_set_id', 'color_id']);
            foreach ($boxRows as $b) {
                $barcodeMap[trim($b->box_no)] = [
                    'product_id' => (int) $b->product_id,
                    'size_set_id' => (int) $b->size_set_id,
                    'color_id' => (int) $b->color_id,
                ];
            }

            // Scan-context images first, so they are always cached
            foreach ($scanImageFiles as $f) {
                $u = asset('assets/products/' . $f);
                if (!in_array($u, $imageUrls, true)) array_unshift($imageUrls, $u);
                if (!in_array($f, $imageFiles, true)) array_unshift($imageFiles, $f);
            }


            // 3e. Design number mappings (so scanning or typing design numbers like 26069, #26069, D26069 resolves instantly offline)
            foreach ($products as $p) {
                if (!empty($p['design_number']) && $p['design_number'] !== '-') {
                    $dn = trim($p['design_number']);
                    $cleanDn = preg_replace('/^[#dD]/', '', $dn);
                    $val = [
                        'product_id' => (int) $p['product_id'],
                        'size_set_id' => (int) $p['size_set_id'],
                    ];
                    // never overwrite real barcodes (fair/sample) with design-number shortcuts
                    foreach (array_filter([$dn, '#' . $dn, 'D' . $dn, ($cleanDn && $cleanDn !== $dn) ? $cleanDn : null]) as $k) {
                        if (!isset($barcodeMap[$k])) $barcodeMap[$k] = $val;
                    }
                }
            }

            // 4. Settings & Salesmen
            $settings = DB::table('settings')->first();
            $sales_men = SalesMan::where('status', 1)->get(['id', 'name', 'phone']);

            return response()->json([
                'success' => true,
                'shops' => $shops,
                'products' => $products,
                'barcodes' => $barcodeMap,
                'scan_payloads' => $scanPayloads,
                'image_urls' => array_slice($imageUrls, 0, 4000),
                'image_files' => array_slice($imageFiles, 0, 4000),
                'settings' => [
                    'gst_order' => $settings->gst_order ?? 5.00,
                    'agent_app_show_stock' => $settings->agent_app_show_stock ?? 1,
                    'agent_app_allow_over_stock' => $settings->agent_app_allow_over_stock ?? 0,
                    'agent_app_allow_over_stock_sample' => $settings->agent_app_allow_over_stock_sample ?? 0,
                ],
                'sales_men' => $sales_men,
                'see_price' => (int) ($agent->see_price ?? 0) === 1,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load offline catalog: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Build the same payload the online "get-variation-by-barcode" endpoint returns,
     * so the offline scanner shows identical size-set / colour matrices.
     *
     * @param array    $colorFilter [sizeSetId => [colorIds]] (empty = no filter)
     * @param callable $discountFor fn(int $sizeSetId): float
     * @param string   $keyPrefix   keeps cart keys unique per pricing context
     * @param array    $ctx         lookups shared by all payloads (open order boxes, size sets, colour images)
     */
    /** Per catalogue download: each product (with its stock colours) is loaded once, however many sample sets show it. */
    private array $payloadProducts = [];

    private function buildScanPayload($productId, array $sizeSetIds, callable $discountFor, array $colorFilter, $keyPrefix, array &$imageFiles, array $ctx, $fixedPrice = true)
    {
        if (!array_key_exists($productId, $this->payloadProducts)) {
            $p = ProductionGoods::with('series', 'brand', 'variants')->find($productId);
            $this->payloadProducts[$productId] = $p ? [$p, $this->payloadMainImage($p), $this->payloadColors($productId)] : null;
        }
        if (!$this->payloadProducts[$productId]) return null;
        [$product, $mainImageBase, $colorsBySize] = $this->payloadProducts[$productId];

        $trackImage = function ($file) use (&$imageFiles) {
            if ($file && !in_array($file, $imageFiles, true)) $imageFiles[] = $file;
            return $file ? asset('assets/products/' . $file) : null;
        };

        $sizeSetIds = array_values(array_unique($sizeSetIds));

        $isAdvanceSample = false;
        $sizeSets = [];
        foreach ($sizeSetIds as $sizeSetId) {
            $colorsRows = $colorsBySize->get($sizeSetId, collect());
            if (!empty($colorFilter[$sizeSetId])) {
                $colorsRows = $colorsRows->filter(fn($c) => in_array((int) $c->id, $colorFilter[$sizeSetId]));
            }

            $variant = $product->variants->where('master_size_measurement_id', $sizeSetId)->first() ?: $product->variants->first();
            $mrp = $variant->mrp ?? 0;
            $unitPrice = ceil($mrp - ($mrp * $discountFor((int) $sizeSetId) / 100));
            $sizeMeta = $ctx['sizes'][$sizeSetId] ?? null;
            $sizeName = $sizeMeta->name ?? ('Size ' . $sizeSetId);
            $mainImage = ($variant && !empty($variant->image)) ? $variant->image : $mainImageBase;

            $colors = [];
            foreach ($colorsRows as $c) {
                $noLimit = (bool) $c->is_advance_sample;
                $isAdvanceSample = $isAdvanceSample || $noLimit;
                $available = $noLimit ? 99999
                    : max(0, (int) $c->available_boxes - (int) ($ctx['open'][$productId . '_' . $c->id . '_' . $sizeSetId] ?? 0));

                $cImg = null;
                if ($variant) {
                    $g = $ctx['variant_color_img'][$variant->id . '_' . $c->id] ?? null;
                    $cImg = $g ? optional($g->first())->image : null;
                }
                if (!$cImg) {
                    $g = $ctx['product_color_img'][$productId . '_' . $c->id] ?? null;
                    $cImg = $g ? optional($g->first())->image : null;
                }
                $imgUrl = $trackImage($cImg ?: $mainImage);

                $pcs = (float) $c->pcs_per_box;
                if ($pcs <= 0) $pcs = (float) ($sizeMeta->no_of_pcs ?? 1);

                $key = $keyPrefix . $productId . '_' . $c->id . '_' . $sizeSetId;
                $colors[] = [
                    'id' => (int) $c->id,
                    'name' => $c->name,
                    'key' => $key,
                    'available_boxes' => $available,
                    'is_advance_sample' => $noLimit,
                    'pcs_per_box' => $pcs,
                    'image' => $imgUrl,
                    // everything the cart / order save needs, so no catalog lookup is required
                    'item' => [
                        'key' => $key,
                        'brand_id' => $product->brand_id,
                        'fixed_price' => $fixedPrice,
                        'product_id' => (int) $productId,
                        'color_id' => (int) $c->id,
                        'size_set_id' => (int) $sizeSetId,
                        'design_number' => $product->design_number,
                        'name_of_garment' => $product->name_of_garment,
                        'series_name' => $product->series->name ?? '',
                        'brand_name' => $product->brand->name ?? '',
                        'color_name' => $c->name,
                        'size_set_name' => $sizeName,
                        'available_boxes' => $available,
                        'is_advance_sample' => $noLimit,
                        'pcs_per_box' => $pcs,
                        'mrp' => $mrp,
                        'unit_price' => $unitPrice,
                        'image_url' => $imgUrl,
                    ],
                ];
            }

            $sizeSets[] = [
                'size_set_id' => (int) $sizeSetId,
                'size_set_name' => $sizeName,
                'brand_id' => $product->brand_id,
                'fixed_price' => $fixedPrice,
                'mrp' => $mrp,
                'unit_price' => $unitPrice,
                'image' => $trackImage($mainImage),
                'colors' => $colors,
            ];
        }

        return [
            'success' => true,
            'is_advance_sample' => $isAdvanceSample,
            'product' => [
                'id' => $product->id,
                'name' => trim(($product->series->name ?? '') . ' ' . $product->name_of_garment),
                'design_number' => $product->design_number,
                'series_name' => $product->series->name ?? '',
                'brand_name' => $product->brand->name ?? '',
                'image' => $trackImage($mainImageBase),
            ],
            'size_sets' => $sizeSets,
        ];
    }

    private function payloadMainImage(ProductionGoods $product)
    {
        $main = optional($product->variants->first(function ($v) { return !empty($v->image); }))->image;
        if (!$main) {
            $main = DB::table('production_goods_variant_colors')
                ->join('production_goods_variants', 'production_goods_variant_colors.variant_id', '=', 'production_goods_variants.id')
                ->where('production_goods_variants.production_goods_id', $product->id)
                ->whereNotNull('production_goods_variant_colors.image')
                ->where('production_goods_variant_colors.image', '!=', '')
                ->value('production_goods_variant_colors.image');
        }
        return $main;
    }

    /**
     * All stock colours of all size sets of a product, per size set: every colour in stock is listed,
     * only boxes in storerooms that take orders count; "no stock limit" per colour + size set.
     */
    private function payloadColors($productId)
    {
        return DomesticInventory::where('domestic_inventories.product_id', $productId)
            ->where('domestic_inventories.status', 1)
            ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
            ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
            ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
            ->select('domestic_inventories.size_set_id', 'master_colors.id', 'master_colors.name',
                DB::raw("SUM(CASE WHEN storerooms.id IS NULL OR storerooms.order_taken = 'Yes' THEN domestic_inventories.total_boxes ELSE 0 END) as available_boxes"),
                DB::raw('MAX(domestic_inventories.quantity) as pcs_per_box'),
                DB::raw('MAX(CASE WHEN ' . Storeroom::overStockSql() . ' THEN 1 ELSE 0 END) as is_advance_sample'))
            ->groupBy('domestic_inventories.size_set_id', 'master_colors.id', 'master_colors.name')
            ->orderBy('domestic_inventories.size_set_id')->orderBy('master_colors.id')
            ->get()
            ->groupBy('size_set_id');
    }

    private static function lastSegment($code)
    {
        $code = trim((string) $code);
        foreach (['/fc/', '/sc/'] as $m) {
            if (strpos($code, $m) !== false) {
                $code = explode('/', explode($m, $code)[1])[0];
                break;
            }
        }
        if (stripos($code, 'http://') === 0 || stripos($code, 'https://') === 0) {
            $parts = explode('/', explode('#', explode('?', $code)[0])[0]);
            $code = end($parts);
        }
        return trim(explode('#', explode('?', $code)[0])[0]);
    }

    public function sync(Request $request)
    {
        $orders = $request->input('orders', []);
        if (is_string($orders)) {
            $orders = json_decode($orders, true) ?: [];
        }
        if (empty($orders) || !is_array($orders)) {
            return response()->json(['success' => false, 'message' => 'No orders provided for sync.']);
        }

        $agent = Auth::guard('sales_agent')->user();
        $agent_id = $agent->id;
        $rules = app(AgentOrderRules::class);
        $synced = [];
        $errors = [];

        foreach ($orders as $orderData) {
            if (!is_array($orderData)) {
                $errors[] = ['local_id' => 'unknown', 'error' => 'Order data is not readable.'];
                continue;
            }
            DB::beginTransaction();
            try {
                $shop_id = $orderData['shop_id'] ?? null;
                $customer = is_numeric($shop_id) ? MasterCustomer::where('id', $shop_id)->where('status', '!=', 3)->first() : null;
                if (!$customer) {
                    throw new AgentOrderException("Shop ID #{$shop_id} not found.");
                }
                if (!$agent->is_master_agent && (int) $customer->sales_agent_id !== (int) $agent_id) {
                    throw new AgentOrderException("Shop '{$customer->name}' is not assigned to you.");
                }

                // Idempotency: a retry after a lost response must not create a duplicate order
                $localMarker = !empty($orderData['local_id']) ? ' [OFF:' . $orderData['local_id'] . ']' : null;
                if ($localMarker) {
                    $existing = AgentOrder::where('remark', 'like', '%' . $localMarker . '%')
                        ->where(fn($q) => $q->where('sales_agent_id', $agent_id)->orWhere('master_agent_id', $agent_id))
                        ->first();
                    if ($existing) {
                        DB::rollBack();
                        $synced[] = [
                            'local_id' => $orderData['local_id'],
                            'server_id' => $existing->id,
                            'order_no' => 'ORD-' . $existing->id,
                            'shop_name' => $customer->name
                        ];
                        continue;
                    }
                }

                if ((int) $customer->status !== 1) {
                    throw new AgentOrderException("Shop '{$customer->name}' is inactive. Activate the shop, then sync again.");
                }

                $master_agent_id = null;
                if ($agent->is_master_agent) {
                    $actual_agent_id = $customer->sales_agent_id ?: 0;
                    $master_agent_id = $agent_id;
                } else {
                    $actual_agent_id = $agent_id;
                }

                // price, pieces per box and rack are decided here; boxes only while stock is free
                [$lines, $need] = $rules->newLines($agent, 'customer', $customer->id, is_array($orderData['variations'] ?? null) ? $orderData['variations'] : []);
                if (empty($lines)) {
                    throw new AgentOrderException('Order has no valid items.');
                }
                $rules->checkStock($need, false);

                $value = 0;
                $total_qty = 0;
                foreach ($lines as $line) {
                    $value += $line['quantity'] * $line['selling_price'];
                    $total_qty += $line['quantity'];
                }
                $discount = $orderData['discount_amount'] ?? 0;
                $other = $orderData['other_charges'] ?? 0;
                $totals = $rules->totals($value, is_numeric($discount) ? $discount : 0, is_numeric($other) ? $other : 0);

                // Order date = the day it was booked on the phone (never in the future, at most 30 days back)
                $orderDate = now();
                $dateNote = '';
                if (!empty($orderData['order_date']) && ($t = strtotime((string) $orderData['order_date'])) !== false) {
                    $booked = date('Y-m-d', $t);
                    if ($booked < date('Y-m-d', strtotime('-30 days'))) {
                        $dateNote = " [booked on phone {$booked}]";
                    } elseif ($booked < date('Y-m-d')) {
                        $orderDate = $booked;
                    }
                }
                $expected = !empty($orderData['expected_dispatch_date']) && strtotime((string) $orderData['expected_dispatch_date']) !== false
                    ? date('Y-m-d', strtotime((string) $orderData['expected_dispatch_date'])) : null;

                $order = AgentOrder::create([
                    'sales_agent_id' => $actual_agent_id,
                    'master_agent_id' => $master_agent_id,
                    'sales_man_id' => $rules->salesManId($orderData['sales_man_id'] ?? null),
                    'party_type' => 'customer',
                    'master_customer_id' => $customer->id,
                    'master_vendor_id' => null,
                    'total_qty' => $total_qty,
                    'expected_dispatch_date' => $expected,
                    'status' => 'pending',
                    'order_type' => 'normal',
                    'sale_type' => 'item',
                    'is_sample_set' => 0,
                    'order_date' => $orderDate,
                    'created_by' => $agent_id,
                    'remark' => trim(($orderData['remark'] ?? '') . ' [Booked Offline]' . $dateNote . ($localMarker ?? '')),
                    'booking_station' => $orderData['booking_station'] ?? null,
                    'transport' => $orderData['transport'] ?? null,
                ] + $totals);

                foreach ($lines as $line) {
                    $line['agent_order_id'] = $order->id;
                    AgentOrderItem::create($line);
                }

                DB::commit();

                $synced[] = [
                    'local_id' => $orderData['local_id'] ?? null,
                    'server_id' => $order->id,
                    'order_no' => 'ORD-' . $order->id,
                    'shop_name' => $customer->name
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = [
                    'local_id' => $orderData['local_id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'synced' => $synced,
            'errors' => $errors
        ]);
    }
}
