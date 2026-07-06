<?php
$path = 'app/Http/Controllers/Unit/UnitAuthController.php';
$content = file_get_contents($path);

// 1. Replace assignments query part
$search1 = <<<'EOT'
        $lotNo = $request->get('lot_no');
        $orderNo = $request->get('order_no');
        $customerSearch = $orderNo; 

        if ($isCutting) {
EOT;
$replace1 = <<<'EOT'
        $lotNo = $request->get('lot_no');
        $orderNo = $request->get('order_no');
        $customerSearch = $orderNo; 
        $activity = $request->get('activity', 'received');

        $effectiveIsCutting = ($activity === 'sent') ? false : $isCutting;

        if ($effectiveIsCutting) {
EOT;

$search2 = <<<'EOT'
        } else {
            $type = 'other';
            $ass1Query = \App\Models\OrderStageTransaction::where('sub_stage_id_to', $unitId)->with(['from_stage', 'getFromUnitMaster', 'orderProduct.orderProductSet.order_cutting_stage']);
            $ass2Query = \App\Models\OrderPrintingStageTransaction::where('sub_stage_id_to', $unitId)->with(['from_stage', 'getFromUnitMaster', 'orderProduct.orderProductSet.order_cutting_stage']);
            $ass3Query = \App\Models\OrderPrintingToStichingTransaction::where('sub_stage_id_to', $unitId)->with(['from_stage', 'getFromUnitMaster', 'orderProduct.orderProductSet.order_cutting_stage']);
EOT;
$replace2 = <<<'EOT'
        } else {
            $type = 'other';
            $column = ($activity === 'sent') ? 'sub_stage_id' : 'sub_stage_id_to';

            $ass1Query = \App\Models\OrderStageTransaction::where($column, $unitId)->with(['from_stage', 'to_stage', 'getFromUnitMaster', 'orderProduct.orderProductSet.order_cutting_stage']);
            $ass2Query = \App\Models\OrderPrintingStageTransaction::where($column, $unitId)->with(['from_stage', 'to_stage', 'getFromUnitMaster', 'orderProduct.orderProductSet.order_cutting_stage']);
            $ass3Query = \App\Models\OrderPrintingToStichingTransaction::where($column, $unitId)->with(['from_stage', 'to_stage', 'getFromUnitMaster', 'orderProduct.orderProductSet.order_cutting_stage']);
EOT;

$search3 = <<<'EOT'
                if ($view === 'closed') {
                    $ass1Query->whereNotNull('image');
                    $ass2Query->whereNotNull('image');
                    $ass3Query->whereNotNull('image');
EOT;
$replace3 = <<<'EOT'
                if ($view === 'closed') {
                    if ($activity !== 'sent') {
                        $ass1Query->whereNotNull('image');
                        $ass2Query->whereNotNull('image');
                        $ass3Query->whereNotNull('image');
                    }
EOT;

$content = str_replace($search1, $replace1, $content);
$content = str_replace($search2, $replace2, $content);
$content = str_replace($search3, $replace3, $content);

// 2. Append the new methods
$newMethods = <<<'EOT'

    public function lotSearch(Request $request)
    {
        if (!session()->has('unit_auth')) return redirect()->route('unit.login');
        return view('unit.lot_search');
    }

    public function lotDetails(Request $request)
    {
        if (!session()->has('unit_auth')) return redirect()->route('unit.login');

        if (!$request->filled('lot_no')) {
            return redirect()->route('unit.lot.search')->with('error', 'Please enter a Lot Number.');
        }

        $service = app(\App\Services\ReportService::class);
        $response['data'] = $service->lotDetails($request->lot_no);

        if (empty($response['data'])) {
            return redirect()->route('unit.lot.search')->with('error', 'Lot not found or has been completely deleted.');
        }

        $response['master_stages'] = $service->master_stages();
        return view('unit.lot_details', $response);
    }
}
EOT;

$content = rtrim($content);
if (substr($content, -1) === '}') {
    $content = substr($content, 0, -1) . $newMethods;
}

file_put_contents($path, $content);
echo "Updated!";
