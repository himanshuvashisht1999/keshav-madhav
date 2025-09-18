<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\GeneralSettingsController as AdminGeneralSettingsController;
use App\Http\Controllers\Admin\VendorController as AdminVendorController;
use App\Http\Controllers\Admin\Master\ItemController as AdminItemController;
use App\Http\Controllers\Admin\Master\FabricDyeController as AdminFabricDyeController;

///// new 
use App\Http\Controllers\Admin\Master\FabricGsmController as AdminFabricGsmController;
use App\Http\Controllers\Admin\Master\FabricCompositionController as AdminAdminFabricCompositionController;
use App\Http\Controllers\Admin\Master\FabricWeaveController as AdminFabricWeaveController;
use App\Http\Controllers\Admin\Master\FabricWidthController as AdminFabricWidthController;


////// Website
Route::get('/',[AdminLoginController::class,'login'])->name('web.homepage');


////////////  Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['web'])->group(function () {
    Route::get('/',[AdminLoginController::class,'login'])->name('login');
    Route::post('/post-admin',[AdminLoginController::class,'postLogin'])->name('postLogin');

    Route::middleware([checkAdminLogin::class])->group(function(){

        Route::get('/logout',[AdminLoginController::class,'logout'])->name('logout');
        Route::get('/dashboard',[AdminDashboardController::class,'dashboard'])->name('dashboard');


        

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

        

        Route::get('edit-profile',[AdminUserController::class,'profileEdit'])->name('user.profileEdit');
        Route::post('profile-update',[AdminUserController::class,'profileUpdate'])->name('user.profileUpdate');
        Route::prefix('master/settings')->name('settings.')->group(function () {
            Route::get('/edit',[AdminGeneralSettingsController::class,'edit'])->name('edit');
            Route::post('/update',[AdminGeneralSettingsController::class,'update'])->name('update');
        });

       
            

    });
});


