<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\PackingService;

class PackingController extends Controller
{
    protected $service;

    public function __construct(PackingService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return view('admin.packing.index');
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function view($id)
    {
        $order = $this->service->getPackingDetailsForOrder($id);
        return view('admin.packing.view', compact('order'));
    }

    public function process($slip_id)
    {
        $slip = $this->service->getSlipDetails($slip_id);
        if ($slip->status == 1) {
            return redirect()->back()->withError('Already digitized slip.');
        }

        $packing = $this->service->getPackingMainWithStructure($slip_id);

        // If packing exists, get the linked order
        $order = null;
        if ($packing && $packing->order_main_id) {
            $order = \App\Models\OrderMain::with('customer', 'OrderProductSets.product_set_details', 'OrderProductSets.colors')->find($packing->order_main_id);
        } else if ($slip->sku) {
            // Fallback to SKU link if exists (legacy support)
            $order = $this->service->getOrderDetails($slip->sku);
        }

        $active_orders = [];
        $packed_quantities = [];
        $order_sets = [];

        if (!$order) {
            // Fetch orders for dropdown if no order is linked yet
            $active_orders = \App\Models\OrderMain::with('customer')->orderBy('id', 'desc')->get();
        } else {
            $packed_quantities = $this->service->getPackedQuantitiesForOrder($order->id);
            // Logic to prepare sets (duplicated from JSON method for initial load)
            $order_sets = $order->OrderProductSets->map(function ($set) use ($packed_quantities) {
                $set_total_qty = $set->set_quantity > 0 ? $set->set_quantity : 1;
                $min_packed_sets = null;
                $details = $set->product_set_details->map(function ($detail) use ($packed_quantities, $set_total_qty, $set) {
                    $item = $detail->toArray();
                    $item['packed_qty'] = $packed_quantities[$detail->id] ?? 0;
                    $item['qty_per_set'] = $detail->total_quantity / $set_total_qty;
                    $item['design_number'] = $set->design_number;
                    $item['color_name'] = $set->colors ? $set->colors->name : 'N/A';
                    return (object) $item;
                });
                foreach ($details as $detail) {
                    if ($detail->qty_per_set > 0) {
                        $sets_packed_for_this_detail = floor($detail->packed_qty / $detail->qty_per_set);
                        if ($min_packed_sets === null || $sets_packed_for_this_detail < $min_packed_sets) {
                            $min_packed_sets = $sets_packed_for_this_detail;
                        }
                    }
                }
                $set->packed_sets = $min_packed_sets ?? 0;
                $set->details_data = $details; // Pass details
                return $set;
            });
        }

        $storerooms = \App\Models\Storeroom::with('racks')->where('status', 1)->get();

        return view('admin.packing.process', compact('slip', 'order', 'packing', 'storerooms', 'active_orders', 'packed_quantities', 'order_sets'));
    }

    public function getOrderDetailsJson($id)
    {
        $order = \App\Models\OrderMain::with(['OrderProductSets.product_set_details', 'OrderProductSets.colors'])->findOrFail($id);

        $packed = $this->service->getPackedQuantitiesForOrder($id); // [detail_id => packed_qty]

        // Prepare Sets Data
        $sets = $order->OrderProductSets->map(function ($set) use ($packed) {
            $set_total_qty = $set->set_quantity > 0 ? $set->set_quantity : 1;
            $min_packed_sets = null;

            $set->details = $set->product_set_details->map(function ($detail) use ($packed, $set_total_qty, $set) {
                $item = $detail->toArray();
                $item['packed_qty'] = $packed[$detail->id] ?? 0;
                $item['qty_per_set'] = $detail->total_quantity / $set_total_qty;
                $item['design_number'] = $set->design_number;
                $item['color_name'] = $set->colors ? $set->colors->name : 'N/A';
                return (object) $item;
            });

            // Calculate how many full sets have been packed
            // Based on the detail with the LOWEST relative completion
            foreach ($set->details as $detail) {
                if ($detail->qty_per_set > 0) {
                    $sets_packed_for_this_detail = floor($detail->packed_qty / $detail->qty_per_set);
                    if ($min_packed_sets === null || $sets_packed_for_this_detail < $min_packed_sets) {
                        $min_packed_sets = $sets_packed_for_this_detail;
                    }
                }
            }
            $set->packed_sets = $min_packed_sets ?? 0;
            return $set;
        });

        // Flatten items for legacy view (if needed)
        $items = $sets->flatMap(function ($set) {
            return $set->details->map(function ($detail) use ($set) {
                // Details are already updated in the map above
                return $detail;
            });
        });

        return response()->json([
            'status' => 'success',
            'order' => $order,
            'items' => $items,
            'sets' => $sets
        ]);
    }

    public function checkCartonNo(Request $request)
    {
        $result = $this->service->checkCartonNo($request->carton_no);
        return response()->json([
            'exists' => $result
        ]);
    }
    // API/AJAX Methods
    public function saveCarton(Request $request)
    {
        $data = $request->all();
        // Validation logic here
        $result = $this->service->saveCarton($data);
        return response()->json($result);
    }

    public function bulkSaveCarton(Request $request)
    {
        $data = $request->all();
        $result = $this->service->bulkSaveCarton($data);
        return response()->json($result);
    }

    public function saveBox(Request $request)
    {
        $data = $request->all();
        $result = $this->service->saveBox($data);
        return response()->json($result);
    }

    public function finalize(Request $request)
    {
        $result = $this->service->finalizePacking($request->packing_main_id);
        return response()->json($result);
    }

    public function createSet(Request $request)
    {
        $data = $request->all();
        $result = $this->service->createAdHocSet($data);
        return response()->json($result);
    }

    public function labels($type, $id)
    {
        $query = \App\Models\DomesticInventory::query();

        if ($type == 'main') {
            $query->where('packing_main_id', $id);
        } elseif ($type == 'carton') {
            $query->where('packing_carton_id', $id);
        } elseif ($type == 'box') {
            $query->where('packing_box_id', $id);
        } else {
            return abort(404);
        }

        $labels = $query->get();

        if ($labels->isEmpty()) {
            return redirect()->back()->withError('No labels found for this record.');
        }

        // Use DomPDF to generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.packing.labels_print', compact('labels'));

        // Set paper size to A4
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('labels-' . $type . '-' . $id . '.pdf');
    }

    public function deleteCarton(Request $request)
    {
        $result = $this->service->deleteCarton($request->carton_id);
        return response()->json($result);
    }
}