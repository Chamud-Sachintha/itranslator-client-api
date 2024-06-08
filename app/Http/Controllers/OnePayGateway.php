<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Client;
use App\Models\OnePayGateway as ModelsOnePayGateway;
use App\Models\Order;
use App\Models\OrderItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class OnePayGateway extends Controller
{
    private $AppHelper;
    private $Client;
    private $Order;
    private $OrderItems;
    private $OnePayGatewayLog;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Order = new Order();
        $this->OrderItems = new OrderItems();
        $this->Client = new Client();
        $this->OnePayGatewayLog = new ModelsOnePayGateway();
    }

    public function placeNewOrderWithGateway(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $valueObjArray = (is_null($request->valueObjModel) || empty($request->valueObjModel)) ? "" : $request->valueObjModel;
        $deliveryTime = (is_null($request->deliveryTimeType) || empty($request->deliveryTimeType)) ? "" : $request->deliveryTimeType;
        $deliveryMethod = (is_null($request->deliveryMethod) || empty($request->deliveryMethod)) ? "" : $request->deliveryMethod;
        $paymentMethod = (is_null($request->paymentMethod) || empty($request->paymentMethod)) ? "" : $request->paymentMethod;
        $totalAmount = (is_null($request->totalAmount) || empty($request->totalAmount)) ? "" : $request->totalAmount;
        $invoiceNo = (is_null($request->invoiceNo) || empty($request->invoiceNo)) ? "" : $request->invoiceNo;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($valueObjArray == "") {
            return $this->AppHelper->responseMessageHandle(0, "Service values are required is required.");
        } else if ($deliveryTime == "") {
            return $this->AppHelper->responseMessageHandle(0, "Delivery Time is Required.");
        } else if ($deliveryMethod == "") {
            return $this->AppHelper->responseMessageHandle(0, "Delivery Method is required.");
        } else {

            try {
                $client = $this->Client->find_by_token($request_token);

                $nameExt = explode(" ", $client->full_name);

                $paymentInfo = array();
                $paymentInfo['amount'] = $totalAmount;
                $paymentInfo['reference'] = $this->AppHelper->generate_ref(10);
                $paymentInfo['firstName'] = $nameExt[0];
                $paymentInfo['lastName'] = "Test";
                $paymentInfo['contact'] = $client->mobile_number;
                $paymentInfo['email'] = $client->email;

                $response = $this->onePayGateway($paymentInfo);

                $paymentInfo['reference'] = $response["ipg_transaction_id"];

                if ($response == null) {
                    return $this->AppHelper->responseMessageHandle(0, "Payment Not Success.");
                } else {
                    $redirectInfo = array();
                    $redirectInfo['reference'] = $paymentInfo['reference'];
                    $redirectInfo['redirect_url'] = $response["gateway"]["redirect_url"];

                    $orderDetails = array();
                    $orderDetails['clientId'] = $client->id;
                    $orderDetails['orderStatus'] = 0;
                    // $orderDetails['invoiceNo'] = $this->AppHelper->generateInvoiceNumber("TR");
                    $orderDetails['invoiceNo'] = $invoiceNo;
                    $orderDetails['createTime'] = $this->AppHelper->get_date_and_time();
                    $orderDetails['deliveryTimeType'] = $deliveryTime;
                    $orderDetails['deliveryMethod'] = $deliveryMethod;
                    $orderDetails['paymentMethod'] = $paymentMethod;
                    $orderDetails['paymentStatus'] = 2; // online gateway payment pending
                    $orderDetails['totalAmount'] = floatval($totalAmount);

                    $order = $this->Order->gateway_add_log($orderDetails);

                    $paymentLogInfo = array();
                    $paymentLogInfo['clientId'] = $client->id;
                    $paymentLogInfo['orderId'] = $order->id;
                    $paymentLogInfo['reference'] = $paymentInfo['reference'];
                    $paymentLogInfo['amount'] = $totalAmount;
                    $paymentLogInfo['status'] = 0;
                    $paymentLogInfo['createTime'] = $this->AppHelper->get_date_and_time();

                    $addPaymentLog = $this->OnePayGatewayLog->add_log($paymentLogInfo);

                     // $orderItemsResp = null;

                    if ($order && $addPaymentLog) {
                        $jsonArray = json_decode(json_encode($valueObjArray));
                        $orderItemsResp = $this->createOrderItemsArray($order, $jsonArray);

                        if ($orderItemsResp) {
                            return $this->AppHelper->responseEntityHandle(1, "Operation Complete Successfully.", $redirectInfo);
                        } else {
                            return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                        }
                    }
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function addPaymentSuccessLog(Request $request) {

        $reference = (is_null($request->reference) || empty($request->reference)) ? "" : $request->reference;

        if ($reference == "") {
            return $this->AppHelper->responseMessageHandle(0, "reference is required.");
        } else {

            try {
                $orderRef = $this->OnePayGatewayLog->get_order_by_ref($reference);

                if ($orderRef) {
                    $paymentConfirmLog = array();
                    $paymentConfirmLog['reference']  = $reference;
                    $paymentConfirmLog['status'] = 1;

                    $orderPayInfo = array();
                    $orderPayInfo['orderId'] = $orderRef->order_id;
                    $orderPayInfo['paymentStatus'] = 1;

                    $updatePaymentLog = $this->OnePayGatewayLog->update_payment_log($paymentConfirmLog);
                    $updateOrder = $this->Order->update_order_pay($orderPayInfo);

                    if ($updatePaymentLog && $updateOrder) {
                        return $this->AppHelper->responseMessageHandle(1, "Operation Successfully");
                    } else {
                        return $this->AppHelper->responseMessageHandle(1, "Error Occured.");
                    }
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    private function createOrderItemsArray($order, $jsonArray) {
        $orderItemsDetails = array();
        $orderItemsDetails['orderId'] = $order->id;
        
        try {
            foreach ($jsonArray as $key => $value) {

                $orderItemsDetails['serviceId'] = $value->serviceId;
    
                if ($value->serviceId == 1) {
                    $frontImageFileName = $this->AppHelper->decodeImage($value->nicTranslateModel->frontImg);
                    $backImageFileName = $this->AppHelper->decodeImage($value->nicTranslateModel->backImg);
    
                    $value->nicTranslateModel->frontImg = $frontImageFileName;
                    $value->nicTranslateModel->backImg = $backImageFileName;
    
                    $orderItemsDetails['jsonValue'] = json_encode($value->nicTranslateModel);
                } else if ($value->serviceId == 2) {
                    $frontImageFileName = $this->AppHelper->decodeImage($value->bcTranslateModel->frontImage);
                    $backImageFileName = $this->AppHelper->decodeImage($value->bcTranslateModel->backImage);
    
                    $value->bcTranslateModel->frontImage = $frontImageFileName;
                    $value->bcTranslateModel->backImage = $backImageFileName;
    
                    $orderItemsDetails['jsonValue'] = json_encode($value->bcTranslateModel);
                } else if ($value->serviceId == 3) {
                    $frontImageFileName = $this->AppHelper->decodeImage( $value->mcTranslateModel->frontImg);
                    $backImageFilename = $this->AppHelper->decodeImage($value->mcTranslateModel->backImg);
    
                    $value->mcTranslateModel->frontImg = $frontImageFileName;
                    $value->mcTranslateModel->backImg = $backImageFilename;
    
                    $orderItemsDetails['jsonValue'] = json_encode($value->mcTranslateModel);
                } else if ($value->serviceId == 4) {
                    $frontImageFileName = $this->AppHelper->decodeImage($value->dcTranslateModel->frontImg);
                    $backImageFileName = $this->AppHelper->decodeImage($value->dcTranslateModel->backImg);
    
                    $value->dcTranslateModel->frontImg = $frontImageFileName;
                    $value->dcTranslateModel->backImg = $backImageFileName;
    
                    $orderItemsDetails['jsonValue'] = json_encode($value->dcTranslateModel);
                } else if ($value->serviceId == 5 || $value->serviceId == 6 || $value->serviceId == 8 || $value->serviceId == 10 || $value->serviceId == 11 || $value->serviceId == 12 || $value->serviceId == 14) {
                    
                    if (property_exists($value->otherDocumentModel, 'page1')) {
                        $page1ImageFileName = $this->AppHelper->decodeImage($value->otherDocumentModel->page1);
                
                        $value->otherDocumentModel->page1 = $page1ImageFileName;
                    }
    
                    if (property_exists($value->otherDocumentModel, 'page2')) {
                        $page2ImageFileName = $this->AppHelper->decodeImage($value->otherDocumentModel->page2);

                        $value->otherDocumentModel->page2 = $page2ImageFileName;
                    }
    
                    if (property_exists($value->otherDocumentModel, 'page3')) {
                        $page3ImageFileName = $this->AppHelper->decodeImage($value->otherDocumentModel->page3);

                        $value->otherDocumentModel->page3 = $page3ImageFileName;
                    }
    
                    if (property_exists($value->otherDocumentModel, 'page4')) {
                        $page4ImageFileName = $this->AppHelper->decodeImage($value->otherDocumentModel->page4);

                        $value->otherDocumentModel->page4 = $page4ImageFileName;
                    }
    
                    if (property_exists($value->otherDocumentModel, 'page5')) {
                        $page5ImageFileName = $this->AppHelper->decodeImage($value->otherDocumentModel->page5);

                        $value->otherDocumentModel->page5 = $page5ImageFileName;
                    }
    
                    if (property_exists($value->otherDocumentModel, 'page6')) {
                        $page6ImageFileName = $this->AppHelper->decodeImage($value->otherDocumentModel->page6);

                        $value->otherDocumentModel->page6 = $page6ImageFileName;
                    }
    
                    $orderItemsDetails['jsonValue'] = json_encode($value->otherDocumentModel);
                } else if ($value->serviceId == 7) {
                    if (property_exists($value->affidavitModel, "page1")) {
                        $page1ImageFileName = $this->AppHelper->decodeImage($value->affidavitModel->page1);

                        $value->affidavitModel->page1 = $page1ImageFileName;
                    }
    
                    if (property_exists($value->affidavitModel, "page2")) {
                        $page2ImageFileName = $this->AppHelper->decodeImage($value->affidavitModel->page2);

                        $value->affidavitModel->page2 = $page2ImageFileName;
                    }
    
                    if (property_exists($value->affidavitModel, "page3")) {
                        $page3ImageFileName = $this->AppHelper->decodeImage($value->affidavitModel->page3);

                        $value->affidavitModel->page3 = $page3ImageFileName;
                    }
    
                    if (property_exists($value->affidavitModel, "page4")) {
                        $page4ImageFileName = $this->AppHelper->decodeImage($value->affidavitModel->page4);

                        $value->affidavitModel->page4 = $page4ImageFileName;
                    }
    
                    if (property_exists($value->affidavitModel, "page5")) {
                        $page5ImageFileName = $this->AppHelper->decodeImage($value->affidavitModel->page5);

                        $value->affidavitModel->page5 = $page5ImageFileName;
                    }
    
                    $orderItemsDetails['jsonValue'] = json_encode($value->affidavitModel);
                } else if ($value->serviceId == 9) {
                    $frontImageFileName = $this->AppHelper->decodeImage($value->schoolLeavingCertificateNModel->frontImage);
                    $backImageFileName = $this->AppHelper->decodeImage($value->schoolLeavingCertificateNModel->backImage);
    
                    $value->schoolLeavingCertificateNModel->frontImage = $frontImageFileName;
                    $value->schoolLeavingCertificateNModel->backImage = $backImageFileName;
    
                    $orderItemsDetails['jsonValue'] = json_encode($value->schoolLeavingCertificateNModel);
                } else if ($value->serviceId == 13 || $value->serviceId == 15) {
                    if (property_exists($value->deedModel, 'page1')) {
                        $page1ImageFileName = $this->AppHelper->decodeImage($value->deedModel->page1);
                    
                        $value->deedModel->page1 = $page1ImageFileName;
                    }

                    if (property_exists($value->deedModel, 'page2')) {
                        $page2ImageFileName = $this->AppHelper->decodeImage($value->deedModel->page2);
                    
                        $value->deedModel->page2 = $page2ImageFileName;
                    }

                    if (property_exists($value->deedModel, 'page3')) {
                        $page3ImageFileName = $this->AppHelper->decodeImage($value->deedModel->page3);
                    
                        $value->deedModel->page3 = $page3ImageFileName;
                    }

                    if (property_exists($value->deedModel, 'page4')) {
                        $page4ImageFileName = $this->AppHelper->decodeImage($value->deedModel->page4);
                    
                        $value->deedModel->page4 = $page4ImageFileName;
                    }

                    if (property_exists($value->deedModel, 'page5')) {
                        $page5ImageFileName = $this->AppHelper->decodeImage($value->deedModel->page5);
                    
                        $value->deedModel->page5 = $page5ImageFileName;
                    }

                    if (property_exists($value->deedModel, 'page6')) {
                        $page6ImageFileName = $this->AppHelper->decodeImage($value->deedModel->page6);
                    
                        $value->deedModel->page6 = $page6ImageFileName;
                    }
                }
                
                $orderItemsDetails['createTime'] = $this->AppHelper->get_date_and_time();
                $orderItemsDetails['modifiedTime'] = $this->AppHelper->get_date_and_time();
    
                $this->OrderItems->add_log($orderItemsDetails);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function onePayGateway($paymentInfo) {
        // $app_id = "HA26118C3C3BC0786F784";
        // $hash_salt = "U0AN118C3C3BC0786F7BF";
        // $app_token = "599dd6ba83f7cef3b1071b1503a036ca2feca6f644e8553934b77d03eb3044740b949e0eed2213b0.MFND118C3C3BC0786F7D8";

        $app_id = "2MJ3118E348BC48E57A30";
        $hash_salt = "V7R4118E348BC48E57A5C";
        $app_token = "17d74e32519225a59421c269cb1efd8447ccade301dee6b4139530e62c8bf8a11dbd58c003519e24.C0VB118E348BC48E57A72";

        $onepay_args = array(
        
        "amount" => $paymentInfo['amount'], //only upto 2 decimal points
        "currency" => "LKR", //LKR OR USD
        "app_id"=> $app_id,
        "reference" => $paymentInfo['reference'], //must have 10 or more digits , spaces are not allowed
        "customer_first_name" => $paymentInfo['firstName'], // spaces are not allowed
        "customer_last_name"=> $paymentInfo['lastName'], // spaces are not allowed
        "customer_phone_number" => $paymentInfo['contact'], //must start with +94, spaces are not allowed
        "customer_email" => $paymentInfo['email'], // spaces are not allowed
        "transaction_redirect_url" => "https://dashboard.itranslate.lk", // spaces are not allowed
        "additional_data" => "sample" //only support string, spaces are not allowed, this will return in response also
        );

        $data=json_encode($onepay_args,JSON_UNESCAPED_SLASHES);

        $data_json = $data."".$hash_salt;

        $hash_result = hash('sha256',$data_json);

        $curl = curl_init();

        $url = 'https://merchant-api-live-v2.onepay.lk/api/ipg/gateway/request-payment-link/?hash=';
        $url .= $hash_result;

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'Authorization:'."".$app_token,
                'Content-Type:application/json'
            ),
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {     
            $error_msg = curl_error($curl); 
            echo $error_msg; 
        } 

        curl_close($curl);

        $result = json_decode($response, true);

        if ($result['message'] == "success") {

            $re_url = $result['data']['gateway']['redirect_url'];

            return $result['data'];
        } else {
            $result['data'] = null;
            return $result;
        }
    }

    public function verifyOnePayPayment(Request $request) {

        Log::info('OnePay Callback:', $request->all());

        if ($request->status_message == "SUCCESS" && $request->status == 1) {
            try {
                $orderRef = $this->OnePayGatewayLog->get_order_by_ref($request->transaction_id);

                if ($orderRef) {
                    $paymentConfirmLog = array();
                    $paymentConfirmLog['reference']  = $request->transaction_id;
                    $paymentConfirmLog['status'] = 1;

                    $orderPayInfo = array();
                    $orderPayInfo['orderId'] = $orderRef->order_id;
                    $orderPayInfo['paymentStatus'] = 1;

                    $updatePaymentLog = $this->OnePayGatewayLog->update_payment_log($paymentConfirmLog);
                    $updateOrder = $this->Order->update_order_pay($orderPayInfo);

                    if ($updatePaymentLog && $updateOrder) {
                        return $this->AppHelper->responseMessageHandle(1, "Operation Successfully");
                    } else {
                        return $this->AppHelper->responseMessageHandle(1, "Error Occured.");
                    }
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }   
        } else {
            return $this->AppHelper->responseMessageHandle(1, $request->status_message);
        }
    }
}
