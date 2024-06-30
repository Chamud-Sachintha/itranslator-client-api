<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Client;
use App\Models\CSService;
use App\Models\CsPaymentModel;
use Illuminate\Http\Request;
use App\Mail\MailService;
use Illuminate\Support\Facades\Mail;

class CSServiceController extends Controller
{
    private $AppHelper;
    private $Client;
    private $CSService;
    private $OrderAssign;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Client = new Client();
        $this->CSService = new CSService();
        $this->OrderAssign = new CsPaymentModel();
    }

    public function placeNewCSOrder(Request $request) {

        $serviceIndex = (is_null($request->serviceIndex) || empty($request->serviceIndex)) ? "" : $request->serviceIndex;
        $companyName = (is_null($request->companyName) || empty($request->companyName)) ? "" : $request->companyName;
        $directorName = (is_null($request->directorNames) || empty($request->directorNames)) ? "" : $request->directorNames;
        $directorAddress = (is_null($request->directorAddress) || empty($request->directorAddress)) ? "" : $request->directorAddress;
        $directorTelephone = (is_null($request->directorTelephones) || empty($request->directorTelephones)) ? "" : $request->directorTelephones;
        $directorEmail = (is_null($request->directorEmails) || empty($request->directorEmails)) ? "" : $request->directorEmails;
        $devisionNumber = (is_null($request->devisionNumber) || empty($request->devisionNumber)) ? "" : $request->devisionNumber;
        $devisionalSectrial = (is_null($request->devisionalSectrial) || empty($request->devisionalSectrial)) ? "" : $request->devisionalSectrial;
        $directorDistrict = (is_null($request->directorDistrict) || empty($request->directorDistrict)) ? "" : $request->directorDistrict;
        $nicNumberOfDirectors = (is_null($request->nicNumberOfDirectors) || empty($request->nicNumberOfDirectors)) ? "" : $request->nicNumberOfDirectors;
        
        
        $regNumber = (is_null($request->regNumber) || empty($request->regNumber)) ? "" : $request->regNumber;
        $description = (is_null($request->description) || empty($request->description)) ? "" : $request->description;

        $dateOfAppointment = (is_null($request->dateOfAppointment) || empty($request->dateOfAppointment)) ? "" : $request->dateOfAppointment;
        $dateOfResign = (is_null($request->dateOfResign) || empty($request->dateOfResign)) ? "" : $request->dateOfResign;

        $documents = $request->files;

        if ($serviceIndex == "") {
            return $this->AppHelper->responseMessageHandle(0, "Service Index is required.");
        } else if ($companyName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Company Name is required.");
        } else {

            try {

                $client = $this->Client->find_by_token($request->token);

                $csServiceOrderInfo = array();
                $csServiceOrderInfo['serviceIndex'] = $serviceIndex;
                $csServiceOrderInfo['invoiceNo'] = $this->AppHelper->generateInvoiceNumber("CS");
                $csServiceOrderInfo['clientId'] = $client['id'];

                $json_value = array();

                if ($serviceIndex == 1) {
                    $json_value['companyName'] = $companyName;
                    $json_value['directorName'] = $directorName;
                    $json_value['directorAddress'] = $directorAddress;
                    $json_value['directorTelephone'] = $directorTelephone;
                    $json_value['directorEmail'] = $directorEmail;
                    $json_value['devisionNumber'] = $devisionNumber;
                    $json_value['devisionalSectrial'] = $devisionalSectrial;
                    $json_value['directorDistrict'] = $directorDistrict;
                    $json_value['nicNumberOfDirectors'] = $nicNumberOfDirectors;
                } else if ($serviceIndex == 2) {
                    $json_value['companyName'] = $companyName;
                    $json_value['regNumber'] = $regNumber;
                    $json_value['description'] = $description;
                } else if ($serviceIndex == 3) {
                    $json_value['companyName'] = $companyName;
                    $json_value['directorName'] = $directorName;
                    $json_value['directorAddress'] = $directorAddress;
                    $json_value['directorTelephone'] = $directorTelephone;
                    $json_value['directorEmail'] = $directorEmail;
                    $json_value['devisionNumber'] = $devisionNumber;
                    $json_value['devisionalSectrial'] = $devisionalSectrial;
                    $json_value['directorDistrict'] = $directorDistrict;
                    $json_value['nicNumberOfDirectors'] = $nicNumberOfDirectors;
                    $json_value['dateOfAppointment'] = $dateOfAppointment;
                } else if ($serviceIndex == 4) {
                    $json_value['companyName'] = $companyName;
                    $json_value['directorName'] = $directorName;
                    $json_value['directorAddress'] = $directorAddress;
                    $json_value['directorTelephone'] = $directorTelephone;
                    $json_value['directorEmail'] = $directorEmail;
                    $json_value['devisionNumber'] = $devisionNumber;
                    $json_value['devisionalSectrial'] = $devisionalSectrial;
                    $json_value['directorDistrict'] = $directorDistrict;
                    $json_value['nicNumberOfDirectors'] = $nicNumberOfDirectors;
                    $json_value['dateOfResign'] = $dateOfResign;
                } else if ($serviceIndex == 5 || $serviceIndex == 6 || $serviceIndex == 7) {
                    $json_value['companyName'] = $companyName;
                    $json_value['regNumber'] = $regNumber;
                    $json_value['description'] = $description;
                } else{

                }
                
                $index_nic = 0;
                $index_doc = 0;
                foreach ($documents as $key => $value) {
                    $ext = explode("-", $key);

                    $image_data = $this->AppHelper->storeImage($value, "cs_service");

                    if ($ext[0] == "nic") {
                        $json_value['nic'][$index_nic] = $image_data;

                        $index_nic++;
                    } else if ($ext[0] == "intent") {
                        $json_value['intent'] = $image_data;
                    } else if ($ext[0] == "resign") {
                        $json_value['resign'] = $image_data;
                    } else {
                        $json_value['doc'][$index_doc] = $image_data;

                        $index_doc++;
                    }
                }

                $csServiceOrderInfo['jsonValue'] = json_encode($json_value);
                $csServiceOrderInfo['paymentType'] = 1;
                $csServiceOrderInfo['createTime'] = $this->AppHelper->get_date_and_time();

                $resp = $this->CSService->add_log($csServiceOrderInfo);

                if ($resp) {
                   
                        $details = [
                            'OrderNo' => $resp->invoice_no ,
                            'full_name' => $client->full_name ,
                            'TypeOfOrder' => 'Company Sectratial Service' ,
                            'created_at' => date('Y-m-d H:i:s'),
                            'bodyType' => '3' 
                            
                        ];
                
                        Mail::to($client['email'])->send(new MailService($details));
                    return $this->AppHelper->responseMessageHandle(1, "Opertion omplete");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }

            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function placeUpdateCSOrder(Request $request) {

        $serviceIndex = (is_null($request->serviceIndex) || empty($request->serviceIndex)) ? "" : $request->serviceIndex;
        $companyName = (is_null($request->companyName) || empty($request->companyName)) ? "" : $request->companyName;
        $directorName = (is_null($request->directorNames) || empty($request->directorNames)) ? "" : $request->directorNames;
        $directorAddress = (is_null($request->directorAddress) || empty($request->directorAddress)) ? "" : $request->directorAddress;
        $directorTelephone = (is_null($request->directorTelephones) || empty($request->directorTelephones)) ? "" : $request->directorTelephones;
        $directorEmail = (is_null($request->directorEmails) || empty($request->directorEmails)) ? "" : $request->directorEmails;
        $devisionNumber = (is_null($request->devisionNumber) || empty($request->devisionNumber)) ? "" : $request->devisionNumber;
        $devisionalSectrial = (is_null($request->devisionalSectrial) || empty($request->devisionalSectrial)) ? "" : $request->devisionalSectrial;
        $directorDistrict = (is_null($request->directorDistrict) || empty($request->directorDistrict)) ? "" : $request->directorDistrict;
        $nicNumberOfDirectors = (is_null($request->nicNumberOfDirectors) || empty($request->nicNumberOfDirectors)) ? "" : $request->nicNumberOfDirectors;
        $invoiceNo = (is_null($request->invoiceNo) || empty($request->invoiceNo)) ? "" : $request->invoiceNo;
        
        
        $regNumber = (is_null($request->regNumber) || empty($request->regNumber)) ? "" : $request->regNumber;
        $description = (is_null($request->description) || empty($request->description)) ? "" : $request->description;

        $dateOfAppointment = (is_null($request->dateOfAppointment) || empty($request->dateOfAppointment)) ? "" : $request->dateOfAppointment;
        $dateOfResign = (is_null($request->dateOfResign) || empty($request->dateOfResign)) ? "" : $request->dateOfResign;

        $documents = $request->files;

        if ($serviceIndex == "") {
            return $this->AppHelper->responseMessageHandle(0, "Service Index is required.");
        } else if ($companyName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Company Name is required.");
        } else {

            try {

                $client = $this->Client->find_by_token($request->token);

                $csServiceOrderInfo = array();
               
                $csServiceOrderInfo['invoiceNo'] =  $invoiceNo;
                

                $json_value = array();

                if ($serviceIndex == 1) {
                    $json_value['companyName'] = $companyName;
                    $json_value['directorName'] = $directorName;
                    $json_value['directorAddress'] = $directorAddress;
                    $json_value['directorTelephone'] = $directorTelephone;
                    $json_value['directorEmail'] = $directorEmail;
                    $json_value['devisionNumber'] = $devisionNumber;
                    $json_value['devisionalSectrial'] = $devisionalSectrial;
                    $json_value['directorDistrict'] = $directorDistrict;
                    $json_value['nicNumberOfDirectors'] = $nicNumberOfDirectors;
                } else if ($serviceIndex == 2) {
                    $json_value['companyName'] = $companyName;
                    $json_value['regNumber'] = $regNumber;
                    $json_value['description'] = $description;
                } else if ($serviceIndex == 3) {
                    $json_value['companyName'] = $companyName;
                    $json_value['directorName'] = $directorName;
                    $json_value['directorAddress'] = $directorAddress;
                    $json_value['directorTelephone'] = $directorTelephone;
                    $json_value['directorEmail'] = $directorEmail;
                    $json_value['devisionNumber'] = $devisionNumber;
                    $json_value['devisionalSectrial'] = $devisionalSectrial;
                    $json_value['directorDistrict'] = $directorDistrict;
                    $json_value['nicNumberOfDirectors'] = $nicNumberOfDirectors;
                    $json_value['dateOfAppointment'] = $dateOfAppointment;
                } else if ($serviceIndex == 4) {
                    $json_value['companyName'] = $companyName;
                    $json_value['directorName'] = $directorName;
                    $json_value['directorAddress'] = $directorAddress;
                    $json_value['directorTelephone'] = $directorTelephone;
                    $json_value['directorEmail'] = $directorEmail;
                    $json_value['devisionNumber'] = $devisionNumber;
                    $json_value['devisionalSectrial'] = $devisionalSectrial;
                    $json_value['directorDistrict'] = $directorDistrict;
                    $json_value['nicNumberOfDirectors'] = $nicNumberOfDirectors;
                    $json_value['dateOfResign'] = $dateOfResign;
                } else if ($serviceIndex == 5 || $serviceIndex == 6 || $serviceIndex == 7) {
                    $json_value['companyName'] = $companyName;
                    $json_value['regNumber'] = $regNumber;
                    $json_value['description'] = $description;
                } else{

                }
                
                $index_nic = 0;
                $index_doc = 0;
                foreach ($documents as $key => $value) {
                    $ext = explode("-", $key);

                    $image_data = $this->AppHelper->storeImage($value, "cs_service");

                    if ($ext[0] == "nic") {
                        $json_value['nic'][$index_nic] = $image_data;

                        $index_nic++;
                    } else if ($ext[0] == "intent") {
                        $json_value['intent'] = $image_data;
                    } else if ($ext[0] == "resign") {
                        $json_value['resign'] = $image_data;
                    } else {
                        $json_value['doc'][$index_doc] = $image_data;

                        $index_doc++;
                    }
                }

                $csServiceOrderInfo['jsonValue'] = json_encode($json_value);
               
               
                $resp = $this->CSService->update_log($csServiceOrderInfo);

                if ($resp) {
                    return $this->AppHelper->responseMessageHandle(1, "Opertion omplete");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }

            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getCSServiceOrderRequests(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $client = $this->Client->find_by_token($request_token);
                $resp = $this->CSService->get_order_requests($client->id);

                if ($resp) {
                    $dataList = array();
                    foreach ($resp as $key => $value) {
                        $dataList[$key]['invoiceNo'] = $value['invoice_no'];
                        $dataList[$key]['paymentStatus'] = $value['payment_status'];
                        $dataList[$key]['createTime'] = $value['create_time'];
                        $dataList[$key]['orderStatus'] = $value['order_status'];
                        $dataList[$key]['totalAmount'] = $value['total_amount'];
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

    public function getCompleteCSServiceOrderRequests(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $client = $this->Client->find_by_token($request_token);
                $resp = $this->CSService->get_complete_order_requests($client->id);

                if ($resp) {
                    $dataList = array();
                    foreach ($resp as $key => $value) {
                        $dataList[$key]['invoiceNo'] = $value['invoice_no'];
                        $dataList[$key]['paymentStatus'] = $value['payment_status'];
                        $dataList[$key]['createTime'] = $value['create_time'];
                        $dataList[$key]['orderStatus'] = $value['order_status'];
                        $dataList[$key]['totalAmount'] = $value['total_amount'];
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

    public function getcsordercheck(Request $request) {
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
    
        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {
            try {
                $client = $this->Client->find_by_token($request_token);
    
                if (!$client) {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid token.");
                }
    
                $resp = $this->OrderAssign->get_order_details($request->invoiceNo);
    
            
                    // Assuming $resp is an associative array
                    $dataList = [
                        'invoiceNo' => $resp['invoiceNo'],
                        'amountInArreas' => $resp['amountInArreas'],
                        'totalAmount' => $resp['totalAmount'],
                        'amountInArreas' => $resp['amountInArreas'],
                        'descriptionOfService' => $resp['descriptionOfService'],
                        'dateOfSubmission' => $resp['dateOfSubmission'],
                        'dateOfMailing' => $resp['dateOfMailing'],
                        'dateOfRegistration' => $resp['dateOfRegistration'],
                        'dateOfPicking' => $resp['pickUpDate'],
                        // Add other necessary fields as needed
                    ];
                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
                
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getcsorderDetails(Request $request) {
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
    
        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {
            try {
                $resp = $this->CSService->get_order_details($request->invoiceNo);
    
                if ($resp) {
                    $decoded = json_decode($resp['json_value'], true);
                    if (isset($decoded)) {
                        $companyDetailsArray = [];
                        foreach ($decoded as $key => $value) {
                            $companyDetailsArray[] = [$key => $value];
                        }
                    } else {
                        $companyDetailsArray = [];
                    }

                   


                    $data = [
                        'id' => $resp['id'],
                        'service_index' => $resp['service_index'],
                        'invoice_no' => $resp['invoice_no'],
                        'client' => $resp['client'],
                       
                        'total_amount' => $resp['total_amount'],
                        'payment_type' => $resp['payment_type'],
                        'bank_slip' => $resp['bank_slip'],
                        'payment_status' => $resp['payment_status'],
                        'order_status' => $resp['order_status'],
                        'is_customer_complete' => $resp['is_customer_complete'],
                        'create_time' => $resp['create_time'],
                        'created_at' => $resp['created_at'],
                        'updated_at' => $resp['updated_at'],
                    ];
    
                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $data, $companyDetailsArray);
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "No order details found.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
    
    public function updateOrderStatus(Request $request){
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

                $resp = $this->CSService->update_order_status_client($orderInfo);

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
}
