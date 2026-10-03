<?php

namespace App\Http\Controllers\Admin\Payment\Voucher;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Payment\Voucher\ConsumableVoucherService as Service;
use App\Models\ConsumableGood;
use Auth;

class ConsumableVoucherController extends Controller
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $response['consumableGoods'] = ConsumableGood::where('status', 1)->orderBy('name')->get();
        return view('admin.payment.voucher.consumable.index', $response);
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function create()
    {
        $response['consumableGoods'] = ConsumableGood::where('status', 1)->get();
        return view('admin.payment.voucher.consumable.create', $response);
    }

    public function store(Request $request)
    {
        $this->service->store($request);
        return redirect()->route('admin.payment.voucher.consumable.index')->withSuccess('The consumable voucher has been successfully created.');
    }

    public function show(Request $request)
    {
        $response['data'] = $this->service->show($request);
        if (!$response['data']) {
            abort(404, 'Consumable voucher not found.');
        }
        return view('admin.payment.voucher.consumable.show', $response);
    }

    public function edit(Request $request)
    {
        $response['data'] = $this->service->edit($request);
        $response['consumableGoods'] = ConsumableGood::where('status', 1)->get();
        return view('admin.payment.voucher.consumable.edit', $response);
    }

    public function update(Request $request)
    {
        $this->service->update($request);
        return redirect()->route('admin.payment.voucher.consumable.index')->withSuccess('The consumable voucher has been successfully updated.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request);
        return redirect()->route('admin.payment.voucher.consumable.index')->withSuccess('The consumable voucher has been successfully deleted.');
    }
}
