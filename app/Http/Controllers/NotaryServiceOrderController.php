<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Client;
use App\Models\MainNotaryServiceCategory;
use App\Models\NotaryDocuments;
use App\Models\NotaryServiceOrder;
use App\Models\SubNotaryServiceCategory;
use Illuminate\Http\Request;

class NotaryServiceOrderController extends Controller
{
    private $AppHelper;
    private $NotaryServiceOrder;
    private $MainNotaryServiceCategory;
    private $SubNotaryServiceCategory;
    private $Client;
    private $NotaryDocuments;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->NotaryServiceOrder = new NotaryServiceOrder();
        $this->MainNotaryServiceCategory = new MainNotaryServiceCategory();
        $this->SubNotaryServiceCategory = new SubNotaryServiceCategory();
        $this->Client = new Client();
        $this->NotaryDocuments = new NotaryDocuments();
    }

    public function placeNewNotaryServiceOrder(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $mainCategory = (is_null($request->mainNotaryCategory) || empty($request->mainNotaryCategory)) ? "" : $request->mainNotaryCategory;
        $subCategory = (is_null($request->subNotaryCategory) || empty($request->subNotaryCategory)) ? "" : $request->subNotaryCategory;
        $serviceDescription = (is_null($request->serviceDescription) || empty($request->serviceDescription)) ? "" : $request->serviceDescription;
        $firstDoc = (is_null($request->firstDoc) || empty($request->firstDoc)) ? "" : $request->firstDoc;
        $secondDoc = (is_null($request->secondDoc) || empty($request->secondDoc)) ? "" : $request->secondDoc;
        $thirdDoc = (is_null($request->thirdDoc) || empty($request->thirdDoc)) ? "" : $request->thirdDoc;
        $dateOfSigning = (is_null($request->dateOfSigning) || empty($request->dateOfSigning)) ? "" : $request->dateOfSigning;
        $startDate = (is_null($request->startDate) || empty($request->startDate)) ? "" : $request->startDate;
        $endDate = (is_null($request->endDate) || empty($request->endDate)) ? "" : $request->endDate;
        $value = (is_null($request->value) || empty($request->value)) ? "" : $request->value;
        $monthlyRent = (is_null($request->monthlyRent) || empty($request->monthlyRent)) ? "" : $request->monthlyRent;
        $advanceAmt = (is_null($request->advanceAmt) || empty($request->advanceAmt)) ? "" : $request->advanceAmt;
        $VODNumber = (is_null($request->vodNumber) || empty($request->vodNumber)) ? "" : $request->vodNumber;
        $ds = (is_null($request->ds) || empty($request->ds)) ? "" : $request->ds;
        $lg = (is_null($request->lg) || empty($request->lg)) ? "" : $request->lg;
        $district = (is_null($request->district) || empty($request->district)) ? "" : $request->district;
        $lro = (is_null($request->lro) || empty($request->lro)) ? "" : $request->lro; 
        $notaryServicePersonList = (is_null($request->notaryServicePersonList) || empty($request->notaryServicePersonList)) ? "" : $request->notaryServicePersonList;
       // dd($notaryServicePersonList);


     
        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $clientInfo = $this->Client->find_by_token($request_token);
                $isValidCategory = $this->validateCategories($mainCategory, $subCategory);

                $notaryServiceOrder = array();

                if ($request->notaryServicePersonList) {
                    $natureOfSignatures = []; // Initialize the array to store person data

                    // Loop through each person in the list
                    foreach ($request->notaryServicePersonList as $person) {
                        // Check if natureOfSignature exists and is not empty for this person
                        if (isset($person['natureOfSignature']) && !empty($person['natureOfSignature'])) {
                            // Add the person's data to the $natureOfSignatures array
                            $natureOfSignatures[] = [
                                'name' => isset($person['name']) ? $person['name'] : null,
                                'address' => isset($person['address']) ? $person['address'] : null,
                                'adultIdNumber' => isset($person['adultIdNumber']) ? $person['adultIdNumber'] : null,
                                'bcNumber' => isset($person['bcNumber']) ? $person['bcNumber'] : null,
                                'drivingLicNo' => isset($person['drivingLicNo']) ? $person['drivingLicNo'] : null,
                                'email' => isset($person['email']) ? $person['email'] : null,
                                'mcNumber' => isset($person['mcNumber']) ? $person['mcNumber'] : null,
                                'nicNumber' => isset($person['nicNumber']) ? $person['nicNumber'] : null,
                                'passportNo' => isset($person['passportNo']) ? $person['passportNo'] : null,
                                'personCategory' => isset($person['personCategory']) ? $person['personCategory'] : null,
                                'phoneNumber' => isset($person['phoneNumber']) ? $person['phoneNumber'] : null,
                                'natureOfSignature' => $this->decodeImageData(isset($person['natureOfSignature']) ? $person['natureOfSignature'] : null),
                            ];
                        }
                    }

                    // $encf = json_encode($natureOfSignatures);
                    // return $this->AppHelper->responseMessageHandle(0, $encf);
                  //  dd($natureOfSignatures);
                     $notaryServiceOrder['clientId'] = $clientInfo->id;
                    $notaryServiceOrder['invoiceNo'] = $this->AppHelper->generateInvoiceNumber("NS");
                    $notaryServiceOrder['mainCategory'] = $mainCategory;
                    $notaryServiceOrder['subCategory'] = $subCategory;
                    $notaryServiceOrder['descriptionOfService'] = $serviceDescription;
                    if($firstDoc){
                        $notaryServiceOrder['firstDoc'] = $this->decodeImageData($firstDoc);
                    }
                    else{
                        $notaryServiceOrder['firstDoc'] = '';
                    }
                    if($secondDoc){
                        $notaryServiceOrder['secondDoc'] = $this->decodeImageData($secondDoc);
                    }
                    else{
                        $notaryServiceOrder['secondDoc'] ='';
                    }
                   if($thirdDoc){
                    $notaryServiceOrder['thirdDoc'] = $this->decodeImageData($thirdDoc);
                   }
                   else{
                    $notaryServiceOrder['thirdDoc'] ='';
                   }
                   
                    $notaryServiceOrder['dateOfSigning'] = strtotime($dateOfSigning);
                    $notaryServiceOrder['startDate'] = strtotime($startDate);
                    $notaryServiceOrder['endDate'] = strtotime($endDate);
                   $notaryServiceOrder['value'] = $value;
                    $notaryServiceOrder['monthlyRent'] = $monthlyRent;
                    $notaryServiceOrder['advanceAmount'] = $advanceAmt;
                    $notaryServiceOrder['von'] = $VODNumber;
                    $notaryServiceOrder['divisionalSec'] = $ds;
                    $notaryServiceOrder['localGov'] = $lg;
                    $notaryServiceOrder['district'] = $district;
                    $notaryServiceOrder['lro'] = $lro;
                    $notaryServiceOrder['notaryPersonJson'] = json_encode($natureOfSignatures);
                    $notaryServiceOrder['paymentStatus'] = 0;
                    $notaryServiceOrder['orderStatus'] = 0;
                    $notaryServiceOrder['createTime'] = $this->AppHelper->get_date_and_time();
                    $notaryServiceOrder['modifiedTime'] = $this->AppHelper->get_date_and_time();
                   // return $this->AppHelper->responseMessageHandle(0, $notaryServiceOrder);
                    $resp = $this->NotaryServiceOrder->add_log($notaryServiceOrder);

                    if ($resp) {
                        return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                    }

                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Category is not Valid");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getMainNotaryCategoryList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $allMainCategoryList = $this->MainNotaryServiceCategory->find_all();

                $dataList = array();
                foreach ($allMainCategoryList as $key => $value) {
                    $dataList[$key]['id'] = $value['id'];
                    $dataList[$key]['categoryName'] = $value['category_name'];
                }
// this error fix 
                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getFirstSubCategoryByMainCategory(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $mainCategoryId = (is_null($request->mainCategoryCode) || empty($request->mainCategoryCode)) ? "" : $request->mainCategoryCode;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token isrequired.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($mainCategoryId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Main Category Id is required.");
        } else {

            try {
                $subCategoryList = $this->SubNotaryServiceCategory->get_all_by_main_cate_code($mainCategoryId);

                $dataList = array();
                foreach ($subCategoryList as $key => $value) {
                    $dataList[$key]['id'] = $value['id'];
                    $dataList[$key]['mainCategoryId'] = $value['main_category_id'];
                    $dataList[$key]['subCategoryName'] = $value['sub_category_name'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getNotaryServiceOrderRequests(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $client = $this->Client->find_by_token($request_token);
                $resp = $this->NotaryServiceOrder->get_order_requests($client->id);

                if ($resp) {
                    $dataList = array();
                    foreach ($resp as $key => $value) {
                        $dataList[$key]['invoiceNo'] = $value['invoice_no'];
                        $dataList[$key]['paymentStatus'] = $value['payment_status'];
                        $dataList[$key]['createTime'] = $value['create_time'];
                        $dataList[$key]['orderStatus'] = $value['order_status'];
                        $dataList[$key]['totalAmount'] = $value['total_amt'];
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

    public function getCompleteNotaryServiceOrderRequests(Request $request){
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $client = $this->Client->find_by_token($request_token);
                $resp = $this->NotaryServiceOrder->get_Complete_order_requests($client->id);

                if ($resp) {
                    $dataList = array();
                    foreach ($resp as $key => $value) {
                        $dataList[$key]['invoiceNo'] = $value['invoice_no'];
                        $dataList[$key]['paymentStatus'] = $value['payment_status'];
                        $dataList[$key]['createTime'] = $value['create_time'];
                        $dataList[$key]['orderStatus'] = $value['order_status'];
                        $dataList[$key]['totalAmount'] = $value['total_amt'];
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

    public function getOrderInfoByInvoice(Request $request) {

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
                $resp = $this->NotaryServiceOrder->get_order_by_invoice($invoiceNo);

                if ($resp) {
                    $dataList['firstDocType'] = json_decode($resp->doc_1);
                    $dataList['secondDocType'] = json_decode($resp->doc_2);
                    $dataList['thirdDocType'] = json_decode($resp->doc_3);

                    $dataList['totalAmount'] = $resp['total_amt'];
                    $dataList['bankSlip'] = false;

                    if ($resp['bank_slip'] != null) {
                        $dataList['bankSlip'] = true;
                    }

                    $dataList['paymentStatus'] = $resp['payment_status'];
                    $dataList['orderStatus'] = $resp['order_status'];

                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Invoice No");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getNotaryDocsList(Request $request) {

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
                $order = $this->NotaryServiceOrder->get_order_by_invoice($invoiceNo);

                if ($order) {
                    $resp = $this->NotaryDocuments->get_doc_list_by_order_id($order->id);

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

    public function updateOrderStausByClient(Request $request) {
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag =(is_null($request->flag) || empty($request->flag)) ? "" : $request->token;
        $invoiceNo = (is_null($request->invoiceNo) || empty($request->invoiceNo)) ? "" : $request->invoiceNo;
        $orderStatus = (is_null($request->orderStatus) || empty($request->orderStatus)) ? "" : $request->orderStatus;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($invoiceNo == "") {
            return $this->AppHelper->responseMessageHandle(0, "Invoice No is required.");
        } else if ($orderStatus == "") {
            return $this->AppHelper->responseMessageHandle(0, "Payment Status is required.");
        } else {

            try {
                $orderInfo = array();
                $orderInfo['invoiceNo'] = $invoiceNo;
                $orderInfo['orderStatus'] = $orderStatus;

                $resp = $this->NotaryServiceOrder->update_order_status_client($orderInfo);

                if ($resp) {
                    return $this->AppHelper->responseMessageHandle(1, "Opertion Complete");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function submitBankSlipForOrder(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag =(is_null($request->flag) || empty($request->flag)) ? "" : $request->token;
        $invoiceNo = (is_null($request->invoiceNo) || empty($request->invoiceNo)) ? "" : $request->invoiceNo;
        $bankSlip = (is_null($request->bankSlip) || empty($request->bankSlip)) ? "" : $request->bankSlip;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($invoiceNo == "") {
            return $this->AppHelper->responseMessageHandle(0, "Invoice No is required.");
        } else if ($bankSlip == "") {
            return $this->AppHelper->responseMessageHandle(0, "Bank Slip is required.");
        } else {

            try {
                $bankSlipInfo = array();
                $bankSlipInfo['invoiceNo'] = $invoiceNo;
                $bankSlipInfo['bankSlip'] = $this->AppHelper->decodeImage($bankSlip);

                $resp = $this->NotaryServiceOrder->submit_bank_slip($bankSlipInfo);

                if ($resp) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    private function decodeImageData($base64Array) {

        $jsonEncodeImageData = array();

        foreach ($base64Array as $key => $value) {
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value));
            $imageFileName = 'image_' . time() . $key . '.png';

            file_put_contents(public_path() . '/images' . '/' . $imageFileName, $imageData);
            $jsonEncodeImageData[$key] = $imageFileName;    
        }

        return json_encode($jsonEncodeImageData);
    }

    private function validateCategories($mainCategoryCode, $subCategoryCode) {

        $isValidCategory = false;

        try {

            $catInfo = array();
            $catInfo['id'] = $subCategoryCode;
            $catInfo['mainCatId'] = $mainCategoryCode;

            $mainCategory = $this->MainNotaryServiceCategory->find_by_id($mainCategoryCode);
            $subCategory = $this->SubNotaryServiceCategory->find_by_main_code($catInfo);

            if (!empty($mainCategory) && !empty($subCategory)) {
                $isValidCategory = true;   
            }
        } catch (\Exception $e) {
            return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
        }

        return $isValidCategory;
    }
}
