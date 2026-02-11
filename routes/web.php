<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\GeneralSettingsController as AdminGeneralSettingsController;
use App\Http\Controllers\Admin\Master\VendorController as AdminVendorController;
use App\Http\Controllers\Admin\Master\ItemController as AdminItemController;
use App\Http\Controllers\Admin\Master\PatternController as AdminPatternController;
use App\Http\Controllers\Admin\Master\DesignPatternController as AdminDesignPatternController;
use App\Http\Controllers\Admin\Master\ItemAttributesController as AdminItemAttributesController;
use App\Http\Controllers\Admin\Master\FabricDyeController as AdminFabricDyeController;
use App\Http\Controllers\Admin\Master\FabricGsmController as AdminFabricGsmController;
use App\Http\Controllers\Admin\Master\FabricCompositionController as AdminFabricCompositionController;
use App\Http\Controllers\Admin\Master\FabricWeaveController as AdminFabricWeaveController;
use App\Http\Controllers\Admin\Master\FabricWidthController as AdminFabricWidthController;
use App\Http\Controllers\Admin\Master\FabricController as AdminFabricController;
use App\Http\Controllers\Admin\PurchaseOrderController as AdminPurchaseOrderController;
use App\Http\Controllers\Admin\FabricReceiptController as AdminFabricReceiptController;
use App\Http\Controllers\Admin\StockController as AdminStockController;
use App\Http\Controllers\Admin\Master\ProductionGoodsController as AdminProductionGoodsController;
use App\Http\Controllers\Admin\Master\ProductionGoodsItemController as AdminProductionGoodsItemController;
use App\Http\Controllers\Admin\Master\SizeMeasurementController as AdminSizeMeasurementController;
use App\Http\Controllers\Admin\Master\SizeController as AdminSizeController;
use App\Http\Controllers\Admin\PurchaseOrderMaterialController as AdminPurchaseOrderMaterialController;
use App\Http\Controllers\Admin\ItemReceiptController as AdminItemReceiptController;
use App\Http\Controllers\Admin\ItemStockController as AdminItemStockController;
use App\Http\Controllers\Admin\OrderDigitalizationController as AdminOrderDigitalizationController;
use App\Http\Controllers\Admin\OrderDispatchController as AdminOrderDispatchController;
use App\Http\Controllers\Admin\PackingCartonController as AdminPackingCartonController;

/// order
use App\Http\Controllers\Admin\ProductOrderController as AdminProductOrderController;
use App\Http\Controllers\Admin\WarehouseController as AdminWarehouseController;

///// Order Stages
use App\Http\Controllers\Admin\OrderStagesController as AdminOrderStagesController;

////new master
use App\Http\Controllers\Admin\Master\MasterColorController as AdminMasterColorController;
use App\Http\Controllers\Admin\Master\MasterFittingController as AdminMasterFittingController;
use App\Http\Controllers\Admin\Master\MasterDesignController as AdminMasterDesignController;
use App\Http\Controllers\Admin\Master\MasterMaterialController as AdminMasterMaterialController;
use App\Http\Controllers\Admin\Master\MasterProductStageController as AdminMasterProductStageController;
use App\Http\Controllers\Admin\Master\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\Master\MasterProductTypeController as AdminMasterProductTypeController;
use App\Http\Controllers\Admin\Master\MasterWarehouseBlocksController as AdminMasterWarehouseBlocksController;
use App\Http\Controllers\Admin\Master\MasterWarehouseController as AdminMasterWarehouseController;
use App\Http\Controllers\Admin\Master\MasterFabricWarehouseController as AdminMasterFabricWarehouseController;
use App\Http\Controllers\Admin\Master\MasterStageUnitController as AdminMasterStageUnitController;

///// Reports
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\PackingController as AdminPackingController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;



////// Website
Route::get('/', [AdminLoginController::class, 'login'])->name('web.homepage');
Route::get('/upload-production-slip/{encryptedId}', [AdminLoginController::class, 'uploadProductionSlip'])->name('uploadProductionSlip');
Route::post('/submit-production-slip', [AdminLoginController::class, 'submitProductionSlip'])->name('submitProductionSlip');
Route::get('/scan', [AdminFabricReceiptController::class, 'scan'])->name('scan');

// ================= UNIT AUTHENTICATION ROUTES (SEPARATE FROM ADMIN) =================
Route::prefix('unit')->name('unit.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Unit\UnitAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Unit\UnitAuthController::class, 'login'])->name('login.post');
    Route::get('/dashboard', [\App\Http\Controllers\Unit\UnitAuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/submit', [\App\Http\Controllers\Unit\UnitAuthController::class, 'submitSlip'])->name('submit');
    Route::get('/history', [\App\Http\Controllers\Unit\UnitAuthController::class, 'history'])->name('history');
    Route::get('/view/{type}/{id}', [\App\Http\Controllers\Unit\UnitAuthController::class, 'viewSlip'])->name('view.slip');
    Route::get('/assignments', [\App\Http\Controllers\Unit\UnitAuthController::class, 'assignments'])->name('assignments');
    Route::get('/assignment-details/{type}/{id}', [App\Http\Controllers\Unit\UnitAuthController::class, 'showAssignmentDetails'])->name('assignments.details');
    Route::post('/assignments/{type}/{id}/close', [\App\Http\Controllers\Unit\UnitAuthController::class, 'closeAssignment'])->name('assignments.close');
    Route::post('/assignments/{type}/{id}/reopen', [\App\Http\Controllers\Unit\UnitAuthController::class, 'reopenAssignment'])->name('assignments.reopen');
    Route::get('/download-slip/{id}', [\App\Http\Controllers\Unit\UnitAuthController::class, 'downloadSlip'])->name('download.slip');
    Route::get('/download-cmpo/{id}', [\App\Http\Controllers\Unit\UnitAuthController::class, 'downloadCmpo'])->name('download.cmpo');
    Route::get('/logout', [\App\Http\Controllers\Unit\UnitAuthController::class, 'logout'])->name('logout');
});

// ================= OWNER AUTHENTICATION ROUTES =================
Route::prefix('owner')->name('owner.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Owner\OwnerAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Owner\OwnerAuthController::class, 'login'])->name('login.post');

    Route::middleware(['checkOwnerLogin'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Owner\OwnerAuthController::class, 'dashboard'])->name('dashboard');
        Route::get('/logout', [\App\Http\Controllers\Owner\OwnerAuthController::class, 'logout'])->name('logout');

        // Report specific routes
        Route::get('/orders', [\App\Http\Controllers\Owner\OwnerAuthController::class, 'orders'])->name('orders');
        Route::get('/lots', [\App\Http\Controllers\Owner\OwnerAuthController::class, 'lots'])->name('lots');
        Route::get('/lot-details', [\App\Http\Controllers\Owner\OwnerAuthController::class, 'lotDetails'])->name('lot-details');
        Route::get('/stock', [\App\Http\Controllers\Owner\OwnerAuthController::class, 'stock'])->name('stock');
        Route::get('/stock/roll-details', [\App\Http\Controllers\Owner\OwnerAuthController::class, 'stockRollDetails'])->name('stock.roll.details');

        Route::prefix('order-summary')->name('order-summary.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Owner\OwnerAuthController::class, 'orderSummary'])->name('index');
            Route::get('/list', [\App\Http\Controllers\Owner\OwnerAuthController::class, 'orderSummaryList'])->name('indexList');
            Route::get('/view', [\App\Http\Controllers\Owner\OwnerAuthController::class, 'orderSummaryView'])->name('view');
            Route::get('/pdf', [\App\Http\Controllers\Owner\OwnerAuthController::class, 'orderSummaryPdf'])->name('pdf');
        });
    });
});


// ================= SALES AGENT ROUTES =================
Route::prefix('agent')->group(function () {
    Route::get('login', [App\Http\Controllers\SalesAgent\AuthController::class, 'showLoginForm'])->name('agent.login');
    Route::post('login', [App\Http\Controllers\SalesAgent\AuthController::class, 'login']);
    Route::post('logout', [App\Http\Controllers\SalesAgent\AuthController::class, 'logout'])->name('agent.logout');

    Route::middleware('auth:sales_agent')->group(function () {
        Route::get('dashboard', [App\Http\Controllers\SalesAgent\DashboardController::class, 'index'])->name('agent.dashboard');

        // Shop Management
        Route::get('shops', [App\Http\Controllers\SalesAgent\ShopController::class, 'index'])->name('agent.shops.index');
        Route::get('shops/create', [App\Http\Controllers\SalesAgent\ShopController::class, 'create'])->name('agent.shops.create');
        Route::post('shops', [App\Http\Controllers\SalesAgent\ShopController::class, 'store'])->name('agent.shops.store');
        Route::get('shops/{id}/edit', [App\Http\Controllers\SalesAgent\ShopController::class, 'edit'])->name('agent.shops.edit');
        Route::put('shops/{id}', [App\Http\Controllers\SalesAgent\ShopController::class, 'update'])->name('agent.shops.update');
        Route::post('shops/{id}/toggle-status', [App\Http\Controllers\SalesAgent\ShopController::class, 'toggleStatus'])->name('agent.shops.toggle-status');

        // Inventory & Ordering
        Route::get('inventory', [App\Http\Controllers\SalesAgent\InventoryController::class, 'index'])->name('agent.inventory.index');
        Route::get('inventory/{box_id}', [App\Http\Controllers\SalesAgent\InventoryController::class, 'show'])->name('agent.inventory.show');

        // Order Creation & Editing
        Route::get('orders/create', [App\Http\Controllers\SalesAgent\OrderController::class, 'create'])->name('agent.orders.create');
        Route::post('orders', [App\Http\Controllers\SalesAgent\OrderController::class, 'store'])->name('agent.orders.store');
        Route::get('orders/{id}/edit', [App\Http\Controllers\SalesAgent\OrderController::class, 'edit'])->name('agent.orders.edit');
        Route::put('orders/{id}', [App\Http\Controllers\SalesAgent\OrderController::class, 'update'])->name('agent.orders.update');

        Route::get('orders', [App\Http\Controllers\SalesAgent\OrderController::class, 'myOrders'])->name('agent.orders.index');
        Route::get('orders/{id}', [App\Http\Controllers\SalesAgent\OrderController::class, 'orderDetails'])->name('agent.orders.show');
        Route::get('orders/{id}/invoice', [App\Http\Controllers\SalesAgent\OrderController::class, 'downloadInvoice'])->name('agent.orders.invoice');
    });
});

////////////  Admin Routes

use App\Http\Controllers\Admin\Master\SalesAgentController as AdminSalesAgentController;
use App\Http\Controllers\Admin\Master\InventoryPriceController as AdminInventoryPriceController;

Route::prefix('admin')->name('admin.')->middleware(['web'])->group(function () {
    Route::get('/', [AdminLoginController::class, 'login'])->name('login');
    Route::post('/post-admin', [AdminLoginController::class, 'postLogin'])->name('postLogin');

    Route::middleware(['checkAdminLogin'])->group(function () {

        Route::get('/logout', [AdminLoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/getDashboardData', [AdminDashboardController::class, 'getDashboardData'])->name('getDashboardData');

        Route::prefix('/purchase-order')->name('purchase_order.')->group(function () {
            Route::get('/index', [AdminPurchaseOrderController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminPurchaseOrderController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminPurchaseOrderController::class, 'create'])->name('create');
            Route::post('/store', [AdminPurchaseOrderController::class, 'store'])->name('store');
            Route::get('/edit', [AdminPurchaseOrderController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminPurchaseOrderController::class, 'update'])->name('update');
            Route::get('/delete', [AdminPurchaseOrderController::class, 'delete'])->name('delete');
            Route::get('/view', [AdminPurchaseOrderController::class, 'view'])->name('view');

            Route::get('/estimation', [AdminPurchaseOrderController::class, 'estimation'])->name('estimation');
            Route::post('/estimation-store', [AdminPurchaseOrderController::class, 'estimation_store'])->name('estimation_store');
            Route::post('/resend', [AdminPurchaseOrderController::class, 'resend'])->name('resend');

            Route::get('/vendor_fabrics/{vendor}', [AdminPurchaseOrderController::class, 'vendorFabrics'])->name('vendor_fabrics');

            Route::get('/adjustment', [AdminPurchaseOrderController::class, 'adjustment'])->name('adjustment');

            Route::get('/adjustment-shipment', [AdminPurchaseOrderController::class, 'adjustmentShipment'])->name('adjustmentShipment');

            Route::POST('/adjustment-submit', [AdminPurchaseOrderController::class, 'adjustmentSubmit'])->name('adjustmentSubmit');

            Route::get('/adjustment-dynamic', [AdminPurchaseOrderController::class, 'adjustmentDynamic'])->name('adjustmentDynamic');
        });


        /*
        |--------------------------------------------------------------------------
        | Admin Agent Order Management
        |--------------------------------------------------------------------------
        */
        Route::get('agent-orders', [App\Http\Controllers\Admin\AgentOrderController::class, 'index'])->name('agent-orders.index');
        Route::get('agent-orders/{id}', [App\Http\Controllers\Admin\AgentOrderController::class, 'show'])->name('agent-orders.show');
        Route::get('agent-orders/{id}/invoice', [App\Http\Controllers\Admin\AgentOrderController::class, 'downloadInvoice'])->name('agent-orders.invoice');
        Route::post('agent-orders/{id}/dispatch', [App\Http\Controllers\Admin\AgentOrderController::class, 'dispatchOrder'])->name('agent-orders.dispatch');
        Route::get('agent-orders/{id}/edit', [App\Http\Controllers\Admin\AgentOrderController::class, 'edit'])->name('agent-orders.edit');
        Route::put('agent-orders/{id}', [App\Http\Controllers\Admin\AgentOrderController::class, 'update'])->name('agent-orders.update');

        Route::prefix('/purchase-order-material')->name('purchase_order_material.')->group(function () {
            Route::get('/index', [AdminPurchaseOrderMaterialController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminPurchaseOrderMaterialController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminPurchaseOrderMaterialController::class, 'create'])->name('create');
            Route::post('/store', [AdminPurchaseOrderMaterialController::class, 'store'])->name('store');
            Route::get('/edit', [AdminPurchaseOrderMaterialController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminPurchaseOrderMaterialController::class, 'update'])->name('update');
            Route::get('/delete', [AdminPurchaseOrderMaterialController::class, 'delete'])->name('delete');
            Route::get('/view', [AdminPurchaseOrderMaterialController::class, 'view'])->name('view');
        });

        Route::prefix('/fabric-receipt')->name('fabric_receipt.')->group(function () {
            Route::get('/index', [AdminFabricReceiptController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminFabricReceiptController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminFabricReceiptController::class, 'create'])->name('create');
            Route::post('/store', [AdminFabricReceiptController::class, 'store'])->name('store');
            Route::get('/edit', [AdminFabricReceiptController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminFabricReceiptController::class, 'update'])->name('update');
            Route::get('/delete', [AdminFabricReceiptController::class, 'delete'])->name('delete');
            Route::get('/view', [AdminFabricReceiptController::class, 'view'])->name('view');
            Route::get('/detail', [AdminFabricReceiptController::class, 'detail'])->name('detail');
            Route::post('/store-detail', [AdminFabricReceiptController::class, 'storeDetail'])->name('storeDetail');
            Route::get('/purchase-order-items/{id}', [AdminFabricReceiptController::class, 'getPurchaseOrderItems'])->name('items');

            Route::get('/vendor_fabrics/{vendor}', [AdminFabricReceiptController::class, 'vendorFabrics'])->name('vendor_fabrics');
            Route::post('/check-roll-no', [AdminFabricReceiptController::class, 'checkRollNo'])->name('check-roll-no');
        });

        Route::prefix('/item-receipt')->name('item_receipt.')->group(function () {
            Route::get('/index', [AdminItemReceiptController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminItemReceiptController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminItemReceiptController::class, 'create'])->name('create');
            Route::post('/store', [AdminItemReceiptController::class, 'store'])->name('store');
            Route::get('/detail', [AdminItemReceiptController::class, 'detail'])->name('detail');
            Route::get('/getPurchaseOrderItems/{id}', [AdminItemReceiptController::class, 'getPurchaseOrderItems'])->name('getPurchaseOrderItems');
            Route::get('/edit', [AdminItemReceiptController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminItemReceiptController::class, 'update'])->name('update');
            Route::post('/storeDetail', [AdminItemReceiptController::class, 'storeDetail'])->name('storeDetail');
            Route::get('/view', [AdminItemReceiptController::class, 'view'])->name('view');
            Route::post('/delete', [AdminItemReceiptController::class, 'delete'])->name('delete');
        });

        Route::prefix('/stock')->name('stock.')->group(function () {
            Route::get('/index', [AdminStockController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminStockController::class, 'indexList'])->name('indexList');
            Route::get('/view', [AdminStockController::class, 'view'])->name('view');
            Route::get('/detail', [AdminStockController::class, 'detail'])->name('detail');
            Route::post('/generatePdf', [AdminStockController::class, 'generateStockReportPDF'])->name('generatePdf');
            Route::post('/generateExcel', [AdminStockController::class, 'generateStockReportExcel'])->name('generateExcel');
            Route::get('/fabricQuantityExcel', [AdminStockController::class, 'generateFebricQuantityReportExcel'])->name('fabricQuantityExcel');

            Route::get('/fabricIndex', [AdminStockController::class, 'fabricIndex'])->name('fabricIndex');
            Route::get('/fabricIndexList', [AdminStockController::class, 'fabricIndexList'])->name('fabricIndexList');
        });

        Route::prefix('/item-stock')->name('item_stock.')->group(function () {
            Route::get('/index', [AdminItemStockController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminItemStockController::class, 'indexList'])->name('indexList');
            Route::get('/view', [AdminItemStockController::class, 'view'])->name('view');
            Route::get('/detail', [AdminItemStockController::class, 'detail'])->name('detail');
            Route::post('/generatePdf', [AdminItemStockController::class, 'generateStockReportPDF'])->name('generatePdf');
            Route::post('/generateExcel', [AdminItemStockController::class, 'generateStockReportExcel'])->name('generateExcel');
            Route::get('/itemQuantityExcel', [AdminItemStockController::class, 'generateItemQuantityReportExcel'])->name('itemQuantityExcel');

            Route::get('/itemIndex', [AdminItemStockController::class, 'itemIndex'])->name('itemIndex');
            Route::get('/itemIndexList', [AdminItemStockController::class, 'itemIndexList'])->name('itemIndexList');
        });

        Route::prefix('/sales-order')->name('sales_order.')->group(function () {
            Route::get('/create', [AdminProductOrderController::class, 'create'])->name('create');
            Route::get('/master-data', [AdminProductOrderController::class, 'master_data'])->name('master_data');
            Route::post('/store', [AdminProductOrderController::class, 'store'])->name('store');
            Route::get('/getCustomerSizes', [AdminProductOrderController::class, 'getCustomerSizes'])->name('getCustomerSizes');
            Route::get('/getCustomerDesign', [AdminProductOrderController::class, 'getCustomerDesign'])->name('getCustomerDesign');
            Route::post('/saveCustomSetSize', [AdminProductOrderController::class, 'saveCustomSetSize'])->name('saveCustomSetSize');
        });

        Route::prefix('/production-order')->name('product_order.')->group(function () {

            Route::get('/index-order', [AdminProductOrderController::class, 'indexOrder'])->name('indexOrder');
            Route::get('/indexListOrder', [AdminProductOrderController::class, 'indexListOrder'])->name('indexListOrder');

            Route::get('/index-order-set', [AdminProductOrderController::class, 'indexOrderSet'])->name('indexOrderSet');
            Route::get('/indexListOrderSet', [AdminProductOrderController::class, 'indexListOrderSet'])->name('indexListOrderSet');
            Route::get('/index-order-set-download', [AdminProductOrderController::class, 'indexOrderSetDownload'])->name('indexOrderSetDownload');
            Route::get('/view-cutting-slip', [AdminProductOrderController::class, 'viewCuttingSlip'])->name('viewCuttingSlip');

            Route::get('/index', [AdminProductOrderController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminProductOrderController::class, 'indexList'])->name('indexList');

            Route::get('/edit', [AdminProductOrderController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminProductOrderController::class, 'update'])->name('update');
            Route::get('/delete', [AdminProductOrderController::class, 'delete'])->name('delete');
            Route::get('/view', [AdminProductOrderController::class, 'view'])->name('view');
            Route::post('/transfer', [AdminProductOrderController::class, 'transfer'])->name('transfer');
            Route::get('/produce', [AdminProductOrderController::class, 'produce'])->name('produce');
            Route::get('/issue-fabric', [AdminProductOrderController::class, 'issueFabric'])->name('issueFabric');
            Route::post('/issue-fabric-post', [AdminProductOrderController::class, 'issueFabricPost'])->name('issueFabricPost');
            Route::get('/issue-slip', [AdminProductOrderController::class, 'issueSlip'])->name('issueSlip');
            Route::get('/status-hover-data', [AdminProductOrderController::class, 'productStatusHoverData'])->name('statusHoverData');

            Route::post('/assign_to', [AdminProductOrderController::class, 'assign_to'])->name('assign_to');
            Route::get('/download-cutting-slip', [AdminProductOrderController::class, 'downloadCuttingSlip'])->name('downloadCuttingSlip');

            // Route::get('/fabric_combined_receipt',[AdminProductOrderController::class,'fabric_combined_receipt'])->name('fabric_combined_receipt');
            Route::post('/getCuttingUnit', [AdminProductOrderController::class, 'getCuttingUnit'])->name('getCuttingUnit');
        });

        Route::prefix('/packing')->name('packing.')->group(function () {

            Route::get('/index', [AdminPackingController::class, 'index'])->name('index');
            Route::get('/process/{id}', [AdminPackingController::class, 'process'])->name('process');
            Route::post('/finalize', [AdminPackingController::class, 'finalize'])->name('finalize');
            Route::post('/box/save', [AdminPackingController::class, 'saveBox'])->name('saveBox');
            Route::post('/carton/save', [AdminPackingController::class, 'saveCarton'])->name('saveCarton');
            Route::get('/order-details/{id}', [AdminPackingController::class, 'getOrderDetailsJson'])->name('orderDeps');
            Route::get('check-carton-no', [AdminPackingController::class, 'checkCartonNo'])->name('check-carton-no');
            Route::post('/bulk-save', [AdminPackingController::class, 'bulkSaveCarton'])->name('bulk-save');
        });

        Route::prefix('/inventory')->name('inventory.')->group(function () {
            Route::get('/index', [\App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('index');
            Route::get('/indexList', [\App\Http\Controllers\Admin\InventoryController::class, 'indexList'])->name('indexList');
        });

        Route::prefix('/warehouse')->name('warehouse.')->group(function () {

            Route::get('/index-order', [AdminWarehouseController::class, 'indexOrder'])->name('indexOrder');
            Route::get('/indexListOrder', [AdminWarehouseController::class, 'indexListOrder'])->name('indexListOrder');
            Route::get('/index', [AdminWarehouseController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminWarehouseController::class, 'indexList'])->name('indexList');
            Route::get('/view', [AdminWarehouseController::class, 'view'])->name('view');
            Route::get('/produce', [AdminWarehouseController::class, 'produce'])->name('produce');
            Route::get('/status-hover-data', [AdminWarehouseController::class, 'productStatusHoverData'])->name('statusHoverData');

            Route::get('/listing', [AdminWarehouseController::class, 'listing'])->name('listing');
            Route::get('/indexListListing', [AdminWarehouseController::class, 'indexListListing'])->name('indexListListing');
            Route::get('/packaging', [AdminWarehouseController::class, 'packaging'])->name('packaging');
            Route::post('/packaging-store', [AdminWarehouseController::class, 'packagingStore'])->name('packagingStore');
            Route::get('/packaging-show', [AdminWarehouseController::class, 'packagingShow'])->name('packagingShow');
            Route::get('/barcode-download', [AdminWarehouseController::class, 'barcodeDownload'])->name('barcodeDownload');
            Route::get('/{warehouse}/blocks', [AdminWarehouseController::class, 'getBlocks'])->name('getBlocks');


        });

        Route::prefix('/order-stages')->name('order-stages.')->group(function () {
            Route::get('/index', [AdminOrderStagesController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminOrderStagesController::class, 'indexList'])->name('indexList');
            Route::get('/download-receipt', [AdminOrderStagesController::class, 'downLoadReceipt'])->name('downLoadReceipt');
            Route::get('/get-sub-stages/{order_product_id}/{from_stage_id}', [AdminOrderStagesController::class, 'getSubStages'])->name('getSubStages');
        });

        Route::prefix('/order_digitalization')->name('order_digitalization.')->group(function () {
            Route::get('/index-slip-production', [AdminOrderDigitalizationController::class, 'index_slip_production'])->name('index-slip-production');
            Route::get('/indexList', [AdminOrderDigitalizationController::class, 'indexList'])->name('indexList');
            Route::get('/create-slips-production', [AdminOrderDigitalizationController::class, 'createSlipsProduction'])->name('create-slips-production');
            Route::post('/store-rolls-assign', [AdminOrderDigitalizationController::class, 'storeRollsAssign'])->name('store-rolls-assign');
            Route::post('/store-slip', [AdminOrderDigitalizationController::class, 'storeProductionSlipDigitization'])->name('store-slip');
            Route::post('/store-hand-slip', [AdminOrderDigitalizationController::class, 'storeHandSlip'])->name('store-hand-slip');
            Route::post('/get-lot-details-for-hand-slip', [AdminOrderDigitalizationController::class, 'getLotDetailsForHandSlip'])->name('get-lot-details-for-hand-slip');
            Route::get('/create-rolls-assign', [AdminOrderDigitalizationController::class, 'createRollsAssign'])->name('create-rolls-assign');


            Route::get('/getRollsData', [AdminOrderDigitalizationController::class, 'getRollsData'])->name('getRollsData');
            Route::post('/skip', [AdminOrderDigitalizationController::class, 'skip'])->name('skip');
            Route::post('/delete-slip', [AdminOrderDigitalizationController::class, 'deleteSlip'])->name('delete-slip');
            Route::post('/add-skip-slip', [AdminOrderDigitalizationController::class, 'addSkipSlips'])->name('add-skip-slip');


            // Route::get('/download-receipt',[AdminOrderDigitalizationController::class,'downLoadReceipt'])->name('downLoadReceipt');
            // Route::get('/get-sub-stages/{order_product_id}/{from_stage_id}',[AdminOrderDigitalizationController::class,'getSubStages'])->name('getSubStages');

            ////////new cutting 
            Route::get('/cutting-master', [AdminOrderDigitalizationController::class, 'cuttingMaster'])->name('cutting-master');

            Route::get('/order-designs', [AdminOrderDigitalizationController::class, 'getDesigns'])->name('order-designs');
            Route::get('/lot-details', [AdminOrderDigitalizationController::class, 'getLotDetails'])->name('lot-details');
            Route::post('/store-stitching', [AdminOrderDigitalizationController::class, 'storeStitching'])->name('store-stitching');
            Route::post('/store-printing', [AdminOrderDigitalizationController::class, 'storePrinting'])->name('store-printing');
            Route::post('/get-lot-details-for-display', [AdminOrderDigitalizationController::class, 'getLotDetailsForDisplay'])->name('get-lot-details-for-display');
        });

        Route::prefix('/time-allocation')->name('time_allocation.')->group(function () {
            Route::get('/create', [\App\Http\Controllers\Admin\TimeAllocationController::class, 'create'])->name('create');
            Route::post('/store', [\App\Http\Controllers\Admin\TimeAllocationController::class, 'store'])->name('store');
            Route::post('/get-lot-details', [\App\Http\Controllers\Admin\TimeAllocationController::class, 'getLotDetails'])->name('get-lot-details');
        });

        Route::prefix('/packing-carton')->name('packing-carton.')->group(function () {
            Route::get('/index', [AdminPackingCartonController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminPackingCartonController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminPackingCartonController::class, 'create'])->name('create');
            Route::get('/view', [AdminPackingCartonController::class, 'view'])->name('view');
            Route::post('/store', [AdminPackingCartonController::class, 'store'])->name('store');
            Route::get('/getCustomerOrders', [AdminPackingCartonController::class, 'getCustomerOrders'])->name('getCustomerOrders');
            Route::get('/getCustomersBybarcode', [AdminPackingCartonController::class, 'getCustomersBybarcode'])->name('getCustomersBybarcode');
            Route::get('/getOrdersDetails', [AdminPackingCartonController::class, 'getOrdersDetails'])->name('getOrdersDetails');

        });

        Route::prefix('/order-dispatch')->name('order-dispatch.')->group(function () {
            Route::get('/index', [AdminOrderDispatchController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminOrderDispatchController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminOrderDispatchController::class, 'create'])->name('create');
            Route::get('/view', [AdminOrderDispatchController::class, 'view'])->name('view');
            Route::post('/store', [AdminOrderDispatchController::class, 'store'])->name('store');
            Route::get('/getOrderPackingData', [AdminOrderDispatchController::class, 'getOrderPackingData'])->name('getOrderPackingData');
            Route::get('/getOrdersByCustomer', [AdminOrderDispatchController::class, 'getOrdersByCustomer'])->name('getOrdersByCustomer');
            Route::get('/comppleteOrder', [AdminOrderDispatchController::class, 'comppleteOrder'])->name('comppleteOrder');
        });
        Route::prefix('master/vendors')->name('master.vendor.')->group(function () {
            Route::get('/index', [AdminVendorController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminVendorController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminVendorController::class, 'create'])->name('create');
            Route::post('/store', [AdminVendorController::class, 'store'])->name('store');
            Route::get('/edit', [AdminVendorController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminVendorController::class, 'update'])->name('update');
            Route::get('/delete', [AdminVendorController::class, 'delete'])->name('delete');
        });
        Route::prefix('master/pattern')->name('master.pattern.')->group(function () {
            Route::get('/index', [AdminPatternController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminPatternController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminPatternController::class, 'create'])->name('create');
            Route::post('/store', [AdminPatternController::class, 'store'])->name('store');
            Route::get('/edit', [AdminPatternController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminPatternController::class, 'update'])->name('update');
            Route::get('/delete', [AdminPatternController::class, 'delete'])->name('delete');
        });

        Route::prefix('master/design-pattern')->name('master.design-pattern.')->group(function () {
            Route::get('/index', [AdminDesignPatternController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminDesignPatternController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminDesignPatternController::class, 'create'])->name('create');
            Route::post('/store', [AdminDesignPatternController::class, 'store'])->name('store');
            Route::get('/edit', [AdminDesignPatternController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminDesignPatternController::class, 'update'])->name('update');
            Route::get('/delete', [AdminDesignPatternController::class, 'delete'])->name('delete');
        });

        Route::prefix('master/stage-unit')->name('master.stage_unit.')->group(function () {
            Route::get('/index', [AdminMasterStageUnitController::class, 'index'])->name('index');
            Route::get('/stage_unit/{master_fabric_warehouse_id}', [AdminMasterStageUnitController::class, 'stageUnit'])->name('stageUnit');
            Route::post('/update', [AdminMasterStageUnitController::class, 'update'])->name('update');
        });

        Route::prefix('master/item')->name('master.item.')->group(function () {
            Route::get('/index', [AdminItemController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminItemController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminItemController::class, 'create'])->name('create');
            Route::post('/store', [AdminItemController::class, 'store'])->name('store');
            Route::get('/edit', [AdminItemController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminItemController::class, 'update'])->name('update');
            Route::get('/delete', [AdminItemController::class, 'delete'])->name('delete');
        });

        Route::prefix('master/item-attributes')->name('master.item-attributes.')->group(function () {
            Route::get('/index', [AdminItemAttributesController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminItemAttributesController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminItemAttributesController::class, 'create'])->name('create');
            Route::post('/store', [AdminItemAttributesController::class, 'store'])->name('store');
            Route::get('/edit', [AdminItemAttributesController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminItemAttributesController::class, 'update'])->name('update');
            Route::get('/delete', [AdminItemAttributesController::class, 'delete'])->name('delete');
        });

        Route::prefix('master/fabric_dye')->name('master.fabric_dye.')->group(function () {
            Route::get('/index', [AdminFabricDyeController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminFabricDyeController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminFabricDyeController::class, 'create'])->name('create');
            Route::post('/store', [AdminFabricDyeController::class, 'store'])->name('store');
            Route::get('/edit', [AdminFabricDyeController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminFabricDyeController::class, 'update'])->name('update');
            Route::get('/delete', [AdminFabricDyeController::class, 'delete'])->name('delete');
        });

        Route::prefix('master/fabric_gsm')->name('master.fabric_gsm.')->group(function () {
            Route::get('/index', [AdminFabricGsmController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminFabricGsmController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminFabricGsmController::class, 'create'])->name('create');
            Route::post('/store', [AdminFabricGsmController::class, 'store'])->name('store');
            Route::get('/edit', [AdminFabricGsmController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminFabricGsmController::class, 'update'])->name('update');
            Route::get('/delete', [AdminFabricGsmController::class, 'delete'])->name('delete');
        });
        Route::prefix('master/fabric-composition')->name('master.fabric_composition.')->group(function () {
            Route::get('/index', [AdminFabricCompositionController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminFabricCompositionController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminFabricCompositionController::class, 'create'])->name('create');
            Route::post('/store', [AdminFabricCompositionController::class, 'store'])->name('store');
            Route::get('/edit', [AdminFabricCompositionController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminFabricCompositionController::class, 'update'])->name('update');
            Route::get('/delete', [AdminFabricCompositionController::class, 'delete'])->name('delete');
        });
        Route::prefix('master/fabric_weave')->name('master.fabric_weave.')->group(function () {
            Route::get('/index', [AdminFabricWeaveController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminFabricWeaveController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminFabricWeaveController::class, 'create'])->name('create');
            Route::post('/store', [AdminFabricWeaveController::class, 'store'])->name('store');
            Route::get('/edit', [AdminFabricWeaveController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminFabricWeaveController::class, 'update'])->name('update');
            Route::get('/delete', [AdminFabricWeaveController::class, 'delete'])->name('delete');
        });
        Route::prefix('master/fabric_width')->name('master.fabric_width.')->group(function () {
            Route::get('/index', [AdminFabricWidthController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminFabricWidthController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminFabricWidthController::class, 'create'])->name('create');
            Route::post('/store', [AdminFabricWidthController::class, 'store'])->name('store');
            Route::get('/edit', [AdminFabricWidthController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminFabricWidthController::class, 'update'])->name('update');
            Route::get('/delete', [AdminFabricWidthController::class, 'delete'])->name('delete');
        });
        Route::prefix('master/fabric')->name('master.fabric.')->group(function () {
            Route::get('/index', [AdminFabricController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminFabricController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminFabricController::class, 'create'])->name('create');
            Route::post('/store', [AdminFabricController::class, 'store'])->name('store');
            Route::get('/edit', [AdminFabricController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminFabricController::class, 'update'])->name('update');
            Route::get('/delete', [AdminFabricController::class, 'delete'])->name('delete');
            Route::get('/deleteImage', [AdminFabricController::class, 'deleteImage'])->name('deleteImage');
        });
        Route::prefix('master/size-measurement')->name('master.size-measurement.')->group(function () {
            Route::get('/index', [AdminSizeMeasurementController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminSizeMeasurementController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminSizeMeasurementController::class, 'create'])->name('create');
            Route::post('/store', [AdminSizeMeasurementController::class, 'store'])->name('store');
            Route::get('/edit', [AdminSizeMeasurementController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminSizeMeasurementController::class, 'update'])->name('update');
            Route::get('/delete', [AdminSizeMeasurementController::class, 'delete'])->name('delete');
        });

        Route::prefix('/packing')->name('packing.')->group(function () {
            Route::get('/index', [AdminPackingController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminPackingController::class, 'indexList'])->name('indexList');
            Route::post('/store', [AdminPackingController::class, 'store'])->name('store');
            Route::post('/finalize', [AdminPackingController::class, 'finalize'])->name('finalize');
            Route::get('/view/{order}', [AdminPackingController::class, 'view'])->name('view');
            Route::get('/print/{main}', [AdminPackingController::class, 'print'])->name('print');
        });

        Route::prefix('/inventory')->name('inventory.')->group(function () {
            Route::get('/index', [AdminInventoryController::class, 'index'])->name('index');
            Route::get('/list', [AdminInventoryController::class, 'indexList'])->name('list');
            Route::get('/show/{box}', [AdminInventoryController::class, 'show'])->name('show');
        });

        Route::prefix('master/size')->name('master.size.')->group(function () {
            Route::get('/index', [AdminSizeController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminSizeController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminSizeController::class, 'create'])->name('create');
            Route::post('/store', [AdminSizeController::class, 'store'])->name('store');
            Route::get('/edit', [AdminSizeController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminSizeController::class, 'update'])->name('update');
        });

        Route::prefix('master/product-stage')->name('master.product_stage.')->group(function () {
            Route::get('/index', [AdminMasterProductStageController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminMasterProductStageController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminMasterProductStageController::class, 'create'])->name('create');
            Route::post('/store', [AdminMasterProductStageController::class, 'store'])->name('store');
            Route::get('/edit', [AdminMasterProductStageController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminMasterProductStageController::class, 'update'])->name('update');
            Route::get('/delete', [AdminMasterProductStageController::class, 'delete'])->name('delete');

            // Route::get('/index',[AdminMasterProductStageController::class,'subStageIndex'])->name('index');
            // Route::get('/subStageList',[AdminMasterProductStageController::class,'subStageList'])->name('subStageList');

        });

        Route::prefix('master/product-sub-stage')->name('master.product-sub-stage.')->group(function () {
            Route::get('/index', [AdminMasterProductStageController::class, 'subStageIndex'])->name('index');
            Route::get('/subStageList', [AdminMasterProductStageController::class, 'subStageList'])->name('subStageList');
            Route::get('/create', [AdminMasterProductStageController::class, 'createSubStage'])->name('create');

            Route::post('/store', [AdminMasterProductStageController::class, 'storeSubStage'])->name('store');

            Route::get('/edit', [AdminMasterProductStageController::class, 'editSubStage'])->name('edit');
            Route::post('/update', [AdminMasterProductStageController::class, 'updateSubStage'])->name('update');

        });

        Route::prefix('master/product')->name('master.production-goods.')->group(function () {
            Route::get('/index', [AdminProductionGoodsController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminProductionGoodsController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminProductionGoodsController::class, 'create'])->name('create');
            Route::post('/store', [AdminProductionGoodsController::class, 'store'])->name('store');
            Route::get('/edit', [AdminProductionGoodsController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminProductionGoodsController::class, 'update'])->name('update');
            Route::get('/delete', [AdminProductionGoodsController::class, 'delete'])->name('delete');
            Route::get('/view', [AdminProductionGoodsController::class, 'view'])->name('view');
        });

        Route::prefix('master/production-goods-item')->name('master.production-goods-item.')->group(function () {
            // Route::get('/index',[AdminProductionGoodsItemController::class,'index'])->name('index');
            // Route::get('/indexList',[AdminProductionGoodsItemController::class,'indexList'])->name('indexList');
            Route::get('/create', [AdminProductionGoodsItemController::class, 'create'])->name('create');
            Route::post('/store', [AdminProductionGoodsItemController::class, 'store'])->name('store');

            // Route::get('/edit',[AdminProductionGoodsItemController::class,'edit'])->name('edit');
            // Route::post('/update',[AdminProductionGoodsItemController::class,'update'])->name('update');
            // Route::get('/delete',[AdminProductionGoodsItemController::class,'delete'])->name('delete');
        });


        Route::prefix('master/colors')->name('master.colors.')->group(function () {
            Route::get('/index', [AdminMasterColorController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminMasterColorController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminMasterColorController::class, 'create'])->name('create');
            Route::post('/store', [AdminMasterColorController::class, 'store'])->name('store');
            Route::get('/edit', [AdminMasterColorController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminMasterColorController::class, 'update'])->name('update');
            Route::get('/delete', [AdminMasterColorController::class, 'delete'])->name('delete');
        });
        Route::prefix('master/fitting')->name('master.fitting.')->group(function () {
            Route::get('/index', [AdminMasterFittingController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminMasterFittingController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminMasterFittingController::class, 'create'])->name('create');
            Route::post('/store', [AdminMasterFittingController::class, 'store'])->name('store');
            Route::get('/edit', [AdminMasterFittingController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminMasterFittingController::class, 'update'])->name('update');
            Route::get('/delete', [AdminMasterFittingController::class, 'delete'])->name('delete');
        });

        Route::prefix('master/product-types')->name('master.product-types.')->group(function () {
            Route::get('/index', [AdminMasterProductTypeController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminMasterProductTypeController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminMasterProductTypeController::class, 'create'])->name('create');
            Route::post('/store', [AdminMasterProductTypeController::class, 'store'])->name('store');
            Route::get('/edit', [AdminMasterProductTypeController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminMasterProductTypeController::class, 'update'])->name('update');
            Route::get('/delete', [AdminMasterProductTypeController::class, 'delete'])->name('delete');
        });

        Route::prefix('master/warehouse-blocks')->name('master.warehouse-blocks.')->group(function () {
            Route::get('/index', [AdminMasterWarehouseBlocksController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminMasterWarehouseBlocksController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminMasterWarehouseBlocksController::class, 'create'])->name('create');
            Route::post('/store', [AdminMasterWarehouseBlocksController::class, 'store'])->name('store');
            Route::get('/edit', [AdminMasterWarehouseBlocksController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminMasterWarehouseBlocksController::class, 'update'])->name('update');
            Route::get('/delete', [AdminMasterWarehouseBlocksController::class, 'delete'])->name('delete');
        });

        Route::prefix('master/warehouse')->name('master.warehouse.')->group(function () {
            Route::get('/index', [AdminMasterWarehouseController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminMasterWarehouseController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminMasterWarehouseController::class, 'create'])->name('create');
            Route::post('/store', [AdminMasterWarehouseController::class, 'store'])->name('store');
            Route::get('/edit', [AdminMasterWarehouseController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminMasterWarehouseController::class, 'update'])->name('update');
            Route::get('/delete', [AdminMasterWarehouseController::class, 'delete'])->name('delete');
        });
        Route::prefix('master/inventory-price')->name('master.inventory-price.')->group(function () {
            Route::get('/index', [AdminInventoryPriceController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminInventoryPriceController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminInventoryPriceController::class, 'create'])->name('create');
            Route::post('/store', [AdminInventoryPriceController::class, 'store'])->name('store');
            Route::get('/edit', [AdminInventoryPriceController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminInventoryPriceController::class, 'update'])->name('update');
            Route::get('/delete', [AdminInventoryPriceController::class, 'delete'])->name('delete');
            Route::post('/image-delete', [\App\Http\Controllers\Admin\Master\InventoryPriceImageController::class, 'delete'])->name('image-delete');
        });

        Route::prefix('master/sales-agent')->name('master.sales-agent.')->group(function () {
            Route::get('/index', [AdminSalesAgentController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminSalesAgentController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminSalesAgentController::class, 'create'])->name('create');
            Route::post('/store', [AdminSalesAgentController::class, 'store'])->name('store');
            Route::get('/edit', [AdminSalesAgentController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminSalesAgentController::class, 'update'])->name('update');
            Route::get('/delete', [AdminSalesAgentController::class, 'delete'])->name('delete');
        });

        Route::prefix('master/fabric-warehouse')->name('master.fabric_warehouse.')->group(function () {
            Route::get('/index', [AdminMasterFabricWarehouseController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminMasterFabricWarehouseController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminMasterFabricWarehouseController::class, 'create'])->name('create');
            Route::post('/store', [AdminMasterFabricWarehouseController::class, 'store'])->name('store');
            Route::get('/edit', [AdminMasterFabricWarehouseController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminMasterFabricWarehouseController::class, 'update'])->name('update');
            Route::get('/delete', [AdminMasterFabricWarehouseController::class, 'delete'])->name('delete');
        });

        Route::prefix('master/designs')->name('master.designs.')->group(function () {
            Route::get('/index', [AdminMasterDesignController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminMasterDesignController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminMasterDesignController::class, 'create'])->name('create');
            Route::post('/store', [AdminMasterDesignController::class, 'store'])->name('store');
            Route::get('/edit', [AdminMasterDesignController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminMasterDesignController::class, 'update'])->name('update');
            Route::get('/delete', [AdminMasterDesignController::class, 'delete'])->name('delete');
        });
        Route::prefix('master/materials')->name('master.materials.')->group(function () {
            Route::get('/index', [AdminMasterMaterialController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminMasterMaterialController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminMasterMaterialController::class, 'create'])->name('create');
            Route::post('/store', [AdminMasterMaterialController::class, 'store'])->name('store');
            Route::get('/edit', [AdminMasterMaterialController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminMasterMaterialController::class, 'update'])->name('update');
            Route::get('/delete', [AdminMasterMaterialController::class, 'delete'])->name('delete');
        });

        Route::prefix('master/customers')->name('master.customer.')->group(function () {
            Route::get('/index', [AdminCustomerController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminCustomerController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminCustomerController::class, 'create'])->name('create');
            Route::post('/store', [AdminCustomerController::class, 'store'])->name('store');
            Route::get('/edit', [AdminCustomerController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminCustomerController::class, 'update'])->name('update');
            Route::get('/delete', [AdminCustomerController::class, 'delete'])->name('delete');
        });

        Route::prefix('master/storerooms')->name('master.storeroom.')->group(function () {
            Route::get('/index', [\App\Http\Controllers\Admin\Master\AdminMasterStoreroomController::class, 'index'])->name('index');
            Route::get('/indexList', [\App\Http\Controllers\Admin\Master\AdminMasterStoreroomController::class, 'indexList'])->name('indexList');
            Route::post('/store', [\App\Http\Controllers\Admin\Master\AdminMasterStoreroomController::class, 'store'])->name('store');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Master\AdminMasterStoreroomController::class, 'edit'])->name('edit'); // Changed to get with Param
            Route::post('/update', [\App\Http\Controllers\Admin\Master\AdminMasterStoreroomController::class, 'update'])->name('update');
            Route::get('/delete/{id}', [\App\Http\Controllers\Admin\Master\AdminMasterStoreroomController::class, 'delete'])->name('delete');

            // Rack AJAX
            Route::post('/rack/store', [\App\Http\Controllers\Admin\Master\AdminMasterStoreroomController::class, 'storeRack'])->name('rack.store');
            Route::post('/rack/update', [\App\Http\Controllers\Admin\Master\AdminMasterStoreroomController::class, 'updateRack'])->name('rack.update');
            Route::get('/rack/delete/{id}', [\App\Http\Controllers\Admin\Master\AdminMasterStoreroomController::class, 'deleteRack'])->name('rack.delete');
        });

        Route::get('edit-profile', [AdminUserController::class, 'profileEdit'])->name('user.profileEdit');
        Route::post('profile-update', [AdminUserController::class, 'profileUpdate'])->name('user.profileUpdate');
        Route::prefix('master/settings')->name('settings.')->group(function () {
            Route::get('/edit', [AdminGeneralSettingsController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminGeneralSettingsController::class, 'update'])->name('update');
        });

        Route::prefix('/report')->name('report.')->group(function () {
            Route::get('/sales-order', [AdminReportController::class, 'salesOrder'])->name('sales-order');
            Route::get('/sales-order/detail/{id}', [AdminReportController::class, 'salesOrderDetail'])->name('sales-order.detail');
            Route::get('/sales-order/export', [AdminReportController::class, 'salesOrderExport'])->name('sales-order.export');
            Route::get('/lots', [AdminReportController::class, 'lots'])->name('lots');
            Route::get('/lots/lot-details/{lot_no}', [AdminReportController::class, 'lotDetails'])->name('lots.lot-details');
            Route::get('/stock', [AdminReportController::class, 'stock'])->name('stock');
            Route::get('/stock/roll-details', [AdminReportController::class, 'fabricRollDetails'])->name('stock.roll.details');
            Route::get('/stock/export', [AdminReportController::class, 'stockExport'])->name('stock.export');
            Route::get('/purchase-order', [AdminReportController::class, 'purchaseOrder'])->name('purchase_order');
            Route::get('/purchase-order/item-details', [AdminReportController::class, 'purchaseOrderItemDetails'])->name('purchase_order.item.details');

            // Order Summary Report
            Route::prefix('order-summary')->name('order-summary.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\Report\AdminOrderSummaryReportController::class, 'index'])->name('index');
                Route::get('/list', [\App\Http\Controllers\Admin\Report\AdminOrderSummaryReportController::class, 'indexList'])->name('indexList');
                Route::get('/view/{id}', [\App\Http\Controllers\Admin\Report\AdminOrderSummaryReportController::class, 'view'])->name('view');
                Route::get('/view/{id}', [\App\Http\Controllers\Admin\Report\AdminOrderSummaryReportController::class, 'view'])->name('view');
                Route::get('/pdf/{id}', [\App\Http\Controllers\Admin\Report\AdminOrderSummaryReportController::class, 'downloadOrderSummaryPdf'])->name('pdf');
            });

            Route::get('/purchase-order/export', [AdminReportController::class, 'purchaseOrderExport'])->name('purchase_order.export');
            Route::get('/order-tracking-system', [AdminReportController::class, 'orderTrackingSystem'])->name('orderTrackingSystem');
            Route::get('/order-lot-tracking', [AdminReportController::class, 'lotTrackingDetails'])->name('lotTrackingDetails');
            Route::get('/order-tracking/export', [AdminReportController::class, 'orderTrackingExport'])->name('orderTracking.export');

            Route::get('/dispatch-order', [AdminReportController::class, 'dispatchOrder'])->name('dispatch-order');

        });

        Route::prefix('/reports')->name('reports.')->group(function () {
            Route::get('/fabric-receipt', [AdminReportController::class, 'fabricReceipt'])->name('fabricReceipt');
            Route::get('/fabric-receipt-list', [AdminReportController::class, 'fabricReceiptList'])->name('fabricReceiptList');
            Route::post('/fabric-receipt-excel', [AdminReportController::class, 'generateFabricReceiptExcel'])->name('fabricReceiptExcel');
            Route::get('/excel-fabric-receipt-report', [AdminReportController::class, 'excelFabricReceiptSingle'])->name('excel-fabric-receipt-report');

            Route::get('/item-receipt', [AdminReportController::class, 'itemReceipt'])->name('itemReceipt');
            Route::get('/item-receipt-list', [AdminReportController::class, 'itemReceiptList'])->name('itemReceiptList');
            Route::post('/item-receipt-excel', [AdminReportController::class, 'generateItemReceiptExcel'])->name('itemReceiptExcel');
            Route::get('/excel-item-receipt-report', [AdminReportController::class, 'excelItemReceiptSingle'])->name('excel-item-receipt-report');

            Route::get('/purchase-order', [AdminReportController::class, 'purchaseOrder'])->name('purchaseOrder');
            Route::get('/purchase-order-list', [AdminReportController::class, 'purchaseOrderList'])->name('purchaseOrderList');
            Route::post('/purchase-order-excel', [AdminReportController::class, 'generatePurchaseOrderExcel'])->name('purchaseOrderExcel');
            Route::get('/excel-purchase-order-report', [AdminReportController::class, 'excelPurchaseOrderSingle'])->name('excel-purchase-order-report');

            Route::get('/item-purchase-order', [AdminReportController::class, 'itemPurchaseOrder'])->name('itemPurchaseOrder');
            Route::get('/item-purchase-order-list', [AdminReportController::class, 'itemPurchaseOrderList'])->name('itemPurchaseOrderList');
            Route::post('/item-purchase-order-excel', [AdminReportController::class, 'itemGeneratePurchaseOrderExcel'])->name('itemPurchaseOrderExcel');
            Route::get('/item-excel-purchase-order-report', [AdminReportController::class, 'itemExcelPurchaseOrderSingle'])->name('item-excel-purchase-order-report');

            Route::get('/item-stock-sku', [AdminReportController::class, 'itemStockSku'])->name('itemStockSku');
            Route::get('/item-stock', [AdminReportController::class, 'itemStock'])->name('itemStock');
            Route::get('/item-stock-details', [AdminReportController::class, 'itemStockDetails'])->name('itemStockDetails');
            Route::get('/item-stock-list', [AdminReportController::class, 'itemStockList'])->name('itemStockList');
            Route::get('/item-stock-sku-list', [AdminReportController::class, 'itemStockSkuList'])->name('itemStockSkuList');
            Route::post('/item-stock-excel', [AdminReportController::class, 'generateItemStockExcel'])->name('itemStockExcel');
            Route::post('/item-stock-sku-excel', [AdminReportController::class, 'generateItemStockSkuExcel'])->name('itemStockSkuExcel');

            Route::get('/fabric-stock-sku', [AdminReportController::class, 'fabricStockSku'])->name('fabricStockSku');
            Route::get('/fabric-stock', [AdminReportController::class, 'fabricStock'])->name('fabricStock');
            Route::get('/fabric-stock-details', [AdminReportController::class, 'fabricStockDetails'])->name('fabricStockDetails');
            Route::get('/fabric-stock-list', [AdminReportController::class, 'fabricStockList'])->name('fabricStockList');
            Route::get('/fabric-stock-sku-list', [AdminReportController::class, 'fabricStockSkuList'])->name('fabricStockSkuList');
            Route::post('/fabric-stock-excel', [AdminReportController::class, 'generateFabricStockExcel'])->name('fabricStockExcel');
            Route::post('/fabric-stock-sku-excel', [AdminReportController::class, 'generateFabricStockSkuExcel'])->name('fabricStockSkuExcel');

            Route::get('/production', [AdminReportController::class, 'production'])->name('production');
            Route::get('production-list', [AdminReportController::class, 'productionList'])->name('productionList');
            Route::post('/production-excel', [AdminReportController::class, 'generateProductionExcel'])->name('productionExcel');
            Route::get('/production-excel-single', [AdminReportController::class, 'generateProductionExcelSingle'])->name('generateProductionExcelSingle');
            Route::get('/production-detail', [AdminReportController::class, 'productionDetail'])->name('productionDetail');

            Route::get('/stages', [AdminReportController::class, 'stages'])->name('stages');
            Route::get('stages-list', [AdminReportController::class, 'stagesList'])->name('stagesList');
            Route::post('/stages-excel', [AdminReportController::class, 'generateStagesReportExcel'])->name('stagesExcel');
        });
    });
    // Packing Module Routes
    Route::prefix('/packing')->name('packing.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PackingController::class, 'index'])->name('index');
        Route::get('/indexList', [\App\Http\Controllers\Admin\PackingController::class, 'indexList'])->name('indexList');
        Route::get('/process/{slip_id}', [\App\Http\Controllers\Admin\PackingController::class, 'process'])->name('process');
        Route::get('/view/{id}', [\App\Http\Controllers\Admin\PackingController::class, 'view'])->name('view');
        Route::post('/save-carton', [\App\Http\Controllers\Admin\PackingController::class, 'saveCarton'])->name('saveCarton');
        Route::post('/save-box', [\App\Http\Controllers\Admin\PackingController::class, 'saveBox'])->name('saveBox');
        Route::post('/finalize', [\App\Http\Controllers\Admin\PackingController::class, 'finalize'])->name('finalize');
        Route::post('/create-set', [\App\Http\Controllers\Admin\PackingController::class, 'createSet'])->name('createSet');
        Route::post('/delete-carton', [\App\Http\Controllers\Admin\PackingController::class, 'deleteCarton'])->name('deleteCarton');
    });
});



Route::prefix('/admin/uploaded-slips')->name('admin.uploaded-slips.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\UploadedSlipsController::class, 'index'])->name('index');
    Route::get('/{id}', [\App\Http\Controllers\Admin\UploadedSlipsController::class, 'show'])->name('show');
    Route::delete('/{id}', [\App\Http\Controllers\Admin\UploadedSlipsController::class, 'destroy'])->name('destroy');
    Route::get(
        '/{id}/download',
        [\App\Http\Controllers\Admin\UploadedSlipsController::class, 'download']
    )->name('download');

});



Route::get('/debug-stages', function () {
    return [
        'lots' => \App\Models\FabricRollAssigning::where('project_id', 1)->orWhere('id', '>', 0)->take(10)->get(), // just get recent lots
        'transactions' => \App\Models\OrderStageTransaction::latest()->take(5)->get()
    ];
});


