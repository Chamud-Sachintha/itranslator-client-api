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

                $orderItemsResp = null;

                if ($order) {
                    
                    $jsonArray = json_decode(json_encode($valueObjArray));

                    $orderItemsResp = $this->createOrderItemsArray($order, $jsonArray);
                }

                if ($order && $orderItemsResp) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete Successfully.");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
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

                $orderAssignInfo = $this->OrderAssign->get_by_invoice_id($orderInfo->invoice_no);

                if (empty($orderAssignInfo)) {
                    return $this->AppHelper->responseMessageHandle(0, "Order is Not Taken by Admin Yet.");
                }

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
}
