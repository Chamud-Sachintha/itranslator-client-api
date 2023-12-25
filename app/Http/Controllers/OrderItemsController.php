<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\AdminOrderAssign;
use App\Models\Client;
use App\Models\NotaryServiceOrder;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Service;
use App\Models\TranslatedDocuments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderItemsController extends Controller
{
    private $AppHelper;
    private $Client;
    private $Order;
    private $OrderItems;
    private $NotaryServiceOrder;
    private $Service;
    private $OrderAssign;
    private $TranslateDocument;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Client = new Client();
        $this->Order = new Order();
        $this->OrderItems = new OrderItems();
        $this->NotaryServiceOrder = new NotaryServiceOrder();
        $this->Service = new Service();
        $this->OrderAssign = new AdminOrderAssign();
        $this->TranslateDocument = new TranslatedDocuments();
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
                $orderDetails['invoiceNo'] = $this->AppHelper->generateInvoiceNumber("TR");
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

                    $dataList = array();
                    foreach ($resp as $key => $value) {
                        $dataList[$key]['invoiceNo'] = $value['invoice_no'];
                        $dataList[$key]['paymentStatus'] = $value['payment_status'];
                        $dataList[$key]['createTime'] = $value['create_time'];
                        $dataList[$key]['orderStatus'] = $value['order_status'];
                        $dataList[$key]['totalAmount'] = $value['total_amount'];
                        $dataList[$key]['paymentType'] = $value['payment_type'];
                    }

                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getTranslateOrderInfoByInvoice(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $invoiceNo = (is_null($request->invoiceNo) || empty($request->invoiceNo)) ? "" : $request->invoiceNo;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($invoiceNo == "") {
            return $this->AppHelper->responseMessageHandle(0, "Invoice No is required.");
        } else {

            try {
                $orderInfo = $this->Order->get_order_by_invoice($invoiceNo);

                if ($orderInfo) {
                    $resp = $this->OrderItems->get_by_orderId($orderInfo->id);

                    $dataList = array();
                    foreach ($resp as $key => $value) {
                        $serviceInfo = $this->Service->find_by_service_id($value['service_id']);
                        $orderAssignInfo = $this->OrderAssign->get_by_invoice_id($orderInfo->invoice_no);
                        $jsonDecodedValue = json_decode($value->json_value);

                        $dataList[$key]['serviceId'] = $value['service_id'];
                        $dataList[$key]['documentTitle'] = $serviceInfo['service_name'];
                        $dataList[$key]['pages'] = $jsonDecodedValue->pages;
                        $dataList[$key]['createTime'] = $value['create_time'];
                        $dataList[$key]['assignedTime'] = $orderAssignInfo['create_time'];
                    }

                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getTranslatedDocsList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $invoiceNo = (is_null($request->invoiceNo) || empty($request->invoiceNo)) ? "" : $request->invoiceNo;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($invoiceNo == "") {
            return $this->AppHelper->responseMessageHandle(0, "Invoice No is required.");
        } else {

            try {
                $order = $this->Order->get_order_by_invoice($invoiceNo);

                if ($order) {
                    $resp = $this->TranslateDocument->get_doc_list_by_order_id($order->id);

                    $dataList = array();
                    foreach ($resp as $key => $value) {
                        $dataList[$key]['orderId'] = $value['order_id'];
                        $dataList[$key]['document'] = $value['document'];
                        $dataList[$key]['createTime'] = $value['create_time'];
                    }

                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
