<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\Payment\FabricShipmentPaymentController;

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
use App\Http\Controllers\Admin\Master\FabricUnitController as AdminFabricUnitController;
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
use App\Http\Controllers\Admin\Master\ProductNatureController as AdminProductNatureController;
use App\Http\Controllers\Admin\Master\FabricTypeController as AdminFabricTypeController;
use App\Http\Controllers\Admin\Master\MasterColorController as AdminMasterColorController;
use App\Http\Controllers\Admin\Master\MasterOrderRemarkController as AdminMasterOrderRemarkController;
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
use App\Http\Controllers\Admin\Master\MasterSeriesController as AdminMasterSeriesController;
use App\Http\Controllers\Admin\Payment\Master\CashPaymentController as AdminCashPaymentController;
use App\Http\Controllers\Admin\Payment\Master\BankAccountController as AdminBankAccountController;
use App\Http\Controllers\Admin\Payment\Master\PaymentTypeController as AdminPaymentTypeController;
use App\Http\Controllers\Admin\Payment\Master\TaxController as AdminTaxController;
use App\Http\Controllers\Admin\Payment\Master\InterestController as AdminInterestController;
use App\Http\Controllers\Admin\Payment\Master\TourExpenseController as AdminTourExpenseController;
use App\Http\Controllers\Admin\Payment\Master\ContractorController as AdminContractorController;
use App\Http\Controllers\Admin\Payment\Master\ConsumableGoodController as AdminConsumableGoodController;
use App\Http\Controllers\Admin\Payment\Master\GeneralExpenseController as AdminGeneralExpenseController;
use App\Http\Controllers\Admin\Payment\Master\ElectricityExpenseController as AdminElectricityExpenseController;
use App\Http\Controllers\Admin\Payment\Master\RentController as AdminRentController;
use App\Http\Controllers\Admin\Payment\Master\TelephoneExpenseController as AdminTelephoneExpenseController;
use App\Http\Controllers\Admin\Payment\Master\CommissionController as AdminCommissionController;
use App\Http\Controllers\Admin\Payment\Master\HulayatiController as AdminHulayatiController;
use App\Http\Controllers\Admin\Payment\Master\MachineryController as AdminMachineryController;
use App\Http\Controllers\Admin\Payment\Master\LoanController as AdminLoanController;
use App\Http\Controllers\Admin\Payment\Master\FactoryHeadController as AdminFactoryHeadController;
use App\Http\Controllers\Admin\Payment\Master\DiscountController as AdminDiscountController;
use App\Http\Controllers\Admin\Payment\Master\SalaryController as AdminSalaryController;
use App\Http\Controllers\Admin\Payment\Master\CapitalController as AdminCapitalController;
use App\Http\Controllers\Admin\Payment\Master\AdjustmentMasterController as AdminAdjustmentMasterController;
use App\Http\Controllers\Admin\Payment\Master\FareExpenseController as AdminFareExpenseController;
use App\Http\Controllers\Admin\Payment\Master\SkExpenseController as AdminSkExpenseController;
use App\Http\Controllers\Admin\Payment\Master\AgentPaymentMasterController as AdminAgentPaymentMasterController;
use App\Http\Controllers\Admin\Payment\Master\WashingMasterController as AdminWashingMasterController;
use App\Http\Controllers\Admin\Payment\Master\CuttingPaymentMasterController as AdminCuttingPaymentMasterController;
use App\Http\Controllers\Admin\Payment\Voucher\ConsumableVoucherController as AdminConsumableVoucherController;
use App\Http\Controllers\Admin\Payment\Voucher\ContractorVoucherController as AdminContractorVoucherController;
use App\Http\Controllers\Admin\Payment\Voucher\WashingVoucherController as AdminWashingVoucherController;
use App\Http\Controllers\Admin\Payment\PaymentAdjustmentController as AdminPaymentAdjustmentController;
use App\Http\Controllers\Admin\Payment\JournalVoucherController as AdminJournalVoucherController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;

///// Reports
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\PackingController as AdminPackingController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\AgentOrderController as AdminAgentOrderController;
use App\Http\Controllers\Admin\Master\SalesAgentController as AdminSalesAgentController;
use App\Http\Controllers\Admin\Master\PurchaseAgentController as AdminPurchaseAgentController;
use App\Http\Controllers\Admin\Master\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\Master\CompanyController as AdminCompanyController;
use App\Http\Controllers\Admin\BalanceCarryForwardController as AdminBalanceCarryForwardController;



////// Website
Route::get('/', [AdminLoginController::class, 'login'])->name('web.homepage');
Route::get('/upload-production-slip/{encryptedId}', [AdminLoginController::class, 'uploadProductionSlip'])->name('uploadProductionSlip');
Route::post('/submit-production-slip', [AdminLoginController::class, 'submitProductionSlip'])->name('submitProductionSlip');
Route::get('/sc/{barcode}', [\App\Http\Controllers\Admin\Inventory\SampleProductController::class, 'showColorChart'])->name('sample-product.color-chart');
Route::get('/fc/{barcode}', [\App\Http\Controllers\Admin\Inventory\FairProductController::class, 'showColorChart'])->name('fair-product.color-chart');

Route::get('/scan', [AdminFabricReceiptController::class, 'scan'])->name('scan');

// ================= UNIT AUTHENTICATION ROUTES (SEPARATE FROM ADMIN) =================
Route::prefix('unit')->name('unit.')->middleware(['unit.remember'])->group(function () {
    Route::get('/login', [\App\Http\Controllers\Unit\UnitAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Unit\UnitAuthController::class, 'login'])->name('login.post');
    Route::get('/dashboard', [\App\Http\Controllers\Unit\UnitAuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/submit', [\App\Http\Controllers\Unit\UnitAuthController::class, 'submitSlip'])->name('submit');
    Route::get('/history', [\App\Http\Controllers\Unit\UnitAuthController::class, 'history'])->name('history');
    Route::get('/view/{type}/{id}', [\App\Http\Controllers\Unit\UnitAuthController::class, 'viewSlip'])->name('view.slip');
    Route::get('/assignments', [\App\Http\Controllers\Unit\UnitAuthController::class, 'assignments'])->name('assignments');
    Route::get('/order-summary/{sku}', [\App\Http\Controllers\Unit\UnitAuthController::class, 'orderSummary'])->name('order-summary');
    Route::get('/assignment-details/{type}/{id}', [App\Http\Controllers\Unit\UnitAuthController::class, 'showAssignmentDetails'])->name('assignments.details');
    Route::post('/assignments/{type}/{id}/close', [\App\Http\Controllers\Unit\UnitAuthController::class, 'closeAssignment'])->name('assignments.close');
    Route::post('/assignments/{type}/{id}/reopen', [\App\Http\Controllers\Unit\UnitAuthController::class, 'reopenAssignment'])->name('assignments.reopen');
    Route::get('/download-slip/{id}', [\App\Http\Controllers\Unit\UnitAuthController::class, 'downloadSlip'])->name('download.slip');
    Route::get('/download-cmpo/{id}', [\App\Http\Controllers\Unit\UnitAuthController::class, 'downloadCmpo'])->name('download.cmpo');
    Route::post('/delete-slip/{type}/{id}', [\App\Http\Controllers\Unit\UnitAuthController::class, 'deleteSlip'])->name('delete.slip');
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
        Route::get('/lots', [\App\Http\Controllers\Owner\ReportController::class, 'lots'])->name('lots');
        Route::get('/lots/lot-details/{lot_no}', [\App\Http\Controllers\Owner\ReportController::class, 'lotDetails'])->name('lot-details');
        Route::get('/unit-assignments', [\App\Http\Controllers\Owner\ReportController::class, 'unitAssignments'])->name('reports.unit-assignments');
        Route::get('/selling-items', [\App\Http\Controllers\Owner\ReportController::class, 'sellingItems'])->name('reports.selling-items');
        Route::get('/delayed-payments', [\App\Http\Controllers\Owner\ReportController::class, 'delayedPayments'])->name('reports.delayed-payments');
        Route::get('/lot-details/pdf', [\App\Http\Controllers\Owner\ReportController::class, 'lotDetailsPdf'])->name('lot-details.pdf');

        // Stock Reports
        Route::get('/stock', [\App\Http\Controllers\Owner\ReportController::class, 'stock'])->name('stock');
        Route::get('/stock-rolls', [\App\Http\Controllers\Owner\ReportController::class, 'stockRolls'])->name('report.stock.rolls');
        Route::get('/stock-rolls/tracking', [\App\Http\Controllers\Owner\ReportController::class, 'stockRollTracking'])->name('report.stock.rolls.tracking');
        Route::get('/stock/roll-details', [\App\Http\Controllers\Owner\ReportController::class, 'stockRollDetails'])->name('stock.roll.details');
        Route::get('/ready-stock', [\App\Http\Controllers\Owner\InventoryController::class, 'index'])->name('ready-stock.index');
        Route::get('/ready-stock/list', [\App\Http\Controllers\Owner\InventoryController::class, 'indexList'])->name('ready-stock.list');

        Route::prefix('order-summary')->name('order-summary.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Owner\OwnerAuthController::class, 'orderSummary'])->name('index');
            Route::get('/list', [\App\Http\Controllers\Owner\OwnerAuthController::class, 'orderSummaryList'])->name('indexList');
            Route::get('/view', [\App\Http\Controllers\Owner\OwnerAuthController::class, 'orderSummaryView'])->name('view');
            Route::get('/pdf', [\App\Http\Controllers\Owner\OwnerAuthController::class, 'orderSummaryPdf'])->name('pdf');
        });

        Route::prefix('party-ledger')->name('party-ledger.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Owner\Ledger\PartyLedgerController::class, 'index'])->name('index');
            Route::get('/show/{type}/{id}', [\App\Http\Controllers\Owner\Ledger\PartyLedgerController::class, 'show'])->name('show');
            Route::get('/download/{type}/{id}', [\App\Http\Controllers\Owner\Ledger\PartyLedgerController::class, 'download'])->name('download');
        });
        
        Route::prefix('bank-cash-ledger')->name('bank-cash-ledger.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Owner\Ledger\BankCashLedgerController::class, 'index'])->name('index');
            Route::get('/show/{type}/{id}', [\App\Http\Controllers\Owner\Ledger\BankCashLedgerController::class, 'show'])->name('show');
            Route::get('/download/{type}/{id}', [\App\Http\Controllers\Owner\Ledger\BankCashLedgerController::class, 'download'])->name('download');
        });

        Route::get('/pending-payments', [\App\Http\Controllers\Admin\Payment\PendingPaymentController::class, 'index'])->name('payment.pending.index');
        Route::get('/payment-history', [\App\Http\Controllers\Admin\Payment\PaymentHistoryController::class, 'index'])->name('payment.history.index');
    });
});


// ================= SALES AGENT ROUTES =================
Route::prefix('agent')->group(function () {
    Route::get('login', [App\Http\Controllers\SalesAgent\AuthController::class, 'showLoginForm'])->name('agent.login');
    Route::post('login', [App\Http\Controllers\SalesAgent\AuthController::class, 'login']);
    Route::post('logout', [App\Http\Controllers\SalesAgent\AuthController::class, 'logout'])->name('agent.logout');

    Route::middleware('auth:sales_agent')->group(function () {
        Route::get('dashboard', [App\Http\Controllers\SalesAgent\DashboardController::class, 'index'])->name('agent.dashboard');

        // Customer Management (Master Agent)
        Route::get('customers/create', [App\Http\Controllers\SalesAgent\CustomerController::class, 'create'])->name('agent.customers.create');
        Route::post('customers/store', [App\Http\Controllers\SalesAgent\CustomerController::class, 'store'])->name('agent.customers.store');


        // Shop Management
        Route::get('shops', [App\Http\Controllers\SalesAgent\ShopController::class, 'index'])->name('agent.shops.index');
        Route::get('shops/create', [App\Http\Controllers\SalesAgent\ShopController::class, 'create'])->name('agent.shops.create');
        Route::post('shops', [App\Http\Controllers\SalesAgent\ShopController::class, 'store'])->name('agent.shops.store');
        Route::get('shops/{id}/edit', [App\Http\Controllers\SalesAgent\ShopController::class, 'edit'])->name('agent.shops.edit');
        Route::put('shops/{id}', [App\Http\Controllers\SalesAgent\ShopController::class, 'update'])->name('agent.shops.update');
        Route::post('shops/{id}/toggle-status', [App\Http\Controllers\SalesAgent\ShopController::class, 'toggleStatus'])->name('agent.shops.toggle-status');

        // Inventory & Ordering


        // Order Creation & Editing
        Route::get('orders/create', [App\Http\Controllers\SalesAgent\OrderController::class, 'create'])->name('agent.orders.create');
        Route::post('orders', [App\Http\Controllers\SalesAgent\OrderController::class, 'store'])->name('agent.orders.store');
        Route::get('orders/{id}/edit', [App\Http\Controllers\SalesAgent\OrderController::class, 'edit'])->name('agent.orders.edit');
        Route::put('orders/{id}', [App\Http\Controllers\SalesAgent\OrderController::class, 'update'])->name('agent.orders.update');

        Route::get('orders', [App\Http\Controllers\SalesAgent\OrderController::class, 'myOrders'])->name('agent.orders.index');
        Route::get('orders/get-variation-by-barcode', [App\Http\Controllers\SalesAgent\OrderController::class, 'getVariationByBarcode'])->name('agent.orders.get-variation-by-barcode');
        Route::get('orders/{id}', [App\Http\Controllers\SalesAgent\OrderController::class, 'orderDetails'])->name('agent.orders.show');
        Route::get('orders/{id}/download-order', [App\Http\Controllers\SalesAgent\OrderController::class, 'downloadOrder'])->name('agent.orders.download-order');
        Route::get('orders/{id}/send-whatsapp-order', [App\Http\Controllers\SalesAgent\OrderController::class, 'sendWhatsappOrder'])->name('agent.orders.send-whatsapp-order');
    });
});

////////////  Admin Routes

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['web']], function () {
    Route::get('/', [AdminLoginController::class, 'login'])->name('login');
    Route::post('/post-admin', [AdminLoginController::class, 'postLogin'])->name('postLogin');

    Route::middleware(['checkAdminLogin'])->group(function () {

        Route::get('/logout', [AdminLoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/download-db', [AdminDashboardController::class, 'downloadDatabase'])->name('download-db');
        Route::get('/getDashboardData', [AdminDashboardController::class, 'getDashboardData'])->name('getDashboardData');
        Route::get('/carry-forward-balances', [AdminBalanceCarryForwardController::class, 'carryForward'])->name('carry-forward-balances');

        Route::prefix('/purchase-order')->name('purchase_order.')->group(function () {
            Route::get('/index', [AdminPurchaseOrderController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminPurchaseOrderController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminPurchaseOrderController::class, 'create'])->name('create');
            Route::post('/store', [AdminPurchaseOrderController::class, 'store'])->name('store');
            Route::get('/edit', [AdminPurchaseOrderController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminPurchaseOrderController::class, 'update'])->name('update');
            Route::get('/delete', [AdminPurchaseOrderController::class, 'delete'])->name('delete');
            Route::get('/view', [AdminPurchaseOrderController::class, 'view'])->name('view');
            Route::get('/download-report', [AdminPurchaseOrderController::class, 'downloadReport'])->name('download_report');
            Route::get('/send-whatsapp-report', [AdminPurchaseOrderController::class, 'sendWhatsappReport'])->name('send_whatsapp_report');


            Route::get('/estimation', [AdminPurchaseOrderController::class, 'estimation'])->name('estimation');
            Route::post('/estimation-store', [AdminPurchaseOrderController::class, 'estimation_store'])->name('estimation_store');
            Route::post('/resend', [AdminPurchaseOrderController::class, 'resend'])->name('resend');

            Route::get('/vendor_fabrics/{vendor}', [AdminPurchaseOrderController::class, 'vendorFabrics'])->name('vendor_fabrics');
            Route::get('/all_vendors', [AdminPurchaseOrderController::class, 'allVendors'])->name('all_vendors');
            Route::get('/all_fabrics', [AdminPurchaseOrderController::class, 'allFabrics'])->name('all_fabrics');
            Route::get('/all_warehouses', [AdminPurchaseOrderController::class, 'allWarehouses'])->name('all_warehouses');
            Route::get('/all_companies', [AdminPurchaseOrderController::class, 'allCompanies'])->name('all_companies');

            Route::get('/adjustment', [AdminPurchaseOrderController::class, 'adjustment'])->name('adjustment');

            Route::get('/adjustment-shipment', [AdminPurchaseOrderController::class, 'adjustmentShipment'])->name('adjustmentShipment');

            Route::POST('/adjustment-submit', [AdminPurchaseOrderController::class, 'adjustmentSubmit'])->name('adjustmentSubmit');

            Route::get('/adjustment-dynamic', [AdminPurchaseOrderController::class, 'adjustmentDynamic'])->name('adjustmentDynamic');
        });



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
            Route::post('/upload-challan', [AdminFabricReceiptController::class, 'uploadChallan'])->name('upload_challan');
            Route::post('/upload-other-images', [AdminFabricReceiptController::class, 'uploadOtherImages'])->name('upload_other_images');
            Route::get('/delete-other-image/{id}', [AdminFabricReceiptController::class, 'deleteOtherImage'])->name('delete_other_image');
            Route::get('/detail', [AdminFabricReceiptController::class, 'detail'])->name('detail');
            Route::post('/store-detail', [AdminFabricReceiptController::class, 'storeDetail'])->name('storeDetail');
            Route::get('/purchase-order-items/{id}', [AdminFabricReceiptController::class, 'getPurchaseOrderItems'])->name('items');

            Route::get('/vendor_fabrics/{vendor}', [AdminFabricReceiptController::class, 'vendorFabrics'])->name('vendor_fabrics');
            Route::get('/all_warehouses', [AdminFabricReceiptController::class, 'allWarehouses'])->name('all_warehouses');
            Route::get('/all_pending_pos', [AdminFabricReceiptController::class, 'allPendingPOs'])->name('all_pending_pos');
            Route::post('/check-roll-no', [AdminFabricReceiptController::class, 'checkRollNo'])->name('check-roll-no');
            Route::post('/check-bill-no', [AdminFabricReceiptController::class, 'checkBillNo'])->name('check-bill-no');
            Route::get('/download-report', [AdminFabricReceiptController::class, 'downloadReport'])->name('download_report');
            Route::post('/return-fabric', [AdminFabricReceiptController::class, 'returnFabric'])->name('return_fabric');
            Route::get('/return', [AdminFabricReceiptController::class, 'returnPage'])->name('return');
            Route::post('/return-store', [AdminFabricReceiptController::class, 'storeReturn'])->name('store_return');
            Route::get('/return-report', [AdminFabricReceiptController::class, 'downloadReturnReport'])->name('download_return_report');
            Route::get('/return-delete/{id}', [AdminFabricReceiptController::class, 'deleteReturn'])->name('delete_return');
            Route::get('/return-edit/{id}', [AdminFabricReceiptController::class, 'editReturnPage'])->name('edit_return');
            Route::post('/return-update', [AdminFabricReceiptController::class, 'updateReturn'])->name('update_return');
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
            Route::get('/create-domestic', [AdminProductOrderController::class, 'createDomestic'])->name('create_domestic');
            Route::get('/master-data', [AdminProductOrderController::class, 'master_data'])->name('master_data');
            Route::post('/store', [AdminProductOrderController::class, 'store'])->name('store');
            Route::post('/store-domestic', [AdminProductOrderController::class, 'storeDomestic'])->name('store_domestic');
            Route::get('/getCustomerSizes', [AdminProductOrderController::class, 'getCustomerSizes'])->name('getCustomerSizes');
            Route::get('/getCustomerDesign', [AdminProductOrderController::class, 'getCustomerDesign'])->name('getCustomerDesign');
            Route::post('/saveCustomSetSize', [AdminProductOrderController::class, 'saveCustomSetSize'])->name('saveCustomSetSize');
            Route::get('/all_customers', [AdminProductOrderController::class, 'allCustomers'])->name('all_customers');
        });

        Route::prefix('/production-order')->name('product_order.')->group(function () {

            Route::get('/index-order', [AdminProductOrderController::class, 'indexOrder'])->name('indexOrder');
            Route::get('/indexListOrder', [AdminProductOrderController::class, 'indexListOrder'])->name('indexListOrder');
            Route::get('/export-orders', [AdminProductOrderController::class, 'exportOrders'])->name('exportOrders');

            Route::get('/index-order-set', [AdminProductOrderController::class, 'indexOrderSet'])->name('indexOrderSet');
            Route::get('/indexListOrderSet', [AdminProductOrderController::class, 'indexListOrderSet'])->name('indexListOrderSet');
            Route::get('/bulk-cmpo-download', [AdminProductOrderController::class, 'bulkCmpoDownload'])->name('bulkCmpoDownload');
            Route::get('/index-order-set-download', [AdminProductOrderController::class, 'indexOrderSetDownload'])->name('indexOrderSetDownload');
            Route::get('/view-cutting-slip', [AdminProductOrderController::class, 'viewCuttingSlip'])->name('viewCuttingSlip');

            Route::get('/index', [AdminProductOrderController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminProductOrderController::class, 'indexList'])->name('indexList');

            Route::get('/edit', [AdminProductOrderController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminProductOrderController::class, 'update'])->name('update');
            Route::get('/edit-order-main/{id}', [AdminProductOrderController::class, 'editOrderMain'])->name('editOrderMain');
            Route::post('/update-order-main/{id}', [AdminProductOrderController::class, 'updateOrderMain'])->name('updateOrderMain');
            Route::get('/delete', [AdminProductOrderController::class, 'delete'])->name('delete');
            Route::get('/deleteOrderMain', [AdminProductOrderController::class, 'deleteOrderMain'])->name('deleteOrderMain');
            Route::get('/view', [AdminProductOrderController::class, 'view'])->name('view');
            Route::post('/transfer', [AdminProductOrderController::class, 'transfer'])->name('transfer');
            Route::get('/produce', [AdminProductOrderController::class, 'produce'])->name('produce');
            Route::get('/issue-fabric', [AdminProductOrderController::class, 'issueFabric'])->name('issueFabric');
            Route::post('/issue-fabric-post', [AdminProductOrderController::class, 'issueFabricPost'])->name('issueFabricPost');
            Route::get('/issue-slip', [AdminProductOrderController::class, 'issueSlip'])->name('issueSlip');
            Route::get('/status-hover-data', [AdminProductOrderController::class, 'productStatusHoverData'])->name('statusHoverData');

            Route::post('/assign_to', [AdminProductOrderController::class, 'assign_to'])->name('assign_to');
            Route::post('/delete-assignment', [AdminProductOrderController::class, 'deleteAssignment'])->name('deleteAssignment');
            Route::post('/create-po', [AdminProductOrderController::class, 'createPO'])->name('createPO');
            Route::get('/download-po', [AdminProductOrderController::class, 'downloadPO'])->name('downloadPO');
            Route::get('/download-cutting-slip', [AdminProductOrderController::class, 'downloadCuttingSlip'])->name('downloadCuttingSlip');

            Route::get('/bulk-po', [AdminProductOrderController::class, 'bulkPO'])->name('bulkPO');
            Route::post('/store-bulk-po', [AdminProductOrderController::class, 'storeBulkPO'])->name('storeBulkPO');
            Route::get('/get-unassigned-sets', [AdminProductOrderController::class, 'getUnassignedSets'])->name('getUnassignedSets');
            Route::get('/get-unassigned-orders', [AdminProductOrderController::class, 'getUnassignedOrders'])->name('getUnassignedOrders');

            Route::get('/po-list', [AdminProductOrderController::class, 'poList'])->name('poList');
            Route::get('/po/{id}/view', [AdminProductOrderController::class, 'viewBulkPO'])->name('viewBulkPO');
            Route::get('/po/{id}/download', [AdminProductOrderController::class, 'downloadBulkPO'])->name('downloadBulkPO');
            Route::get('/po/{id}/edit', [AdminProductOrderController::class, 'editBulkPO'])->name('editBulkPO');
            Route::post('/po/{id}/update', [AdminProductOrderController::class, 'updateBulkPO'])->name('updateBulkPO');
            Route::delete('/po/{id}/delete', [AdminProductOrderController::class, 'deletePO'])->name('deletePO');

            // Route::get('/fabric_combined_receipt',[AdminProductOrderController::class,'fabric_combined_receipt'])->name('fabric_combined_receipt');
            Route::post('/getCuttingUnit', [AdminProductOrderController::class, 'getCuttingUnit'])->name('getCuttingUnit');
        });

        Route::prefix('/packing')->name('packing.')->group(function () {

            Route::get('/index', [AdminPackingController::class, 'index'])->name('index');
            Route::get('/process/{id}', [AdminPackingController::class, 'process'])->name('process');
            Route::get('/process-domestic/{id}', [AdminPackingController::class, 'processDomestic'])->name('processDomestic');
            Route::post('/finalize', [AdminPackingController::class, 'finalize'])->name('finalize');
            Route::get('/download-slip-barcode/{id}', [AdminPackingController::class, 'downloadSlipBarcodeTxt'])->name('downloadSlipBarcode');
            Route::post('/box/save', [AdminPackingController::class, 'saveBox'])->name('saveBox');
            Route::post('/carton/save', [AdminPackingController::class, 'saveCarton'])->name('saveCarton');
            Route::get('/order-details/{id}', [AdminPackingController::class, 'getOrderDetailsJson'])->name('orderDeps');
            Route::get('check-carton-no', [AdminPackingController::class, 'checkCartonNo'])->name('check-carton-no');
            Route::post('/bulk-save', [AdminPackingController::class, 'bulkSaveCarton'])->name('bulk-save');
            Route::post('/save-multi-plan', [AdminPackingController::class, 'saveMultiCartonPlan'])->name('saveMultiCartonPlan');
            Route::post('/save-domestic-box', [AdminPackingController::class, 'saveDomesticBox'])->name('saveDomesticBox');
            Route::post('/save-corporate-domestic-bulk', [AdminPackingController::class, 'saveCorporateDomesticBulk'])->name('saveCorporateDomesticBulk');
            Route::post('/save-domestic-bulk', [AdminPackingController::class, 'saveDomesticBulk'])->name('saveDomesticBulk');
            Route::post('/delete-domestic-box/{id}', [AdminPackingController::class, 'deleteDomesticBox'])->name('deleteDomesticBox');
            Route::post('/delete-outflow/{id}', [AdminPackingController::class, 'deleteOutflow'])->name('deleteOutflow');
            Route::post('/delete-rework/{id}', [AdminPackingController::class, 'deleteRework'])->name('deleteRework');
            Route::get('/download-outflow-barcode/{id}', [AdminPackingController::class, 'downloadOutflowBarcode'])->name('downloadOutflowBarcode');

            // Rework Routes
            Route::post('/reassign-rework', [AdminPackingController::class, 'reassignRework'])->name('reassignRework');
            Route::post('/record-dead-stock', [AdminPackingController::class, 'recordDeadStock'])->name('recordDeadStock');
            Route::post('/record-sampling-stock', [AdminPackingController::class, 'recordSamplingStock'])->name('recordSamplingStock');
            Route::post('/record-unit-debit', [AdminPackingController::class, 'recordUnitDebit'])->name('recordUnitDebit');
            Route::get('/rework-stages', [AdminPackingController::class, 'getReworkStages'])->name('reworkStages');
            Route::get('/stage-units/{stageId}', [AdminPackingController::class, 'getStageUnits'])->name('stageUnits');
            Route::get('/download-prn/{id}', [AdminPackingController::class, 'downloadPrn'])->name('downloadPrn');
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
            Route::get('/assignment-details', [AdminOrderDigitalizationController::class, 'getAssignmentDetails'])->name('assignment-details');
            Route::post('/store-stitching', [AdminOrderDigitalizationController::class, 'storeStitching'])->name('store-stitching');
            Route::post('/store-printing', [AdminOrderDigitalizationController::class, 'storePrinting'])->name('store-printing');
            Route::post('/get-lot-details-for-display', [AdminOrderDigitalizationController::class, 'getLotDetailsForDisplay'])->name('get-lot-details-for-display');
        });

        Route::prefix('/time-allocation')->name('time_allocation.')->group(function () {
            Route::get('/index', [\App\Http\Controllers\Admin\TimeAllocationController::class, 'index'])->name('index');
            Route::get('/indexList', [\App\Http\Controllers\Admin\TimeAllocationController::class, 'indexList'])->name('indexList');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\TimeAllocationController::class, 'edit'])->name('edit');
            Route::post('/update/{id}', [\App\Http\Controllers\Admin\TimeAllocationController::class, 'update'])->name('update');
            Route::post('/get-lot-details', [\App\Http\Controllers\Admin\TimeAllocationController::class, 'getLotDetails'])->name('get-lot-details');
            Route::get('/backfill', [\App\Http\Controllers\Admin\TimeAllocationController::class, 'backfill'])->name('backfill');
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
            Route::get('/download-pdf', [AdminOrderDispatchController::class, 'downloadPdf'])->name('download-pdf');
            Route::get('/download-invoice', [AdminOrderDispatchController::class, 'downloadInvoice'])->name('download-invoice');
            Route::get('/download-packing-slip', [AdminOrderDispatchController::class, 'downloadPackingSlip'])->name('download-packing-slip');
            Route::post('/update-invoice', [AdminOrderDispatchController::class, 'updateInvoice'])->name('update-invoice');
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

        Route::prefix('agent-orders')->name('agent-orders.')->group(function () {
            Route::get('/', [AdminAgentOrderController::class, 'index'])->name('index');
            Route::get('/create', [AdminAgentOrderController::class, 'create'])->name('create');
            Route::post('/store', [AdminAgentOrderController::class, 'store'])->name('store');
            Route::get('/get-shops', [AdminAgentOrderController::class, 'getShops'])->name('get-shops');
            Route::get('/get-fabric-rolls/{id}', [AdminAgentOrderController::class, 'getFabricRolls'])->name('get-fabric-rolls');
            Route::get('/{id}/show', [AdminAgentOrderController::class, 'show'])->name('show');
            Route::get('/{id}/delete', [AdminAgentOrderController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/edit', [AdminAgentOrderController::class, 'edit'])->name('edit');
            Route::put('/{id}/update', [AdminAgentOrderController::class, 'update'])->name('update');
            Route::get('/{id}/download-invoice', [AdminAgentOrderController::class, 'downloadInvoice'])->name('download-invoice');
            Route::get('/{id}/download-order', [AdminAgentOrderController::class, 'downloadOrder'])->name('download-order');
            Route::get('/{id}/send-whatsapp-order', [AdminAgentOrderController::class, 'sendWhatsappOrder'])->name('send-whatsapp-order');
            Route::get('/{id}/download-packing-slip', [AdminAgentOrderController::class, 'downloadPackingSlip'])->name('download-packing-slip');
            Route::get('/{id}/dispatch-scan', [AdminAgentOrderController::class, 'dispatchScan'])->name('dispatch-scan');
            Route::post('/{id}/process-scan', [AdminAgentOrderController::class, 'processScan'])->name('process-scan');
            Route::post('/{id}/remove-scan', [AdminAgentOrderController::class, 'removeScan'])->name('remove-scan');
            Route::post('/{id}/dispatch', [AdminAgentOrderController::class, 'dispatchOrder'])->name('dispatch');
            Route::post('/{id}/dispatch-fabric', [AdminAgentOrderController::class, 'dispatchFabric'])->name('dispatch-fabric');
            Route::get('/export/pdf',   [AdminAgentOrderController::class, 'exportPdf'])->name('export-pdf');
            Route::get('/export/excel', [AdminAgentOrderController::class, 'exportExcel'])->name('export-excel');
            Route::post('/update-item-location', [AdminAgentOrderController::class, 'updateItemLocation'])->name('update-item-location');

            // New Dispatch Session Routes
            Route::get('/dispatches', [AdminAgentOrderController::class, 'indexDispatches'])->name('dispatches.index');
            Route::get('/dispatches/{id}', [AdminAgentOrderController::class, 'dispatchShow'])->name('dispatches.show');
            Route::get('/dispatches/{id}/invoice', [AdminAgentOrderController::class, 'downloadDispatchInvoice'])->name('dispatches.download-invoice');
            Route::get('/dispatches/{id}/send-whatsapp-invoice', [AdminAgentOrderController::class, 'sendWhatsappDispatchInvoice'])->name('dispatches.send-whatsapp-invoice');
            Route::get('/dispatches/{id}/packing-slip', [AdminAgentOrderController::class, 'downloadDispatchPackingSlip'])->name('dispatches.download-packing-slip');
            Route::get('/dispatches/{id}/send-whatsapp-packing-slip', [AdminAgentOrderController::class, 'sendWhatsappDispatchPackingSlip'])->name('dispatches.send-whatsapp-packing-slip');
            Route::post('/dispatches/{id}/update-invoice', [AdminAgentOrderController::class, 'updateDispatchInvoice'])->name('dispatches.update-invoice');
            Route::get('/dispatches/{id}/delete', [AdminAgentOrderController::class, 'destroyDispatch'])->name('dispatches.destroy');
            Route::post('/dispatch-selected', [AdminAgentOrderController::class, 'dispatchSelected'])->name('dispatch-selected');

            // Returns
            Route::get('/dispatches/{id}/return', [AdminAgentOrderController::class, 'returnCreate'])->name('dispatches.return.create');
            Route::post('/dispatches/{id}/return', [AdminAgentOrderController::class, 'returnStore'])->name('dispatches.return.store');
            Route::get('/returns', [AdminAgentOrderController::class, 'indexReturns'])->name('returns.index');
            Route::get('/returns/{id}/download-pdf', [AdminAgentOrderController::class, 'downloadReturnPdf'])->name('returns.download-pdf');
            Route::get('/returns/{id}/send-whatsapp-pdf', [AdminAgentOrderController::class, 'sendWhatsappReturnPdf'])->name('returns.send-whatsapp-pdf');
            Route::get('/returns/{id}', [AdminAgentOrderController::class, 'returnShow'])->name('returns.show');
            Route::get('/returns/{id}/edit', [AdminAgentOrderController::class, 'returnEdit'])->name('returns.edit');
            Route::post('/returns/{id}/update', [AdminAgentOrderController::class, 'returnUpdate'])->name('returns.update');
            Route::delete('/returns/{id}/delete', [AdminAgentOrderController::class, 'returnDestroy'])->name('returns.destroy');
        });

        Route::prefix('direct-sales')->name('direct-sales.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SalesOrderController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\SalesOrderController::class, 'create'])->name('create');
            Route::post('/store', [\App\Http\Controllers\Admin\SalesOrderController::class, 'store'])->name('store');
            Route::get('/{id}/show', [\App\Http\Controllers\Admin\SalesOrderController::class, 'show'])->name('show');
        });

        Route::prefix('master')->name('master.')->group(function () {
            Route::resource('employees', \App\Http\Controllers\Admin\EmployeeController::class);
        });

        Route::prefix('payment/salary')->name('payment.salary.')->group(function () {
            Route::get('/create', [\App\Http\Controllers\Admin\Payment\SalaryPaymentController::class, 'create'])->name('create');
            Route::post('/store', [\App\Http\Controllers\Admin\Payment\SalaryPaymentController::class, 'store'])->name('store');
        });

        Route::prefix('payment/other')->name('payment.other.')->group(function () {
            Route::get('/create', [\App\Http\Controllers\Admin\Payment\OtherPaymentController::class, 'create'])->name('create');
            Route::post('/store', [\App\Http\Controllers\Admin\Payment\OtherPaymentController::class, 'store'])->name('store');
        });

        Route::prefix('payment/history')->name('payment.history.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Payment\PaymentHistoryController::class, 'index'])->name('index');
            Route::get('/{payment}', [\App\Http\Controllers\Admin\Payment\PaymentHistoryController::class, 'show'])->name('show');
            Route::get('/{payment}/edit', [\App\Http\Controllers\Admin\Payment\PaymentHistoryController::class, 'edit'])->name('edit');
            Route::put('/{payment}', [\App\Http\Controllers\Admin\Payment\PaymentHistoryController::class, 'update'])->name('update');
            Route::delete('/{payment}', [\App\Http\Controllers\Admin\Payment\PaymentHistoryController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('payment/dashboard')->name('payment.dashboard.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Payment\PaymentDashboardController::class, 'index'])->name('index');
            Route::get('/data', [\App\Http\Controllers\Admin\Payment\PaymentDashboardController::class, 'getData'])->name('getData');
        });
        Route::prefix('master/pattern')->name('master.pattern.')->group(function () {
            Route::get('/index', [AdminPatternController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminPatternController::class, 'indexList'])->name('indexList');
            Route::get('/all_patterns', [AdminPatternController::class, 'allPatterns'])->name('all_patterns');
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
            Route::get('/all_compositions', [AdminFabricCompositionController::class, 'allCompositions'])->name('all_compositions');
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
        Route::prefix('master/fabric_unit')->name('master.fabric_unit.')->group(function () {
            Route::get('/index', [AdminFabricUnitController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminFabricUnitController::class, 'indexList'])->name('indexList');
            Route::get('/all_units', [AdminFabricUnitController::class, 'allUnits'])->name('all_units');
            Route::get('/create', [AdminFabricUnitController::class, 'create'])->name('create');
            Route::post('/store', [AdminFabricUnitController::class, 'store'])->name('store');
            Route::get('/edit', [AdminFabricUnitController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminFabricUnitController::class, 'update'])->name('update');
            Route::get('/delete', [AdminFabricUnitController::class, 'delete'])->name('delete');
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
            Route::get('/export', [AdminInventoryController::class, 'export'])->name('export');
            Route::post('/update-attributes', [AdminInventoryController::class, 'updateAttributes'])->name('update_attributes');
            Route::post('/delete-boxes', [AdminInventoryController::class, 'deleteBoxes'])->name('delete_boxes');
            Route::get('/show', [AdminInventoryController::class, 'show'])->name('show');
            Route::get('/create', [AdminInventoryController::class, 'create'])->name('create');
            Route::get('/purchase', [AdminInventoryController::class, 'purchase'])->name('purchase');
            Route::get('/get-po-items/{id}', [AdminInventoryController::class, 'getProductionPOItems'])->name('get_po_items');
            Route::get('/purchase-list', [AdminInventoryController::class, 'purchaseList'])->name('purchase_list');
            Route::get('/purchase-list/data', [AdminInventoryController::class, 'purchaseListData'])->name('purchase_list.data');
            Route::delete('/purchase/{id}', [AdminInventoryController::class, 'destroyPurchase'])->name('purchase.destroy');

            // Warehouse Stock Routes
            Route::get('/warehouse-stock', [\App\Http\Controllers\Admin\WarehouseInventoryController::class, 'index'])->name('warehouse_stock');
            Route::get('/warehouse-stock/show/{product_id}/{size_set_id}/{rack_id}', [\App\Http\Controllers\Admin\WarehouseInventoryController::class, 'show'])->name('warehouse_stock.show');
            Route::get('/warehouse-stock/list', [\App\Http\Controllers\Admin\WarehouseInventoryController::class, 'indexList'])->name('warehouse_stock.list');
            Route::get('/warehouse-stock/export', [\App\Http\Controllers\Admin\WarehouseInventoryController::class, 'export'])->name('warehouse_stock.export');
            Route::get('/warehouse-stock/history', [\App\Http\Controllers\Admin\WarehouseInventoryController::class, 'history'])->name('warehouse_stock.history');
            Route::get('/warehouse-stock/history/{id}', [\App\Http\Controllers\Admin\WarehouseInventoryController::class, 'showHistory'])->name('warehouse_stock.history.show');
            Route::get('/warehouse-stock/history-list', [\App\Http\Controllers\Admin\WarehouseInventoryController::class, 'indexHistoryList'])->name('warehouse_stock.history.list');
            Route::post('/warehouse-stock/transfer-row', [\App\Http\Controllers\Admin\WarehouseInventoryController::class, 'transferRow'])->name('warehouse_stock.transfer_row');
            Route::post('/warehouse-stock/update-attributes', [\App\Http\Controllers\Admin\WarehouseInventoryController::class, 'updateAttributes'])->name('warehouse_stock.update_attributes');
            Route::post('/warehouse-stock/delete-boxes', [\App\Http\Controllers\Admin\WarehouseInventoryController::class, 'deleteBoxes'])->name('warehouse_stock.delete_boxes');
            Route::post('/warehouse-stock/transfer', [\App\Http\Controllers\Admin\WarehouseInventoryController::class, 'transfer'])->name('warehouse_stock.transfer');
            Route::get('/warehouse-stock/racks/{id}', [\App\Http\Controllers\Admin\WarehouseInventoryController::class, 'getRacksByStoreroom']);
            Route::get('/warehouse-stock/download-slip/{id}', [\App\Http\Controllers\Admin\WarehouseInventoryController::class, 'downloadSlip'])->name('warehouse_stock.download_slip');

            // Stock Transfer Routes
            Route::prefix('/stock-transfer')->name('stock_transfer.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\Inventory\StockTransferController::class, 'index'])->name('index');
                Route::get('/search', [\App\Http\Controllers\Admin\Inventory\StockTransferController::class, 'search'])->name('search');
                Route::get('/scan-barcode', [\App\Http\Controllers\Admin\Inventory\StockTransferController::class, 'scanBarcode'])->name('scan_barcode');
                Route::post('/transfer', [\App\Http\Controllers\Admin\Inventory\StockTransferController::class, 'transfer'])->name('transfer');
            });

            // Fabric Transfer Routes
            Route::prefix('/fabric-transfer')->name('fabric_transfer.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\Inventory\FabricTransferController::class, 'index'])->name('index');
                Route::get('/get-fabrics', [\App\Http\Controllers\Admin\Inventory\FabricTransferController::class, 'getFabrics'])->name('get-fabrics');
                Route::get('/get-rolls', [\App\Http\Controllers\Admin\Inventory\FabricTransferController::class, 'getRolls'])->name('get-rolls');
                Route::post('/store', [\App\Http\Controllers\Admin\Inventory\FabricTransferController::class, 'store'])->name('store');
                Route::get('/history', [\App\Http\Controllers\Admin\Inventory\FabricTransferController::class, 'history'])->name('history');
                Route::get('/history-list', [\App\Http\Controllers\Admin\Inventory\FabricTransferController::class, 'historyList'])->name('history-list');
                Route::get('/show/{id}', [\App\Http\Controllers\Admin\Inventory\FabricTransferController::class, 'show'])->name('show');
                Route::get('/download-pdf/{id}', [\App\Http\Controllers\Admin\Inventory\FabricTransferController::class, 'downloadPdf'])->name('download-pdf');
            });

            Route::post('/store', [AdminInventoryController::class, 'store'])->name('store');
            Route::get('/get-size-set-info/{id}', [AdminInventoryController::class, 'getSizeSetInfo'])->name('get_size_set_info');
            Route::get('/get-product-full-details', [AdminInventoryController::class, 'getProductFullDetails'])->name('get_product_full_details');
            Route::get('/master-data', [AdminInventoryController::class, 'getMasterData'])->name('master_data');
            Route::get('/get-pricing-info', [AdminInventoryController::class, 'getPricingInfo'])->name('get_pricing_info');
            Route::get('/get-locations', [AdminInventoryController::class, 'getLocations'])->name('get_locations');
            Route::get('/get-domestic-inventory-for-consume', [AdminInventoryController::class, 'getDomesticInventoryForConsume'])->name('get_domestic_inventory_for_consume');
            Route::get('/barcode-generator', [\App\Http\Controllers\Admin\Inventory\BarcodeGeneratorController::class, 'index'])->name('barcode-generator.index');
            Route::post('/barcode-generator/generate', [\App\Http\Controllers\Admin\Inventory\BarcodeGeneratorController::class, 'generate'])->name('barcode-generator.generate');
            Route::post('/barcode-generator/generate-tspl', [\App\Http\Controllers\Admin\Inventory\BarcodeGeneratorController::class, 'generateTspl'])->name('barcode-generator.generate-tspl');
            Route::post('/barcode-generator/generate-bulk', [\App\Http\Controllers\Admin\Inventory\BarcodeGeneratorController::class, 'generateBulk'])->name('barcode-generator.generate-bulk');
            Route::post('/barcode-generator/generate-bulk-tspl', [\App\Http\Controllers\Admin\Inventory\BarcodeGeneratorController::class, 'generateBulkTspl'])->name('barcode-generator.generate-bulk-tspl');
            Route::post('/barcode-generator/generate-by-barcodes', [\App\Http\Controllers\Admin\Inventory\BarcodeGeneratorController::class, 'generateByBarcodes'])->name('barcode-generator.generate-by-barcodes');
            Route::post('/barcode-generator/generate-by-location', [\App\Http\Controllers\Admin\Inventory\BarcodeGeneratorController::class, 'generateByLocation'])->name('barcode-generator.generate-by-location');
            Route::post('/barcode-generator/generate-by-location-tspl', [\App\Http\Controllers\Admin\Inventory\BarcodeGeneratorController::class, 'generateByLocationTspl'])->name('barcode-generator.generate-by-location-tspl');

            Route::get('/get-products-by-series', [AdminInventoryController::class, 'getProductsBySeries'])->name('get_products_by_series');
            Route::get('/get-product-details', [AdminInventoryController::class, 'getProductDetails'])->name('get_product_details');
            Route::get('/get-size-sets-by-product/{product_id}', [AdminInventoryController::class, 'getSizeSetsByProduct'])->name('get_size_sets_by_product');
            Route::get('/get-colors-by-product-size/{product_id}/{size_set_id}', [AdminInventoryController::class, 'getColorsByProductSize'])->name('get_colors_by_product_size');

            // Outflow / Loss Reporting
            Route::prefix('/outflow')->name('outflow.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\Inventory\OutflowInventoryController::class, 'index'])->name('index');
                Route::get('/list', [\App\Http\Controllers\Admin\Inventory\OutflowInventoryController::class, 'indexList'])->name('list');
            });

            // Attribute Change History
            Route::prefix('/attribute-history')->name('attribute-history.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\Inventory\OutflowInventoryController::class, 'attributeHistoryIndex'])->name('index');
                Route::get('/list', [\App\Http\Controllers\Admin\Inventory\OutflowInventoryController::class, 'attributeHistoryList'])->name('list');
            });

            // Sample Product Routes
            Route::prefix('/sample-product')->name('sample-product.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\Inventory\SampleProductController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\Admin\Inventory\SampleProductController::class, 'create'])->name('create');
                Route::get('/get-products', [\App\Http\Controllers\Admin\Inventory\SampleProductController::class, 'getProducts'])->name('get-products');
                Route::post('/store', [\App\Http\Controllers\Admin\Inventory\SampleProductController::class, 'store'])->name('store');
                Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Inventory\SampleProductController::class, 'edit'])->name('edit');
                Route::get('/show/{id}', [\App\Http\Controllers\Admin\Inventory\SampleProductController::class, 'show'])->name('show');
                Route::put('/update/{id}', [\App\Http\Controllers\Admin\Inventory\SampleProductController::class, 'update'])->name('update');
                Route::get('/generate-pdf-batch/{id}', [\App\Http\Controllers\Admin\Inventory\SampleProductController::class, 'generatePdfFromBatch'])->name('generate-pdf-batch');
                Route::delete('/destroy/{id}', [\App\Http\Controllers\Admin\Inventory\SampleProductController::class, 'destroy'])->name('destroy');
            });

            // Fair Product Routes
            Route::prefix('/fair-product')->name('fair-product.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\Inventory\FairProductController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\Admin\Inventory\FairProductController::class, 'create'])->name('create');
                Route::get('/get-products', [\App\Http\Controllers\Admin\Inventory\FairProductController::class, 'getProducts'])->name('get-products');
                Route::get('/search-by-design', [\App\Http\Controllers\Admin\Inventory\FairProductController::class, 'searchByDesign'])->name('search-by-design');
                Route::post('/store', [\App\Http\Controllers\Admin\Inventory\FairProductController::class, 'store'])->name('store');
                Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Inventory\FairProductController::class, 'edit'])->name('edit');
                Route::get('/show/{id}', [\App\Http\Controllers\Admin\Inventory\FairProductController::class, 'show'])->name('show');
                Route::put('/update/{id}', [\App\Http\Controllers\Admin\Inventory\FairProductController::class, 'update'])->name('update');
                Route::get('/generate-pdf-batch/{id}', [\App\Http\Controllers\Admin\Inventory\FairProductController::class, 'generatePdfFromBatch'])->name('generate-pdf-batch');
                Route::get('/download-prn', [\App\Http\Controllers\Admin\Inventory\FairProductController::class, 'downloadPrn'])->name('download-prn');
                Route::post('/download-prn-by-barcodes', [\App\Http\Controllers\Admin\Inventory\FairProductController::class, 'downloadPrnByBarcodes'])->name('download-prn-by-barcodes');
                Route::delete('/destroy/{id}', [\App\Http\Controllers\Admin\Inventory\FairProductController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('/purchase-history')->name('purchase_history.')->group(function () {
                Route::get('/', [AdminInventoryController::class, 'purchaseHistory'])->name('index');
                Route::get('/list', [AdminInventoryController::class, 'purchaseHistoryList'])->name('list');
                Route::get('/{id}/edit', [AdminInventoryController::class, 'purchaseHistoryEdit'])->name('edit');
                Route::get('/{id}/view', [AdminInventoryController::class, 'purchaseHistoryShow'])->name('show');
                Route::get('/{id}/download-prn', [\App\Http\Controllers\Admin\Inventory\BarcodeGeneratorController::class, 'generatePurchasePrn'])->name('download-prn');
                Route::post('/{id}/update', [AdminInventoryController::class, 'purchaseHistoryUpdate'])->name('update');
                Route::delete('/{id}/delete', [AdminInventoryController::class, 'purchaseHistoryDestroy'])->name('destroy');
            });

            Route::prefix('/inbound-history')->name('inbound_history.')->group(function () {
                Route::get('/', [AdminInventoryController::class, 'inboundHistoryIndex'])->name('index');
                Route::get('/list', [AdminInventoryController::class, 'inboundHistoryList'])->name('list');
                Route::get('/show/{id}', [AdminInventoryController::class, 'inboundHistoryShow'])->name('show');
                Route::get('/{id}/download-prn', [\App\Http\Controllers\Admin\Inventory\BarcodeGeneratorController::class, 'generateInboundPrn'])->name('download-prn');
            });

            // Stock Disposal Routes
            Route::prefix('/stock-disposal')->name('stock_disposal.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\StockDisposalController::class, 'index'])->name('index');
                Route::get('/list', [\App\Http\Controllers\Admin\StockDisposalController::class, 'historyList'])->name('list');
                Route::get('/create', [\App\Http\Controllers\Admin\StockDisposalController::class, 'create'])->name('create');
                Route::get('/search', [\App\Http\Controllers\Admin\StockDisposalController::class, 'search'])->name('search');
                Route::get('/get-fabrics', [\App\Http\Controllers\Admin\StockDisposalController::class, 'getFabrics'])->name('get-fabrics');
                Route::get('/get-rolls', [\App\Http\Controllers\Admin\StockDisposalController::class, 'getRolls'])->name('get-rolls');
                Route::get('/get-domestic-stock', [\App\Http\Controllers\Admin\StockDisposalController::class, 'getDomesticStock'])->name('get-domestic-stock');
                Route::get('/get-product-details', [\App\Http\Controllers\Admin\StockDisposalController::class, 'getProductDetails'])->name('get-product-details');
                Route::get('/get-size-colors', [\App\Http\Controllers\Admin\StockDisposalController::class, 'getSizeColors'])->name('get-size-colors');
                Route::get('/show/{id}', [\App\Http\Controllers\Admin\StockDisposalController::class, 'show'])->name('show');
                Route::get('/edit/{id}', [\App\Http\Controllers\Admin\StockDisposalController::class, 'edit'])->name('edit');
                Route::post('/update/{id}', [\App\Http\Controllers\Admin\StockDisposalController::class, 'update'])->name('update');
                Route::delete('/delete/{id}', [\App\Http\Controllers\Admin\StockDisposalController::class, 'destroy'])->name('delete');
                Route::post('/store', [\App\Http\Controllers\Admin\StockDisposalController::class, 'store'])->name('store');
            });
        });



        Route::prefix('/inventory-prices')->name('inventory-prices.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\InventoryPriceController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\InventoryPriceController::class, 'create'])->name('create');
            Route::post('/store', [\App\Http\Controllers\Admin\InventoryPriceController::class, 'store'])->name('store');
            Route::post('/update-price', [\App\Http\Controllers\Admin\InventoryPriceController::class, 'updatePrice'])->name('update-price');
        });

        Route::prefix('master/size')->name('master.size.')->group(function () {
            Route::get('/index', [AdminSizeController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminSizeController::class, 'indexList'])->name('indexList');
            Route::get('/all_sizes', [AdminSizeController::class, 'allSizes'])->name('all_sizes');
            Route::get('/create', [AdminSizeController::class, 'create'])->name('create');
            Route::post('/store', [AdminSizeController::class, 'store'])->name('store');
            Route::get('/edit', [AdminSizeController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminSizeController::class, 'update'])->name('update');
            Route::get('/delete', [AdminSizeController::class, 'delete'])->name('delete');
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
            Route::get('/get-next-product-name', [AdminProductionGoodsController::class, 'getNextProductName'])->name('get-next-product-name');
            Route::get('/check-design-number', [AdminProductionGoodsController::class, 'checkDesignNumber'])->name('check-design-number');
            Route::get('/export', [AdminProductionGoodsController::class, 'export'])->name('export');
            Route::get('/pdf', [AdminProductionGoodsController::class, 'pdf'])->name('pdf');
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


        Route::prefix('master/order-remarks')->name('master.order-remarks.')->group(function () {
            Route::get('/index', [AdminMasterOrderRemarkController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminMasterOrderRemarkController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminMasterOrderRemarkController::class, 'create'])->name('create');
            Route::post('/store', [AdminMasterOrderRemarkController::class, 'store'])->name('store');
            Route::get('/edit', [AdminMasterOrderRemarkController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminMasterOrderRemarkController::class, 'update'])->name('update');
            Route::get('/delete', [AdminMasterOrderRemarkController::class, 'delete'])->name('delete');
        });

        Route::prefix('master/colors')->name('master.colors.')->group(function () {
            Route::get('/index', [AdminMasterColorController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminMasterColorController::class, 'indexList'])->name('indexList');
            Route::get('/all_colors', [AdminMasterColorController::class, 'allColors'])->name('all_colors');
            Route::get('/create', [AdminMasterColorController::class, 'create'])->name('create');
            Route::post('/store', [AdminMasterColorController::class, 'store'])->name('store');
            Route::get('/edit', [AdminMasterColorController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminMasterColorController::class, 'update'])->name('update');
            Route::get('/delete', [AdminMasterColorController::class, 'delete'])->name('delete');
        });

        Route::prefix('master/product-nature')->name('master.product-nature.')->group(function () {
            Route::get('/index', [AdminProductNatureController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminProductNatureController::class, 'indexList'])->name('indexList');
            Route::get('/all_product_natures', [AdminProductNatureController::class, 'allProductNatures'])->name('all_product_natures');
            Route::get('/create', [AdminProductNatureController::class, 'create'])->name('create');
            Route::post('/store', [AdminProductNatureController::class, 'store'])->name('store');
            Route::get('/edit', [AdminProductNatureController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminProductNatureController::class, 'update'])->name('update');
            Route::get('/delete', [AdminProductNatureController::class, 'delete'])->name('delete');
        });

        Route::prefix('master/fabric-type')->name('master.fabric-type.')->group(function () {
            Route::get('/index', [AdminFabricTypeController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminFabricTypeController::class, 'indexList'])->name('indexList');
            Route::get('/all_fabric_types', [AdminFabricTypeController::class, 'allFabricTypes'])->name('all_fabric_types');
            Route::get('/create', [AdminFabricTypeController::class, 'create'])->name('create');
            Route::post('/store', [AdminFabricTypeController::class, 'store'])->name('store');
            Route::get('/edit', [AdminFabricTypeController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminFabricTypeController::class, 'update'])->name('update');
            Route::get('/delete', [AdminFabricTypeController::class, 'delete'])->name('delete');
        });
        Route::prefix('master/series')->name('master.series.')->group(function () {
            Route::get('/index', [AdminMasterSeriesController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminMasterSeriesController::class, 'indexList'])->name('indexList');
            Route::get('/all_series', [AdminMasterSeriesController::class, 'allSeries'])->name('all_series');
            Route::get('/create', [AdminMasterSeriesController::class, 'create'])->name('create');
            Route::post('/store', [AdminMasterSeriesController::class, 'store'])->name('store');
            Route::get('/edit', [AdminMasterSeriesController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminMasterSeriesController::class, 'update'])->name('update');
            Route::get('/delete', [AdminMasterSeriesController::class, 'delete'])->name('delete');
        });
        Route::prefix('master/fitting')->name('master.fitting.')->group(function () {
            Route::get('/index', [AdminMasterFittingController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminMasterFittingController::class, 'indexList'])->name('indexList');
            Route::get('/all_fittings', [AdminMasterFittingController::class, 'allFittings'])->name('all_fittings');
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

        Route::prefix('master/sales-agent')->name('master.sales-agent.')->group(function () {
            Route::get('/index', [AdminSalesAgentController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminSalesAgentController::class, 'indexList'])->name('indexList');
            Route::get('/downloadPdf', [AdminSalesAgentController::class, 'downloadPdf'])->name('downloadPdf');
            Route::get('/create', [AdminSalesAgentController::class, 'create'])->name('create');
            Route::post('/store', [AdminSalesAgentController::class, 'store'])->name('store');
            Route::get('/edit', [AdminSalesAgentController::class, 'edit'])->name('edit');
            Route::get('/view', [AdminSalesAgentController::class, 'view'])->name('view');
            Route::post('/update', [AdminSalesAgentController::class, 'update'])->name('update');
            Route::get('/delete', [AdminSalesAgentController::class, 'delete'])->name('delete');
        });

        Route::prefix('master/sales-man')->name('master.sales-man.')->group(function () {
            Route::get('/index', [\App\Http\Controllers\Admin\Master\SalesManController::class, 'index'])->name('index');
            Route::get('/all_sales_men', [\App\Http\Controllers\Admin\Master\SalesManController::class, 'allSalesMen'])->name('all_sales_men');
            Route::get('/create', [\App\Http\Controllers\Admin\Master\SalesManController::class, 'create'])->name('create');
            Route::post('/store', [\App\Http\Controllers\Admin\Master\SalesManController::class, 'store'])->name('store');
            Route::get('/edit/{salesMan}', [\App\Http\Controllers\Admin\Master\SalesManController::class, 'edit'])->name('edit');
            Route::put('/update/{salesMan}', [\App\Http\Controllers\Admin\Master\SalesManController::class, 'update'])->name('update');
            Route::delete('/destroy/{salesMan}', [\App\Http\Controllers\Admin\Master\SalesManController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('master/purchase-agent')->name('master.purchase-agent.')->group(function () {
            Route::get('/index', [AdminPurchaseAgentController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminPurchaseAgentController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminPurchaseAgentController::class, 'create'])->name('create');
            Route::post('/store', [AdminPurchaseAgentController::class, 'store'])->name('store');
            Route::get('/edit', [AdminPurchaseAgentController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminPurchaseAgentController::class, 'update'])->name('update');
            Route::get('/delete', [AdminPurchaseAgentController::class, 'delete'])->name('delete');
        });

        Route::prefix('master/sales-agent-shops')->name('master.sales-agent-shops.')->group(function () {
            Route::get('/{agent_id}', [\App\Http\Controllers\Admin\Master\SalesAgentShopController::class, 'index'])->name('index');
            Route::get('/{agent_id}/indexList', [\App\Http\Controllers\Admin\Master\SalesAgentShopController::class, 'indexList'])->name('indexList');
            Route::get('/{agent_id}/create', [\App\Http\Controllers\Admin\Master\SalesAgentShopController::class, 'create'])->name('create');
            Route::post('/{agent_id}/store', [\App\Http\Controllers\Admin\Master\SalesAgentShopController::class, 'store'])->name('store');
            Route::get('/{agent_id}/edit/{id}', [\App\Http\Controllers\Admin\Master\SalesAgentShopController::class, 'edit'])->name('edit');
            Route::post('/{agent_id}/update/{id}', [\App\Http\Controllers\Admin\Master\SalesAgentShopController::class, 'update'])->name('update');
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

        Route::prefix('master/brand')->name('master.brand.')->group(function () {
            Route::get('/index', [AdminBrandController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminBrandController::class, 'indexList'])->name('indexList');
            Route::get('/all_brands', [AdminBrandController::class, 'allBrands'])->name('all_brands');
            Route::get('/create', [AdminBrandController::class, 'create'])->name('create');
            Route::post('/store', [AdminBrandController::class, 'store'])->name('store');
            Route::get('/edit', [AdminBrandController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminBrandController::class, 'update'])->name('update');
            Route::post('/delete', [AdminBrandController::class, 'delete'])->name('delete');
        });

        Route::prefix('master/company')->name('master.company.')->group(function () {
            Route::get('/index', [AdminCompanyController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminCompanyController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminCompanyController::class, 'create'])->name('create');
            Route::post('/store', [AdminCompanyController::class, 'store'])->name('store');
            Route::get('/edit', [AdminCompanyController::class, 'edit'])->name('edit');
            Route::post('/update', [AdminCompanyController::class, 'update'])->name('update');
            Route::post('/delete', [AdminCompanyController::class, 'delete'])->name('delete');
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
            Route::get('/downloadPdf', [AdminCustomerController::class, 'downloadPdf'])->name('downloadPdf');
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
            Route::get('/edit', [\App\Http\Controllers\Admin\Master\AdminMasterStoreroomController::class, 'edit'])->name('edit');
            Route::post('/update', [\App\Http\Controllers\Admin\Master\AdminMasterStoreroomController::class, 'update'])->name('update');
            Route::get('/delete', [\App\Http\Controllers\Admin\Master\AdminMasterStoreroomController::class, 'delete'])->name('delete');
            Route::get('/{id}/racks', [\App\Http\Controllers\Admin\Master\AdminMasterStoreroomController::class, 'racks'])->name('racks');

            // Rack AJAX
            Route::post('/rack/store', [\App\Http\Controllers\Admin\Master\AdminMasterStoreroomController::class, 'storeRack'])->name('rack.store');
            Route::post('/rack/update', [\App\Http\Controllers\Admin\Master\AdminMasterStoreroomController::class, 'updateRack'])->name('rack.update');
            Route::get('/rack/delete/{id}', [\App\Http\Controllers\Admin\Master\AdminMasterStoreroomController::class, 'deleteRack'])->name('rack.delete');
        });

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/index', [AdminUserController::class, 'index'])->name('index');
            Route::get('/indexList', [AdminUserController::class, 'indexList'])->name('indexList');
            Route::get('/create', [AdminUserController::class, 'create'])->name('create');
            Route::post('/store', [AdminUserController::class, 'store'])->name('store');
            Route::get('/edit/{id}', [AdminUserController::class, 'edit'])->name('edit');
            Route::post('/update/{id}', [AdminUserController::class, 'update'])->name('update');
            Route::get('/delete/{id}', [AdminUserController::class, 'delete'])->name('delete');
        });
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/index', [AdminRoleController::class, 'index'])->name('index');
            Route::get('/create', [AdminRoleController::class, 'create'])->name('create');
            Route::post('/store', [AdminRoleController::class, 'store'])->name('store');
            Route::get('/edit/{role}', [AdminRoleController::class, 'edit'])->name('edit');
            Route::post('/update/{role}', [AdminRoleController::class, 'update'])->name('update');
            Route::get('/delete/{role}', [AdminRoleController::class, 'delete'])->name('delete');
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
            Route::post('/lots/delete-multiple', [AdminReportController::class, 'deleteMultipleLots'])->name('lots.delete-multiple');
            Route::get('/lots/lot-details/{lot_no}', [AdminReportController::class, 'lotDetails'])->name('lots.lot-details');
            Route::delete('/lots/delete-session/{type}/{id}', [AdminReportController::class, 'deleteLotSession'])->name('lots.delete-session');
            Route::get('/lots/lot-details-pdf/{lot_no}', [AdminReportController::class, 'lotDetailsPdf'])->name('lots.lot-details.pdf');
            Route::get('/stock', [AdminReportController::class, 'stock'])->name('stock');
            Route::get('/stock/roll-details', [AdminReportController::class, 'fabricRollDetails'])->name('stock.roll.details');
            Route::get('/stock/export', [AdminReportController::class, 'stockExport'])->name('stock.export');
            Route::get('/stock/pdf', [AdminReportController::class, 'stockPdf'])->name('stock.pdf');
            Route::get('/stock-rolls', [AdminReportController::class, 'stockRolls'])->name('stock.rolls');
            Route::get('/stock-rolls/tracking', [AdminReportController::class, 'stockRollTracking'])->name('stock.rolls.tracking');
            Route::get('/fabric-return', [AdminReportController::class, 'fabricReturn'])->name('fabric_return');
            Route::get('/fabric-return/view/{id}', [AdminReportController::class, 'fabricReturnView'])->name('fabric_return_view');
            Route::get('/purchase-order', [AdminReportController::class, 'purchaseOrder'])->name('purchase_order');
            Route::get('/purchase-order-fabric-wise', [AdminReportController::class, 'purchaseOrderFabricWise'])->name('purchase_order_fabric_wise');
            Route::get('/purchase-order-fabric-wise-shipments', [AdminReportController::class, 'purchaseOrderFabricWiseShipments'])->name('purchase_order_fabric_wise_shipments');
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
            Route::post('/purchase-order/close/{id}', [AdminReportController::class, 'closePurchaseOrder'])->name('purchase_order.close');
            Route::get('/order-tracking-system', [AdminReportController::class, 'orderTrackingSystem'])->name('orderTrackingSystem');
            Route::get('/order-lot-tracking', [AdminReportController::class, 'lotTrackingDetails'])->name('lotTrackingDetails');
            Route::get('/order-tracking/export', [AdminReportController::class, 'orderTrackingExport'])->name('orderTracking.export');

            Route::get('/dispatch-order', [AdminReportController::class, 'dispatchOrder'])->name('dispatch-order');

            // Agent Ledger Report
            Route::prefix('agent-ledger')->name('agent-ledger.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\Report\AgentLedgerReportController::class, 'index'])->name('index');
                Route::get('/{id}', [\App\Http\Controllers\Admin\Report\AgentLedgerReportController::class, 'show'])->name('show');
            });
        });

        // Ledger Reports
        Route::prefix('/ledger')->name('ledger.')->group(function () {
            Route::prefix('/fabric')->name('fabric.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\Ledger\FabricLedgerController::class, 'index'])->name('index');
                Route::get('/show/{id}', [App\Http\Controllers\Admin\Ledger\FabricLedgerController::class, 'show'])->name('show');
            });
            Route::prefix('/production-goods')->name('production-goods.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\Ledger\ProductionGoodsLedgerController::class, 'index'])->name('index');
                Route::get('/show/{id}', [App\Http\Controllers\Admin\Ledger\ProductionGoodsLedgerController::class, 'show'])->name('show');
            });
            Route::prefix('/lot')->name('lot.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\Ledger\LotLedgerController::class, 'index'])->name('index');
                Route::get('/show/{lot_no}', [App\Http\Controllers\Admin\Ledger\LotLedgerController::class, 'show'])->name('show');
            });
            Route::prefix('/party')->name('party.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\Ledger\PartyLedgerController::class, 'index'])->name('index');
                Route::get('/show/{type}/{id}', [App\Http\Controllers\Admin\Ledger\PartyLedgerController::class, 'show'])->name('show');
                Route::get('/download/{type}/{id}', [App\Http\Controllers\Admin\Ledger\PartyLedgerController::class, 'download'])->name('download');
            });
            Route::prefix('/bank-cash-ledger')->name('bank-cash-ledger.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\Ledger\BankCashLedgerController::class, 'index'])->name('index');
                Route::get('/show/{type}/{id}', [App\Http\Controllers\Admin\Ledger\BankCashLedgerController::class, 'show'])->name('show');
                Route::get('/download/{type}/{id}', [App\Http\Controllers\Admin\Ledger\BankCashLedgerController::class, 'download'])->name('download');
            });
            Route::prefix('/sales')->name('sales.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\Ledger\SalesLedgerController::class, 'index'])->name('index');
            });
            Route::prefix('/purchase')->name('purchase.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\Ledger\PurchaseLedgerController::class, 'index'])->name('index');
            });
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

            Route::get('/unit-assignments', [AdminReportController::class, 'unitAssignments'])->name('unit-assignments');
            Route::get('/unit-assignments/export', [AdminReportController::class, 'unitAssignmentsExport'])->name('unit-assignments.export');
            Route::get('/unit-assignments/pdf', [AdminReportController::class, 'unitAssignmentsPdf'])->name('unit-assignments.pdf');
            Route::post('/unit-assignments/{type}/{id}/close', [AdminReportController::class, 'closeUnitAssignment'])->name('unit-assignments.close');
            Route::post('/unit-assignments/{type}/{id}/reopen', [AdminReportController::class, 'reopenUnitAssignment'])->name('unit-assignments.reopen');

            Route::get('/design-wip', [AdminReportController::class, 'designWip'])->name('design-wip');
            
            Route::get('/sales-man-report', [AdminReportController::class, 'salesManReport'])->name('salesManReport');
            Route::get('/sales-man-report/{id}', [AdminReportController::class, 'salesManReportDetail'])->name('salesManReportDetail');
        });
    });

    Route::middleware(['checkAdminLogin'])->group(function () {

        Route::prefix('payment')->name('payment.')->group(function () {
            Route::prefix('fabric-shipment')->name('fabric-shipment.')->group(function () {
                Route::get('/create', [FabricShipmentPaymentController::class, 'create'])->name('create');
                Route::get('/get-shipments', [FabricShipmentPaymentController::class, 'getShipments'])->name('get-shipments');
                Route::post('/store', [FabricShipmentPaymentController::class, 'store'])->name('store');
            });

            Route::prefix('agent-order')->name('agent-order.')->group(function () {
                Route::get('/create', [\App\Http\Controllers\Admin\Payment\AgentOrderPaymentController::class, 'create'])->name('create');
                Route::get('/get-orders', [\App\Http\Controllers\Admin\Payment\AgentOrderPaymentController::class, 'getOrders'])->name('get-orders');
                Route::post('/store', [\App\Http\Controllers\Admin\Payment\AgentOrderPaymentController::class, 'store'])->name('store');
            });

            Route::get('/pending', [\App\Http\Controllers\Admin\Payment\PendingPaymentController::class, 'index'])->name('pending.index');

            Route::prefix('corporate-order')->name('corporate-order.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\Payment\CorporateOrderPaymentController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\Admin\Payment\CorporateOrderPaymentController::class, 'create'])->name('create');
                Route::get('/get-dispatches', [\App\Http\Controllers\Admin\Payment\CorporateOrderPaymentController::class, 'getDispatches'])->name('get-dispatches');
                Route::post('/store', [\App\Http\Controllers\Admin\Payment\CorporateOrderPaymentController::class, 'store'])->name('store');
            });

            Route::prefix('master')->name('master.')->group(function () {
                Route::prefix('cash-payment')->name('cash_payment.')->group(function () {
                    Route::get('/index', [AdminCashPaymentController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminCashPaymentController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminCashPaymentController::class, 'create'])->name('create');
                    Route::post('/store', [AdminCashPaymentController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminCashPaymentController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminCashPaymentController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminCashPaymentController::class, 'delete'])->name('delete');
                });
                Route::prefix('bank-account')->name('bank_account.')->group(function () {
                    Route::get('/index', [AdminBankAccountController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminBankAccountController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminBankAccountController::class, 'create'])->name('create');
                    Route::post('/store', [AdminBankAccountController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminBankAccountController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminBankAccountController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminBankAccountController::class, 'delete'])->name('delete');
                });
                Route::prefix('payment-type')->name('payment_type.')->group(function () {
                    Route::get('/index', [AdminPaymentTypeController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminPaymentTypeController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminPaymentTypeController::class, 'create'])->name('create');
                    Route::post('/store', [AdminPaymentTypeController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminPaymentTypeController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminPaymentTypeController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminPaymentTypeController::class, 'delete'])->name('delete');
                });
                Route::prefix('tax')->name('tax.')->group(function () {
                    Route::get('/index', [AdminTaxController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminTaxController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminTaxController::class, 'create'])->name('create');
                    Route::post('/store', [AdminTaxController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminTaxController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminTaxController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminTaxController::class, 'delete'])->name('delete');
                });
                Route::prefix('interest')->name('interest.')->group(function () {
                    Route::get('/index', [AdminInterestController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminInterestController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminInterestController::class, 'create'])->name('create');
                    Route::post('/store', [AdminInterestController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminInterestController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminInterestController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminInterestController::class, 'delete'])->name('delete');
                });
                Route::prefix('tour_expense')->name('tour_expense.')->group(function () {
                    Route::get('/index', [AdminTourExpenseController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminTourExpenseController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminTourExpenseController::class, 'create'])->name('create');
                    Route::post('/store', [AdminTourExpenseController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminTourExpenseController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminTourExpenseController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminTourExpenseController::class, 'delete'])->name('delete');
                });
                Route::prefix('fare_expense')->name('fare_expense.')->group(function () {
                    Route::get('/index', [AdminFareExpenseController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminFareExpenseController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminFareExpenseController::class, 'create'])->name('create');
                    Route::post('/store', [AdminFareExpenseController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminFareExpenseController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminFareExpenseController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminFareExpenseController::class, 'delete'])->name('delete');
                });
                Route::prefix('sk_expense')->name('sk_expense.')->group(function () {
                    Route::get('/index', [AdminSkExpenseController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminSkExpenseController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminSkExpenseController::class, 'create'])->name('create');
                    Route::post('/store', [AdminSkExpenseController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminSkExpenseController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminSkExpenseController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminSkExpenseController::class, 'delete'])->name('delete');
                });
                Route::prefix('agent_payment')->name('agent_payment.')->group(function () {
                    Route::get('/index', [AdminAgentPaymentMasterController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminAgentPaymentMasterController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminAgentPaymentMasterController::class, 'create'])->name('create');
                    Route::post('/store', [AdminAgentPaymentMasterController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminAgentPaymentMasterController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminAgentPaymentMasterController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminAgentPaymentMasterController::class, 'delete'])->name('delete');
                });
                Route::prefix('washing_master')->name('washing_master.')->group(function () {
                    Route::get('/index', [AdminWashingMasterController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminWashingMasterController::class, 'indexList'])->name('indexList');
                    Route::get('/all_washing_masters', [AdminWashingMasterController::class, 'allWashingMasters'])->name('all_washing_masters');
                    Route::get('/create', [AdminWashingMasterController::class, 'create'])->name('create');
                    Route::post('/store', [AdminWashingMasterController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminWashingMasterController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminWashingMasterController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminWashingMasterController::class, 'delete'])->name('delete');
                });
                Route::prefix('cutting_payment')->name('cutting_payment.')->group(function () {
                    Route::get('/index', [AdminCuttingPaymentMasterController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminCuttingPaymentMasterController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminCuttingPaymentMasterController::class, 'create'])->name('create');
                    Route::post('/store', [AdminCuttingPaymentMasterController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminCuttingPaymentMasterController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminCuttingPaymentMasterController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminCuttingPaymentMasterController::class, 'delete'])->name('delete');
                });
                Route::prefix('contractor')->name('contractor.')->group(function () {
                    Route::get('/index', [AdminContractorController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminContractorController::class, 'indexList'])->name('indexList');
                    Route::get('/all_contractors', [AdminContractorController::class, 'allContractors'])->name('all_contractors');
                    Route::get('/create', [AdminContractorController::class, 'create'])->name('create');
                    Route::post('/store', [AdminContractorController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminContractorController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminContractorController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminContractorController::class, 'delete'])->name('delete');
                });
                Route::prefix('consumable_good')->name('consumable_good.')->group(function () {
                    Route::get('/index', [AdminConsumableGoodController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminConsumableGoodController::class, 'indexList'])->name('indexList');
                    Route::get('/all_consumables', [AdminConsumableGoodController::class, 'allConsumables'])->name('all_consumables');
                    Route::get('/create', [AdminConsumableGoodController::class, 'create'])->name('create');
                    Route::post('/store', [AdminConsumableGoodController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminConsumableGoodController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminConsumableGoodController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminConsumableGoodController::class, 'delete'])->name('delete');
                });
                Route::prefix('committee')->name('committee.')->group(function () {
                    Route::get('/index', [\App\Http\Controllers\Admin\Payment\Master\CommitteeController::class, 'index'])->name('index');
                    Route::get('/indexList', [\App\Http\Controllers\Admin\Payment\Master\CommitteeController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [\App\Http\Controllers\Admin\Payment\Master\CommitteeController::class, 'create'])->name('create');
                    Route::post('/store', [\App\Http\Controllers\Admin\Payment\Master\CommitteeController::class, 'store'])->name('store');
                    Route::get('/edit', [\App\Http\Controllers\Admin\Payment\Master\CommitteeController::class, 'edit'])->name('edit');
                    Route::post('/update', [\App\Http\Controllers\Admin\Payment\Master\CommitteeController::class, 'update'])->name('update');
                    Route::get('/delete', [\App\Http\Controllers\Admin\Payment\Master\CommitteeController::class, 'delete'])->name('delete');
                });
                Route::prefix('company_capital')->name('company_capital.')->group(function () {
                    Route::get('/index', [\App\Http\Controllers\Admin\Payment\Master\CompanyCapitalController::class, 'index'])->name('index');
                    Route::get('/indexList', [\App\Http\Controllers\Admin\Payment\Master\CompanyCapitalController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [\App\Http\Controllers\Admin\Payment\Master\CompanyCapitalController::class, 'create'])->name('create');
                    Route::post('/store', [\App\Http\Controllers\Admin\Payment\Master\CompanyCapitalController::class, 'store'])->name('store');
                });
                Route::prefix('general_expense')->name('general_expense.')->group(function () {
                    Route::get('/index', [AdminGeneralExpenseController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminGeneralExpenseController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminGeneralExpenseController::class, 'create'])->name('create');
                    Route::post('/store', [AdminGeneralExpenseController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminGeneralExpenseController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminGeneralExpenseController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminGeneralExpenseController::class, 'delete'])->name('delete');
                });
                Route::prefix('electricity_expense')->name('electricity_expense.')->group(function () {
                    Route::get('/index', [AdminElectricityExpenseController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminElectricityExpenseController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminElectricityExpenseController::class, 'create'])->name('create');
                    Route::post('/store', [AdminElectricityExpenseController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminElectricityExpenseController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminElectricityExpenseController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminElectricityExpenseController::class, 'delete'])->name('delete');
                });
                Route::prefix('rent')->name('rent.')->group(function () {
                    Route::get('/index', [AdminRentController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminRentController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminRentController::class, 'create'])->name('create');
                    Route::post('/store', [AdminRentController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminRentController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminRentController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminRentController::class, 'delete'])->name('delete');
                });
                Route::prefix('telephone_expense')->name('telephone_expense.')->group(function () {
                    Route::get('/index', [AdminTelephoneExpenseController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminTelephoneExpenseController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminTelephoneExpenseController::class, 'create'])->name('create');
                    Route::post('/store', [AdminTelephoneExpenseController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminTelephoneExpenseController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminTelephoneExpenseController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminTelephoneExpenseController::class, 'delete'])->name('delete');
                });

                Route::prefix('commission')->name('commission.')->group(function () {
                    Route::get('/index', [AdminCommissionController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminCommissionController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminCommissionController::class, 'create'])->name('create');
                    Route::post('/store', [AdminCommissionController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminCommissionController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminCommissionController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminCommissionController::class, 'delete'])->name('delete');
                });

                Route::prefix('hulayati')->name('hulayati.')->group(function () {
                    Route::get('/index', [AdminHulayatiController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminHulayatiController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminHulayatiController::class, 'create'])->name('create');
                    Route::post('/store', [AdminHulayatiController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminHulayatiController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminHulayatiController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminHulayatiController::class, 'delete'])->name('delete');
                });

                Route::prefix('machinery')->name('machinery.')->group(function () {
                    Route::get('/index', [AdminMachineryController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminMachineryController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminMachineryController::class, 'create'])->name('create');
                    Route::post('/store', [AdminMachineryController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminMachineryController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminMachineryController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminMachineryController::class, 'delete'])->name('delete');
                });

                Route::prefix('loan')->name('loan.')->group(function () {
                    Route::get('/index', [AdminLoanController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminLoanController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminLoanController::class, 'create'])->name('create');
                    Route::post('/store', [AdminLoanController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminLoanController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminLoanController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminLoanController::class, 'delete'])->name('delete');
                });

                Route::prefix('factory_head')->name('factory_head.')->group(function () {
                    Route::get('/index', [AdminFactoryHeadController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminFactoryHeadController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminFactoryHeadController::class, 'create'])->name('create');
                    Route::post('/store', [AdminFactoryHeadController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminFactoryHeadController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminFactoryHeadController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminFactoryHeadController::class, 'delete'])->name('delete');
                });

                Route::prefix('discount')->name('discount.')->group(function () {
                    Route::get('/index', [AdminDiscountController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminDiscountController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminDiscountController::class, 'create'])->name('create');
                    Route::post('/store', [AdminDiscountController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminDiscountController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminDiscountController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminDiscountController::class, 'delete'])->name('delete');
                });

                Route::prefix('salary')->name('salary.')->group(function () {
                    Route::get('/index', [AdminSalaryController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminSalaryController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminSalaryController::class, 'create'])->name('create');
                    Route::post('/store', [AdminSalaryController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminSalaryController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminSalaryController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminSalaryController::class, 'delete'])->name('delete');
                });

                Route::prefix('capital')->name('capital.')->group(function () {
                    Route::get('/index', [AdminCapitalController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminCapitalController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminCapitalController::class, 'create'])->name('create');
                    Route::post('/store', [AdminCapitalController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminCapitalController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminCapitalController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminCapitalController::class, 'delete'])->name('delete');
                });

                Route::prefix('adjustment_master')->name('adjustment_master.')->group(function () {
                    Route::get('/index', [AdminAdjustmentMasterController::class, 'index'])->name('index');
                    Route::get('/create', [AdminAdjustmentMasterController::class, 'create'])->name('create');
                    Route::post('/store', [AdminAdjustmentMasterController::class, 'store'])->name('store');
                    Route::get('/edit/{id}', [AdminAdjustmentMasterController::class, 'edit'])->name('edit');
                    Route::post('/update/{id}', [AdminAdjustmentMasterController::class, 'update'])->name('update');
                    Route::get('/delete/{id}', [AdminAdjustmentMasterController::class, 'delete'])->name('delete');
                });
            });

            Route::prefix('voucher')->name('voucher.')->group(function () {
                Route::prefix('consumable')->name('consumable.')->group(function () {
                    Route::get('/index', [AdminConsumableVoucherController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminConsumableVoucherController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminConsumableVoucherController::class, 'create'])->name('create');
                    Route::post('/store', [AdminConsumableVoucherController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminConsumableVoucherController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminConsumableVoucherController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminConsumableVoucherController::class, 'delete'])->name('delete');
                });

                Route::prefix('contractor')->name('contractor.')->group(function () {
                    Route::get('/index', [AdminContractorVoucherController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminContractorVoucherController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminContractorVoucherController::class, 'create'])->name('create');
                    Route::post('/store', [AdminContractorVoucherController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminContractorVoucherController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminContractorVoucherController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminContractorVoucherController::class, 'delete'])->name('delete');
                });

                Route::prefix('washing')->name('washing.')->group(function () {
                    Route::get('/index', [AdminWashingVoucherController::class, 'index'])->name('index');
                    Route::get('/indexList', [AdminWashingVoucherController::class, 'indexList'])->name('indexList');
                    Route::get('/create', [AdminWashingVoucherController::class, 'create'])->name('create');
                    Route::post('/store', [AdminWashingVoucherController::class, 'store'])->name('store');
                    Route::get('/edit', [AdminWashingVoucherController::class, 'edit'])->name('edit');
                    Route::post('/update', [AdminWashingVoucherController::class, 'update'])->name('update');
                    Route::get('/delete', [AdminWashingVoucherController::class, 'delete'])->name('delete');
                });
            });

            Route::prefix('adjustment')->name('adjustment.')->group(function () {
                Route::get('/index', [AdminPaymentAdjustmentController::class, 'index'])->name('index');
                Route::get('/create', [AdminPaymentAdjustmentController::class, 'create'])->name('create');
                Route::post('/store', [AdminPaymentAdjustmentController::class, 'store'])->name('store');
                Route::get('/show/{batchId}', [AdminPaymentAdjustmentController::class, 'show'])->name('show');
                Route::get('/edit/{batchId}', [AdminPaymentAdjustmentController::class, 'edit'])->name('edit');
                Route::post('/update/{batchId}', [AdminPaymentAdjustmentController::class, 'update'])->name('update');
                Route::get('/delete/{batchId}', [AdminPaymentAdjustmentController::class, 'delete'])->name('delete');
                Route::get('/getSubMasters', [AdminPaymentAdjustmentController::class, 'getSubMasters'])->name('getSubMasters');
                Route::get('/getSubMastersAll', [AdminPaymentAdjustmentController::class, 'getSubMastersAll'])->name('getSubMastersAll');
                Route::get('/getVendorShipments', [AdminPaymentAdjustmentController::class, 'getVendorShipments'])->name('getVendorShipments');
                Route::get('/getAccounts', [AdminPaymentAdjustmentController::class, 'getAccounts'])->name('getAccounts');
            });

            Route::prefix('journal-voucher')->name('journal-voucher.')->group(function () {
                Route::get('/index', [AdminJournalVoucherController::class, 'index'])->name('index');
                Route::get('/create', [AdminJournalVoucherController::class, 'create'])->name('create');
                Route::post('/store', [AdminJournalVoucherController::class, 'store'])->name('store');
                Route::get('/edit/{id}', [AdminJournalVoucherController::class, 'edit'])->name('edit');
                Route::get('/show/{id}', [AdminJournalVoucherController::class, 'show'])->name('show');
                Route::get('/download/{id}', [AdminJournalVoucherController::class, 'download'])->name('download');
                Route::post('/update/{id}', [AdminJournalVoucherController::class, 'update'])->name('update');
                Route::get('/delete/{id}', [AdminJournalVoucherController::class, 'destroy'])->name('delete');
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
            Route::post('/delete-domestic-box/{id}', [\App\Http\Controllers\Admin\PackingController::class, 'deleteDomesticBox'])->name('deleteDomesticBox');
            Route::post('/save-domestic-box', [\App\Http\Controllers\Admin\PackingController::class, 'saveDomesticBox'])->name('saveDomesticBox');
            Route::get('/process-domestic/{slip_id}', [\App\Http\Controllers\Admin\PackingController::class, 'processDomestic'])->name('processDomestic');
            Route::get('/download-domestic-barcode-txt/{id}', [\App\Http\Controllers\Admin\PackingController::class, 'downloadDomesticBarcodeTxt'])->name('downloadDomesticBarcodeTxt');
            Route::get('/download-all-domestic-txt/{slip_id}', [\App\Http\Controllers\Admin\PackingController::class, 'downloadAllDomesticTxt'])->name('downloadAllDomesticTxt');
            Route::get('/download-all-domestic-barcode/{slip_id}', [\App\Http\Controllers\Admin\PackingController::class, 'downloadAllDomesticBarcode'])->name('downloadAllDomesticBarcode');
        });

    }); // end checkAdminLogin for payment & packing

});



Route::prefix('/admin')->name('admin.')->middleware(['web', 'checkAdminLogin'])->group(function () {
    Route::prefix('/uploaded-slips')->name('uploaded-slips.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\UploadedSlipsController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\Admin\UploadedSlipsController::class, 'show'])->name('show');
        Route::post('/{id}/finalize', [\App\Http\Controllers\Admin\UploadedSlipsController::class, 'finalize'])->name('finalize');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\UploadedSlipsController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/download', [\App\Http\Controllers\Admin\UploadedSlipsController::class, 'download'])->name('download');
        Route::get('/outflow-receipt/{id}', [\App\Http\Controllers\Admin\UploadedSlipsController::class, 'downloadOutflowReceipt'])->name('outflow-receipt');
        Route::post('/corporate-excel/{id}', [\App\Http\Controllers\Admin\UploadedSlipsController::class, 'corporateExcel'])->name('corporate-excel');
        Route::get('/delete-session/{type}/{id}', [\App\Http\Controllers\Admin\UploadedSlipsController::class, 'deleteSession'])->name('delete-session');
    });
});



Route::get('/debug-stages', function () {
    return [
        'lots' => \App\Models\FabricRollAssigning::where('project_id', 1)->orWhere('id', '>', 0)->take(10)->get(), // just get recent lots
        'transactions' => \App\Models\OrderStageTransaction::latest()->take(5)->get()
    ];
});




