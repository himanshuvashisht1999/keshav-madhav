<?php

namespace App\Http\Controllers\Admin\Payment\Voucher;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Payment\Voucher\ContractorVoucherService as Service;
use App\Models\Contractor;
use App\Models\FabricRollAssigning;
use Auth;

class ContractorVoucherController extends Controller
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('admin.payment.voucher.contractor.index');
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function create()
    {
        $response['contractors'] = Contractor::where('status', 1)->get();
        $response['lots'] = FabricRollAssigning::select('id', 'lot_no')->groupBy('lot_no')->get();
        return view('admin.payment.voucher.contractor.create', $response);
    }

    public function store(Request $request)
    {
        $this->service->store($request);
        return redirect()->route('admin.payment.voucher.contractor.index')->withSuccess('The contractor voucher has been successfully created.');
    }

    public function edit(Request $request)
    {
        $response['data'] = $this->service->edit($request);
        $response['contractors'] = Contractor::where('status', 1)->get();
        $response['lots'] = FabricRollAssigning::select('id', 'lot_no')->groupBy('lot_no')->get();
        return view('admin.payment.voucher.contractor.edit', $response);
    }

    public function update(Request $request)
    {
        $this->service->update($request);
        return redirect()->route('admin.payment.voucher.contractor.index')->withSuccess('The contractor voucher has been successfully updated.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request);
        return redirect()->route('admin.payment.voucher.contractor.index')->withSuccess('The contractor voucher has been successfully deleted.');
    }
}
