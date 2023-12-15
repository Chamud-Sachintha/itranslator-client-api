<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotaryServiceOrderController;
use App\Http\Controllers\OrderItemsController;
use App\Http\Controllers\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('signin', [AuthController::class, 'authenticateUser']);
Route::post('signup', [AuthController::class, 'signUpNewUser']);
Route::middleware('authToken')->post('get-service-list', [ServiceController::class, 'getTranslateServiceList']);
Route::middleware('authToken')->post('get-service-price-by-delivery-time', [ServiceController::class, 'getPriceByServiceIdAndDeliveryTime']);
Route::middleware('authToken')->post('place-order-with-bslip', [OrderItemsController::class, 'placeNewOrderWithBankSlip']);
Route::middleware('authToken')->post('get-order-requests', [OrderItemsController::class, 'getOrderRequests']);
Route::middleware('authToken')->post('get-main-notary-cat-list', [NotaryServiceOrderController::class, 'getMainNotaryCategoryList']);
Route::middleware('authToken')->post('get-first-cat-list-by-main-cat-code', [NotaryServiceOrderController::class, 'getFirstSubCategoryByMainCategory']);

Route::middleware('authToken')->post('place-notary-service-order', [NotaryServiceOrderController::class, 'placeNewNotaryServiceOrder']);