<?php

use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\PermissionController;
use App\Http\Controllers\Backend\ProfileController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\FundController;
use App\Http\Controllers\Backend\CatagoriesController;
use App\Http\Controllers\Backend\SubCatagoriesController;
use App\Http\Controllers\Backend\SubCatagoriesListController;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth','checkUserStatus'])->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'home')->name('dashboard');
    });
    // setting routes;
    Route::middleware(['permission:setting-index|setting-create|setting-edit|setting-delete'])->group(function () {

//        Route::resource('setting',SettingController::class);
        Route::controller(SettingController::class)->group(function () {
            Route::get('setting/{slug}', 'index')->name('setting.index');
            Route::post('setting-store', 'store')->name('setting.store');
        });

    });

    // profile routes;
    Route::controller(ProfileController::class)->group(function () {
        Route::get('profile', 'profile')->name('profile');
        Route::post('profile', 'profileUpdate')->name('profile.update');
        Route::post('user-status-update', 'profileChangeStatus')->name('user-status-update')->middleware('permission:profile-status-update');
        //change password
        Route::get('change-password', 'passwordChange')->name('change-password');
        Route::post('check-old-password', 'oldPasswordCheck')->name('check-old-password');

        Route::get('new-password', 'newPassword')->name('new-password');
        Route::post('password-update', 'passwordUpdate')->name('password-update');
    });
    Route::resource('project',ProjectController::class);//no need this

    Route::middleware(['permission:user-index|user-create|user-edit|user-delete'])->group(function () {
        Route::resource('user',UserController::class);
    });
    //confidential routes
    Route::middleware(['permission:role-index|role-create|role-edit|role-delete'])->group(function () {
        Route::resource('role',RoleController::class);
    });
    Route::middleware(['permission:permission-index|permission-create|permission-edit|permission-delete'])->group(function () {
        Route::resource('permission',PermissionController::class);
    });

      Route::resource('fund', FundController::class);
      Route::resource('catagories', CatagoriesController::class);
      Route::resource('subCatagories', SubCatagoriesController::class);
      Route::resource('sub-Catagories-list', SubCatagoriesListController::class);


Route::get('/subcategoriespdf/{id}/{fund_id}/{categorie_id}',[PdfController::class, 'Subcategoriespdf'])->name('subcategoriespdf');

Route::get('/subcategoriesAllPdf', [PdfController::class, 'SubcategoriespdfList'])->name('subcategoriesAllPdf');


});

?>
