<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductionGoods;
use App\Models\MasterSizeMeasurement;
use App\Models\MasterColor;
use App\Models\MasterDesignPattern;
use App\Models\MasterProductFitting;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Storeroom;
use App\Models\Rack;

class BarcodeGeneratorController extends Controller
{
    public function index()
    {
        $designs = ProductionGoods::with('series')->where('status', 1)->orderBy('design_number')->get();
        $sizeSets = MasterSizeMeasurement::whereIn('status', [1, 2])->orderBy('name')->get();
        $colors = MasterColor::where('status', 1)->orderBy('name')->get();
        $patterns = MasterDesignPattern::where('status', 1)->orderBy('name')->get();
        $fittings = MasterProductFitting::where('status', 1)->orderBy('name')->get();
        
        $storerooms = Storeroom::where('status', 1)->orderBy('name')->get();
        $racks = Rack::where('status', 1)->orderBy('name')->get();

        return view('admin.inventory.barcode_generator.index', compact(
            'designs',
            'sizeSets',
            'colors',
            'patterns',
            'fittings',
            'storerooms',
            'racks'
        ));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'design_id' => 'required|exists:production_goods,id',

            'color_ids' => 'required|array',
            'color_ids.*' => 'required|exists:master_colors,id',
            'size_set_ids' => 'required|array',
            'size_set_ids.*' => 'required|exists:master_size_measurements,id',
            'quantities' => 'required|array',
            'quantities.*' => 'required|integer|min:1|max:500',
        ]);

        $design = ProductionGoods::with('series')->find($request->design_id);
        $pattern = $design->pattern;
        $fitting = $design->fitting;

        $labels = [];

        foreach ($request->color_ids as $key => $color_id) {
            if (!$color_id)
                continue;
            $color = MasterColor::find($color_id);
            $sizeSet = MasterSizeMeasurement::find($request->size_set_ids[$key]);
            $quantity = $request->quantities[$key];

            $seriesName = $design->series->name ?? '';
            $productName = $design->name_of_garment ?? '';
            $fullProductName = $seriesName . ' ' . $productName;

            // Generate a unique barcode string
            $barcode = 'D' . $design->id . 'S' . $sizeSet->id . 'C' . $color->id;

            for ($i = 0; $i < $quantity; $i++) {
                $labels[] = (object) [
                    'product_name' => $fullProductName,
                    'fitting_name' => $fitting->name,
                    'pattern_name' => $pattern->name,
                    'size_group' => $sizeSet->name,
                    'no_of_pcs' => $sizeSet->no_of_pcs,
                    'color_name' => $color->name,
                    'color_id' => $color->id,
                    'design_number' => $design->design_number,
                    'barcode' => $barcode,
                    'qrcode' => $barcode,
                ];
            }
        }

        if (empty($labels)) {
            return back()->withError('No labels to generate.');
        }

        $chunks = array_chunk($labels, 2);

        $html = view('admin.inventory.barcode_generator.pdf', compact('chunks'))->render();
        $html = trim($html); // CRITICAL: Strip any leading/trailing whitespace

        ob_clean();
        $pdf = Pdf::loadHTML($html);
        $pdf->setOptions(['defaultFont' => 'sans-serif', 'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
        // 100mm width (283.46pt) x 90mm height (255.12pt)
        $pdf->setPaper([0, 0, 283.46, 255.12]);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="barcodes-' . count($chunks) . '-' . time() . '.pdf"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function generateBulk(Request $request)
    {
        $ids = $request->ids;
        if (empty($ids)) {
            return back()->withError('No inventory IDs provided.');
        }

        $items = \App\Models\DomesticInventory::whereIn('id', $ids)->get();
        $labels = [];

        foreach ($items as $item) {
            $labels[] = (object) [
                'product_name' => $item->product_name,
                'fitting_name' => $item->fitting_name,
                'pattern_name' => $item->pattern_name,
                'size_group' => $item->size_set_name,
                'no_of_pcs' => $item->quantity,
                'color_name' => $item->color_name,
                'color_id' => $item->color_id,
                'design_number' => $item->design_number,
                'barcode' => $item->barcode,
                'qrcode' => $item->barcode,
            ];
        }

        if (empty($labels)) {
            return back()->withError('No labels to generate.');
        }

        $chunks = array_chunk($labels, 2);

        $html = view('admin.inventory.barcode_generator.pdf', compact('chunks'))->render();
        $html = trim($html);

        ob_clean();
        $pdf = Pdf::loadHTML($html);
        $pdf->setOptions(['defaultFont' => 'sans-serif', 'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
        // 100mm width (283.46pt) x 90mm height (255.12pt)
        $pdf->setPaper([0, 0, 283.46, 255.12]);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="bulk-barcodes-' . count($chunks) . '-' . time() . '.pdf"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }


    public function generateTspl(Request $request)
    {
        $request->validate([
            'design_id' => 'required|exists:production_goods,id',

            'color_ids' => 'required|array',
            'color_ids.*' => 'required|exists:master_colors,id',
            'size_set_ids' => 'required|array',
            'size_set_ids.*' => 'required|exists:master_size_measurements,id',
            'quantities' => 'required|array',
            'quantities.*' => 'required|integer|min:1|max:500',
        ]);

        $barcodeList = [];

        foreach ($request->color_ids as $key => $color_id) {
            if (!$color_id)
                continue;

            $barcode = 'D' . $request->design_id .
                'S' . $request->size_set_ids[$key] .
                'C' . $color_id;

            $quantity = $request->quantities[$key];

            for ($i = 0; $i < $quantity; $i++) {
                $barcodeList[] = $barcode;
            }
        }

        if (empty($barcodeList)) {
            return back()->withError('No labels to generate.');
        }

        $tspl = generateBulkTsplByBarcodes($barcodeList);

        $fileName = 'barcode_print_' . time() . '.prn';

        return response($tspl, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function generateBulkTspl(Request $request)
    {
        $ids = $request->ids;
        if (empty($ids))
            return back()->withError('No inventory IDs provided.');

        $items = \App\Models\DomesticInventory::whereIn('id', $ids)->get();
        $barcodeList = [];

        foreach ($items as $item) {
            // As per updated user request: "if 5 boxes then 5 barcode"
            $boxes = (int) $item->total_boxes;

            for ($i = 0; $i < $boxes; $i++) {
                if ($item->barcode) {
                    $barcodeList[] = $item->barcode;
                }
            }
        }

        if (empty($barcodeList))
            return back()->withError('No valid barcodes found.');

        // Use the global helper method
        $tspl = generateBulkTsplByBarcodes($barcodeList);

        $fileName = 'bulk_barcodes_pcs_' . time() . '.prn';

        return response($tspl, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function generatePurchasePrn($id)
    {
        $purchase = \App\Models\DomesticInventoryPurchase::with('items')->findOrFail($id);
        $barcodeList = [];

        foreach ($purchase->items as $item) {
            // Reconstruct barcode logic (consistent with InventoryController)
            $barcode = 'D' . $item->new_product_id . 
                      'S' . $item->new_size_set_id . 
                      'C' . $item->new_color_id;

            $boxes = (int) $item->box_quantity;

            for ($i = 0; $i < $boxes; $i++) {
                $barcodeList[] = $barcode;
            }
        }

        if (empty($barcodeList)) {
            return back()->withError('No valid barcodes found for this purchase.');
        }

        // Use the global helper method
        $tspl = generateBulkTsplByBarcodes($barcodeList);

        $fileName = 'purchase_labels_' . $purchase->id . '_' . time() . '.prn';

        return response($tspl, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function generateInboundPrn($id)
    {
        $session = \App\Models\PackingMain::where('order_main_id', 0)
            ->where('slip_id', 0)
            ->findOrFail($id);
            
        $items = \App\Models\DomesticInventoryHistory::where('created_at', $session->created_at)
          ->whereIn('type', ['creation', 'sample'])
          ->get();

        $barcodeList = [];

        foreach ($items as $item) {
            $barcode = 'D' . $item->new_product_id . 
                      'S' . $item->new_size_set_id . 
                      'C' . $item->new_color_id;

            $boxes = (int) $item->box_quantity;

            for ($i = 0; $i < $boxes; $i++) {
                $barcodeList[] = $barcode;
            }
        }

        if (empty($barcodeList)) {
            return back()->withError('No valid barcodes found for this session.');
        }

        $tspl = generateBulkTsplByBarcodes($barcodeList);

        $fileName = 'inbound_session_labels_' . $session->id . '_' . time() . '.prn';

        return response($tspl, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
    public function generateByBarcodes(Request $request)
    {
        $request->validate([
            'barcodes' => 'required|string',
        ]);

        $barcodes = array_map('trim', explode("\n", $request->barcodes));
        $barcodes = array_filter($barcodes);

        if (empty($barcodes)) {
            return back()->withError('No valid barcodes provided.');
        }

        $tspl = generateBulkTsplByBarcodes($barcodes);

        if (empty($tspl)) {
            return back()->withError('Could not generate PRN for the provided barcodes. They might be invalid or not found.');
        }

        $fileName = 'custom_barcodes_' . time() . '.prn';

        return response($tspl, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
    public function generateByLocation(Request $request)
    {
        $request->validate([
            'rack_id' => 'required|exists:racks,id',
        ]);

        $items = \App\Models\DomesticInventory::where('rack_id', $request->rack_id)->get();
        
        if ($items->isEmpty()) {
            return back()->withError('No inventory found on the selected rack.');
        }

        $labels = [];
        foreach ($items as $item) {
            $boxes = (int) $item->total_boxes;
            
            // Generate one label per box
            for ($i = 0; $i < $boxes; $i++) {
                $labels[] = (object) [
                    'product_name' => $item->product_name,
                    'fitting_name' => $item->fitting_name,
                    'pattern_name' => $item->pattern_name,
                    'size_group' => $item->size_set_name,
                    'no_of_pcs' => $item->quantity, // pcs per box
                    'color_name' => $item->color_name,
                    'color_id' => $item->color_id,
                    'design_number' => $item->design_number,
                    'barcode' => $item->barcode,
                    'qrcode' => $item->barcode,
                ];
            }
        }

        if (empty($labels)) {
            return back()->withError('No labels to generate.');
        }

        $chunks = array_chunk($labels, 2);
        $html = view('admin.inventory.barcode_generator.pdf', compact('chunks'))->render();
        $html = trim($html);

        ob_clean();
        $pdf = Pdf::loadHTML($html);
        $pdf->setOptions(['defaultFont' => 'sans-serif', 'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
        $pdf->setPaper([0, 0, 283.46, 255.12]);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="location-barcodes-' . count($chunks) . '-' . time() . '.pdf"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function generateByLocationTspl(Request $request)
    {
        $request->validate([
            'storeroom_id' => 'required|exists:storerooms,id',
            'rack_id' => 'nullable|exists:racks,id',
        ]);

        $query = \App\Models\DomesticInventory::query();

        if ($request->rack_id) {
            $query->where('rack_id', $request->rack_id);
        } else {
            $rackIds = \App\Models\Rack::where('storeroom_id', $request->storeroom_id)->pluck('id');
            $query->whereIn('rack_id', $rackIds);
        }

        $items = $query->get();
        $barcodeList = [];

        foreach ($items as $item) {
            $boxes = (int) $item->total_boxes;
            for ($i = 0; $i < $boxes; $i++) {
                if ($item->barcode) {
                    $barcodeList[] = $item->barcode;
                }
            }
        }

        if (empty($barcodeList)) {
            return back()->withError('No valid barcodes found on the selected rack.');
        }

        $tspl = generateBulkTsplByBarcodes($barcodeList);
        $fileName = 'location_barcodes_pcs_' . time() . '.prn';

        return response($tspl, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}


