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

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $selectedCartons = !empty($request->cartons) && is_array($request->cartons) ? $request->cartons : [];
            $selectedPurchaseItems = !empty($request->purchase_items) && is_array($request->purchase_items) ? $request->purchase_items : [];

            // Safety check: at least one carton or purchase item
            if (empty($selectedCartons) && empty($selectedPurchaseItems)) {
                return [
                    'status_code' => 0,
                    'message' => 'No cartons or purchase items selected for dispatch'
                ];
            }

            // Guard against duplicate carton dispatch: Check if already in order_dispatch_details
            if (!empty($selectedCartons)) {
                $alreadyInDetails = OrderDispatchDetails::whereIn('carton_packing_id', $selectedCartons)
                    ->pluck('carton_packing_id')
                    ->toArray();
                if (!empty($alreadyInDetails)) {
                    return [
                        'status_code' => 0,
                        'message' => 'Carton(s) #' . implode(', #', $alreadyInDetails) . ' have already been dispatched. Duplicate dispatch prevented.'
                    ];
                }

                $alreadyDispatched = PackingCarton::whereIn('id', $selectedCartons)
                    ->where('status', 2)
                    ->pluck('id')
                    ->toArray();
                if (!empty($alreadyDispatched)) {
                    return [
                        'status_code' => 0,
                        'message' => 'Carton(s) #' . implode(', #', $alreadyDispatched) . ' are already marked as dispatched.'
                    ];
                }
            }

            // Guard against duplicate purchase items
            if (!empty($selectedPurchaseItems)) {
                $historyIds = [];
                foreach ($selectedPurchaseItems as $pi) {
                    if (!empty($pi['history_id'])) {
                        $historyIds[] = $pi['history_id'];
                    }
                }
                if (!empty($historyIds)) {
                    $alreadyDispatchedPur = OrderDispatchPurchase::whereIn('purchase_history_id', $historyIds)
                        ->pluck('purchase_history_id')
                        ->toArray();
                    if (!empty($alreadyDispatchedPur)) {
                        return [
                            'status_code' => 0,
                            'message' => 'Some selected purchase items have already been dispatched.'
                        ];
                    }
                }
            }

            // Count total units
            $totalPurchaseBoxes = 0;
            foreach ($selectedPurchaseItems as $pi) {
                $totalPurchaseBoxes += intval($pi['boxes'] ?? 0);
            }

            // ================= MAIN DISPATCH =================
            $data_save = new OrderDispatch();
            $data_save->customer_id = $request->final_customer_id ?? $request->master_customer_id;
            $data_save->main_order_id = $request->final_order_no ?? $request->order_no;
            $data_save->dispatch_date = $request->dispatch_date ?? now();
            $data_save->bill_number = $request->bill_number;
            $data_save->company_id = $request->company_id;
            $data_save->total_quantity = count($selectedCartons) + $totalPurchaseBoxes;
            $data_save->gst_percentage = $request->gst_percentage ?? 0.00;
            $data_save->gst_amount = $request->gst_amount ?? 0.00;
            $data_save->discount_percentage = $request->discount_percentage ?? 0.00;
            $data_save->discount_amount = $request->discount_amount ?? 0.00;
            $data_save->total_amount = $request->total_amount ?? 0.00;
            $data_save->other_charges = $request->other_charges ?? 0.00;
            $data_save->remark = $request->remark;
            $data_save->status = 1;
            $data_save->save();

            $data_save->sku = date('d/m/Y') . '/' . $data_save->main_order_id . "/" . $data_save->customer_id . "/" . $data_save->id ?? $data_save->id;
            $data_save->save();

            // Update Customer Balance (Subtraction means they owe the admin more)
            $customer = \App\Models\MasterCustomer::find($data_save->customer_id);
            if ($customer) {
                $customer->balance -= $data_save->total_amount;
                $customer->save();
            }

            // ================= UPDATE CARTONS (IF ANY) =================
            if (!empty($selectedCartons)) {
                if (!empty($request->global_prices) || !empty($request->global_mrps)) {
                    foreach ($request->global_prices ?? [] as $setId => $newPrice) {
                        $newMrp = $request->global_mrps[$setId] ?? null;
                        $detailIds = \App\Models\OrderProductSetDetail::where('order_products_set_id', $setId)->pluck('id');
                        
                        $updateData = ['selling_price' => (float)$newPrice];
                        if ($newMrp !== null && $newMrp !== '') {
                            $updateData['mrp'] = (float)$newMrp;
                        }
                        
                        PackingItem::whereIn('packing_carton_id', $selectedCartons)
                            ->whereIn('size_id', $detailIds)
                            ->update($updateData);
                    }
                }

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
            if (!empty($selectedPurchaseItems)) {
                foreach ($selectedPurchaseItems as $pi) {
                    $historyId = $pi['history_id'] ?? null;
                    if (!$historyId) continue;
                    $history = DomesticInventoryHistory::find($historyId);
                    if (!$history) continue;

                    $boxes = intval($pi['boxes'] ?? $history->box_quantity);
                    $piecesPerBox = intval($history->pieces_per_box > 0 ? $history->pieces_per_box : 1);
                    $totalPieces = $boxes * $piecesPerBox;
                    $sellingPrice = floatval($pi['price'] ?? 0);
                    $mrp = floatval($pi['mrp'] ?? $history->mrp ?? 0);
                    $totalAmount = round($sellingPrice * $totalPieces, 2);

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
                        'mrp' => $mrp,
                        'selling_price' => $sellingPrice,
                        'total_amount' => $totalAmount,
                        'status' => 1
                    ]);

                    // Deduct from DomesticInventory
                    $inv = DomesticInventory::where('product_id', $history->new_product_id)
                        ->where('color_id', $history->new_color_id)
                        ->where('size_set_id', $history->new_size_set_id)
                        ->where(function($q) use ($history) {
                            if ($history->new_rack_id) {
                                $q->where('rack_id', $history->new_rack_id);
                            }
                        })
                        ->where('total_boxes', '>', 0)
                        ->first();

                    if ($inv) {
                        $inv->total_boxes = max(0, $inv->total_boxes - $boxes);
                        $inv->save();
                    }
                }
            }

            $pack_data = $this->getOrderDispatchData($data_save->main_order_id);
            if (!empty($pack_data) && $pack_data['remaining'] == 0) {
                OrderMain::where('id', $data_save->main_order_id)
                    ->update([
                        'status' => 3
                    ]);
            } elseif (!empty($pack_data) && ($pack_data['packed'] > 0 || !empty($selectedPurchaseItems))) {
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

        $filteredSubtotal = $total_dispatch_amount;
        $discountAmt = $order_dispatch_data['discount_amount'];
        $gstPercentage = $order_dispatch_model->gst_percentage ?? 5;
        $filteredGst = (($filteredSubtotal - $discountAmt) * $gstPercentage) / 100;
        $filteredGrandTotal = ($filteredSubtotal - $discountAmt) + $filteredGst;

        $data = [
            'dispatch' => $order_dispatch_model,
            'order_dispatch_data' => $order_dispatch_data,
            'cartonsDetails' => $finalCartonData,
            'groupedItems' => array_values($consolidatedGroupedItems),
            'settings' => GeneralSettings::first(),
            'filteredSubtotal' => $filteredSubtotal,
            'discountAmt' => $discountAmt,
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
            $dispatch = OrderDispatch::find($id);
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
            $customer = \App\Models\MasterCustomer::find($customerId);
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

            // 4b. Restore stock for any dispatched purchase items
            $dispatchPurchases = OrderDispatchPurchase::where('order_dispatch_id', $dispatch->id)->get();
            foreach ($dispatchPurchases as $dp) {
                $inv = DomesticInventory::where('product_id', $dp->product_id)
                    ->where('color_id', $dp->color_id)
                    ->where('size_set_id', $dp->size_set_id)
                    ->where(function($q) use ($dp) {
                        if ($dp->rack_id) {
                            $q->where('rack_id', $dp->rack_id);
                        }
                    })
                    ->first();
                if ($inv) {
                    $inv->total_boxes += $dp->boxes_count;
                    $inv->save();
                }
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
