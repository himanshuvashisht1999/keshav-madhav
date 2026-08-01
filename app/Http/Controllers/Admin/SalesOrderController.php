<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\AgentOrder;
use App\Models\AgentOrderItem;
use App\Models\AgentOrderDispatch;
use App\Models\AgentOrderDispatchItem;
use App\Models\DomesticInventory;
use App\Models\MasterCustomer;
use App\Models\ProductionGoods;
use App\Models\MasterColor;
use App\Models\MasterSizeMeasurement;

class SalesOrderController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('admin.agent-orders.index', ['agent_id' => 'direct']);
    }

    public function create(Request $request)
    {
        $shop_id = $request->get('master_customer_id');

        if (!$shop_id) {
            $shops = DB::table('master_customers')->select('id', 'name')->where('status', 1)->get();
            return view('admin.direct_sales.select_customer', compact('shops'));
        }

        $shop = DB::table('master_customers')->where('id', $shop_id)->first();
        if (!$shop) {
            return redirect()->route('admin.direct-sales.create')->with('error', 'Invalid Customer selected.');
        }

        $designs = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->distinct()->pluck('production_goods.design_number');

        $product_names = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->leftJoin('master_series', 'production_goods.master_series_id', '=', 'master_series.id')
            ->select(DB::raw('DISTINCT(TRIM(CONCAT(COALESCE(master_series.name, " "), " ", production_goods.name_of_garment))) as full_name'))
            ->pluck('full_name');

        $colors = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
            ->distinct()->pluck('master_colors.name');

        $size_sets = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
            ->distinct()->pluck('master_size_measurements.name');

        // Available Inventory Query (same as AgentOrder)
        $prices = DB::table('production_goods_variants')
            ->select('production_goods_id as product_id', 'master_size_measurement_id as size_set_id', DB::raw('MAX(mrp) as mrp'))
            ->groupBy('production_goods_id', 'master_size_measurement_id');

        $allocated = DB::table('agent_order_items')
            ->join('agent_orders', 'agent_order_items.agent_order_id', '=', 'agent_orders.id')
            ->where('agent_orders.status', 'pending')
            ->select('product_id', 'color_id', 'size_set_id', DB::raw('SUM(box_qty) as total_allocated'))
            ->groupBy('product_id', 'color_id', 'size_set_id');

        $query = DomesticInventory::where('domestic_inventories.status', 1)
            ->join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->leftJoin('master_series', 'production_goods.master_series_id', '=', 'master_series.id')
            ->leftJoin('master_product_fittings', 'production_goods.master_product_fitting_id', '=', 'master_product_fittings.id')
            ->leftJoin('master_design_patterns', 'production_goods.master_pattern_id', '=', 'master_design_patterns.id')
            ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
            ->join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')

            ->leftJoinSub($prices, 'ip', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'ip.product_id')
                    ->on('domestic_inventories.size_set_id', '=', 'ip.size_set_id');
            });

        // Customer Brand Discount
        $query->leftJoin('customer_brand_discounts', function ($join) use ($shop_id) {
            $join->on('production_goods.brand_id', '=', 'customer_brand_discounts.brand_id')
                ->where('customer_brand_discounts.customer_id', '=', $shop_id);
        });
        $discount_col = 'COALESCE(customer_brand_discounts.discount_percentage, 0)';

        if ($request->filled('design_number')) {
            $query->where('production_goods.design_number', $request->design_number);
        }
        if ($request->filled('product_name')) {
            $query->where(DB::raw('TRIM(CONCAT(COALESCE(master_series.name, ""), " ", production_goods.name_of_garment))'), $request->product_name);
        }
        if ($request->filled('color_name')) {
            $query->where('master_colors.name', $request->color_name);
        }
        if ($request->filled('size_set_name')) {
            $query->where('master_size_measurements.name', $request->size_set_name);
        }

        $query->leftJoinSub($allocated, 'alloc', function ($join) {
            $join->on('domestic_inventories.product_id', '=', 'alloc.product_id')
                ->on('domestic_inventories.color_id', '=', 'alloc.color_id')
                ->on('domestic_inventories.size_set_id', '=', 'alloc.size_set_id');
        })
            ->select(
                'domestic_inventories.product_id',
                'domestic_inventories.color_id',
                'domestic_inventories.size_set_id',
                'production_goods.design_number',
                'production_goods.name_of_garment',
                'master_series.name as series_name',
                'master_colors.name as color_name',
                'master_size_measurements.name as size_set_name',
                'master_product_fittings.name as fitting_name',
                'master_design_patterns.name as pattern_name',

                DB::raw('SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) as available_boxes'),
                DB::raw('MAX(domestic_inventories.quantity) as pcs_per_box'),
                DB::raw('CEILING(MAX(COALESCE(ip.mrp, 0)) * (100 - ' . $discount_col . ') / 100) as unit_price'),
                DB::raw('MAX(COALESCE(ip.mrp, 0)) as mrp')
            );

        $boxes = $query->groupBy(
            'domestic_inventories.product_id',
            'domestic_inventories.color_id',
            'domestic_inventories.size_set_id',
            'production_goods.design_number',
            'production_goods.name_of_garment',
            'master_series.name',
            'master_colors.name',
            'master_size_measurements.name',
            'master_product_fittings.name',
            'master_design_patterns.name',

            DB::raw($discount_col)
        )
            ->havingRaw('MAX(COALESCE(ip.mrp, 0)) > 0')
            ->havingRaw('SUM(domestic_inventories.total_boxes) - COALESCE(MAX(alloc.total_allocated), 0) > 0')
            ->orderBy('production_goods.design_number')
            ->paginate(20)
            ->appends($request->except('page'));

        $gst_percentage = DB::table('settings')->value('gst_order') ?? 5.00;

        return view('admin.direct_sales.create', compact('shop', 'designs', 'product_names', 'colors', 'size_sets', 'boxes', 'gst_percentage'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'master_customer_id' => 'required|exists:master_customers,id',
            'variations' => 'required|array|min:1',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $total_qty = 0;
        $total_amount = 0;
        $items_to_create = [];
        $inventory_to_deduct = [];

        foreach ($request->variations as $var) {
            $product = ProductionGoods::with('series')->find($var['product_id']);
            $color = MasterColor::find($var['color_id']);
            $sizeSet = MasterSizeMeasurement::find($var['size_set_id']);

            if (!$product || !$color || !$sizeSet)
                continue;

            $variant = DB::table('production_goods_variants')
                ->where('production_goods_id', $var['product_id'])
                ->where('master_size_measurement_id', $var['size_set_id'])
                ->first();

            $mrp = $variant->mrp ?? 0;
            $unit_price = (float) ($var['unit_price'] ?? $mrp);
            $unit_price = ceil($unit_price);
            $pcs_per_box = (float) ($var['pcs_per_box'] ?? ($sizeSet->total_pieces ?? 0));
            $total_pcs = $var['qty'] * $pcs_per_box;

            $items_to_create[] = [
                'product_id' => $var['product_id'],
                'color_id' => $var['color_id'],
                'size_set_id' => $var['size_set_id'],
                'product_name' => trim(($product->series->name ?? '') . ' ' . $product->name_of_garment),
                'design_number' => $product->design_number,
                'color_name' => $color->name,
                'size_set_name' => $sizeSet->name,
                'quantity' => $total_pcs,
                'box_qty' => $var['qty'],
                'mrp' => $mrp,
                'selling_price' => $unit_price,
                'barcode' => 'D' . $var['product_id'] . 'S' . $var['size_set_id'] . 'C' . $var['color_id'],
                'scanned_box_qty' => $var['qty'],
                'scanned_quantity' => $total_pcs,
                'dispatched_at' => now(),
            ];

            $total_qty += $total_pcs;
            $total_amount += ($total_pcs * $unit_price);

            // Track for inventory deduction
            $inventory_to_deduct[] = [
                'barcode' => $items_to_create[count($items_to_create) - 1]['barcode'],
                'qty' => $var['qty']
            ];
        }

        $gst_percentage = $request->gst_percentage ?? 5.00;
        $discount_percentage = $request->discount_percentage ?? 0;
        $discount_amount = ($total_amount * $discount_percentage / 100);
        $taxable_amount = $total_amount - $discount_amount;
        $gst_amount = $taxable_amount * ($gst_percentage / 100);
        $grand_total = ceil($taxable_amount + $gst_amount);

        DB::beginTransaction();
        try {
            $customer = MasterCustomer::findOrFail($request->master_customer_id);

            // 1. Create Order (Status: Dispatched)
            $order = AgentOrder::create([
                'sales_agent_id' => 0, // Direct
                'master_customer_id' => $request->master_customer_id,
                'total_qty' => $total_qty,
                'total_amount' => $total_amount,
                'discount_percentage' => $discount_percentage,
                'discount_amount' => $discount_amount,
                'gst_percentage' => $gst_percentage,
                'gst_amount' => $gst_amount,
                'grand_total' => $grand_total,
                'status' => 'dispatched',
                'order_date' => date('Y-m-d'),
                'created_by' => Auth::id(),
                'remark' => $request->remark,
                'booking_station' => $request->booking_station,
                'transport' => $request->transport,
            ]);

            // 2. Create Dispatch Record
            $dispatch = AgentOrderDispatch::create([
                'master_customer_id' => $request->master_customer_id,
                'sales_agent_id' => 0,
                'status' => 'dispatched',
                'created_by' => Auth::id(),
                'dispatch_date' => now(),
                'total_amount' => $total_amount,
                'discount_amount' => $discount_amount,
                'gst_amount' => $gst_amount,
                'gst_percentage' => $gst_percentage,
                'grand_total' => $grand_total,
            ]);

            AgentOrderDispatchItem::create([
                'agent_order_dispatch_id' => $dispatch->id,
                'agent_order_id' => $order->id,
            ]);

            // 3. Create Items and Deduct Stock
            foreach ($items_to_create as $item) {
                $item['agent_order_id'] = $order->id;
                $item['agent_order_dispatch_id'] = $dispatch->id;
                AgentOrderItem::create($item);

                // Find inventory to deduct
                $inventories = DomesticInventory::where('barcode', $item['barcode'])
                    ->where('total_boxes', '>', 0)
                    ->get();

                $remainingToDeduct = $item['box_qty'];
                foreach ($inventories as $inv) {
                    if ($remainingToDeduct <= 0)
                        break;
                    $deduct = min($inv->total_boxes, $remainingToDeduct);

                    // Log to History
                    \App\Models\DomesticInventoryHistory::create([
                        'domestic_inventory_id' => $inv->id,
                        'user_id' => Auth::id(),
                        'type' => 'stock_consume',
                        'old_product_id' => $inv->product_id,
                        'old_color_id' => $inv->color_id,
                        'old_size_set_id' => $inv->size_set_id,

                        'old_rack_id' => $inv->warehouse_rack_id,
                        'box_quantity' => $deduct,
                    ]);

                    $inv->decrement('total_boxes', $deduct);
                    if ($inv->total_boxes <= 0)
                        $inv->delete();
                    $remainingToDeduct -= $deduct;
                }
            }

            // 4. Update Customer Balance
            $customer->decrement('balance', $grand_total);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Direct Sales Order created and stock deducted!', 'redirect_url' => route('admin.agent-orders.dispatches.show', $dispatch->id)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $order = AgentOrder::with(['shop'])->findOrFail($id);
        $items = AgentOrderItem::where('agent_order_id', $id)->get();
        return view('admin.direct_sales.show', compact('order', 'items'));
    }
}
