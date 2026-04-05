<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Payment\Master\CompanyCapitalService as Service;
use App\Requests\Admin\Master\CompanyCapitalStoreRequest;
use Auth;

class CompanyCapitalController extends Controller
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $response['summary'] = $this->service->getSummary();
        $response['methods'] = $this->service->getPaymentMethods();
        return view('admin.payment.master.company_capital.index', $response);
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function create()
    {
        $response['methods'] = $this->service->getPaymentMethods();
        return view('admin.payment.master.company_capital.create', $response);
    }

    public function store(CompanyCapitalStoreRequest $request)
    {
        $this->service->store($request);
        return redirect()->route('admin.payment.master.company_capital.index')->withSuccess('Capital has been successfully added.');
    }
}
