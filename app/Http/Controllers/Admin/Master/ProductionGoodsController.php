<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\ProductionGoodsService as Service;
use App\Requests\Admin\Master\ProductionGoodsStoreRequest;
use App\Requests\Admin\Master\ProductionGoodsUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use App\Services\Admin\ProductOrderService;
use Auth;

class ProductionGoodsController extends Controller { 
    protected $service;
    public function __construct(Service $service, ProductOrderService $ProductOrderService) {
        $this->service = $service;
        $this->ProductOrderService = $ProductOrderService;
    }
    public function index(){
        $response['colors'] = $this->service->colors();
        $response['sizes'] = $this->service->sizes();
        $response['designs'] = $this->service->designs();
        $response['materials'] = $this->service->materials();
        $response['fabrics'] = $this->service->fabrics();
 
        return view('admin.master.production-goods.index',$response);
    }
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        $response['sizes'] = $this->service->sizes();
        $response['fabrics'] = $this->service->fabrics();
        $response['product_types'] = $this->service->product_types();
        $response['customers'] = $this->ProductOrderService->customers();
        // dd($response['garment_types']);
        $response['garment_patterns'] = $this->service->garment_patterns();
        $response['colors'] = $this->service->colors();
        $response['product_stages'] = $this->service->product_stages();
        $response['series_names'] = $this->service->series();
        $response['brands'] = $this->service->brands();
        $response['fittings'] = $this->service->fittings();
        $response['product_natures'] = $this->service->productNatures();
        $response['fabric_types'] = $this->service->fabricTypes();
        return view('admin.master.production-goods.create',$response);
    }
    public function store(ProductionGoodsStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.production-goods.index')->withSuccess('The product has been successfully created.');
    }
    public function delete(Request $request){
        $product = \App\Models\ProductionGoods::with(['variants', 'items'])->find($request->id);
        if ($product) {
            log_deletion('Master Product (Style)', $request->id, [
                'product'  => $product->toArray(),
                'variants' => $product->variants ? $product->variants->toArray() : [],
                'items'    => $product->items ? $product->items->toArray() : [],
            ]);
        }
        $res = $this->service->delete($request);
        if ($res === true) {
            return redirect()->route('admin.master.production-goods.index')->withSuccess('The product has been successfully deleted.');
        } else {
            return redirect()->route('admin.master.production-goods.index')->withError($res);
        }
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        $existing_size_ids = $response['data'] ? $response['data']->variants->pluck('master_size_measurement_id')->filter()->unique()->toArray() : [];
        $response['sizes'] = $this->service->sizes($existing_size_ids);
        $response['fabrics'] = $this->service->fabrics();
        $response['product_types'] = $this->service->product_types();
        $response['garment_patterns'] = $this->service->garment_patterns();
        $response['colors'] = $this->service->colors();
        $response['product_stages'] = $this->service->product_stages();
        $response['series_names'] = $this->service->series();
        $response['brands'] = $this->service->brands();
        $response['fittings'] = $this->service->fittings();
        $response['product_natures'] = $this->service->productNatures();
        $response['fabric_types'] = $this->service->fabricTypes();
        return view('admin.master.production-goods.edit',$response);
    }
    public function view(Request $request){
        $response['data'] = $this->service->edit($request);
        $existing_size_ids = $response['data'] ? $response['data']->variants->pluck('master_size_measurement_id')->filter()->unique()->toArray() : [];
        $response['sizes'] = $this->service->sizes($existing_size_ids);
        $response['fabrics'] = $this->service->fabrics();
        $response['product_types'] = $this->service->product_types();
        $response['garment_patterns'] = $this->service->garment_patterns();
        $response['colors'] = $this->service->colors();
        $response['product_stages'] = $this->service->product_stages();
        $response['series_names'] = $this->service->series();
        $response['brands'] = $this->service->brands();
        $response['fittings'] = $this->service->fittings();
        $response['product_natures'] = $this->service->productNatures();
        $response['fabric_types'] = $this->service->fabricTypes();
        return view('admin.master.production-goods.view',$response);
    }
    public function update(ProductionGoodsUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.production-goods.index')->withSuccess('The product has been successfully updated.');
    }
    public function getNextProductName(Request $request){
        $nextName = $this->service->getNextProductName($request->master_series_id);
        return response()->json(['next_name' => $nextName]);
    }

    public function checkDesignNumber(Request $request) {
        $designNumber = $request->design_number;
        $id = $request->id;

        $query = \App\Models\ProductionGoods::where('design_number', $designNumber)
            ->where('status', '!=', 3);
        if ($id) {
            $query->where('id', '!=', $id);
        }
        
        return response()->json(['exists' => $query->exists()]);
    }

    public function export(Request $request)
    {
        $data = $this->service->exportData($request);

        return response()
            ->view('admin.master.production-goods.export', [
                'data' => $data,
                'exportedAt' => now()
            ])
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header(
                'Content-Disposition',
                'attachment; filename="product-master-report-' . now()->format('d-m-Y_H-i') . '.xls"'
            );
    }

    public function pdf(Request $request)
    {
        $data = $this->service->exportData($request);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.master.production-goods.export', [
            'data' => $data,
            'exportedAt' => now()
        ])->setPaper('A4', 'landscape');

        return $pdf->download('product-master-report-' . now()->format('d-m-Y_H-i') . '.pdf');
    }

    public function busyMasterExcel(Request $request)
    {
        $data = $this->service->exportData($request);
        $data->load('variants.sizeSet');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Busy Master');

        $headers = ['Design Number', 'Item Name', 'MRP'];
        foreach ($headers as $i => $h) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . '1';
            $sheet->setCellValue($cell, $h);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        $row = 2;
        foreach ($data as $product) {
            $seriesName = $product->series ? $product->series->name : '';
            $garmentName = $product->name_of_garment;
            $designNumber = $product->design_number;
            
            if ($product->variants->count() > 0) {
                foreach ($product->variants as $variant) {
                    $sizeName = $variant->sizeSet ? $variant->sizeSet->name : '';
                    $itemName = trim($seriesName . ' ' . $garmentName . ' ' . $sizeName);
                    
                    $sheet->setCellValue('A' . $row, $designNumber);
                    $sheet->setCellValue('B' . $row, strtoupper($itemName));
                    $sheet->setCellValue('C' . $row, $variant->mrp);
                    $row++;
                }
            } else {
                $sizeName = $product->size ? $product->size->name : '';
                $itemName = trim($seriesName . ' ' . $garmentName . ' ' . $sizeName);
                
                $sheet->setCellValue('A' . $row, $designNumber);
                $sheet->setCellValue('B' . $row, strtoupper($itemName));
                $sheet->setCellValue('C' . $row, '');
                $row++;
            }
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Busy_Master_Excel_' . now()->format('d-m-Y_H-i') . '.xlsx';
        
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

}