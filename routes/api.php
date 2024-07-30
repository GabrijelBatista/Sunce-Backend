<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('login', 'Login');
    Route::post('register', 'Register');
    Route::post('refresh', 'Refresh');
    Route::put('send-verification-code', 'SendVerificationCode');
    Route::put('reset-password', 'ResetPassword');
});

Route::middleware('auth:api')->group(function () {
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('logout', 'Logout');
    });

    Route::prefix('user')->controller(UserController::class)->group(function () {
        Route::put('change-password', 'ChangePassword');
        Route::put('verify-email', 'VerifyEmail');
        Route::delete('delete-account', 'DeleteAccount');
    });

    Route::prefix('category')->controller(CategoryController::class)->group(function () {
        Route::get('get-product-categories', 'GetProductCategories');
        Route::get('get-material-categories', 'GetMaterialCategories');
        Route::get('autocomplete', 'AutocompleteCategories');
        Route::post('add', 'AddCategory');
        Route::put('edit', 'EditCategory');
        Route::delete('delete/{id}', 'DeleteCategory');
    });

    Route::prefix('material')->controller(MaterialController::class)->group(function () {
        Route::get('get', 'GetMaterials');
        Route::get('autocomplete', 'AutocompleteMaterials');
        Route::get('{id}/get-products', 'GetMaterialProducts');
        Route::post('add', 'AddMaterial');
        Route::put('edit', 'EditMaterial');
        Route::delete('delete/{id}', 'DeleteMaterial');
    });

    Route::prefix('product')->controller(ProductController::class)->group(function () {
        Route::get('get', 'GetProducts');
        Route::get('autocomplete', 'AutocompleteProducts');
        Route::get('{id}/get-materials', 'GetProductMaterials');
        Route::post('add', 'AddProduct');
        Route::put('edit', 'EditProduct');
        Route::delete('delete/{id}', 'DeleteProduct');
    });
});
