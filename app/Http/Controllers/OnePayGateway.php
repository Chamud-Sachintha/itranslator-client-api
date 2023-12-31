<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OnePayGateway extends Controller
{
    private $AppHelper;
    private $Client;
    private $Order;
    private $OrderItems;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Order = new Order();
        $this->OrderItems = new OrderItems();
        $this->Client = new Client();
    }

    public function placeNewOrderWithGateway(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $valueObjArray = (is_null($request->valueObjModel) || empty($request->valueObjModel)) ? "" : $request->valueObjModel;
        $deliveryTime = (is_null($request->deliveryTimeType) || empty($request->deliveryTimeType)) ? "" : $request->deliveryTimeType;
        $deliveryMethod = (is_null($request->deliveryMethod) || empty($request->deliveryMethod)) ? "" : $request->deliveryMethod;
        $paymentMethod = (is_null($request->paymentMethod) || empty($request->paymentMethod)) ? "" : $request->paymentMethod;
        $totalAmount = (is_null($request->totalAmount) || empty($request->totalAmount)) ? "" : $request->totalAmount;

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

                $paymentInfo = array();
                $paymentInfo['amount'] = $totalAmount;
                $paymentInfo['reference'] = $this->AppHelper->generate_ref(10);
                $paymentInfo['firstName'] = $client->full_name;
                $paymentInfo['lastName'] = "Test";
                $paymentInfo['contact'] = $client->mobile_number;
                $paymentInfo['email'] = $client->email;

                $response = $this->onePayGateway($paymentInfo);

                // print($response);

                // $orderDetails = array();
                // $orderDetails['clientId'] = $client->id;
                // $orderDetails['orderStatus'] = 0;
                // $orderDetails['invoiceNo'] = $this->AppHelper->generateInvoiceNumber("TR");
                // $orderDetails['createTime'] = $this->AppHelper->get_date_and_time();
                // $orderDetails['deliveryTimeType'] = $deliveryTime;
                // $orderDetails['deliveryMethod'] = $deliveryMethod;
                // $orderDetails['paymentMethod'] = $paymentMethod;
                // $orderDetails['totalAmount'] = floatval($totalAmount);

                // $order = $this->Order->add_log($orderDetails);

                // $orderItemsResp = null;

                // if ($order) {
                    
                //     $jsonArray = json_decode(json_encode($valueObjArray));

                //     $orderItemsResp = $this->createOrderItemsArray($order, $jsonArray);
                // }

                // if ($order && $orderItemsResp) {
                //     return $this->AppHelper->responseMessageHandle(1, "Operation Complete Successfully.");
                // } else {
                //     return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                // }
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
                    $frontImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->nicTranslateModel->frontImg));
                    $frontImagefilename = 'image_' . time() . '.png';
    
                    $backImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->nicTranslateModel->backImg));
                    $backImagefilename = 'image_' . time() . '.png';
    
                    Storage::disk('public')->put($frontImagefilename, $frontImageData);
                    Storage::disk('public')->put($backImagefilename, $backImageData);
    
                    $value->nicTranslateModel->frontImg = $frontImagefilename;
                    $value->nicTranslateModel->backImg = $backImagefilename;
    
                    $orderItemsDetails['jsonValue'] = json_encode($value->nicTranslateModel);
                } else if ($value->serviceId == 2) {
                    $frontImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->bcTranslateModel->frontImage));
                    $frontImagefilename = 'image_' . time() . '.png';
    
                    $backImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->bcTranslateModel->backImage));
                    $backImagefilename = 'image_' . time() . '.png';
    
                    Storage::disk('public')->put($frontImagefilename, $frontImageData);
                    Storage::disk('public')->put($backImagefilename, $backImageData);
    
                    $value->bcTranslateModel->frontImage = $frontImagefilename;
                    $value->bcTranslateModel->backImage = $backImagefilename;
    
                    $orderItemsDetails['jsonValue'] = json_encode($value->bcTranslateModel);
                } else if ($value->serviceId == 3) {
                    $frontImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->mcTranslateModel->frontImg));
                    $frontImagefilename = 'image_' . time() . '.png';
    
                    $backImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->mcTranslateModel->backImg));
                    $backImagefilename = 'image_' . time() . '.png';
    
                    Storage::disk('public')->put($frontImagefilename, $frontImageData);
                    Storage::disk('public')->put($backImagefilename, $backImageData);
    
                    $value->mcTranslateModel->frontImg = $frontImagefilename;
                    $value->mcTranslateModel->backImg = $backImagefilename;
    
                    $orderItemsDetails['jsonValue'] = json_encode($value->mcTranslateModel);
                } else if ($value->serviceId == 4) {
                    $frontImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->dcTranslateModel->frontImg));
                    $frontImagefilename = 'image_' . time() . '.png';
    
                    $backImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->dcTranslateModel->backImg));
                    $backImagefilename = 'image_' . time() . '.png';
    
                    Storage::disk('public')->put($frontImagefilename, $frontImageData);
                    Storage::disk('public')->put($backImagefilename, $backImageData);
    
                    $value->dcTranslateModel->frontImg = $frontImagefilename;
                    $value->dcTranslateModel->backImg = $backImagefilename;
    
                    $orderItemsDetails['jsonValue'] = json_encode($value->dcTranslateModel);
                } else if ($value->serviceId == 5 || $value->serviceId == 6 || $value->serviceId == 8 || $value->serviceId == 10 || $value->serviceId == 11 || $value->serviceId == 12 || $value->serviceId == 14) {
                    
                    if (property_exists($value->otherDocumentModel, 'page1')) {
                        $page1ImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->otherDocumentModel->page1));
                        $page1ImageFileName = 'image_' . time() . '.png';
    
                        Storage::disk('public')->put($page1ImageFileName, $page1ImageData);
                        $value->otherDocumentModel->page1 = $page1ImageFileName;
                    }
    
                    if (property_exists($value->otherDocumentModel, 'page2')) {
                        $page1ImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->otherDocumentModel->page2));
                        $page1ImageFileName = 'image_' . time() . '.png';
    
                        Storage::disk('public')->put($page1ImageFileName, $page1ImageData);
                        $value->otherDocumentModel->page2 = $page1ImageFileName;
                    }
    
                    if (property_exists($value->otherDocumentModel, 'page3')) {
                        $page1ImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->otherDocumentModel->page3));
                        $page1ImageFileName = 'image_' . time() . '.png';
    
                        Storage::disk('public')->put($page1ImageFileName, $page1ImageData);
                        $value->otherDocumentModel->page3 = $page1ImageFileName;
                    }
    
                    if (property_exists($value->otherDocumentModel, 'page4')) {
                        $page1ImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->otherDocumentModel->page4));
                        $page1ImageFileName = 'image_' . time() . '.png';
    
                        Storage::disk('public')->put($page1ImageFileName, $page1ImageData);
                        $value->otherDocumentModel->page4 = $page1ImageFileName;
                    }
    
                    if (property_exists($value->otherDocumentModel, 'page5')) {
                        $page1ImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->otherDocumentModel->page5));
                        $page1ImageFileName = 'image_' . time() . '.png';
    
                        Storage::disk('public')->put($page1ImageFileName, $page1ImageData);
                        $value->otherDocumentModel->page5 = $page1ImageFileName;
                    }
    
                    if (property_exists($value->otherDocumentModel, 'page6')) {
                        $page1ImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->otherDocumentModel->page6));
                        $page1ImageFileName = 'image_' . time() . '.png';
    
                        Storage::disk('public')->put($page1ImageFileName, $page1ImageData);
                        $value->otherDocumentModel->page6 = $page1ImageFileName;
                    }
    
                    $orderItemsDetails['jsonValue'] = json_encode($value->otherDocumentModel);
                } else if ($value->serviceId == 7) {
                    if (property_exists($value->affidavitModel, "page1")) {
                        $page1ImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->affidavitModel->page1));
                        $page1ImageFileName = 'image_' . time() . '.png';
    
                        Storage::disk('public')->put($page1ImageFileName, $page1ImageData);
                        $value->affidavitModel->page1 = $page1ImageFileName;
                    }
    
                    if (property_exists($value->affidavitModel, "page2")) {
                        $page1ImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->affidavitModel->page2));
                        $page1ImageFileName = 'image_' . time() . '.png';
    
                        Storage::disk('public')->put($page1ImageFileName, $page1ImageData);
                        $value->affidavitModel->page2 = $page1ImageFileName;
                    }
    
                    if (property_exists($value->affidavitModel, "page3")) {
                        $page1ImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->affidavitModel->page3));
                        $page1ImageFileName = 'image_' . time() . '.png';
    
                        Storage::disk('public')->put($page1ImageFileName, $page1ImageData);
                        $value->affidavitModel->page3 = $page1ImageFileName;
                    }
    
                    if (property_exists($value->affidavitModel, "page4")) {
                        $page1ImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->affidavitModel->page4));
                        $page1ImageFileName = 'image_' . time() . '.png';
    
                        Storage::disk('public')->put($page1ImageFileName, $page1ImageData);
                        $value->affidavitModel->page4 = $page1ImageFileName;
                    }
    
                    if (property_exists($value->affidavitModel, "page5")) {
                        $page1ImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->affidavitModel->page5));
                        $page1ImageFileName = 'image_' . time() . '.png';
    
                        Storage::disk('public')->put($page1ImageFileName, $page1ImageData);
                        $value->affidavitModel->page5 = $page1ImageFileName;
                    }
    
                    $orderItemsDetails['jsonValue'] = json_encode($value->affidavitModel);
                } else if ($value->serviceId == 9) {
                    $frontImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->schoolLeavingCertificateNModel->frontImage));
                    $frontImagefilename = 'image_' . time() . '.png';
    
                    $backImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->schoolLeavingCertificateNModel->backImage));
                    $backImagefilename = 'image_' . time() . '.png';
    
                    Storage::disk('public')->put($frontImagefilename, $frontImageData);
                    Storage::disk('public')->put($backImagefilename, $backImageData);
    
                    $value->schoolLeavingCertificateNModel->frontImage = $frontImagefilename;
                    $value->schoolLeavingCertificateNModel->backImage = $backImagefilename;
    
                    $orderItemsDetails['jsonValue'] = json_encode($value->schoolLeavingCertificateNModel);
                } else if ($value->serviceId == 13 || $value->serviceId == 15) {
                    if (property_exists($value->deedModel, 'page1')) {
                        $page1ImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->deedModel->page1));
                        $page1ImageFileName = 'image_' . time() . '.png';
    
                        Storage::disk('public')->put($page1ImageFileName, $page1ImageData);
                        $value->deedModel->page1 = $page1ImageFileName;
                    }

                    if (property_exists($value->deedModel, 'page2')) {
                        $page1ImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->deedModel->page2));
                        $page1ImageFileName = 'image_' . time() . '.png';
    
                        Storage::disk('public')->put($page1ImageFileName, $page1ImageData);
                        $value->deedModel->page2 = $page1ImageFileName;
                    }

                    if (property_exists($value->deedModel, 'page3')) {
                        $page1ImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->deedModel->page3));
                        $page1ImageFileName = 'image_' . time() . '.png';
    
                        Storage::disk('public')->put($page1ImageFileName, $page1ImageData);
                        $value->deedModel->page3 = $page1ImageFileName;
                    }

                    if (property_exists($value->deedModel, 'page4')) {
                        $page1ImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->deedModel->page4));
                        $page1ImageFileName = 'image_' . time() . '.png';
    
                        Storage::disk('public')->put($page1ImageFileName, $page1ImageData);
                        $value->deedModel->page4 = $page1ImageFileName;
                    }

                    if (property_exists($value->deedModel, 'page5')) {
                        $page1ImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->deedModel->page5));
                        $page1ImageFileName = 'image_' . time() . '.png';
    
                        Storage::disk('public')->put($page1ImageFileName, $page1ImageData);
                        $value->deedModel->page5 = $page1ImageFileName;
                    }

                    if (property_exists($value->deedModel, 'page6')) {
                        $page1ImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value->deedModel->page6));
                        $page1ImageFileName = 'image_' . time() . '.png';
    
                        Storage::disk('public')->put($page1ImageFileName, $page1ImageData);
                        $value->deedModel->page6 = $page1ImageFileName;
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
        $app_id = "HA26118C3C3BC0786F784";
        $hash_salt = "U0AN118C3C3BC0786F7BF";
        $app_token = "599dd6ba83f7cef3b1071b1503a036ca2feca6f644e8553934b77d03eb3044740b949e0eed2213b0.MFND118C3C3BC0786F7D8";

        $onepay_args = array(
        
        "amount" => $paymentInfo['amount'], //only upto 2 decimal points
        "currency" => "LKR", //LKR OR USD
        "app_id"=> $app_id,
        "reference" => $paymentInfo['reference'], //must have 10 or more digits , spaces are not allowed
        "customer_first_name" => $paymentInfo['firstName'], // spaces are not allowed
        "customer_last_name"=> $paymentInfo['lastName'], // spaces are not allowed
        "customer_phone_number" => $paymentInfo['contact'], //must start with +94, spaces are not allowed
        "customer_email" => $paymentInfo['email'], // spaces are not allowed
        "transaction_redirect_url" => "https://exmple.lk/respones", // spaces are not allowed
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

        if (isset($result['data']['gateway']['redirect_url'])) {

            $re_url = $result['data']['gateway']['redirect_url'];

            return $re_url;
        }
    }
}
