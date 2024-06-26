<?php

use App\Http\Controllers\AdminMessageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CSServiceController;
use App\Http\Controllers\NotaryServiceOrderController;
use App\Http\Controllers\OnePayGateway;
use App\Http\Controllers\OrderItemsController;
use App\Http\Controllers\ServiceController;
use App\Models\NotaryServiceOrder;
use App\Http\Controllers\LegalAdviceController;
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
Route::middleware('authToken')->post('place-new-order-with-gateway', [OnePayGateway::class, 'placeNewOrderWithGateway']);
Route::middleware('authToken')->post('add-pay-success-log', [OnePayGateway::class, 'addPaymentSuccessLog']);
Route::middleware('authToken')->post('get-order-requests', [OrderItemsController::class, 'getOrderRequests']);
Route::middleware('authToken')->post('get-notary-order-list', [NotaryServiceOrderController::class, 'getNotaryServiceOrderRequests']);

Route::middleware('authToken')->post('get-complete-notary-order-list', [NotaryServiceOrderController::class, 'getCompleteNotaryServiceOrderRequests']);
Route::middleware('authToken')->post('get-main-notary-cat-list', [NotaryServiceOrderController::class, 'getMainNotaryCategoryList']);
Route::middleware('authToken')->post('get-first-cat-list-by-main-cat-code', [NotaryServiceOrderController::class, 'getFirstSubCategoryByMainCategory']);

Route::middleware('authToken')->post('place-notary-service-order', [NotaryServiceOrderController::class, 'placeNewNotaryServiceOrder']);
Route::middleware('authToken')->post('get-tr-order-info-by-invoice', [OrderItemsController::class, 'getTranslateOrderInfoByInvoice']);
Route::middleware('authToken')->post('get-doc-list-by-order', [OrderItemsController::class, 'getTranslatedDocsList']);
Route::middleware('authToken')->post('send-message', [AdminMessageController::class, 'sendMessageToAdmin']);
Route::middleware('authToken')->post('get-message-list', [AdminMessageController::class, 'getAdminMessageList']);
Route::middleware('authToken')->post('get-profile-info', [ClientController::class, 'getProfileInfo']);
Route::middleware('authToken')->post('update-order-status', [OrderItemsController::class, 'updateOrderStausByClient']);

Route::middleware('authToken')->post('get-complete-orders', [OrderItemsController::class, 'getCompleteOrders']);
Route::middleware('authToken')->post('get-notary-order-by-invoice', [NotaryServiceOrderController::class, 'getOrderInfoByInvoice']);
Route::middleware('authToken')->post('get-notary-doc-list', [NotaryServiceOrderController::class, 'getNotaryDocsList']);
Route::middleware('authToken')->post('update-notary-order-status', [NotaryServiceOrderController::class, 'updateOrderStausByClient']);
Route::middleware('authToken')->post('submit-bank-slip-ns-order', [NotaryServiceOrderController::class, 'submitBankSlipForOrder']);

Route::middleware('authToken')->post('place-cs-order', [CSServiceController::class, 'placeNewCSOrder']);
Route::middleware('authToken')->post('place-cs-update', [CSServiceController::class, 'placeUpdateCSOrder']);
Route::middleware('authToken')->post('get-cs-order-check', [CSServiceController::class, 'getcsordercheck']);
Route::middleware('authToken')->post('update-cs-order-status', [CSServiceController::class, 'updateOrderStatus']);
Route::middleware('authToken')->post('get-cs-order-Details', [CSServiceController::class, 'getcsorderDetails']);
Route::middleware('authToken')->post('get-cs-order-requests', [CSServiceController::class, 'getCSServiceOrderRequests']);
Route::middleware('authToken')->post('get-complete-cs-order-requests', [CSServiceController::class, 'getCompleteCSServiceOrderRequests']);

Route::middleware('authToken')->post('send-Legal-Request', [LegalAdviceController::class, 'sendLegalRequest']);
Route::middleware('authToken')->post('get-Legal-Request', [LegalAdviceController::class, 'getLegalRequest']);
Route::middleware('authToken')->post('get-admin-Legal-Message', [LegalAdviceController::class, 'GetAdminLegalmessage']);
Route::middleware('authToken')->post('send-Legal-Message', [LegalAdviceController::class, 'sendLegalMessage']);

Route::middleware('authToken')->post('get-lgODoc-List', [LegalAdviceController::class, 'getLegalDocs']);
Route::middleware('authToken')->post('get-lgDoc-List', [LegalAdviceController::class, 'getLegalFDocs']);
Route::middleware('authToken')->post('view-lgDoc', [LegalAdviceController::class, 'viewLegalDocs']);
Route::middleware('authToken')->post('Complete-legal-order', [LegalAdviceController::class, 'completeLegalorder']);
Route::middleware('authToken')->post('get-Complete-Legal-Request', [LegalAdviceController::class, 'GetCompleteLegalmessage']);