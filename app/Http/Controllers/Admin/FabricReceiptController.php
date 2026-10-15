<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FabricReturn;
use App\Services\Admin\FabricReceiptService as Service;
use App\Requests\Admin\FabricReceiptStoreRequest;
use App\Requests\Admin\FabricReceiptUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class FabricReceiptController extends Controller
{
    protected $service;
    public function __construct(Service $service)
    {
        $this->service = $service;
    }
    public function index()
    {
        $response['vendors'] = $this->service->vendors();
        $response['cutting_units'] = $this->service->cutting_units();
        return view('admin.fabric_receipt.index', $response);
    }
    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }
    public function create()
    {
        $response['vendors'] = $this->service->vendors();
        $response['cutting_units'] = $this->service->cutting_units();
        $response['purchase_orders'] = $this->service->pendingPurchaseOrders(); // only POs with fabric still to receive
        $vendor_id = 0;
        $response['fabrics'] = $this->service->fabric_list_by_vendor($vendor_id);
        return view('admin.fabric_receipt.create_new', $response);
    }

    public function vendorFabrics($vendor_id)
    {
        $fabrics = $this->service->fabric_list_by_vendor($vendor_id);

        $payload = $fabrics->map(function ($f) {
            return [
                'id' => $f->id,
                'name' => $f->name,
                'sku' => $f->sku ?? '',
            ];
        });

        return response()->json($payload);
    }
    public function store(FabricReceiptStoreRequest $request)
    {
        try {
            $data = $this->service->store($request);
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()->with('error', 'The shipment was not saved: ' . $e->getMessage());
        }
        return redirect()->route('admin.fabric_receipt.index')->withSuccess('The fabric receipt has been successfully created.');
    }
    public function getPurchaseOrderItems($id)
    {
        $fabrics = \DB::table('purchase_order_items')
            ->join('fabrics', 'purchase_order_items.fabric_id', '=', 'fabrics.id')
            ->where('purchase_order_items.purchase_order_id', $id)
            ->select('fabrics.id', 'fabrics.name', 'fabrics.sku')
            ->distinct()
            ->get();
        
        return response()->json($fabrics);
    }

    public function edit(Request $request)
    {
        $response['data'] = $this->service->edit($request);
        if (!$response['data']) {
            return redirect()->route('admin.fabric_receipt.index')->with('error', 'Shipment not found.');
        }
        // rolls already used somewhere cannot be removed from the shipment
        $response['used_rolls'] = \App\Services\Admin\FabricPurchaseLink::usedRolls($response['data']->details->pluck('id')->all());
        $response['vendors'] = $this->service->vendors();
        $response['cutting_units'] = $this->service->cutting_units();
        $response['fabrics'] = $this->service->fabrics();

        return view('admin.fabric_receipt.edit', $response);
    }
    public function update(FabricReceiptUpdateRequest $request)
    {
        try {
            $data = $this->service->update($request);
            return redirect()->route('admin.fabric_receipt.index')->withSuccess('The fabric receipt has been successfully updated.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->withError($e->getMessage());
        }
    }
    public function view(Request $request)
    {
        $response['data'] = $this->service->view($request);
        return view('admin.fabric_receipt.view', $response);
    }
    public function downloadReport(Request $request)
    {
        return $this->service->downloadReport($request);
    }
    public function delete(Request $request)
    {
        $status = $this->service->delete($request);
        if ($status) {
            return redirect()->route('admin.fabric_receipt.index')->withSuccess('The fabric receipt has been successfully deleted.');
        } else {
            return redirect()->route('admin.fabric_receipt.index')->withError('Delete Failed! This shipment has payments, returns or rolls that are already used.');
        }
    }

    public function scan(Request $request)
    {
        $response['detail'] = $this->service->scan($request);
        return view('admin.fabric_receipt.scan', $response);
    }

    public function checkRollNo(Request $request)
    {
        $exists = $this->service->checkRollNo($request);
        return response()->json([
            'exists' => $exists
        ]);
    }

    public function checkBillNo(Request $request)
    {
        $exists = $this->service->checkBillNo($request);
        return response()->json([
            'exists' => $exists
        ]);
    }
    public function uploadChallan(Request $request)
    {
        $check = \Validator::make($request->all(), ['challan_photo' => 'required|' . Service::UPLOAD_RULE], [], ['challan_photo' => 'challan']);
        if ($check->fails()) {
            return redirect()->back()->with('error', $check->errors()->first());
        }
        $receipt = \App\Models\FabricReceipt::find($request->receipt_id);
        if ($receipt) {
            $receipt->challan_photo = $this->service->saveUpload($request->file('challan_photo'), 'challan-image', 'challan-image');
            $receipt->save();
        }
        return redirect()->back()->withSuccess('Challan photo has been successfully updated.');
    }

    public function uploadOtherImages(Request $request)
    {
        $check = \Validator::make($request->all(), ['other_images' => 'required|array', 'other_images.*' => Service::UPLOAD_RULE], [], ['other_images.*' => 'image']);
        if ($check->fails()) {
            return redirect()->back()->with('error', $check->errors()->first());
        }
        $receipt = \App\Models\FabricReceipt::find($request->receipt_id);
        if ($receipt) {
            foreach ($request->file('other_images') as $image) {
                \App\Models\FabricReceiptOtherImage::create([
                    'fabric_receipt_id' => $receipt->id,
                    'image' => $this->service->saveUpload($image, 'other-images', 'other-image')
                ]);
            }
        }
        return redirect()->back()->withSuccess('Other images have been successfully uploaded.');
    }

    public function returnPage(Request $request)
    {
        $response['data'] = $this->service->view($request);
        return view('admin.fabric_receipt.return', $response);
    }

    public function storeReturn(Request $request)
    {
        try {
            $this->service->storeReturn($request);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'The return was not saved: ' . $e->getMessage());
        }
        return redirect()->route('admin.fabric_receipt.view', ['id' => $request->fabric_receipt_id])->withSuccess('Fabric return has been successfully processed.');
    }

    public function downloadReturnReport(Request $request)
    {
        return $this->service->downloadReturnReport($request);
    }

    public function deleteReturn($id)
    {
        $this->service->deleteReturn($id);
        return redirect()->back()->withSuccess('Fabric return record has been successfully deleted.');
    }

    public function editReturnPage($id)
    {
        $response['return'] = FabricReturn::with(['details.receipt_detail', 'receipt.details.fabric'])->find($id);
        if (!$response['return']) abort(404);
        if ($this->service->returnHasOtherShipments($response['return'])) {
            return redirect()->route('admin.fabric_return.view', $id)->with('error', 'This return has rolls of several shipments, so it is shown here in Fabric Return.');
        }
        
        $response['data'] = $response['return']->receipt;
        return view('admin.fabric_receipt.edit_return', $response);
    }

    public function updateReturn(Request $request)
    {
        try {
            $this->service->updateReturn($request);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'The return was not saved: ' . $e->getMessage());
        }
        return redirect()->route('admin.fabric_receipt.view', ['id' => $request->fabric_receipt_id])->withSuccess('Fabric return has been successfully updated.');
    }

    public function deleteOtherImage($id)
    {
        $image = \App\Models\FabricReceiptOtherImage::find($id);
        if ($image) {
            $path = public_path('assets/receipts/other-images/' . $image->image);
            if (file_exists($path) && $image->image) {
                unlink($path);
            }
            $image->delete();
            return redirect()->back()->withSuccess('Image deleted successfully.');
        }
        return redirect()->back()->withError('Image not found.');
    }

    public function allWarehouses()
    {
        return response()->json($this->service->cutting_units());
    }

    public function allPendingPOs()
    {
        return response()->json($this->service->pendingPurchaseOrders());
    }
}