<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\Order;
use App\Models\OrderMain;
use App\Models\StageMasterUnit;
use App\Models\ProductionSlipDigitization;
use App\Models\ProductionSlipDigitizationParts;

class PackingService {

    public function lot_numbers(){
        $data = ProductionSlipDigitizationParts::where('to_stage_id',11)->get();
        return $data;
    }
    
}
