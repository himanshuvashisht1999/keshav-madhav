<?php

namespace App\Http\Controllers\Admin\Ledger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductionGoods;
use App\Models\DomesticInventoryHistory;
use App\Models\DomesticInventory;
use DB;

class ProductionGoodsLedgerController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->getProductionGoodsListData($request, true);
        return view('admin.ledger.production_goods.index', $data);
    }

    public function exportListPdf(Request $request)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '300');
        $data = $this->getProductionGoodsListData($request, false, 150);
        $pdf = \PDF::loadView('admin.ledger.production_goods.list_pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->download('Production_Goods_Ledger_List_' . date('Y-m-d_His') . '.pdf');
    }

    public function exportListExcel(Request $request)
    {
        $data = $this->getProductionGoodsListData($request, false);
        $goods = $data['goods'];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Production Goods Summary');

        // Header Title
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'SNAPKID - Production Goods Ledger Summary');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF1E3C72');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Date
        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', 'Generated on: ' . date('d M Y, h:i A'));
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setARGB('FF666666');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Headers
        $headers = ['Design No', 'Garment Name', 'Series', 'Size Set', 'Inward (Boxes)', 'Outward (Boxes)', 'Balance (Boxes)'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
        foreach ($headers as $idx => $h) {
            $sheet->setCellValue($cols[$idx] . '4', $h);
        }

        $headerRange = 'A4:G4';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3C72');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $row = 5;
        $totalIn = 0;
        $totalOut = 0;
        $totalBal = 0;

        foreach ($goods as $good) {
            foreach ($good->variants as $variant) {
                $in = (float)($variant->total_inward ?? 0);
                $out = (float)($variant->total_outward ?? 0);
                $bal = (float)($variant->current_balance ?? 0);

                $totalIn += $in;
                $totalOut += $out;
                $totalBal += $bal;

                $sheet->setCellValue('A' . $row, $good->design_number ?? '-');
                $sheet->setCellValue('B' . $row, $good->name_of_garment ?? '-');
                $sheet->setCellValue('C' . $row, $good->series?->name ?? '-');
                $sheet->setCellValue('D' . $row, $variant->sizeSet?->name ?? '-');
                $sheet->setCellValue('E' . $row, $in);
                $sheet->setCellValue('F' . $row, $out);
                $sheet->setCellValue('G' . $row, $bal);

                $row++;
            }
        }

        // Summary Row
        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->setCellValue('E' . $row, $totalIn);
        $sheet->setCellValue('F' . $row, $totalOut);
        $sheet->setCellValue('G' . $row, $totalBal);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

        $sheet->getStyle('E5:G' . $row)->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Production_Goods_Ledger_List_' . date('Y-m-d_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempPath = storage_path('app/public/' . $fileName);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    private function getProductionGoodsListData(Request $request, $paginate = true, $limit = null)
    {
        $search = $request->query('search');
        $warehouseIds = $request->query('warehouse_ids', []);
        
        $hasUnassigned = in_array('unassigned', $warehouseIds);
        $filteredWarehouseIds = array_filter($warehouseIds, fn($id) => $id !== 'unassigned');

        $warehouses = \App\Models\Storeroom::where('status', 1)->get();

        $query = ProductionGoods::with(['series', 'variants.sizeSet'])
            ->where('status', 1)
            ->when($search, function ($q) use ($search) {
                $q->where('name_of_garment', 'LIKE', "%$search%")
                  ->orWhere('design_number', 'LIKE', "%$search%");
            })
            ->orderBy('id', 'desc');

        if ($paginate) {
            $goods = $query->paginate(15)->withQueryString();
        } else {
            if ($limit) {
                $query->limit($limit);
            }
            $goods = $query->get();
        }

        foreach ($goods as $good) {
            foreach ($good->variants as $variant) {
                $inwardQuery = DB::table('domestic_inventory_histories')
                    ->where('new_product_id', $good->id)
                    ->where('new_size_set_id', $variant->master_size_measurement_id)
                    ->where('type', '!=', 'transfer');
                
                if (!empty($warehouseIds)) {
                    $inwardQuery->leftJoin('racks', 'domestic_inventory_histories.new_rack_id', '=', 'racks.id')
                        ->where(function($q) use ($filteredWarehouseIds, $hasUnassigned) {
                            if (!empty($filteredWarehouseIds)) {
                                $q->whereIn('domestic_inventory_histories.new_warehouse_id', $filteredWarehouseIds)
                                  ->orWhereIn('racks.storeroom_id', $filteredWarehouseIds);
                            }
                            if ($hasUnassigned) {
                                $q->orWhere(function($sub) {
                                    $sub->whereNull('domestic_inventory_histories.new_warehouse_id')
                                        ->whereNull('racks.storeroom_id');
                                });
                            }
                        });
                }
                
                $variant->total_inward = $inwardQuery->sum('box_quantity');

                $outwardHistoryQuery = DB::table('domestic_inventory_histories')
                    ->where('old_product_id', $good->id)
                    ->where('old_size_set_id', $variant->master_size_measurement_id)
                    ->whereNotIn('type', ['transfer', 'stock_consume']);
                    
                if (!empty($warehouseIds)) {
                    $outwardHistoryQuery->leftJoin('racks', 'domestic_inventory_histories.old_rack_id', '=', 'racks.id')
                        ->where(function($q) use ($filteredWarehouseIds, $hasUnassigned) {
                            if (!empty($filteredWarehouseIds)) {
                                $q->whereIn('domestic_inventory_histories.old_warehouse_id', $filteredWarehouseIds)
                                  ->orWhereIn('racks.storeroom_id', $filteredWarehouseIds);
                            }
                            if ($hasUnassigned) {
                                $q->orWhere(function($sub) {
                                    $sub->whereNull('domestic_inventory_histories.old_warehouse_id')
                                        ->whereNull('racks.storeroom_id');
                                });
                            }
                        });
                }
                
                $historyOutward = $outwardHistoryQuery->sum('box_quantity');

                $orderQuery = DB::table('agent_order_items')
                    ->where('agent_order_items.product_id', $good->id)
                    ->where('agent_order_items.size_set_id', $variant->master_size_measurement_id);
                    
                if (!empty($warehouseIds)) {
                    $orderQuery->leftJoin('racks', 'agent_order_items.rack_id', '=', 'racks.id')
                               ->where(function($q) use ($filteredWarehouseIds, $hasUnassigned) {
                                   if (!empty($filteredWarehouseIds)) {
                                       $q->whereIn('racks.storeroom_id', $filteredWarehouseIds);
                                   }
                                   if ($hasUnassigned) {
                                       $q->orWhereNull('racks.storeroom_id');
                                   }
                               });
                }
                
                $orderOutward = $orderQuery->sum('agent_order_items.box_qty');

                $variant->total_outward = $historyOutward + $orderOutward;
                $variant->current_balance = $variant->total_inward - $variant->total_outward;
            }
        }

        $inwardTotalQuery = DB::table('domestic_inventory_histories')
            ->join('production_goods', 'domestic_inventory_histories.new_product_id', '=', 'production_goods.id')
            ->where('production_goods.status', 1)
            ->where('domestic_inventory_histories.type', '!=', 'transfer');
            
        if ($search) {
            $inwardTotalQuery->where(function($q) use ($search) {
                $q->where('production_goods.name_of_garment', 'LIKE', "%$search%")
                  ->orWhere('production_goods.design_number', 'LIKE', "%$search%");
            });
        }
        
        if (!empty($warehouseIds)) {
            $inwardTotalQuery->leftJoin('racks', 'domestic_inventory_histories.new_rack_id', '=', 'racks.id')
                ->where(function($q) use ($filteredWarehouseIds, $hasUnassigned) {
                    if (!empty($filteredWarehouseIds)) {
                        $q->whereIn('domestic_inventory_histories.new_warehouse_id', $filteredWarehouseIds)
                          ->orWhereIn('racks.storeroom_id', $filteredWarehouseIds);
                    }
                    if ($hasUnassigned) {
                        $q->orWhere(function($sub) {
                            $sub->whereNull('domestic_inventory_histories.new_warehouse_id')
                                ->whereNull('racks.storeroom_id');
                        });
                    }
                });
        }
        $totalInwardOverall = $inwardTotalQuery->sum('domestic_inventory_histories.box_quantity');

        $outwardHistoryTotalQuery = DB::table('domestic_inventory_histories')
            ->join('production_goods', 'domestic_inventory_histories.old_product_id', '=', 'production_goods.id')
            ->where('production_goods.status', 1)
            ->whereNotIn('domestic_inventory_histories.type', ['transfer', 'stock_consume']);
            
        if ($search) {
            $outwardHistoryTotalQuery->where(function($q) use ($search) {
                $q->where('production_goods.name_of_garment', 'LIKE', "%$search%")
                  ->orWhere('production_goods.design_number', 'LIKE', "%$search%");
            });
        }
        
        if (!empty($warehouseIds)) {
            $outwardHistoryTotalQuery->leftJoin('racks', 'domestic_inventory_histories.old_rack_id', '=', 'racks.id')
                ->where(function($q) use ($filteredWarehouseIds, $hasUnassigned) {
                    if (!empty($filteredWarehouseIds)) {
                        $q->whereIn('domestic_inventory_histories.old_warehouse_id', $filteredWarehouseIds)
                          ->orWhereIn('racks.storeroom_id', $filteredWarehouseIds);
                    }
                    if ($hasUnassigned) {
                        $q->orWhere(function($sub) {
                            $sub->whereNull('domestic_inventory_histories.old_warehouse_id')
                                ->whereNull('racks.storeroom_id');
                        });
                    }
                });
        }
        $totalOutwardHistoryOverall = $outwardHistoryTotalQuery->sum('domestic_inventory_histories.box_quantity');

        $orderTotalQuery = DB::table('agent_order_items')
            ->join('production_goods', 'agent_order_items.product_id', '=', 'production_goods.id')
            ->where('production_goods.status', 1);
            
        if ($search) {
            $orderTotalQuery->where(function($q) use ($search) {
                $q->where('production_goods.name_of_garment', 'LIKE', "%$search%")
                  ->orWhere('production_goods.design_number', 'LIKE', "%$search%");
            });
        }
        
        if (!empty($warehouseIds)) {
            $orderTotalQuery->leftJoin('racks', 'agent_order_items.rack_id', '=', 'racks.id')
                ->where(function($q) use ($filteredWarehouseIds, $hasUnassigned) {
                    if (!empty($filteredWarehouseIds)) {
                        $q->whereIn('racks.storeroom_id', $filteredWarehouseIds);
                    }
                    if ($hasUnassigned) {
                        $q->orWhereNull('racks.storeroom_id');
                    }
                });
        }
        $totalOutwardOrderOverall = $orderTotalQuery->sum('agent_order_items.box_qty');
        
        $totalOutwardOverall = $totalOutwardHistoryOverall + $totalOutwardOrderOverall;
        $totalBalanceOverall = $totalInwardOverall - $totalOutwardOverall;

        return compact(
            'goods', 'search', 'warehouses', 'warehouseIds',
            'totalInwardOverall', 'totalOutwardOverall', 'totalBalanceOverall'
        );
    }

    public function show(Request $request, $id, $size_set_id)
    {
        $data = $this->getLedgerData($request, $id, $size_set_id);
        return view('admin.ledger.production_goods.show', $data);
    }

    public function exportPdf(Request $request, $id, $size_set_id)
    {
        $data = $this->getLedgerData($request, $id, $size_set_id);
        $pdf = \PDF::loadView('admin.ledger.production_goods.pdf', $data);
        $safeDesign = preg_replace('/[^A-Za-z0-9_\-]/', '_', $data['good']->design_number ?? 'Good');
        $safeSize = preg_replace('/[^A-Za-z0-9_\-]/', '_', $data['sizeSet']->name ?? 'Size');
        $name = 'Production_Goods_Ledger_' . $safeDesign . '_' . $safeSize . '_' . date('Y-m-d') . '.pdf';
        return $pdf->download($name);
    }

    public function exportExcel(Request $request, $id, $size_set_id)
    {
        $data = $this->getLedgerData($request, $id, $size_set_id);
        $good = $data['good'];
        $sizeSet = $data['sizeSet'];
        $transactions = $data['transactions'];
        $openingBalanceAmount = $data['openingBalanceAmount'];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Goods Ledger');

        // Header Title
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'SNAPKID - Production Goods Ledger');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF1E3C72');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Subtitle
        $sheet->mergeCells('A2:F2');
        $periodText = 'All Dates';
        if ($data['startDate'] && $data['endDate']) {
            $periodText = $data['startDate'] . ' to ' . $data['endDate'];
        } elseif ($data['startDate']) {
            $periodText = 'From ' . $data['startDate'];
        } elseif ($data['endDate']) {
            $periodText = 'Up to ' . $data['endDate'];
        }
        $sheet->setCellValue('A2', 'Design: ' . ($good->design_number ?? '-') . ' | Garment: ' . ($good->name_of_garment ?? '-') . ' | Size: ' . ($sizeSet->name ?? '-') . ' | Period: ' . $periodText);
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setARGB('FF666666');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Headers
        $headers = ['Date', 'Type', 'Particulars', 'Inward (Boxes)', 'Outward (Boxes)', 'Balance (Boxes)'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F'];
        foreach ($headers as $idx => $h) {
            $sheet->setCellValue($cols[$idx] . '4', $h);
        }

        $headerRange = 'A4:F4';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3C72');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $row = 5;
        $totalInward = 0;
        $totalOutward = 0;

        // Opening balance row
        $sheet->setCellValue('A' . $row, $data['startDate'] ? \Carbon\Carbon::parse($data['startDate'])->format('d M Y') : '-');
        $sheet->setCellValue('B' . $row, 'Opening');
        $sheet->setCellValue('C' . $row, 'Opening Stock B/F');
        $sheet->setCellValue('D' . $row, 0);
        $sheet->setCellValue('E' . $row, 0);
        $sheet->setCellValue('F' . $row, $openingBalanceAmount);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setItalic(true);
        $row++;

        foreach ($transactions as $tx) {
            $in = (float)($tx->inward ?? 0);
            $out = (float)($tx->outward ?? 0);
            $bal = (float)($tx->running_balance ?? 0);

            $totalInward += $in;
            $totalOutward += $out;

            $sheet->setCellValue('A' . $row, $tx->date ? \Carbon\Carbon::parse($tx->date)->format('d M Y, h:i A') : '-');
            $sheet->setCellValue('B' . $row, $tx->type ?? '-');
            $sheet->setCellValue('C' . $row, $tx->particulars ?? '-');
            $sheet->setCellValue('D' . $row, $in);
            $sheet->setCellValue('E' . $row, $out);
            $sheet->setCellValue('F' . $row, $bal);

            $row++;
        }

        // Total Row
        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->setCellValue('D' . $row, $totalInward);
        $sheet->setCellValue('E' . $row, $totalOutward);
        $finalBal = end($transactions) ? end($transactions)->running_balance : $openingBalanceAmount;
        $sheet->setCellValue('F' . $row, $finalBal);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

        $sheet->getStyle('D5:F' . $row)->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $safeDesign = preg_replace('/[^A-Za-z0-9_\-]/', '_', $good->design_number ?? 'Good');
        $safeSize = preg_replace('/[^A-Za-z0-9_\-]/', '_', $sizeSet->name ?? 'Size');
        $fileName = 'Production_Goods_Ledger_' . $safeDesign . '_' . $safeSize . '_' . date('Y-m-d_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempPath = storage_path('app/public/' . $fileName);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    private function getLedgerData(Request $request, $id, $size_set_id)
    {
        $good = ProductionGoods::with('series')->findOrFail($id);
        $sizeSet = \App\Models\MasterSizeMeasurement::findOrFail($size_set_id);
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $warehouseIds = $request->query('warehouse_ids', []);
        
        $hasUnassigned = in_array('unassigned', $warehouseIds);
        $filteredWarehouseIds = array_filter($warehouseIds, fn($id) => $id !== 'unassigned');

        // Fetch all related history records (excluding transfer and stock_consume)
        $histories = DomesticInventoryHistory::select('domestic_inventory_histories.*', 'domestic_inventory_purchases.purchase_date')
            ->leftJoin('racks as new_rack', 'domestic_inventory_histories.new_rack_id', '=', 'new_rack.id')
            ->leftJoin('racks as old_rack', 'domestic_inventory_histories.old_rack_id', '=', 'old_rack.id')
            ->leftJoin('domestic_inventory_purchases', 'domestic_inventory_histories.purchase_id', '=', 'domestic_inventory_purchases.id')
            ->where(function($q) use ($id, $size_set_id, $warehouseIds, $filteredWarehouseIds, $hasUnassigned) {
                $q->where(function($q1) use ($id, $size_set_id, $warehouseIds, $filteredWarehouseIds, $hasUnassigned) {
                    $q1->where('domestic_inventory_histories.old_product_id', $id)
                       ->where('domestic_inventory_histories.old_size_set_id', $size_set_id);
                    if (!empty($warehouseIds)) {
                        $q1->where(function($q1a) use ($filteredWarehouseIds, $hasUnassigned) {
                            if (!empty($filteredWarehouseIds)) {
                                $q1a->whereIn('domestic_inventory_histories.old_warehouse_id', $filteredWarehouseIds)
                                    ->orWhereIn('old_rack.storeroom_id', $filteredWarehouseIds);
                            }
                            if ($hasUnassigned) {
                                $q1a->orWhere(function($sub) {
                                    $sub->whereNull('domestic_inventory_histories.old_warehouse_id')
                                        ->whereNull('old_rack.storeroom_id');
                                });
                            }
                        });
                    }
                })
                ->orWhere(function($q2) use ($id, $size_set_id, $warehouseIds, $filteredWarehouseIds, $hasUnassigned) {
                    $q2->where('domestic_inventory_histories.new_product_id', $id)
                       ->where('domestic_inventory_histories.new_size_set_id', $size_set_id);
                    if (!empty($warehouseIds)) {
                        $q2->where(function($q2a) use ($filteredWarehouseIds, $hasUnassigned) {
                            if (!empty($filteredWarehouseIds)) {
                                $q2a->whereIn('domestic_inventory_histories.new_warehouse_id', $filteredWarehouseIds)
                                    ->orWhereIn('new_rack.storeroom_id', $filteredWarehouseIds);
                            }
                            if ($hasUnassigned) {
                                $q2a->orWhere(function($sub) {
                                    $sub->whereNull('domestic_inventory_histories.new_warehouse_id')
                                        ->whereNull('new_rack.storeroom_id');
                                });
                            }
                        });
                    }
                });
            })
            ->whereNotIn('domestic_inventory_histories.type', ['transfer', 'stock_consume'])
            ->when($startDate, fn($q) => $q->whereDate('domestic_inventory_histories.created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('domestic_inventory_histories.created_at', '<=', $endDate))
            ->orderBy('domestic_inventory_histories.created_at', 'asc')
            ->with(['newRack', 'oldRack'])
            ->get();

        $transactions = collect();

        foreach ($histories as $history) {
            $actualNewWarehouseId = $history->new_warehouse_id ?? $history->newRack?->storeroom_id ?? null;
            // Inward logic
            $matchesWarehouseInward = empty($warehouseIds) || 
                                      ($hasUnassigned && is_null($actualNewWarehouseId)) || 
                                      (!empty($filteredWarehouseIds) && in_array($actualNewWarehouseId, $filteredWarehouseIds));
                                      
            if ($history->new_product_id == $id && $history->new_size_set_id == $size_set_id && $matchesWarehouseInward) {
                $particulars = 'Inward / ' . ucfirst(str_replace('_', ' ', $history->type));
                if ($history->type === 'creation') $particulars = 'Production / Stock In';
                if ($history->type === 'attribute_change') $particulars = 'Attribute Change (In)';
                if ($history->type === 'Edit (Restored)') $particulars = 'Stock Restored';

                $transactions->push((object)[
                    'date' => $history->purchase_date ? \Carbon\Carbon::parse($history->purchase_date) : $history->created_at,
                    'type' => 'Inward',
                    'particulars' => $particulars,
                    'inward' => (int)$history->box_quantity,
                    'outward' => 0,
                    'remarks' => $history->remarks ?: 'View Details',
                    'link' => route('admin.inventory.attribute-history.show', $history->id)
                ]);
            }
            
            $actualOldWarehouseId = $history->old_warehouse_id ?? $history->oldRack?->storeroom_id ?? null;
            // Outward logic (excluding stock_consume as it is now covered by orders)
            $matchesWarehouseOutward = empty($warehouseIds) || 
                                       ($hasUnassigned && is_null($actualOldWarehouseId)) || 
                                       (!empty($filteredWarehouseIds) && in_array($actualOldWarehouseId, $filteredWarehouseIds));
                                       
            if ($history->old_product_id == $id && $history->old_size_set_id == $size_set_id && $matchesWarehouseOutward) {
                $particulars = 'Outward / ' . ucfirst(str_replace('_', ' ', $history->type));
                if ($history->type === 'deletion') $particulars = 'Stock Deletion';
                if ($history->type === 'attribute_change') $particulars = 'Attribute Change (Out)';

                $transactions->push((object)[
                    'date' => $history->created_at,
                    'type' => 'Outward',
                    'particulars' => $particulars,
                    'inward' => 0,
                    'outward' => (int)$history->box_quantity,
                    'remarks' => $history->remarks ?: 'View Details',
                    'link' => route('admin.inventory.attribute-history.show', $history->id)
                ]);
            }
        }

        // Fetch Order Items as Outward, grouped by order to prevent duplicate rows
        $orderQuery = DB::table('agent_order_items')
            ->join('agent_orders', 'agent_order_items.agent_order_id', '=', 'agent_orders.id')
            ->leftJoin('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
            ->leftJoin('vendors', 'agent_orders.master_vendor_id', '=', 'vendors.id')
            ->where('agent_order_items.product_id', $id)
            ->where('agent_order_items.size_set_id', $size_set_id);
            
        if (!empty($warehouseIds)) {
            $orderQuery->leftJoin('racks', 'agent_order_items.rack_id', '=', 'racks.id')
                       ->where(function($q) use ($filteredWarehouseIds, $hasUnassigned) {
                           if (!empty($filteredWarehouseIds)) $q->whereIn('racks.storeroom_id', $filteredWarehouseIds);
                           if ($hasUnassigned) $q->orWhereNull('racks.storeroom_id');
                       });
        }
            
        $orderItems = $orderQuery->when($startDate, fn($q) => $q->whereDate('agent_order_items.created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('agent_order_items.created_at', '<=', $endDate))
            ->select(
                DB::raw('MIN(agent_order_items.created_at) as date'),
                DB::raw('SUM(agent_order_items.box_qty) as outward'),
                'agent_orders.id as remarks',
                'agent_orders.party_type',
                'master_customers.name as customer_name',
                'vendors.name as vendor_name'
            )
            ->groupBy('agent_orders.id', 'agent_orders.party_type', 'master_customers.name', 'vendors.name')
            ->get();

        foreach ($orderItems as $order) {
            $partyName = $order->party_type === 'vendor' ? $order->vendor_name : $order->customer_name;
            $partyNameStr = $partyName ? " ($partyName)" : '';
            
            $transactions->push((object)[
                'date' => $order->date,
                'type' => 'Outward',
                'particulars' => 'Order Added',
                'inward' => 0,
                'outward' => (int)$order->outward,
                'remarks' => 'Order No: ' . $order->remarks . $partyNameStr,
                'link' => route('admin.agent-orders.show', $order->remarks)
            ]);
        }

        // Calculate Opening Balance
        $openingBalanceAmount = 0;
        if ($startDate) {
            $inwardBeforeQuery = DB::table('domestic_inventory_histories')
                ->where('new_product_id', $id)
                ->where('new_size_set_id', $size_set_id)
                ->whereNotIn('type', ['transfer', 'stock_consume'])
                ->whereDate('domestic_inventory_histories.created_at', '<', $startDate);
                
            if (!empty($warehouseIds)) {
                $inwardBeforeQuery->leftJoin('racks as new_rack', 'domestic_inventory_histories.new_rack_id', '=', 'new_rack.id')
                    ->where(function($q) use ($filteredWarehouseIds, $hasUnassigned) {
                        if (!empty($filteredWarehouseIds)) {
                            $q->whereIn('domestic_inventory_histories.new_warehouse_id', $filteredWarehouseIds)
                              ->orWhereIn('new_rack.storeroom_id', $filteredWarehouseIds);
                        }
                        if ($hasUnassigned) {
                            $q->orWhere(function($sub) {
                                $sub->whereNull('domestic_inventory_histories.new_warehouse_id')
                                    ->whereNull('new_rack.storeroom_id');
                            });
                        }
                    });
            }
            $inwardBefore = $inwardBeforeQuery->sum('box_quantity');

            $outwardBeforeQuery = DB::table('domestic_inventory_histories')
                ->where('old_product_id', $id)
                ->where('old_size_set_id', $size_set_id)
                ->whereNotIn('type', ['transfer', 'stock_consume'])
                ->whereDate('domestic_inventory_histories.created_at', '<', $startDate);
                
            if (!empty($warehouseIds)) {
                $outwardBeforeQuery->leftJoin('racks as old_rack', 'domestic_inventory_histories.old_rack_id', '=', 'old_rack.id')
                    ->where(function($q) use ($filteredWarehouseIds, $hasUnassigned) {
                        if (!empty($filteredWarehouseIds)) {
                            $q->whereIn('domestic_inventory_histories.old_warehouse_id', $filteredWarehouseIds)
                              ->orWhereIn('old_rack.storeroom_id', $filteredWarehouseIds);
                        }
                        if ($hasUnassigned) {
                            $q->orWhere(function($sub) {
                                $sub->whereNull('domestic_inventory_histories.old_warehouse_id')
                                    ->whereNull('old_rack.storeroom_id');
                            });
                        }
                    });
            }
            $historyOutwardBefore = $outwardBeforeQuery->sum('box_quantity');

            $orderOutwardBeforeQuery = DB::table('agent_order_items')
                ->where('agent_order_items.product_id', $id)
                ->where('agent_order_items.size_set_id', $size_set_id)
                ->whereDate('agent_order_items.created_at', '<', $startDate);
                
            if (!empty($warehouseIds)) {
                $orderOutwardBeforeQuery->leftJoin('racks', 'agent_order_items.rack_id', '=', 'racks.id')
                                        ->where(function($q) use ($filteredWarehouseIds, $hasUnassigned) {
                                            if (!empty($filteredWarehouseIds)) $q->whereIn('racks.storeroom_id', $filteredWarehouseIds);
                                            if ($hasUnassigned) $q->orWhereNull('racks.storeroom_id');
                                        });
            }
            $orderOutwardBefore = $orderOutwardBeforeQuery->sum('agent_order_items.box_qty');

            $outwardBefore = $historyOutwardBefore + $orderOutwardBefore;

            $openingBalanceAmount = $inwardBefore - $outwardBefore;
        }

        // Running balance calculation
        $transactions = $transactions->sortBy('date')->values();
        $balance = $openingBalanceAmount;
        foreach ($transactions as $tx) {
            $balance += ($tx->inward - $tx->outward);
            $tx->running_balance = $balance;
        }

        $warehouses = !empty($filteredWarehouseIds) ? \App\Models\Storeroom::whereIn('id', $filteredWarehouseIds)->get() : collect();
        if ($hasUnassigned) {
            $warehouses->push((object)['id' => 'unassigned', 'name' => 'Unassigned (No Warehouse)']);
        }

        return compact('good', 'sizeSet', 'transactions', 'startDate', 'endDate', 'openingBalanceAmount', 'warehouses', 'warehouseIds');
    }
}
