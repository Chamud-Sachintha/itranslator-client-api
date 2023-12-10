<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderItemsController extends Controller
{
    private $AppHelper;
    private $Client;
    private $Order;
    private $OrderItems;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Client = new Client();
        $this->Order = new Order();
        $this->OrderItems = new OrderItems();
    }

    public function placeNewOrderWithBankSlip(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $valueObjArray = (is_null($request->valueObjModel) || empty($request->valueObjModel)) ? "" : $request->valueObjModel;
        $deliveryTime = (is_null($request->deliveryTimeType) || empty($request->deliveryTimeType)) ? "" : $request->deliveryTimeType;
        $deliveryMethod = (is_null($request->deliveryMethod) || empty($request->deliveryMethod)) ? "" : $request->deliveryMethod;
        $paymentMethod = (is_null($request->paymentMethod) || empty($request->paymentMethod)) ? "" : $request->paymentMethod;
        $totalAmount = (is_null($request->totalAmount) || empty($request->totalAmount)) ? "" : $request->totalAmount;
        $bankSlip = (is_null($request->bankSlip) || empty($request->bankSlip)) ? "" : $request->bankSlip;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($valueObjArray == "") {
            return $this->AppHelper->responseMessageHandle(0, "Service values are required.");
        } else {

            try {

                $client = $this->Client->find_by_token($request_token);

                $orderDetails = array();
                $orderDetails['clientId'] = $client->id;

                if ($bankSlip != null) {
                    $orderDetails['paymentStatus'] = 0;

                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $bankSlip));
                    $filename = 'image_' . time() . '.png';

                    Storage::disk('public')->put($filename, $imageData);

                    $orderDetails['bankSlip'] = $filename;
                }

                $orderDetails['orderStatus'] = 0;
                $orderDetails['createTime'] = $this->AppHelper->get_date_and_time();
                $orderDetails['deliveryTimeType'] = $deliveryTime;
                $orderDetails['deliveryMethod'] = $deliveryMethod;
                $orderDetails['paymentMethod'] = $paymentMethod;
                $orderDetails['totalAmount'] = floatval($totalAmount);

                $order = $this->Order->add_log($orderDetails);

                $orderItemsDetails = array();

                if ($order) {
                    
                    $jsonArray = json_decode(json_encode($valueObjArray));

                    $orderItemsDetails['orderId'] = $order->id;
                    
                    foreach ($jsonArray as $key => $value) {

                        $orderItemsDetails['serviceId'] = $value->serviceId;

                        if ($value->serviceId == 1) {
                            $frontImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->nicTranslateModel->frontImg));
                            $frontImagefilename = 'image_' . time() . '.png';

                            $backImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->nicTranslateModel->backImg));
                            $backImagefilename = 'image_' . time() . '.png';

                            Storage::disk('public')->put($frontImagefilename, $frontImageData);
                            Storage::disk('public')->put($backImagefilename, $backImageData);

                            $value->nicTranslateModel->frontImg = $frontImagefilename;
                            $value->nicTranslateModel->backImg = $backImagefilename;

                            $orderItemsDetails['jsonValue'] = json_encode($value->nicTranslateModel);
                        }
                        
                        $orderItemsDetails['createTime'] = $this->AppHelper->get_date_and_time();
                        $orderItemsDetails['modifiedTime'] = $this->AppHelper->get_date_and_time();

                        $this->OrderItems->add_log($orderItemsDetails);
                    }
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getOrderRequests(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is reuirqed.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {

                $client = $this->Client->find_by_token($request_token);
                $resp = $this->Order->get_order_requests($client->id);

                if ($resp) {
                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $resp);
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
