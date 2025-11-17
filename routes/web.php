<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\GeneralSettingsController as AdminGeneralSettingsController;
use App\Http\Controllers\Admin\Master\VendorController as AdminVendorController;
use App\Http\Controllers\Admin\Master\ItemController as AdminItemController;
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
use App\Http\Controllers\Admin\PurchaseOrderMaterialController as AdminPurchaseOrderMaterialController;
use App\Http\Controllers\Admin\ItemReceiptController as AdminItemReceiptController;
use App\Http\Controllers\Admin\ItemStockController as AdminItemStockController;

/// order
use App\Http\Controllers\Admin\ProductOrderController as AdminProductOrderController;

///// Order Stages
use App\Http\Controllers\Admin\OrderStagesController as AdminOrderStagesController;

////new master
use App\Http\Controllers\Admin\Master\MasterColorController as AdminMasterColorController;
use App\Http\Controllers\Admin\Master\MasterDesignController as AdminMasterDesignController;
use App\Http\Controllers\Admin\Master\MasterMaterialController as AdminMasterMaterialController;
use App\Http\Controllers\Admin\Master\MasterProductStageController as AdminMasterProductStageController;
use App\Http\Controllers\Admin\Master\CustomerController as AdminCustomerController;

///// Reports
use App\Http\Controllers\Admin\ReportController as AdminReportController;
////// Website
Route::get('/',[AdminLoginController::class,'login'])->name('web.homepage');


////////////  Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['web'])->group(function () {
    Route::get('/',[AdminLoginController::class,'login'])->name('login');
    Route::post('/post-admin',[AdminLoginController::class,'postLogin'])->name('postLogin');

    Route::middleware([checkAdminLogin::class])->group(function(){

        Route::get('/logout',[AdminLoginController::class,'logout'])->name('logout');
        Route::get('/dashboard',[AdminDashboardController::class,'dashboard'])->name('dashboard');

        Route::prefix('/purchase-order')->name('purchase_order.')->group(function () {
            Route::get('/index',[AdminPurchaseOrderController::class,'index'])->name('index');
            Route::get('/indexList',[AdminPurchaseOrderController::class,'indexList'])->name('indexList');
            Route::get('/create',[AdminPurchaseOrderController::class,'create'])->name('create');
            Route::post('/store',[AdminPurchaseOrderController::class,'store'])->name('store');
            Route::get('/edit',[AdminPurchaseOrderController::class,'edit'])->name('edit');
            Route::post('/update',[AdminPurchaseOrderController::class,'update'])->name('update');
            Route::get('/delete',[AdminPurchaseOrderController::class,'delete'])->name('delete');
            Route::get('/view',[AdminPurchaseOrderController::class,'view'])->name('view');

        });

        Route::prefix('/purchase-order-material')->name('purchase_order_material.')->group(function () {
            Route::get('/index',[AdminPurchaseOrderMaterialController::class,'index'])->name('index');
            Route::get('/indexList',[AdminPurchaseOrderMaterialController::class,'indexList'])->name('indexList');
            Route::get('/create',[AdminPurchaseOrderMaterialController::class,'create'])->name('create');
            Route::post('/store',[AdminPurchaseOrderMaterialController::class,'store'])->name('store');
            Route::get('/edit',[AdminPurchaseOrderMaterialController::class,'edit'])->name('edit');
            Route::post('/update',[AdminPurchaseOrderMaterialController::class,'update'])->name('update');
            Route::get('/delete',[AdminPurchaseOrderMaterialController::class,'delete'])->name('delete');
            Route::get('/view',[AdminPurchaseOrderMaterialController::class,'view'])->name('view');
        });

        Route::prefix('/fabric-receipt')->name('fabric_receipt.')->group(function () {
            Route::get('/index',[AdminFabricReceiptController::class,'index'])->name('index');
            Route::get('/indexList',[AdminFabricReceiptController::class,'indexList'])->name('indexList');
            Route::get('/create',[AdminFabricReceiptController::class,'create'])->name('create');
            Route::post('/store',[AdminFabricReceiptController::class,'store'])->name('store');
            Route::get('/edit',[AdminFabricReceiptController::class,'edit'])->name('edit');
            Route::post('/update',[AdminFabricReceiptController::class,'update'])->name('update');
            Route::get('/delete',[AdminFabricReceiptController::class,'delete'])->name('delete');
            Route::get('/view',[AdminFabricReceiptController::class,'view'])->name('view');
            Route::get('/detail',[AdminFabricReceiptController::class,'detail'])->name('detail');
            Route::post('/store-detail',[AdminFabricReceiptController::class,'storeDetail'])->name('storeDetail');
            Route::get('/purchase-order-items/{id}', [AdminFabricReceiptController::class, 'getPurchaseOrderItems'])->name('items');

        });

        Route::prefix('/item-receipt')->name('item_receipt.')->group(function () {
        Route::get('/index',[AdminItemReceiptController::class,'index'])->name('index');
        Route::get('/indexList',[AdminItemReceiptController::class,'indexList'])->name('indexList');
        Route::get('/create',[AdminItemReceiptController::class,'create'])->name('create');
        Route::post('/store',[AdminItemReceiptController::class,'store'])->name('store');
        Route::get('/detail',[AdminItemReceiptController::class,'detail'])->name('detail');
        Route::get('/getPurchaseOrderItems/{id}', [AdminItemReceiptController::class, 'getPurchaseOrderItems'])->name('getPurchaseOrderItems');
        Route::get('/edit',[AdminItemReceiptController::class,'edit'])->name('edit');
        Route::post('/update',[AdminItemReceiptController::class,'update'])->name('update');
        Route::post('/storeDetail',[AdminItemReceiptController::class,'storeDetail'])->name('storeDetail');
        Route::get('/view',[AdminItemReceiptController::class,'view'])->name('view');
        Route::post('/delete',[AdminItemReceiptController::class,'delete'])->name('delete');
    });

        Route::prefix('/stock')->name('stock.')->group(function () {
            Route::get('/index',[AdminStockController::class,'index'])->name('index');
            Route::get('/indexList',[AdminStockController::class,'indexList'])->name('indexList');
            Route::get('/view',[AdminStockController::class,'view'])->name('view');
            Route::get('/detail',[AdminStockController::class,'detail'])->name('detail');
            Route::post('/generatePdf', [AdminStockController::class, 'generateStockReportPDF'])->name('generatePdf');
            Route::post('/generateExcel', [AdminStockController::class, 'generateStockReportExcel'])->name('generateExcel');
            Route::get('/fabricQuantityExcel', [AdminStockController::class, 'generateFebricQuantityReportExcel'])->name('fabricQuantityExcel');
            
            Route::get('/fabricIndex',[AdminStockController::class,'fabricIndex'])->name('fabricIndex');
            Route::get('/fabricIndexList',[AdminStockController::class,'fabricIndexList'])->name('fabricIndexList');
        });

        Route::prefix('/item-stock')->name('item_stock.')->group(function () {
            Route::get('/index',[AdminItemStockController::class,'index'])->name('index');
            Route::get('/indexList',[AdminItemStockController::class,'indexList'])->name('indexList');
            Route::get('/view',[AdminItemStockController::class,'view'])->name('view');
            Route::get('/detail',[AdminItemStockController::class,'detail'])->name('detail');
            Route::post('/generatePdf', [AdminItemStockController::class, 'generateStockReportPDF'])->name('generatePdf');
            Route::post('/generateExcel', [AdminItemStockController::class, 'generateStockReportExcel'])->name('generateExcel');
            Route::get('/itemQuantityExcel', [AdminItemStockController::class, 'generateItemQuantityReportExcel'])->name('itemQuantityExcel');
            
            Route::get('/itemIndex',[AdminItemStockController::class,'itemIndex'])->name('itemIndex');
            Route::get('/itemIndexList',[AdminItemStockController::class,'itemIndexList'])->name('itemIndexList');
        });

        Route::prefix('/sales-order')->name('sales_order.')->group(function () {
            Route::get('/create',[AdminProductOrderController::class,'create'])->name('create');
            Route::post('/store',[AdminProductOrderController::class,'store'])->name('store');
        });

        Route::prefix('/production-order')->name('product_order.')->group(function () {
            Route::get('/index',[AdminProductOrderController::class,'index'])->name('index');
            Route::get('/indexList',[AdminProductOrderController::class,'indexList'])->name('indexList');
            
            Route::get('/edit',[AdminProductOrderController::class,'edit'])->name('edit');
            Route::post('/update',[AdminProductOrderController::class,'update'])->name('update');
            Route::get('/delete',[AdminProductOrderController::class,'delete'])->name('delete');
            Route::get('/view',[AdminProductOrderController::class,'view'])->name('view');
            Route::post('/transfer',[AdminProductOrderController::class,'transfer'])->name('transfer');
            Route::get('/produce',[AdminProductOrderController::class,'produce'])->name('produce');
            Route::get('/issue-fabric',[AdminProductOrderController::class,'issueFabric'])->name('issueFabric');
            Route::post('/issue-fabric-post',[AdminProductOrderController::class,'issueFabricPost'])->name('issueFabricPost');
            Route::get('/issue-slip',[AdminProductOrderController::class,'issueSlip'])->name('issueSlip');
            Route::get('/status-hover-data',[AdminProductOrderController::class,'productStatusHoverData'])->name('statusHoverData');
            
        });

        Route::prefix('/order-stages')->name('order-stages.')->group(function () {
            Route::get('/index',[AdminOrderStagesController::class,'index'])->name('index');
            Route::get('/indexList',[AdminOrderStagesController::class,'indexList'])->name('indexList');
            Route::get('/download-receipt',[AdminOrderStagesController::class,'downLoadReceipt'])->name('downLoadReceipt');
            Route::get('/get-sub-stages/{order_product_id}/{from_stage_id}',[AdminOrderStagesController::class,'getSubStages'])->name('getSubStages');
        });

        

        Route::prefix('master/vendors')->name('master.vendor.')->group(function () {
            Route::get('/index',[AdminVendorController::class,'index'])->name('index');
            Route::get('/indexList',[AdminVendorController::class,'indexList'])->name('indexList');
            Route::get('/create',[AdminVendorController::class,'create'])->name('create');
            Route::post('/store',[AdminVendorController::class,'store'])->name('store');
            Route::get('/edit',[AdminVendorController::class,'edit'])->name('edit');
            Route::post('/update',[AdminVendorController::class,'update'])->name('update');
            Route::get('/delete',[AdminVendorController::class,'delete'])->name('delete');
        });

        Route::prefix('master/item')->name('master.item.')->group(function () {
            Route::get('/index',[AdminItemController::class,'index'])->name('index');
            Route::get('/indexList',[AdminItemController::class,'indexList'])->name('indexList');
            Route::get('/create',[AdminItemController::class,'create'])->name('create');
            Route::post('/store',[AdminItemController::class,'store'])->name('store');
            Route::get('/edit',[AdminItemController::class,'edit'])->name('edit');
            Route::post('/update',[AdminItemController::class,'update'])->name('update');
            Route::get('/delete',[AdminItemController::class,'delete'])->name('delete');
        });

        Route::prefix('master/item-attributes')->name('master.item-attributes.')->group(function () {
            Route::get('/index',[AdminItemAttributesController::class,'index'])->name('index');
            Route::get('/indexList',[AdminItemAttributesController::class,'indexList'])->name('indexList');
            Route::get('/create',[AdminItemAttributesController::class,'create'])->name('create');
            Route::post('/store',[AdminItemAttributesController::class,'store'])->name('store');
            Route::get('/edit',[AdminItemAttributesController::class,'edit'])->name('edit');
            Route::post('/update',[AdminItemAttributesController::class,'update'])->name('update');
            Route::get('/delete',[AdminItemAttributesController::class,'delete'])->name('delete');
        });

        Route::prefix('master/fabric_dye')->name('master.fabric_dye.')->group(function () {
            Route::get('/index',[AdminFabricDyeController::class,'index'])->name('index');
            Route::get('/indexList',[AdminFabricDyeController::class,'indexList'])->name('indexList');
            Route::get('/create',[AdminFabricDyeController::class,'create'])->name('create');
            Route::post('/store',[AdminFabricDyeController::class,'store'])->name('store');
            Route::get('/edit',[AdminFabricDyeController::class,'edit'])->name('edit');
            Route::post('/update',[AdminFabricDyeController::class,'update'])->name('update');
            Route::get('/delete',[AdminFabricDyeController::class,'delete'])->name('delete');
        });

        Route::prefix('master/fabric_gsm')->name('master.fabric_gsm.')->group(function () {
            Route::get('/index',[AdminFabricGsmController::class,'index'])->name('index');
            Route::get('/indexList',[AdminFabricGsmController::class,'indexList'])->name('indexList');
            Route::get('/create',[AdminFabricGsmController::class,'create'])->name('create');
            Route::post('/store',[AdminFabricGsmController::class,'store'])->name('store');
            Route::get('/edit',[AdminFabricGsmController::class,'edit'])->name('edit');
            Route::post('/update',[AdminFabricGsmController::class,'update'])->name('update');
            Route::get('/delete',[AdminFabricGsmController::class,'delete'])->name('delete');
        });
        Route::prefix('master/fabric_composition')->name('master.fabric_composition.')->group(function () {
            Route::get('/index',[AdminFabricCompositionController::class,'index'])->name('index');
            Route::get('/indexList',[AdminFabricCompositionController::class,'indexList'])->name('indexList');
            Route::get('/create',[AdminFabricCompositionController::class,'create'])->name('create');
            Route::post('/store',[AdminFabricCompositionController::class,'store'])->name('store');
            Route::get('/edit',[AdminFabricCompositionController::class,'edit'])->name('edit');
            Route::post('/update',[AdminFabricCompositionController::class,'update'])->name('update');
            Route::get('/delete',[AdminFabricCompositionController::class,'delete'])->name('delete');
        });
        Route::prefix('master/fabric_weave')->name('master.fabric_weave.')->group(function () {
            Route::get('/index',[AdminFabricWeaveController::class,'index'])->name('index');
            Route::get('/indexList',[AdminFabricWeaveController::class,'indexList'])->name('indexList');
            Route::get('/create',[AdminFabricWeaveController::class,'create'])->name('create');
            Route::post('/store',[AdminFabricWeaveController::class,'store'])->name('store');
            Route::get('/edit',[AdminFabricWeaveController::class,'edit'])->name('edit');
            Route::post('/update',[AdminFabricWeaveController::class,'update'])->name('update');
            Route::get('/delete',[AdminFabricWeaveController::class,'delete'])->name('delete');
        });
        Route::prefix('master/fabric_width')->name('master.fabric_width.')->group(function () {
            Route::get('/index',[AdminFabricWidthController::class,'index'])->name('index');
            Route::get('/indexList',[AdminFabricWidthController::class,'indexList'])->name('indexList');
            Route::get('/create',[AdminFabricWidthController::class,'create'])->name('create');
            Route::post('/store',[AdminFabricWidthController::class,'store'])->name('store');
            Route::get('/edit',[AdminFabricWidthController::class,'edit'])->name('edit');
            Route::post('/update',[AdminFabricWidthController::class,'update'])->name('update');
            Route::get('/delete',[AdminFabricWidthController::class,'delete'])->name('delete');
        });
        Route::prefix('master/fabric')->name('master.fabric.')->group(function () {
            Route::get('/index',[AdminFabricController::class,'index'])->name('index');
            Route::get('/indexList',[AdminFabricController::class,'indexList'])->name('indexList');
            Route::get('/create',[AdminFabricController::class,'create'])->name('create');
            Route::post('/store',[AdminFabricController::class,'store'])->name('store');
            Route::get('/edit',[AdminFabricController::class,'edit'])->name('edit');
            Route::post('/update',[AdminFabricController::class,'update'])->name('update');
            Route::get('/delete',[AdminFabricController::class,'delete'])->name('delete');
            Route::get('/deleteImage',[AdminFabricController::class,'deleteImage'])->name('deleteImage');
        });
        Route::prefix('master/size-measurement')->name('master.size-measurement.')->group(function () {
            Route::get('/index',[AdminSizeMeasurementController::class,'index'])->name('index');
            Route::get('/indexList',[AdminSizeMeasurementController::class,'indexList'])->name('indexList');
            Route::get('/create',[AdminSizeMeasurementController::class,'create'])->name('create');
            Route::post('/store',[AdminSizeMeasurementController::class,'store'])->name('store');
            Route::get('/edit',[AdminSizeMeasurementController::class,'edit'])->name('edit');
            Route::post('/update',[AdminSizeMeasurementController::class,'update'])->name('update');
            Route::get('/delete',[AdminSizeMeasurementController::class,'delete'])->name('delete');
        });

        Route::prefix('master/product-stage')->name('master.product_stage.')->group(function () {
            Route::get('/index',[AdminMasterProductStageController::class,'index'])->name('index');
            Route::get('/indexList',[AdminMasterProductStageController::class,'indexList'])->name('indexList');
            Route::get('/create',[AdminMasterProductStageController::class,'create'])->name('create');
            Route::post('/store',[AdminMasterProductStageController::class,'store'])->name('store');
            Route::get('/edit',[AdminMasterProductStageController::class,'edit'])->name('edit');
            Route::post('/update',[AdminMasterProductStageController::class,'update'])->name('update');
            Route::get('/delete',[AdminMasterProductStageController::class,'delete'])->name('delete');

            // Route::get('/index',[AdminMasterProductStageController::class,'subStageIndex'])->name('index');
            // Route::get('/subStageList',[AdminMasterProductStageController::class,'subStageList'])->name('subStageList');
            
        });

        Route::prefix('master/product-sub-stage')->name('master.product-sub-stage.')->group(function () {
            Route::get('/index',[AdminMasterProductStageController::class,'subStageIndex'])->name('index');
            Route::get('/subStageList',[AdminMasterProductStageController::class,'subStageList'])->name('subStageList');
            Route::get('/create',[AdminMasterProductStageController::class,'createSubStage'])->name('create');

            Route::post('/store', [AdminMasterProductStageController::class,'storeSubStage']) ->name('store');

            Route::get('/edit',[AdminMasterProductStageController::class,'editSubStage'])->name('edit');
             Route::post('/update',[AdminMasterProductStageController::class,'updateSubStage'])->name('update');
            
        });

        Route::prefix('master/product')->name('master.production-goods.')->group(function () {
            Route::get('/index',[AdminProductionGoodsController::class,'index'])->name('index');
            Route::get('/indexList',[AdminProductionGoodsController::class,'indexList'])->name('indexList');
            Route::get('/create',[AdminProductionGoodsController::class,'create'])->name('create');
            Route::post('/store',[AdminProductionGoodsController::class,'store'])->name('store');
            Route::get('/edit',[AdminProductionGoodsController::class,'edit'])->name('edit');
            Route::post('/update',[AdminProductionGoodsController::class,'update'])->name('update');
            Route::get('/delete',[AdminProductionGoodsController::class,'delete'])->name('delete');
        });

        Route::prefix('master/production-goods-item')->name('master.production-goods-item.')->group(function () {
            // Route::get('/index',[AdminProductionGoodsItemController::class,'index'])->name('index');
            // Route::get('/indexList',[AdminProductionGoodsItemController::class,'indexList'])->name('indexList');
            Route::get('/create',[AdminProductionGoodsItemController::class,'create'])->name('create');
            Route::post('/store',[AdminProductionGoodsItemController::class,'store'])->name('store');
            
            // Route::get('/edit',[AdminProductionGoodsItemController::class,'edit'])->name('edit');
            // Route::post('/update',[AdminProductionGoodsItemController::class,'update'])->name('update');
            // Route::get('/delete',[AdminProductionGoodsItemController::class,'delete'])->name('delete');
        });

        
        Route::prefix('master/colors')->name('master.colors.')->group(function () {
            Route::get('/index',[AdminMasterColorController::class,'index'])->name('index');
            Route::get('/indexList',[AdminMasterColorController::class,'indexList'])->name('indexList');
            Route::get('/create',[AdminMasterColorController::class,'create'])->name('create');
            Route::post('/store',[AdminMasterColorController::class,'store'])->name('store');
            Route::get('/edit',[AdminMasterColorController::class,'edit'])->name('edit');
            Route::post('/update',[AdminMasterColorController::class,'update'])->name('update');
            Route::get('/delete',[AdminMasterColorController::class,'delete'])->name('delete');
        });
        Route::prefix('master/designs')->name('master.designs.')->group(function () {
            Route::get('/index',[AdminMasterDesignController::class,'index'])->name('index');
            Route::get('/indexList',[AdminMasterDesignController::class,'indexList'])->name('indexList');
            Route::get('/create',[AdminMasterDesignController::class,'create'])->name('create');
            Route::post('/store',[AdminMasterDesignController::class,'store'])->name('store');
            Route::get('/edit',[AdminMasterDesignController::class,'edit'])->name('edit');
            Route::post('/update',[AdminMasterDesignController::class,'update'])->name('update');
            Route::get('/delete',[AdminMasterDesignController::class,'delete'])->name('delete');
        });
        Route::prefix('master/materials')->name('master.materials.')->group(function () {
            Route::get('/index',[AdminMasterMaterialController::class,'index'])->name('index');
            Route::get('/indexList',[AdminMasterMaterialController::class,'indexList'])->name('indexList');
            Route::get('/create',[AdminMasterMaterialController::class,'create'])->name('create');
            Route::post('/store',[AdminMasterMaterialController::class,'store'])->name('store');
            Route::get('/edit',[AdminMasterMaterialController::class,'edit'])->name('edit');
            Route::post('/update',[AdminMasterMaterialController::class,'update'])->name('update');
            Route::get('/delete',[AdminMasterMaterialController::class,'delete'])->name('delete');
        });

        Route::prefix('master/customers')->name('master.customer.')->group(function () {
            Route::get('/index',[AdminCustomerController::class,'index'])->name('index');
            Route::get('/indexList',[AdminCustomerController::class,'indexList'])->name('indexList');
            Route::get('/create',[AdminCustomerController::class,'create'])->name('create');
            Route::post('/store',[AdminCustomerController::class,'store'])->name('store');
            Route::get('/edit',[AdminCustomerController::class,'edit'])->name('edit');
            Route::post('/update',[AdminCustomerController::class,'update'])->name('update');
            Route::get('/delete',[AdminCustomerController::class,'delete'])->name('delete');
        }); 

        Route::get('edit-profile',[AdminUserController::class,'profileEdit'])->name('user.profileEdit');
        Route::post('profile-update',[AdminUserController::class,'profileUpdate'])->name('user.profileUpdate');
        Route::prefix('master/settings')->name('settings.')->group(function () {
            Route::get('/edit',[AdminGeneralSettingsController::class,'edit'])->name('edit');
            Route::post('/update',[AdminGeneralSettingsController::class,'update'])->name('update');
        });

        Route::prefix('/reports')->name('reports.')->group(function () {
            Route::get('/fabric-receipt',[AdminReportController::class,'fabricReceipt'])->name('fabricReceipt');
            Route::get('/fabric-receipt-list',[AdminReportController::class,'fabricReceiptList'])->name('fabricReceiptList');
            Route::post('/fabric-receipt-excel',[AdminReportController::class,'generateFabricReceiptExcel'])->name('fabricReceiptExcel');

            Route::get('/purchase-order',[AdminReportController::class,'purchaseOrder'])->name('purchaseOrder');
            Route::get('/purchase-order-list',[AdminReportController::class,'purchaseOrderList'])->name('purchaseOrderList');
            Route::post('/purchase-order-excel',[AdminReportController::class,'generatePurchaseOrderExcel'])->name('purchaseOrderExcel');

            Route::get('/fabric-stock-sku',[AdminReportController::class,'fabricStockSku'])->name('fabricStockSku');
            Route::get('/fabric-stock',[AdminReportController::class,'fabricStock'])->name('fabricStock');
            Route::get('/fabric-stock-details',[AdminReportController::class,'fabricStockDetails'])->name('fabricStockDetails');
            Route::get('/fabric-stock-list',[AdminReportController::class,'fabricStockList'])->name('fabricStockList');
            Route::get('/fabric-stock-sku-list',[AdminReportController::class,'fabricStockSkuList'])->name('fabricStockSkuList');
            Route::post('/fabric-stock-excel',[AdminReportController::class,'generateFabricStockExcel'])->name('fabricStockExcel');
            Route::post('/fabric-stock-sku-excel',[AdminReportController::class,'generateFabricStockSkuExcel'])->name('fabricStockSkuExcel');

            Route::get('/production',[AdminReportController::class,'production'])->name('production');
            Route::get('production-list',[AdminReportController::class,'productionList'])->name('productionList');
            Route::post('/production-excel',[AdminReportController::class,'generateProductionExcel'])->name('productionExcel');

            Route::get('/stages',[AdminReportController::class,'stages'])->name('stages');
            Route::get('stages-list',[AdminReportController::class,'stagesList'])->name('stagesList');
            Route::post('/stages-excel',[AdminReportController::class,'generateStagesReportExcel'])->name('stagesExcel');
        });
    });
});


