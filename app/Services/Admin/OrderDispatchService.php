<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\OrderDispatch;
use App\Models\OrderDispatchPurchase;
use App\Models\DomesticInventoryPurchase;
use App\Models\DomesticInventoryHistory;
use App\Models\DomesticInventory;
use App\Models\PackingCarton;
use App\Models\PackingCartonsDetails;
use App\Models\OrderDispatchDetails;
use App\Models\OrderProductSet;
use App\Models\OrderMain;
use App\Models\PackingMain;
use App\Models\PackingItem;
use App\Models\GeneralSettings;
use PDF;


use App\Http\DataTable\Admin\OrderDispatchDataTable as DataTable;
use Illuminate\Support\Facades\DB;

class OrderDispatchService
{
    protected $datatable;

    public function __construct(
        DataTable $datatable
    ) {
        $this->datatable = $datatable;
    }

    public function index(Request $request)
    {
        return true;
    }

    public function indexList(Request $request)
    {
        return $this->datatable->indexList($request);
    }

    /**
     * Bill of a dispatch: items - discount + other charges, then GST on that. The dispatch screen, the invoice edit,
     * the dispatch page and the invoice PDF all use this, so they always show the same total.
     */
    public static function billTotals(float $subtotal, float $discount, float $other, float $gstPct): array
    {
        $base = $subtotal - $discount + $other;
        $gst = $base * $gstPct / 100;
        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'other' => round($other, 2),
            'gst_percentage' => $gstPct,
            'gst' => round($gst, 2),
            'total' => round($base + $gst, 2),
        ];
    }

    /** Price of one packed item: its selling price, else the order line's price per piece (same rule as the dispatch screen). */
    public static function itemPrice($item): float
    {
        $price = (float) ($item->selling_price ?? 0);
        if ($price == 0) {
            $set = $item->detail->orderProductSet ?? null;
            if ($set && ($set->total_quantity ?? 0) > 0) {
                $price = (float) ($set->basic_amount ?? 0) / $set->total_quantity;
            }
        }
        return $price;
    }

    /** Checks a charge typed on the dispatch / invoice screen: a number, not below 0. */
    public static function charge($value, string $name, ?float $max = null): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }
        if (!is_numeric($value) || (float) $value < 0 || ($max !== null && (float) $value > $max)) {
            throw new \Exception(ucfirst($name) . " must be a number from 0" . ($max !== null ? " to {$max}." : " up."));
        }
        return (float) $value;
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $selectedCartons = !empty($request->cartons) && is_array($request->cartons) ? array_values(array_unique(array_map('intval', $request->cartons))) : [];
            $selectedPurchaseItems = !empty($request->purchase_items) && is_array($request->purchase_items) ? $request->purchase_items : [];

            // Safety check: at least one carton or purchase item
            if (empty($selectedCartons) && empty($selectedPurchaseItems)) {
                return [
                    'status_code' => 0,
                    'message' => 'No cartons or purchase items selected for dispatch'
                ];
            }

            // The order (locked: two dispatches of one order are saved one after the other)
            $orderId = (int) ($request->final_order_no ?: $request->order_no);
            $order = OrderMain::lockForUpdate()->find($orderId);
            if (!$order || $order->order_type !== 'corporate' || !in_array((int) $order->status, [1, 2], true)) {
                throw new \Exception('This order cannot be dispatched (not found, not a corporate order, or already completed).');
            }

            $gstPct = self::charge($request->gst_percentage, 'GST %', 100);
            $otherCharges = self::charge($request->other_charges, 'other charges');

            // ================= CARTONS: locked, ready, of this order, not dispatched or moved to stock =================
            if (!empty($selectedCartons)) {
                $cartons = PackingCarton::with('main')->whereIn('id', $selectedCartons)->lockForUpdate()->get()->keyBy('id');
                $diverted = DB::table('domestic_inventories')->whereIn('packing_carton_id', $selectedCartons)->pluck('packing_carton_id')->all();
                $alreadyInDetails = OrderDispatchDetails::whereIn('carton_packing_id', $selectedCartons)->pluck('carton_packing_id')->all();
                foreach ($selectedCartons as $cartonId) {
                    $carton = $cartons->get($cartonId);
                    if (!$carton) {
                        throw new \Exception("Carton #{$cartonId} was not found.");
                    }
                    if (in_array($cartonId, $alreadyInDetails) || (int) $carton->status === 2) {
                        throw new \Exception("Carton {$carton->carton_no} has already been dispatched. Duplicate dispatch prevented.");
                    }
                    if ((int) $carton->status !== 1 || in_array($cartonId, $diverted)) {
                        throw new \Exception("Carton {$carton->carton_no} is not ready for dispatch (moved to stock or not packed).");
                    }
                    if ((int) optional($carton->main)->order_main_id !== (int) $order->id) {
                        throw new \Exception("Carton {$carton->carton_no} belongs to another order.");
                    }
                }
            }

            // ================= PURCHASE ITEMS: of this order's production POs, not dispatched =================
            $purchaseLines = [];
            foreach ($selectedPurchaseItems as $pi) {
                $historyId = (int) ($pi['history_id'] ?? 0);
                if (!$historyId) continue;
                $history = DomesticInventoryHistory::lockForUpdate()->find($historyId);
                if (!$history) {
                    throw new \Exception('A selected purchase item was not found.');
                }
                $poOrderId = DB::table('domestic_inventory_purchases as p')->join('production_pos as po', 'po.id', '=', 'p.production_po_id')
                    ->where('p.id', $history->purchase_id)->value('po.order_main_id');
                if ((int) $poOrderId !== (int) $order->id) {
                    throw new \Exception('A selected purchase item belongs to another order.');
                }
                if (OrderDispatchPurchase::where('purchase_history_id', $history->id)->exists()) {
                    throw new \Exception('Some selected purchase items have already been dispatched.');
                }
                $boxes = (int) ($pi['boxes'] ?? $history->box_quantity);
                if ($boxes < 1 || $boxes > (int) $history->box_quantity) {
                    throw new \Exception("The boxes of a purchase item must be between 1 and {$history->box_quantity}.");
                }
                $purchaseLines[] = [
                    'history' => $history,
                    'boxes' => $boxes,
                    'price' => self::charge($pi['price'] ?? 0, 'purchase item price'),
                    'mrp' => self::charge($pi['mrp'] ?? $history->mrp ?? 0, 'purchase item MRP'),
                ];
            }
            if (empty($selectedCartons) && empty($purchaseLines)) {
                throw new \Exception('No cartons or purchase items selected for dispatch');
            }

            // ================= PRICES CHANGED ON THE SCREEN (per design) =================
            if (!empty($selectedCartons)) {
                $modifiedPrices = $request->modified_global_prices ?? [];
                $modifiedMrps = $request->modified_global_mrps ?? [];

                if (!empty($modifiedPrices) || !empty($modifiedMrps)) {
                    $allModifiedSetIds = array_unique(array_merge($modifiedPrices, $modifiedMrps));
                    foreach ($allModifiedSetIds as $setId) {
                        $detailIds = \App\Models\OrderProductSetDetail::where('order_products_set_id', $setId)->pluck('id');
                        $updateData = [];

                        if (in_array($setId, $modifiedPrices) && isset($request->global_prices[$setId])) {
                            $updateData['selling_price'] = self::charge($request->global_prices[$setId], 'price');
                        }
                        if (in_array($setId, $modifiedMrps) && isset($request->global_mrps[$setId])) {
                            $updateData['mrp'] = self::charge($request->global_mrps[$setId], 'MRP');
                        }

                        if (!empty($updateData)) {
                            PackingItem::whereIn('packing_carton_id', $selectedCartons)
                                ->whereIn('size_id', $detailIds)
                                ->update($updateData);
                        }
                    }
                }
            }

            // ================= BILL: worked out here from the items (never taken from the page) =================
            $subtotal = 0.0;
            if (!empty($selectedCartons)) {
                foreach (PackingItem::with('detail.orderProductSet')->whereIn('packing_carton_id', $selectedCartons)->get() as $item) {
                    $subtotal += (float) $item->quantity * self::itemPrice($item);
                }
            }
            foreach ($purchaseLines as $line) {
                $pcsPerBox = (int) ($line['history']->pieces_per_box > 0 ? $line['history']->pieces_per_box : 1);
                $subtotal += round($line['price'] * $line['boxes'] * $pcsPerBox, 2);
            }
            if ($request->filled('discount_amount')) {
                $discount = self::charge($request->discount_amount, 'discount');
            } else {
                $discount = round($subtotal * self::charge($request->discount_percentage, 'discount %', 100) / 100, 2);
            }
            if ($discount > $subtotal + 0.001) {
                throw new \Exception('The discount is more than the bill.');
            }
            $bill = self::billTotals($subtotal, $discount, $otherCharges, $gstPct);

            // Count total units
            $totalPurchaseBoxes = array_sum(array_column($purchaseLines, 'boxes'));

            // ================= MAIN DISPATCH =================
            $data_save = new OrderDispatch();
            $data_save->customer_id = $order->master_customer_id; // always the order's own customer
            $data_save->main_order_id = $order->id;
            $data_save->dispatch_date = $request->dispatch_date ?? now();
            $data_save->bill_number = $request->bill_number;
            $data_save->company_id = $request->company_id;
            $data_save->total_quantity = count($selectedCartons) + $totalPurchaseBoxes;
            $data_save->gst_percentage = $gstPct;
            $data_save->gst_amount = $bill['gst'];
            $data_save->discount_percentage = $subtotal > 0 ? round($bill['discount'] / $subtotal * 100, 6) : 0;
            $data_save->discount_amount = $bill['discount'];
            $data_save->total_amount = $bill['total'];
            $data_save->other_charges = $bill['other'];
            $data_save->remark = $request->remark;
            $data_save->status = 1;
            $data_save->save();

            $data_save->sku = date('d/m/Y') . '/' . $data_save->main_order_id . "/" . $data_save->customer_id . "/" . $data_save->id ?? $data_save->id;
            $data_save->save();

            // Update Customer Balance (Subtraction means they owe the admin more)
            $customer = \App\Models\MasterCustomer::lockForUpdate()->find($data_save->customer_id);
            if ($customer) {
                $customer->balance -= $data_save->total_amount;
                $customer->save();
            }

            // ================= UPDATE CARTONS (IF ANY) =================
            if (!empty($selectedCartons)) {
                $detailsData = [];
                foreach ($selectedCartons as $cartonId) {
                    $detailsData[] = [
                        'order_dispatch_id' => $data_save->id,
                        'carton_packing_id' => $cartonId,
                        'status' => 1,
                    ];
                }
                OrderDispatchDetails::insert($detailsData);

                PackingCarton::whereIn('id', $selectedCartons)
                    ->update([
                        'status' => 2
                    ]);
            }

            // ================= SAVE PURCHASE ITEMS (IF ANY) =================
            foreach ($purchaseLines as $line) {
                $history = $line['history'];
                $boxes = $line['boxes'];
                $piecesPerBox = intval($history->pieces_per_box > 0 ? $history->pieces_per_box : 1);
                $totalPieces = $boxes * $piecesPerBox;

                OrderDispatchPurchase::create([
                    'order_dispatch_id' => $data_save->id,
                    'domestic_inventory_purchase_id' => $history->purchase_id,
                    'purchase_history_id' => $history->id,
                    'product_id' => $history->new_product_id,
                    'color_id' => $history->new_color_id,
                    'size_set_id' => $history->new_size_set_id,
                    'rack_id' => $history->new_rack_id,
                    'boxes_count' => $boxes,
                    'pieces_per_box' => $piecesPerBox,
                    'total_pieces' => $totalPieces,
                    'mrp' => $line['mrp'],
                    'selling_price' => $line['price'],
                    'total_amount' => round($line['price'] * $totalPieces, 2),
                    'status' => 1
                ]);

                // Take the boxes out of stock (refused when they are no longer there)
                $this->takePurchaseBoxes($history, $boxes);
            }

            $pack_data = $this->getOrderDispatchData($data_save->main_order_id);
            if (!empty($pack_data) && $pack_data['remaining'] == 0) {
                OrderMain::where('id', $data_save->main_order_id)
                    ->update([
                        'status' => 3
                    ]);
            } elseif (!empty($pack_data) && ($pack_data['packed'] > 0 || !empty($purchaseLines))) {
                OrderMain::where('id', $data_save->main_order_id)
                    ->update([
                        'status' => 2   // partial
                    ]);
            }
            // Commit everything if all successful
            DB::commit();

            return [
                'id' => 1,
                'status_code' => 1,
                'message' => 'Order successfully Dispatched.'
            ];

        } catch (\Exception $e) {
            //  Rollback everything on any error
            DB::rollBack();

            $return_data['message'] = $e->getMessage();
            $return_data['status_code'] = 0;
            return $return_data;
        }
    }

    /** Takes the boxes of a dispatched purchase line out of stock: its own rack first, then other racks (same item and pieces per box). */
    private function takePurchaseBoxes($history, int $boxes): void
    {
        $pcs = (int) $history->pieces_per_box;
        $rows = DomesticInventory::where('product_id', $history->new_product_id)
            ->where('color_id', $history->new_color_id)
            ->where('size_set_id', $history->new_size_set_id)
            ->when($pcs > 0, fn($q) => $q->where('quantity', $pcs))
            ->where('total_boxes', '>', 0)
            ->orderByRaw('CASE WHEN rack_id <=> ? THEN 0 ELSE 1 END', [$history->new_rack_id])
            ->orderByDesc('total_boxes')->orderBy('id')
            ->lockForUpdate()->get();
        $have = (int) $rows->sum('total_boxes');
        if ($have < $boxes) {
            $label = DB::table('production_goods')->where('id', $history->new_product_id)->value('design_number') ?? ('item #' . $history->new_product_id);
            throw new \Exception("Only {$have} box(es) of purchased design {$label} are in stock, {$boxes} needed. They may have been sold or moved.");
        }
        $stock = app(InventoryStockService::class);
        foreach ($rows as $row) {
            if ($boxes <= 0) break;
            $take = min($boxes, (int) $row->total_boxes);
            $stock->take($row, $take);
            $boxes -= $take;
        }
    }

    public function view(Request $request)
    {

        $order_dispatch_model = OrderDispatch::with([
            'dispatchDetails:id,order_dispatch_id,carton_packing_id',
            'orderDispatchPurchases.product',
            'orderDispatchPurchases.color',
            'orderDispatchPurchases.sizeSet',
            'orderDispatchPurchases.rack.storeroom',
            'orderDispatchPurchases.purchase.productionPO',
            'orderDispatchPurchases.purchase.vendor',
            'orderMain.customer',
        ])->where('id', $request->id)->first();

        if (!$order_dispatch_model) {
            return null;
        }

        $order_dispatch = $order_dispatch_model->toArray();

        // Basic Info
        $order_dispatch_data = [
            'id' => $order_dispatch['id'],
            'order_dispatch_no' => $order_dispatch['sku'],
            'bill_number' => $order_dispatch['bill_number'] ?? '',
            'order_no' => $order_dispatch['order_main']['sku'] ?? '',
            'customer' => $order_dispatch['order_main']['customer']['name'] ?? '',
            'address' => $order_dispatch['order_main']['customer']['address'] ?? '',
            'dispatch_date' => date("d-m-Y h:i A", strtotime($order_dispatch['dispatch_date'])) ?? '',
            'gst_percentage' => $order_dispatch['gst_percentage'],
            'discount_percentage' => $order_dispatch['discount_percentage'],
            'discount_amount' => $order_dispatch['discount_amount'] ?? 0,
            'total_amount' => $order_dispatch['total_amount'],
            'total_cartons' => count($order_dispatch['dispatch_details']),
            'total_items_dispatch' => 0,
            'total_dispatch_amount' => 0,
            'cartons' => []
        ];

        $dispatch_carton_ids = [];
        foreach ($order_dispatch['dispatch_details'] as $v) {
            $dispatch_carton_ids[] = $v['carton_packing_id'];
        }

        // Fetch Cartons with Items and Details
        $cartons_data_models = PackingCarton::with([
            'items.detail.orderProductSet.colors',
            'items.detail.orderProductSet.size_measurement',
            'rack.storeroom'
        ])->whereIn('id', $dispatch_carton_ids)->get();

        // Disable expensive appends that cause N+1 query storms during serialization
        foreach ($cartons_data_models as $carton) {
            foreach ($carton->items as $item) {
                if ($item->detail && $item->detail->orderProductSet) {
                    $item->detail->orderProductSet->setAppends([]);
                }
            }
        }

        $cartons_data = $cartons_data_models->toArray();

        $total_items_dispatch = 0;
        $total_dispatch_amount = 0;
        $finalCartonData = [];
        $consolidatedGroupedItems = [];

        foreach ($cartons_data as $carton) {
            $total_items_in_carton = 0;

            // detailed summary logic
            $sets = [];
            if (isset($carton['items']) && is_array($carton['items'])) {
                foreach ($carton['items'] as $item) {
                    $qty = $item['quantity'];
                    
                    // Fallback to order basic price if selling_price is zero
                    $price = $item['selling_price'] ?? 0;
                    if ($price == 0) {
                        $ops = $item['detail']['order_product_set'] ?? null;
                        if ($ops && ($ops['total_quantity'] ?? 0) > 0) {
                            $price = ($ops['basic_amount'] ?? 0) / $ops['total_quantity'];
                        }
                    }

                    $total_items_in_carton += $qty;
                    $total_items_dispatch += $qty;
                    $total_dispatch_amount += ($qty * $price);

                    $setId = $item['detail']['order_products_set_id'] ?? 0;
                    
                    // Specific design/color identification for CORPORATE orders (via OrderProductSet)
                    $design = $item['detail']['order_product_set']['design_number'] ?? 'N/A';
                    $color = $item['detail']['order_product_set']['colors']['name'] ?? 'N/A';
                    $sizeSet = $item['detail']['order_product_set']['size_measurement']['name'] ?? 'N/A';

                    if (!isset($sets[$setId])) {
                        $sets[$setId] = [
                            'design' => $design,
                            'color' => $color,
                            'size_set' => $sizeSet,
                            'price' => $price,
                            'total_qty' => 0,
                            'sizes_text' => []
                        ];
                    }
                    $sets[$setId]['total_qty'] += $qty;
                    $sets[$setId]['sizes_text'][] = ($item['detail']['size'] ?? 'N/A') . " (" . $qty . ")";

                    // For consolidated grouped items (whole dispatch)
                    $groupId = $design . '_' . $color . '_' . $sizeSet . '_' . $price;
                    if(!isset($consolidatedGroupedItems[$groupId])) {
                        $consolidatedGroupedItems[$groupId] = [
                            'product_name' => $design,
                            'color_name' => $color,
                            'size_set_name' => $sizeSet,
                            'selling_price' => $price,
                            'total_qty' => 0,
                            'box_count' => 0,
                            'carton_ids' => [],
                            'box_ids' => []
                        ];
                    }
                    $consolidatedGroupedItems[$groupId]['total_qty'] += $qty;
                    $consolidatedGroupedItems[$groupId]['carton_ids'][] = $carton['id'];
                }
            }

            $finalCartonData[] = [
                'id' => $carton['id'],
                'carton_no' => $carton['carton_no'] ?? $carton['id'],
                'storeroom' => $carton['rack']['storeroom']['name'] ?? 'N/A',
                'rack' => $carton['rack']['name'] ?? 'N/A',
                'status' => $carton['status'] ?? 1,
                'total_items' => $total_items_in_carton,
                'sets' => array_values($sets), // Grouped by set
            ];
        }

        // Carton count correction for consolidated items
        foreach ($consolidatedGroupedItems as $k => $group) {
             $consolidatedGroupedItems[$k]['carton_count'] = count(array_unique($group['carton_ids']));
             // Keep box_count for backward compatibility if UI uses it
             $consolidatedGroupedItems[$k]['box_count'] = count(array_unique($group['carton_ids']));
        }

        // Process outside vendor purchase items
        if ($order_dispatch_model->orderDispatchPurchases && count($order_dispatch_model->orderDispatchPurchases) > 0) {
            foreach ($order_dispatch_model->orderDispatchPurchases as $odp) {
                $qty = $odp->total_pieces;
                $price = (float)$odp->selling_price;
                $design = $odp->product ? ($odp->product->series_name ?? $odp->product->design_number ?? 'PO Product') : 'PO Product';
                $color = $odp->color ? $odp->color->name : 'N/A';
                $sizeSet = $odp->sizeSet ? $odp->sizeSet->name : 'N/A';
                $vendorName = $odp->purchase && $odp->purchase->vendor ? ($odp->purchase->vendor->company_name ?? $odp->purchase->vendor->name) : 'Vendor';
                $poNumber = $odp->purchase && $odp->purchase->productionPO ? $odp->purchase->productionPO->po_number : 'PO';

                $total_items_dispatch += $qty;
                $total_dispatch_amount += ($qty * $price);

                $groupId = $design . '_' . $color . '_' . $sizeSet . '_' . $price;
                if (!isset($consolidatedGroupedItems[$groupId])) {
                    $consolidatedGroupedItems[$groupId] = [
                        'product_name' => $design,
                        'color_name' => $color,
                        'size_set_name' => $sizeSet,
                        'selling_price' => $price,
                        'total_qty' => 0,
                        'box_count' => 0,
                        'carton_count' => 0,
                        'carton_ids' => [],
                        'box_ids' => []
                    ];
                }
                $consolidatedGroupedItems[$groupId]['total_qty'] += $qty;
                $consolidatedGroupedItems[$groupId]['box_count'] += $odp->boxes_count;
                $consolidatedGroupedItems[$groupId]['carton_count'] += $odp->boxes_count;

                $finalCartonData[] = [
                    'id' => 'PO-' . $odp->id,
                    'carton_no' => 'Box (' . $vendorName . ')',
                    'storeroom' => $odp->rack && $odp->rack->storeroom ? $odp->rack->storeroom->name : 'Vendor Stock',
                    'rack' => $odp->rack ? $odp->rack->name : 'Outside Purchase',
                    'status' => 2,
                    'total_items' => $qty,
                    'sets' => [
                        [
                            'design' => $design,
                            'color' => $color,
                            'size_set' => $sizeSet,
                            'price' => $price,
                            'total_qty' => $qty,
                            'sizes_text' => [$odp->boxes_count . " Boxes x " . $odp->pieces_per_box . " pcs"]
                        ]
                    ]
                ];
            }
        }

        $order_dispatch_data['total_cartons'] = count($finalCartonData);
        $order_dispatch_data['total_items_dispatch'] = $total_items_dispatch;
        $order_dispatch_data['total_dispatch_amount'] = $total_dispatch_amount;

        // same bill rule as the dispatch screen and the invoice edit (other charges included)
        $filteredSubtotal = $total_dispatch_amount;
        $discountAmt = (float) $order_dispatch_data['discount_amount'];
        $otherCharges = (float) ($order_dispatch_model->other_charges ?? 0);
        $gstPercentage = $order_dispatch_model->gst_percentage ?? 5;
        $bill = self::billTotals((float) $filteredSubtotal, $discountAmt, $otherCharges, (float) $gstPercentage);
        $filteredGst = $bill['gst'];
        $filteredGrandTotal = $bill['total'];

        $data = [
            'dispatch' => $order_dispatch_model,
            'order_dispatch_data' => $order_dispatch_data,
            'cartonsDetails' => $finalCartonData,
            'groupedItems' => array_values($consolidatedGroupedItems),
            'settings' => GeneralSettings::first(),
            'filteredSubtotal' => $filteredSubtotal,
            'discountAmt' => $discountAmt,
            'otherCharges' => $otherCharges,
            'filteredGst' => $filteredGst,
            'filteredGrandTotal' => $filteredGrandTotal,
        ];

        return $data;
    }


    function getOrderPackingData($request)
    {
        $search_order_no = $request->search_order_no ?? "";
        $all_unique_sets = [];

        $results = OrderMain::with([
            'customer',
            'dispatchCartons' => function ($q) {
                $q->where('packing_cartons.status', 1)
                  ->whereNotIn('packing_cartons.id', function ($sub) {
                      $sub->select('carton_packing_id')->from('order_dispatch_details');
                  })
                  ->whereNotIn('packing_cartons.id', function ($sub) {
                      $sub->select('packing_carton_id')
                          ->from('domestic_inventories')
                          ->whereNotNull('packing_carton_id')
                          ->where('packing_carton_id', '>', 0);
                  })
                  ->whereNotExists(function ($sub) {
                      $sub->select(DB::raw(1))
                          ->from('domestic_inventories')
                          ->whereColumn('domestic_inventories.packing_main_id', 'packing_cartons.packing_main_id')
                          ->where(function ($w) {
                              $w->whereColumn('domestic_inventories.carton_no', 'packing_cartons.carton_no')
                                ->orWhere(function ($bw) {
                                    $bw->whereNotNull('packing_cartons.barcode')
                                       ->where('packing_cartons.barcode', '!=', '')
                                       ->whereColumn('domestic_inventories.barcode', 'packing_cartons.barcode');
                                });
                          });
                  });
            },
            'dispatchCartons.items.detail.orderProductSet.colors',
            'dispatchCartons.items.detail.orderProductSet.size_measurement', 
        ])
            ->where(function($q) use ($search_order_no) {
                $q->where('sku', $search_order_no)
                  ->orWhere('id', $search_order_no);
            })
            ->where('order_type', 'corporate')
            ->whereIn('status', [1, 2])
            ->orderBy('id', 'asc')
            ->get();

        $data = [];
        foreach ($results as $val) {
            $cartons = [];
            foreach ($val->dispatchCartons as $value) {

                // Aggregate items
                $summary = [];
                $pcs_in_carton = 0;
                $sets_list = [];

                if ($value->items) {
                    foreach ($value->items as $item) {
                        $sizeName = $item->detail->size ?? ($item->size->name ?? 'N/A');
                        $qty = $item->quantity;
                        $pcs_in_carton += $qty;

                        // Summary Text
                        if (!isset($summary[$sizeName])) $summary[$sizeName] = 0;
                        $summary[$sizeName] += $qty;

                        // Set info logic 
                        $orderSet = $item->detail->orderProductSet ?? null;
                        $setId = $orderSet->id ?? 0;

                        if ($setId && !isset($all_unique_sets[$setId])) {
                            $fallbackPrice = ($orderSet->total_quantity > 0) ? ($orderSet->basic_amount / $orderSet->total_quantity) : 0;
                            $fallbackMrp = ($orderSet->total_quantity > 0) ? ($orderSet->total_amount / $orderSet->total_quantity) : 0;
                            $all_unique_sets[$setId] = [
                                'set_id' => $setId,
                                'design' => $orderSet->design_number ?? 'N/A',
                                'color' => $orderSet->colors->name ?? 'N/A',
                                'size_set' => $orderSet->size_measurement->name ?? 'N/A',
                                'suggested_price' => $item->selling_price ?: $fallbackPrice,
                                'mrp' => $item->mrp ?: $fallbackMrp,
                            ];
                        }

                        if ($setId && !isset($sets_list[$setId])) {
                            $fallbackPrice = ($orderSet->total_quantity > 0) ? ($orderSet->basic_amount / $orderSet->total_quantity) : 0;
                            $fallbackMrp = ($orderSet->total_quantity > 0) ? ($orderSet->total_amount / $orderSet->total_quantity) : 0;
                            $sets_list[$setId] = [
                                'set_id' => $setId,
                                'design' => $orderSet->design_number ?? 'N/A',
                                'color' => $orderSet->colors->name ?? 'N/A',
                                'size_set' => $orderSet->size_measurement->name ?? 'N/A',
                                'suggested_price' => $item->selling_price ?: $fallbackPrice,
                                'mrp' => $item->mrp ?: $fallbackMrp,
                                'total_qty' => 0,
                                'sizes_text' => []
                            ];
                        }
                        
                        if($setId) {
                            $sets_list[$setId]['total_qty'] += $qty;
                            $sets_list[$setId]['sizes_text'][] = "$sizeName ($qty)";
                        }
                    }
                }

                $contents_text = [];
                foreach ($summary as $size => $qty) {
                    $contents_text[] = "$size ($qty)";
                }

                $cartons[] = [
                    'id' => $value->id,
                    'carton_no' => $value->carton_no,
                    'boxes_in_carton' => 0,
                    'contents' => implode(', ', $contents_text),
                    'pcs_in_carton' => $pcs_in_carton,
                    'sets' => array_values($sets_list), 
                ];
            }

            // Fetch outside vendor purchases received for this order
            $poPurchases = DomesticInventoryPurchase::whereHas('productionPO', function ($q) use ($val) {
                $q->where('order_main_id', $val->id);
            })->with([
                'productionPO',
                'vendor',
                'items' => function ($q) {
                    $q->whereNotIn('id', function ($sub) {
                        $sub->select('purchase_history_id')
                            ->from('order_dispatch_purchases')
                            ->whereNotNull('purchase_history_id');
                    })->with(['newProduct', 'newColor', 'newSizeSet', 'newRack.storeroom']);
                }
            ])->get();

            $purchasesData = [];
            foreach ($poPurchases as $poPur) {
                foreach ($poPur->items as $hItem) {
                    $prodName = $hItem->newProduct ? ($hItem->newProduct->series_name ?? $hItem->newProduct->name ?? 'N/A') : 'N/A';
                    $designNo = $hItem->newProduct ? ($hItem->newProduct->design_number ?? 'N/A') : 'N/A';
                    $colorName = $hItem->newColor ? $hItem->newColor->name : 'N/A';
                    $sizeSetName = $hItem->newSizeSet ? $hItem->newSizeSet->name : 'N/A';
                    $storeroomName = $hItem->newRack && $hItem->newRack->storeroom ? $hItem->newRack->storeroom->name : 'N/A';
                    $rackName = $hItem->newRack ? $hItem->newRack->name : 'N/A';

                    // Fallback to matching order set selling price if possible
                    $matchingSet = OrderProductSet::where('order_main_id', $val->id)
                        ->where('production_goods_id', $hItem->new_product_id)
                        ->where('color_id', $hItem->new_color_id)
                        ->first();

                    $suggestedPrice = 0;
                    if ($matchingSet && $matchingSet->total_quantity > 0) {
                        $suggestedPrice = round($matchingSet->basic_amount / $matchingSet->total_quantity, 2);
                    }
                    if ($suggestedPrice == 0) {
                        $suggestedPrice = (float)($hItem->mrp ?? 0);
                    }

                    $totalPcs = $hItem->box_quantity * ($hItem->pieces_per_box > 0 ? $hItem->pieces_per_box : 1);

                    // Check physical availability in DomesticInventory
                    $currentStock = DomesticInventory::where('product_id', $hItem->new_product_id)
                        ->where('color_id', $hItem->new_color_id)
                        ->where('size_set_id', $hItem->new_size_set_id)
                        ->where(function($q) use ($hItem) {
                            if ($hItem->new_rack_id) {
                                $q->where('rack_id', $hItem->new_rack_id);
                            }
                        })
                        ->sum('total_boxes');

                    $availableBoxes = min($hItem->box_quantity, (int)$currentStock);

                    $purchasesData[] = [
                        'history_id' => $hItem->id,
                        'purchase_id' => $poPur->id,
                        'po_number' => $poPur->productionPO ? $poPur->productionPO->po_number : 'N/A',
                        'vendor_name' => $poPur->vendor ? ($poPur->vendor->company_name ?? $poPur->vendor->name) : 'N/A',
                        'purchase_date' => $poPur->purchase_date ? date('d-m-Y', strtotime($poPur->purchase_date)) : '',
                        'product_id' => $hItem->new_product_id,
                        'design_number' => $designNo,
                        'product_name' => $prodName,
                        'color_id' => $hItem->new_color_id,
                        'color_name' => $colorName,
                        'size_set_id' => $hItem->new_size_set_id,
                        'size_set_name' => $sizeSetName,
                        'rack_id' => $hItem->new_rack_id,
                        'rack_name' => $rackName,
                        'storeroom_name' => $storeroomName,
                        'box_quantity' => $hItem->box_quantity,
                        'available_boxes' => $availableBoxes,
                        'pieces_per_box' => $hItem->pieces_per_box,
                        'total_pieces' => $totalPcs,
                        'mrp' => (float)($hItem->mrp ?? 0),
                        'suggested_price' => $suggestedPrice,
                        'total_amount' => round($suggestedPrice * $totalPcs, 2)
                    ];
                }
            }

            $data[] = [
                'id' => $val->id,
                'sku' => $val->sku ?? '',
                'master_customer_id' => $val->master_customer_id,
                'customer' => $val->customer->name ?? 'N/A',
                'address' => $val->customer->address ?? '',
                'total_quantity' => $val->dispatchCartons->count() + count($purchasesData),
                'cartons' => $cartons,
                'purchases' => $purchasesData,
                'unique_sets' => array_values($all_unique_sets)
            ];
        }
        return $data;
    }

    function getOrdersByCustomer($request)
    {
        $customer_id = $request->customer_id ?? "";
        $data = OrderMain::where('master_customer_id', $customer_id)
            ->where('order_type', 'corporate')
            ->whereIn('status', [1, 2])
            ->where(function ($query) {
                $query->whereHas('dispatchCartons', function ($q) {
                    $q->where('packing_cartons.status', 1)
                      ->whereNotIn('packing_cartons.id', function ($sub) {
                          $sub->select('carton_packing_id')->from('order_dispatch_details');
                      })
                      ->whereNotIn('packing_cartons.id', function ($sub) {
                          $sub->select('packing_carton_id')
                              ->from('domestic_inventories')
                              ->whereNotNull('packing_carton_id')
                              ->where('packing_carton_id', '>', 0);
                      });
                })
                ->orWhereHas('productionPOs.inventoryPurchases.items', function ($q) {
                    $q->whereNotIn('domestic_inventory_histories.id', function ($sub) {
                        $sub->select('purchase_history_id')->from('order_dispatch_purchases');
                    });
                });
            })
            ->orderBy('id', 'DESC')
            ->get(['id', 'sku as order_no']);

        return $data;
    }

    public function getOrders()
    {
        $data = OrderMain::whereIn('status', [1, 2])
            ->where('order_type', 'corporate')
            ->where(function ($query) {
                $query->whereHas('dispatchCartons', function ($q) {
                    $q->where('packing_cartons.status', 1)
                      ->whereNotIn('packing_cartons.id', function ($sub) {
                          $sub->select('carton_packing_id')->from('order_dispatch_details');
                      })
                      ->whereNotIn('packing_cartons.id', function ($sub) {
                          $sub->select('packing_carton_id')
                              ->from('domestic_inventories')
                              ->whereNotNull('packing_carton_id')
                              ->where('packing_carton_id', '>', 0);
                      });
                })
                ->orWhereHas('productionPOs.inventoryPurchases.items', function ($q) {
                    $q->whereNotIn('domestic_inventory_histories.id', function ($sub) {
                        $sub->select('purchase_history_id')->from('order_dispatch_purchases');
                    });
                });
            })
            ->orderBy('id', 'DESC')
            ->get(['id', 'sku as order_no']);
        return $data;
    }

    public function comppleteOrder()
    {
        return true;
    }
    public function getOrderDispatchData($orderMainId)
    {
        $total = DB::table('order_products_sets')
            ->where('order_main_id', $orderMainId)
            ->sum('total_quantity');

        $pack_mains = PackingMain::with([
            'cartons' => function ($q) {
                $q->whereIn('status', [2, 3]) // Dispatched (2) or Diverted to Domestic Inventory (3)
                    ->withSum('items', 'quantity');
            }
        ])->where('order_main_id', $orderMainId)->get();

        $packed = 0;
        foreach($pack_mains as $session) {
            $packed += $session->cartons->sum('items_sum_quantity');
        }

        return [
            'total' => (int) $total,
            'packed' => (int) $packed,
            'remaining' => max(0, $total - $packed),
        ];
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $dispatch = OrderDispatch::lockForUpdate()->find($id);
            if (!$dispatch) {
                return ['status_code' => 0, 'message' => 'Dispatch record not found.'];
            }

            $customerId = $dispatch->customer_id;
            $orderMainId = $dispatch->main_order_id;
            $amount = (float) $dispatch->total_amount;

            // 1. Get all cartons in this dispatch
            $cartonIds = OrderDispatchDetails::where('order_dispatch_id', $dispatch->id)
                ->pluck('carton_packing_id')
                ->toArray();

            // 2. Restore customer balance
            $customer = \App\Models\MasterCustomer::lockForUpdate()->find($customerId);
            if ($customer) {
                $customer->balance += $amount;
                $customer->save();
            }

            // 3. Delete dispatch details
            OrderDispatchDetails::where('order_dispatch_id', $dispatch->id)->delete();

            // 4. For cartons that are NOT in any other dispatch, reset status back to 1 (Ready for dispatch)
            if (!empty($cartonIds)) {
                $stillDispatchedCartonIds = OrderDispatchDetails::whereIn('carton_packing_id', $cartonIds)
                    ->pluck('carton_packing_id')
                    ->toArray();
                $revertCartonIds = array_diff($cartonIds, $stillDispatchedCartonIds);
                if (!empty($revertCartonIds)) {
                    PackingCarton::whereIn('id', $revertCartonIds)->update(['status' => 1]);
                }
            }

            // 4b. Put the boxes of dispatched purchase items back in their rack (same pieces per box)
            $dispatchPurchases = OrderDispatchPurchase::where('order_dispatch_id', $dispatch->id)->get();
            $stock = app(InventoryStockService::class);
            foreach ($dispatchPurchases as $dp) {
                $row = $stock->rowAt($dp->product_id, $dp->color_id, $dp->size_set_id, $dp->rack_id, (int) $dp->pieces_per_box);
                $stock->add($row, (int) $dp->boxes_count);
            }
            OrderDispatchPurchase::where('order_dispatch_id', $dispatch->id)->delete();

            // 5. Delete dispatch record
            $dispatch->delete();

            // 6. Recalculate order status
            $pack_data = $this->getOrderDispatchData($orderMainId);
            if (!empty($pack_data)) {
                if ($pack_data['remaining'] == 0 && $pack_data['packed'] > 0) {
                    OrderMain::where('id', $orderMainId)->update(['status' => 3]);
                } elseif ($pack_data['packed'] > 0) {
                    OrderMain::where('id', $orderMainId)->update(['status' => 2]);
                } else {
                    OrderMain::where('id', $orderMainId)->update(['status' => 1]);
                }
            }

            DB::commit();
            return ['status_code' => 1, 'message' => 'Dispatch deleted successfully and customer balance updated.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status_code' => 0, 'message' => 'Failed to delete dispatch: ' . $e->getMessage()];
        }
    }

}
