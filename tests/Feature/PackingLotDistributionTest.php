<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\PackingController;
use Illuminate\Http\Request;
use App\Models\PackingMain;
use App\Models\PackingSelectedLot;
use App\Models\PackingCarton;
use App\Models\PackingItem;
use App\Models\ProductionOutflowInventory;

class PackingLotDistributionTest extends TestCase
{
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = app(PackingController::class);
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    public function test_real_order_240_and_slip_2619_baseline_verification()
    {
        $slipId = 2619;
        $request = new Request(['order_id' => 240]);
        $view = $this->controller->processNew($request, $slipId);
        $data = $view->getData();
        $lots = collect($data['unit_lots']);

        // In real data, Lot 81331 was fully packed in slip 1635.
        // Lots 81329 and 81330 are untouched (750 pcs gross each).
        $this->assertEquals(2, $lots->count(), "Expected exactly 2 lots for Order 240");
        $this->assertFalse($lots->contains('lot_no', '81331'), "Fully packed lot 81331 is properly EXCLUDED");

        $lot81329 = $lots->firstWhere('lot_no', '81329');
        $this->assertNotNull($lot81329);
        $this->assertEquals(750, $lot81329->quantity);
        $this->assertEquals(750, $lot81329->remaining_quantity);

        $lot81330 = $lots->firstWhere('lot_no', '81330');
        $this->assertNotNull($lot81330);
        $this->assertEquals(750, $lot81330->quantity);
        $this->assertEquals(750, $lot81330->remaining_quantity);
    }

    public function test_partially_packed_lot_shows_remaining_pending_pieces()
    {
        // Simulate a prior packing session where 300 pieces of lot 81329 were packed.
        $pmPrior = PackingMain::create([
            'slip_id' => 88881,
            'order_main_id' => 240,
            'status' => 1
        ]);
        $cartonPrior = PackingCarton::create([
            'packing_main_id' => $pmPrior->id,
            'carton_no' => 'CTN-TEST-01',
            'status' => 1
        ]);
        PackingItem::create([
            'packing_main_id' => $pmPrior->id,
            'packing_carton_id' => $cartonPrior->id,
            'lot_no' => '81329',
            'size_id' => 1,
            'quantity' => 300,
            'total_boxes' => 1,
            'mrp' => 0,
            'selling_price' => 0
        ]);

        // View Order 240 on Slip 2619
        $request = new Request(['order_id' => 240]);
        $view = $this->controller->processNew($request, 2619);
        $lots = collect($view->getData()['unit_lots']);

        $lot81329 = $lots->firstWhere('lot_no', '81329');
        $this->assertNotNull($lot81329);
        $this->assertEquals(750, $lot81329->quantity);
        $this->assertEquals(450, $lot81329->remaining_quantity, "750 gross minus 300 packed = 450 pending");

        $lot81330 = $lots->firstWhere('lot_no', '81330');
        $this->assertEquals(750, $lot81330->remaining_quantity);
    }

    public function test_saving_and_planning_partially_packed_lot()
    {
        // 300 pieces packed in prior slip
        $pmPrior = PackingMain::create([
            'slip_id' => 88881,
            'order_main_id' => 240,
            'status' => 1
        ]);
        $cartonPrior = PackingCarton::create([
            'packing_main_id' => $pmPrior->id,
            'carton_no' => 'CTN-TEST-01',
            'status' => 1
        ]);
        PackingItem::create([
            'packing_main_id' => $pmPrior->id,
            'packing_carton_id' => $cartonPrior->id,
            'lot_no' => '81329',
            'size_id' => 4554,
            'quantity' => 150,
            'total_boxes' => 1,
            'mrp' => 0,
            'selling_price' => 0
        ]);
        PackingItem::create([
            'packing_main_id' => $pmPrior->id,
            'packing_carton_id' => $cartonPrior->id,
            'lot_no' => '81329',
            'size_id' => 4555,
            'quantity' => 150,
            'total_boxes' => 1,
            'mrp' => 0,
            'selling_price' => 0
        ]);

        // Save selected lots for Slip 2619
        $request = new Request([
            'order_id' => 240,
            'lots' => ['81329', '81330']
        ]);
        $this->controller->saveSelectedLots($request, 2619);

        $savedLots = PackingSelectedLot::where('slip_id', 2619)->pluck('lot_no')->toArray();
        $this->assertContains('81329', $savedLots);
        $this->assertContains('81330', $savedLots);

        // Pack Lots Step 2 Planner
        $view = $this->controller->packLots(new Request(), 2619);
        $lotsData = collect($view->getData()['lots_data']);

        $lot81329 = $lotsData->firstWhere('lot_no', '81329');
        $this->assertNotNull($lot81329);
        $this->assertEquals(450, $lot81329->remaining_quantity);
        $this->assertEquals(450, array_sum($lot81329->incoming_sizes));
    }

    public function test_outflows_and_reworks_deduction()
    {
        $testLotNo = 'TESTLOT999';
        $sampleOrderLot = DB::table('order_lots')->where('lot_no', '81329')->first();
        DB::table('order_lots')->insert([
            'order_main_id' => 240,
            'order_products_set_id' => $sampleOrderLot->order_products_set_id,
            'lot_no' => $testLotNo
        ]);

        $txSample = (array) DB::table('order_stage_transactions')->where('id', 1944)->first();
        unset($txSample['id']);
        $txSample['lot_no'] = $testLotNo;
        $txSample['quantity'] = 500;
        $txSample['remaining_quantity'] = 500;
        $txSample['from_stage_id'] = 10;
        $txSample['to_stage_id'] = 11;
        $txSample['type'] = 'normal';
        DB::table('order_stage_transactions')->insert($txSample);

        ProductionOutflowInventory::create([
            'slip_id' => 88882,
            'order_main_id' => 240,
            'lot_no' => $testLotNo,
            'size_id' => 1,
            'quantity' => 50,
            'type' => 'dead',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $reworkSample = (array) DB::table('order_stage_transactions')->where('id', 1944)->first();
        unset($reworkSample['id']);
        $reworkSample['lot_no'] = $testLotNo;
        $reworkSample['quantity'] = 30;
        $reworkSample['remaining_quantity'] = 30;
        $reworkSample['from_stage_id'] = 11;
        $reworkSample['to_stage_id'] = 10;
        $reworkSample['type'] = 'rework';
        $reworkSample['production_slip_digitization_id'] = 88883;
        DB::table('order_stage_transactions')->insert($reworkSample);

        $slipRow = (array) DB::table('production_slip_digitization')->where('id', 2619)->first();
        $slipRow['id'] = 99995;
        DB::table('production_slip_digitization')->insert($slipRow);

        $view = $this->controller->processNew(new Request(['order_id' => 240]), 99995);
        $lots = collect($view->getData()['unit_lots']);

        $testLot = $lots->firstWhere('lot_no', $testLotNo);
        $this->assertNotNull($testLot);
        $this->assertEquals(500, $testLot->quantity);
        $this->assertEquals(420, $testLot->remaining_quantity, "500 - 50 dead - 30 rework = 420");
    }

    public function test_all_lots_packed_removes_order_from_active_orders()
    {
        // Pack all remaining lots for order 240
        $pm = PackingMain::create([
            'slip_id' => 99993,
            'order_main_id' => 240,
            'status' => 0
        ]);
        $carton = PackingCarton::create([
            'packing_main_id' => $pm->id,
            'carton_no' => 'CTN-FULL-01',
            'status' => 1
        ]);
        PackingItem::create([
            'packing_main_id' => $pm->id,
            'packing_carton_id' => $carton->id,
            'lot_no' => '81329',
            'size_id' => 1,
            'quantity' => 750,
            'total_boxes' => 1,
            'mrp' => 0,
            'selling_price' => 0
        ]);
        PackingItem::create([
            'packing_main_id' => $pm->id,
            'packing_carton_id' => $carton->id,
            'lot_no' => '81330',
            'size_id' => 1,
            'quantity' => 750,
            'total_boxes' => 1,
            'mrp' => 0,
            'selling_price' => 0
        ]);

        $slipRow = (array) DB::table('production_slip_digitization')->where('id', 2619)->first();
        $slipRow['id'] = 99994;
        DB::table('production_slip_digitization')->insert($slipRow);

        $view = $this->controller->processNew(new Request(), 99994);
        $activeOrders = collect($view->getData()['active_orders']);

        $this->assertFalse($activeOrders->contains('id', 240), "Order 240 is omitted when fully packed");
    }
}
