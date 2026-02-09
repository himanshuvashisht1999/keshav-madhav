<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\InventoryPriceImage;
use Illuminate\Http\Request;

class InventoryPriceImageController extends Controller
{
    public function delete(Request $request)
    {
        $image = InventoryPriceImage::find($request->id);
        if ($image) {
            if (file_exists(public_path('uploads/inventory_prices/' . $image->image_path))) {
                unlink(public_path('uploads/inventory_prices/' . $image->image_path));
            }
            $image->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }
}
