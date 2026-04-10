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

class BarcodeGeneratorController extends Controller
{
    public function index()
    {
        $designs = ProductionGoods::with('series')->where('status', 1)->orderBy('design_number')->get();
        $sizeSets = MasterSizeMeasurement::where('status', 1)->orderBy('name')->get();
        $colors = MasterColor::where('status', 1)->orderBy('name')->get();
        $patterns = MasterDesignPattern::where('status', 1)->orderBy('name')->get();
        $fittings = MasterProductFitting::where('status', 1)->orderBy('name')->get();

        return view('admin.inventory.barcode_generator.index', compact(
            'designs',
            'sizeSets',
            'colors',
            'patterns',
            'fittings'
        ));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'design_id' => 'required|exists:production_goods,id',
            'pattern_id' => 'required|exists:master_design_patterns,id',
            'fitting_id' => 'required|exists:master_product_fittings,id',
            'color_ids' => 'required|array',
            'color_ids.*' => 'required|exists:master_colors,id',
            'size_set_ids' => 'required|array',
            'size_set_ids.*' => 'required|exists:master_size_measurements,id',
            'quantities' => 'required|array',
            'quantities.*' => 'required|integer|min:1|max:500',
        ]);

        $design = ProductionGoods::with('series')->find($request->design_id);
        $pattern = MasterDesignPattern::find($request->pattern_id);
        $fitting = MasterProductFitting::find($request->fitting_id);

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
            $barcode = 'D' . $design->id . 'S' . $sizeSet->id . 'C' . $color->id . 'P' . $pattern->id . 'F' . $fitting->id;

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
            'pattern_id' => 'required|exists:master_design_patterns,id',
            'fitting_id' => 'required|exists:master_product_fittings,id',
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
                'C' . $color_id .
                'P' . $request->pattern_id .
                'F' . $request->fitting_id;

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

}
