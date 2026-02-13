<?php

namespace App\Http\Controllers\Admin\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\InventoryPriceService as Service;
use App\Models\ProductionGoods;
use App\Models\MasterColor;
use App\Models\InventoryPrice;
use App\Models\MasterSizeMeasurement;

class InventoryPriceController extends Controller
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $designs = ProductionGoods::where('status', 1)->get();
        $colors = MasterColor::where('status', 1)->orderBy('name')->get();
        $sizeSets = MasterSizeMeasurement::where('status', 1)->get();
        return view('admin.master.inventory_pricing.index', compact('designs', 'colors', 'sizeSets'));
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function create()
    {
        $designs = ProductionGoods::where('status', 1)->orderBy('design_number')->get();
        $colors = MasterColor::where('status', 1)->orderBy('name')->get();
        $sizeSets = MasterSizeMeasurement::where('status', 1)->get();
        $pendingItems = $this->service->getPendingPricingItems();
        return view('admin.master.inventory_pricing.create', compact('designs', 'colors', 'sizeSets', 'pendingItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'design_id' => 'required|exists:production_goods,id',
            'color_ids' => 'required|array',
            'mrp' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'name' => 'required',
            'size_set_id' => 'required|exists:master_size_measurements,id',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
        ]);

        $this->service->store($request);
        return redirect()->route('admin.master.inventory-price.index')->withSuccess('Inventory prices have been successfully updated.');
    }

    public function edit(Request $request)
    {
        $data = InventoryPrice::with('images')->find($request->id);
        $designs = ProductionGoods::where('status', 1)->get();
        $colors = MasterColor::where('status', 1)->get();
        $sizeSets = MasterSizeMeasurement::where('status', 1)->get();
        return view('admin.master.inventory_pricing.edit', compact('data', 'designs', 'colors', 'sizeSets'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:inventory_prices,id',
            'mrp' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
            'status' => 'required|in:1,0',
        ]);

        $this->service->update($request);
        return redirect()->route('admin.master.inventory-price.index')->withSuccess('Inventory price has been successfully updated.');
    }

    public function delete(Request $request)
    {
        $price = InventoryPrice::with('images')->find($request->id);
        if ($price) {
            $price->status = 0;
            $price->save();
            return redirect()->route('admin.master.inventory-price.index')->withSuccess('Inventory price has been successfully deactivated.');
        }
        return redirect()->route('admin.master.inventory-price.index')->withError('Inventory price not found.');
    }
}
