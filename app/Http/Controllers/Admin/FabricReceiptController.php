<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FabricReturn;
use App\Services\Admin\FabricReceiptService as Service;
use App\Requests\Admin\FabricReceiptStoreRequest;
use App\Requests\Admin\FabricReceiptUpdateRequest;
use App\Requests\Admin\FabricReceiptDetailStoreRequest;
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
        $response['purchase_orders'] = \App\Models\PurchaseOrder::with('vendor')->where('status', 1)->where('is_closed', 0)->orderBy('id', 'desc')->get(); // Only pending POs
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
        $data = $this->service->store($request);
        return redirect()->route('admin.fabric_receipt.detail', ['id' => $data])->withSuccess('The fabric receipt has been successfully created.');
    }
    public function detail(Request $request)
    {
        $response['data'] = $this->service->view($request);
        $response['vendors'] = $this->service->vendors();
        $response['fabrics'] = $this->service->fabric_list_by_vendor($response['data']->vendor_id);

        $response['new_batch_no'] = $this->service->new_batch_no();

        $request->merge(['vendor_id' => $response['data']->vendor_id]);
        $response['purchase_orders'] = $this->service->purchase_orders($request);
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
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withError($e->getMessage());
        }
    }
    public function storeDetail(FabricReceiptDetailStoreRequest $request)
    {
        $data = $this->service->storeDetail($request);
        return redirect()->route('admin.fabric_receipt.index')->withSuccess('The fabric receipt detail has been successfully created.');
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
            return redirect()->route('admin.fabric_receipt.index')->withError('Delete Failed! This shipment has existing payment adjustments or used rolls.');
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
        $receipt = \App\Models\FabricReceipt::find($request->receipt_id);
        if ($receipt && $request->hasFile('challan_photo')) {
            $image = $request->file('challan_photo');
            $extImage = $image->getClientOriginalExtension();
            $imgName = "challan-image-" . rand() . "_" . time() . "." . $extImage;
            $destinationPath = public_path() . '/assets/receipts/challan-image';
            $image->move($destinationPath, $imgName);
            $receipt->challan_photo = $imgName;
            $receipt->save();
        }
        return redirect()->back()->withSuccess('Challan photo has been successfully updated.');
    }

    public function uploadOtherImages(Request $request)
    {
        $receipt = \App\Models\FabricReceipt::find($request->receipt_id);
        if ($receipt && $request->hasFile('other_images')) {
            foreach ($request->file('other_images') as $image) {
                $extImage = $image->getClientOriginalExtension();
                $imgName = "other-image-" . rand() . "_" . time() . "." . $extImage;
                $destinationPath = public_path() . '/assets/receipts/other-images';
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $image->move($destinationPath, $imgName);

                \App\Models\FabricReceiptOtherImage::create([
                    'fabric_receipt_id' => $receipt->id,
                    'image' => $imgName
                ]);
            }
        }
        return redirect()->back()->withSuccess('Other images have been successfully uploaded.');
    }

    public function returnFabric(Request $request)
    {
        $status = $this->service->returnFabric($request);
        if ($status) {
            return response()->json(['success' => true, 'message' => 'Fabric returned successfully.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Return failed! Fabric might be already used or not found.'], 400);
        }
    }

    public function returnPage(Request $request)
    {
        $response['data'] = $this->service->view($request);
        return view('admin.fabric_receipt.return', $response);
    }

    public function storeReturn(Request $request)
    {
        $this->service->storeReturn($request);
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
        
        $response['data'] = $response['return']->receipt;
        return view('admin.fabric_receipt.edit_return', $response);
    }

    public function updateReturn(Request $request)
    {
        $this->service->updateReturn($request);
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
        $data = \App\Models\PurchaseOrder::with('vendor')->where('status', 1)->where('is_closed', 0)->orderBy('id', 'desc')->get();
        return response()->json($data);
    }
}