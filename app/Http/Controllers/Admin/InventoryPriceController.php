<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomesticInventoryImage;
use App\Models\DomesticInventory;
use App\Models\ProductionGoods;
use App\Models\MasterColor;
use App\Models\MasterSizeMeasurement;
use App\Models\MasterProductFitting;
use App\Models\MasterDesignPattern;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryPriceController extends Controller
{
    public function index(Request $request)
    {
        $query = DomesticInventoryImage::query()->with([
            'product', 'color', 'sizeSet', 'fitting', 'pattern'
        ]);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('color_id')) {
            $query->where('color_id', $request->color_id);
        }

        $prices = $query->orderBy('id', 'desc')->paginate(50)->appends($request->except('page'));

        $products = ProductionGoods::all();
        $colors = MasterColor::all();

        return view('admin.inventory_prices.index', compact('prices', 'products', 'colors'));
    }

    public function updatePrice(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:domestic_inventory_images,id',
            'mrp' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();
            
            $priceImage = DomesticInventoryImage::findOrFail($request->id);
            $priceImage->mrp = $request->mrp;
            $priceImage->save();


            DB::commit();

            return response()->json(['success' => true, 'message' => 'Price updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error updating price: ' . $e->getMessage()], 500);
        }
    }

    public function create()
    {
        $products = ProductionGoods::with('series')->get();
        $colors = MasterColor::all();
        $fittings = MasterProductFitting::all();
        $patterns = MasterDesignPattern::all();
        $size_sets = MasterSizeMeasurement::all();

        return view('admin.inventory_prices.create', compact('products', 'colors', 'fittings', 'patterns', 'size_sets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'color_id' => 'required|array',
            'size_set_id' => 'required',
            'mrp' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $product = ProductionGoods::with('series')->find($request->product_id);
        $seriesName = ($product && $product->series) ? $product->series->name : '';
        $product_name = trim($seriesName . ' ' . ($product ? $product->name_of_garment : ''));

        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = time() . '_' . uniqid() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/inventory_prices'), $imageName);
        }

        $colorsToProcess = $request->color_id ?? [];
        foreach ($colorsToProcess as $color_id) {
            $data = [
                'product_id' => $request->product_id,
                'product_name' => $product_name,
                'color_id' => $color_id,
                'size_set_id' => $request->size_set_id,
                'fitting_id' => null,
                'pattern_id' => null,
                'mrp' => $request->mrp,
                'is_main' => 1,
                'status' => 1
            ];

            if ($imageName) {
                $data['image_path'] = $imageName;
            }

            DomesticInventoryImage::updateOrCreate(
                [
                    'product_id' => $request->product_id,
                    'color_id' => $color_id,
                    'size_set_id' => $request->size_set_id,
                    'is_main' => 1
                ],
                $data
            );
        }

        return redirect()->route('admin.inventory-prices.index')->with('success', 'Pricing profile saved successfully.');
    }
}
