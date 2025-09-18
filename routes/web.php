<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\GeneralSettingsController as AdminGeneralSettingsController;
use App\Http\Controllers\Admin\VendorController as AdminVendorController;


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

        

        Route::get('edit-profile',[AdminUserController::class,'profileEdit'])->name('user.profileEdit');
        Route::post('profile-update',[AdminUserController::class,'profileUpdate'])->name('user.profileUpdate');
        Route::prefix('master/settings')->name('settings.')->group(function () {
            Route::get('/edit',[AdminGeneralSettingsController::class,'edit'])->name('edit');
            Route::post('/update',[AdminGeneralSettingsController::class,'update'])->name('update');
        });

       
            

    });
});


