<?php

namespace App\Services\Admin\Master;

use Illuminate\Http\Request;
use App\Models\ItemAttributeValue;

class ItemAttributeValueService
{
    public function getItemAttributeValueById(Request $request)
    {
        $data = ItemAttributeValue::where('id', $request->id)->first();
        return $data;
    }

    public function getItemAttributeValueBySku(Request $request)
    {
        $data = ItemAttributeValue::where('sku', $request->sku)->first();
        return $data;
    }
}
