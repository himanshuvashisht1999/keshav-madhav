<?php

namespace App\Http\Controllers\SalesAgent;

use App\Http\Controllers\Controller;
use App\Models\AgentOrder;
use App\Models\AgentOrderItem;
use App\Models\DomesticInventory;
use App\Models\MasterColor;
use App\Models\MasterCustomer;
use App\Models\MasterDesignPattern;
use App\Models\MasterProductFitting;
use App\Models\MasterSizeMeasurement;
use App\Models\ProductionGoods;
use App\Models\SalesMan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OfflineOrderController extends Controller
{
    public function index()
    {
        $agent = Auth::guard('sales_agent')->user();
        $shops_count = MasterCustomer::where('sales_agent_id', $agent->id)->where('status', 1)->count();
        return view('sales_agent.offline.index', compact('agent', 'shops_count'));
    }

    public function create(Request $request)
    {
        $agent = Auth::guard('sales_agent')->user();
        $pre_selected_shop_id = $request->get('shop_id', null);
        return view('sales_agent.offline.create', compact('agent', 'pre_selected_shop_id'));
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
            $shops = $shopsQuery->orderBy('name')->get([
                'id', 'name', 'phone', 'address', 'gst_number', 'balance'
            ]);

            // 2. Fetch Active Products / Variations
            $brand_discounts = DB::table('sales_agent_brand_discounts')
                ->where('sales_agent_id', $agent_id)
                ->pluck('discount_percentage', 'brand_id');

            $inventories = DB::table('domestic_inventories')
                ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
                ->leftJoin('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
                ->leftJoin('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
                ->leftJoin('master_series', 'production_goods.master_series_id', '=', 'master_series.id')
                ->leftJoin('brands', 'production_goods.brand_id', '=', 'brands.id')
                ->leftJoin('master_product_fittings', 'production_goods.master_product_fitting_id', '=', 'master_product_fittings.id')
                ->leftJoin('master_design_patterns', 'production_goods.master_pattern_id', '=', 'master_design_patterns.id')
                ->where('domestic_inventories.status', 1)
                ->where('domestic_inventories.total_boxes', '>', 0)
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
                    DB::raw('SUM(domestic_inventories.total_boxes) as available_boxes'),
                    DB::raw('AVG(domestic_inventories.quantity) as pcs_per_box')
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
                ->get();

            $products = [];
            $imageUrls = [];

            foreach ($inventories as $inv) {
                $vKey = $inv->product_id . '_' . $inv->color_id . '_' . $inv->size_set_id;

                // Variant MRP & Price
                $variant = DB::table('production_goods_variants')
                    ->where('production_goods_id', $inv->product_id)
                    ->where('master_size_measurement_id', $inv->size_set_id)
                    ->first();

                $mrp = $variant->mrp ?? 0;
                $brand_disc = $brand_discounts[$inv->brand_id] ?? 0;
                $selling_price = ceil($mrp > 0 ? ($mrp - ($mrp * $brand_disc / 100)) : 0);

                // Variant Color Image
                $image = null;
                if ($variant) {
                    $image = DB::table('production_goods_variant_colors')
                        ->where('variant_id', $variant->id)
                        ->where('master_color_id', $inv->color_id)
                        ->value('image');
                    if (!$image) {
                        $image = $variant->image;
                    }
                }

                $imageUrl = $image ? asset('assets/products/' . $image) : null;
                if ($imageUrl && !in_array($imageUrl, $imageUrls)) {
                    $imageUrls[] = $imageUrl;
                }

                $barcode = 'D' . $inv->product_id . 'S' . $inv->size_set_id . 'C' . $inv->color_id;

                $pcs = (float) $inv->pcs_per_box;
                if ($pcs <= 0) {
                    $pcs = (float) ($inv->no_of_pcs ?? 1);
                }

                $products[] = [
                    'key' => $vKey,
                    'product_id' => $inv->product_id,
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
                    'available_boxes' => (int) $inv->available_boxes,
                    'pcs_per_box' => $pcs,
                    'mrp' => $mrp,
                    'unit_price' => $selling_price,
                    'barcode' => $barcode,
                    'image' => $image,
                    'image_url' => $imageUrl
                ];
            }

            // 3. Settings & Salesmen
            $settings = DB::table('settings')->first();
            $sales_men = SalesMan::where('status', 1)->get(['id', 'name', 'phone']);

            return response()->json([
                'success' => true,
                'shops' => $shops,
                'products' => $products,
                'image_urls' => array_slice($imageUrls, 0, 300), // Pre-cache up to 300 images
                'settings' => [
                    'gst_order' => $settings->gst_order ?? 5.00,
                    'agent_app_show_stock' => $settings->agent_app_show_stock ?? 1,
                    'agent_app_allow_over_stock' => $settings->agent_app_allow_over_stock ?? 0,
                    'agent_app_allow_over_stock_sample' => $settings->agent_app_allow_over_stock_sample ?? 0,
                ],
                'sales_men' => $sales_men,
                'see_price' => (bool) $agent->see_price,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load offline catalog: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sync(Request $request)
    {
        $orders = $request->input('orders', []);
        if (empty($orders)) {
            return response()->json(['success' => false, 'message' => 'No orders provided for sync.']);
        }

        $agent = Auth::guard('sales_agent')->user();
        $agent_id = $agent->id;
        $synced = [];
        $errors = [];

        foreach ($orders as $orderData) {
            DB::beginTransaction();
            try {
                $shop_id = $orderData['shop_id'];
                $customer = MasterCustomer::find($shop_id);
                if (!$customer) {
                    throw new \Exception("Shop ID #{$shop_id} not found.");
                }

                $master_agent_id = null;
                if ($agent->is_master_agent) {
                    $actual_agent_id = $customer->sales_agent_id ?: 0;
                    $master_agent_id = $agent_id;
                } else {
                    $actual_agent_id = $agent_id;
                }

                $total_qty = 0;
                $total_amount = 0;
                $items_to_create = [];

                foreach ($orderData['variations'] as $var) {
                    $product = ProductionGoods::with('series', 'brand')->find($var['product_id']);
                    $color = MasterColor::find($var['color_id']);
                    $sizeSet = MasterSizeMeasurement::find($var['size_set_id']);

                    if (!$product || !$color || !$sizeSet) continue;

                    $variant = DB::table('production_goods_variants')
                        ->where('production_goods_id', $var['product_id'])
                        ->where('master_size_measurement_id', $var['size_set_id'])
                        ->first();

                    $mrp = $variant->mrp ?? 0;
                    $selling_price = isset($var['unit_price']) ? (float) $var['unit_price'] : $mrp;
                    $selling_price = ceil($selling_price);

                    $seriesName = ($product->series) ? $product->series->name : '';
                    $product_name = trim($seriesName . ' ' . $product->name_of_garment);

                    $fitting = $product->master_product_fitting_id ? MasterProductFitting::find($product->master_product_fitting_id) : null;
                    $pattern = $product->master_pattern_id ? MasterDesignPattern::find($product->master_pattern_id) : null;

                    $pcs_per_box = isset($var['pcs_per_box']) && $var['pcs_per_box'] > 0 ? (float) $var['pcs_per_box'] : ($sizeSet->no_of_pcs ?? 1);
                    $total_pcs = $var['qty'] * $pcs_per_box;
                    $barcode = 'D' . $var['product_id'] . 'S' . $var['size_set_id'] . 'C' . $var['color_id'];

                    $items_to_create[] = [
                        'rack_id' => null,
                        'product_id' => $var['product_id'],
                        'color_id' => $var['color_id'],
                        'size_set_id' => $var['size_set_id'],
                        'product_name' => $product_name ?: 'N/A',
                        'design_number' => $product->design_number,
                        'color_name' => $color->name,
                        'size_set_name' => $sizeSet->name,
                        'fitting_name' => $fitting->name ?? null,
                        'pattern_name' => $pattern->name ?? null,
                        'quantity' => $total_pcs,
                        'box_qty' => $var['qty'],
                        'mrp' => $mrp,
                        'selling_price' => $selling_price,
                        'barcode' => $barcode,
                        'packing_box_id' => null,
                        'scanned_box_qty' => 0,
                        'scanned_quantity' => 0,
                        'dispatched_at' => null,
                        'remark' => $var['remark'] ?? null,
                    ];

                    $total_qty += $total_pcs;
                    $total_amount += ($total_pcs * $selling_price);
                }

                $total_amount = ceil($total_amount);
                $other_charges = ceil((float) ($orderData['other_charges'] ?? 0));
                $discount_amount = ceil((float) ($orderData['discount_amount'] ?? 0));
                $discount_percentage = ($total_amount > 0) ? ($discount_amount / $total_amount * 100) : 0;
                $taxable_amount = $total_amount - $discount_amount;
                $gst_percentage = (float) ($orderData['gst_percentage'] ?? 5.00);
                $gst_amount = ceil((float) ($orderData['gst_amount'] ?? ($taxable_amount * $gst_percentage / 100)));
                $grand_total = ceil($taxable_amount + $gst_amount + $other_charges);

                $orderDate = !empty($orderData['order_date']) ? $orderData['order_date'] : now();

                $order = AgentOrder::create([
                    'sales_agent_id' => $actual_agent_id,
                    'master_agent_id' => $master_agent_id,
                    'sales_man_id' => $orderData['sales_man_id'] ?? null,
                    'party_type' => 'customer',
                    'master_customer_id' => $shop_id,
                    'master_vendor_id' => null,
                    'total_qty' => $total_qty,
                    'total_amount' => $total_amount,
                    'discount_percentage' => $discount_percentage,
                    'discount_amount' => $discount_amount,
                    'gst_percentage' => $gst_percentage,
                    'gst_amount' => $gst_amount,
                    'other_charges' => $other_charges,
                    'grand_total' => $grand_total,
                    'expected_dispatch_date' => $orderData['expected_dispatch_date'] ?? null,
                    'status' => 'pending',
                    'order_type' => 'normal',
                    'sale_type' => 'item',
                    'is_sample_set' => 0,
                    'order_date' => $orderDate,
                    'created_by' => $agent_id,
                    'remark' => ($orderData['remark'] ?? '') . ' [Booked Offline]',
                    'booking_station' => $orderData['booking_station'] ?? null,
                    'transport' => $orderData['transport'] ?? null,
                ]);

                foreach ($items_to_create as $item) {
                    $item['agent_order_id'] = $order->id;
                    AgentOrderItem::create($item);
                }

                DB::commit();

                $synced[] = [
                    'local_id' => $orderData['local_id'],
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
