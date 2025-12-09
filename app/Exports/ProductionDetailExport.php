<?php

namespace App\Exports;

use App\Models\OrderMain;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ProductionDetailExport implements FromView
{
    protected $orderMain;

    public function __construct(OrderMain $orderMain)
    {
        $this->orderMain = $orderMain;
    }

    public function view(): View
    {
        return view('admin.reports.production_detail_excel', [
            'orderMain' => $this->orderMain,
        ]);
    }
}
