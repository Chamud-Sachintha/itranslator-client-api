<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Client;
use App\Models\CSService;
use App\Models\LegalAdvice;
use App\Models\LegalAdviceSerivce;
use App\Models\NotaryServiceOrder;
use App\Models\Order;
use App\Models\SMSModel;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GClient;

class SMSModelController extends Controller
{
    private $SMSModel;
    private $Client;
    private $AppHelper;
    private $TranslationOrder;
    private $NotaryServiceOrder;
    private $CSOrder;
    private $LegalAdviceOrder;

    public const API_URL = "http://sender.marxhal.com/api/v2/send.php";
    public const USER_ID = "105547";
    public const API_KEY = "bntz7067pk4iw3fm6";

    public function __construct()
    {
        $this->Client = new Client();
        $this->SMSModel = new SMSModel();
        $this->AppHelper = new AppHelper();
        $this->TranslationOrder = new Order();
        $this->NotaryServiceOrder = new NotaryServiceOrder();
        $this->CSOrder = new CSService();
        $this->LegalAdviceOrder = new LegalAdvice();
    }

    public function sendSmsVerificationCode(Request $request) {
        $mobileNumber = (is_null($request->mobileNumber) || empty($request->mobileNumber)) ? "" : $request->mobileNumber;

        if ($mobileNumber == "") {
            return $this->AppHelper->responseMessageHandle(0, "Mobile Number is required.");
        }

        $clientInfo = $this->Client->get_by_mobile($mobileNumber);

        if ($clientInfo) {
            $authRef = $this->AppHelper->generate_ref(4);
            $content = "Your Verification Code is " . $authRef . ". Put this code into your verification box and submit within 5 minutes";
            $smsResponse = $this->sendCode($clientInfo->email, $mobileNumber, $content);

            if ($smsResponse["status"] == "success" && $smsResponse['result'] == "sent") {

                $smsData['clientId'] = $clientInfo->id;
                $smsData['messageId'] = $smsResponse['msg_id'];
                $smsData['verifyCode'] = $authRef;
                $smsData['content'] = $content;
                $smsData['createTime'] = $this->AppHelper->day_time();

                $smsLog = $this->SMSModel->add_log($smsData);

                if ($smsLog) {
                    return $this->AppHelper->responseMessageHandle(1, "Message Sent Successfully.");
                } else {
                    $this->Client->delete_by_id($clientInfo->id);
                    return $this->AppHelper->responseMessageHandle(0, "Error Occued.");
                }
            } else {
                $this->Client->delete_by_id($clientInfo->id);
                return $this->AppHelper->responseMessageHandle(0, "Error Occued." . $smsResponse['status']);
            }
        }
    }

    public function sendOrderPlaceSMSNotification(Request $request) {

        $orderNumber = (is_null($request->orderNumber) || empty($request->orderNumber)) ? "" : $request->orderNumber;
        $orderType = (is_null($request->orderType) || empty($request->orderType)) ? "" : $request->orderType;

        $content = ""; $orderInfo = ""; $clientInfo = "";

        if ($orderNumber == "" || $orderType == "") {
            return $this->AppHelper->responseMessageHandle(0, "Order Number & Order Type is Required.");
        } 

        if ($orderType == "TR") {
            $content = "Thank you for your order! Your order " . $orderNumber . " has been placed successfully. We\'ll notify you once it\'s completed. Service - Translation Service";
            $orderInfo = $this->TranslationOrder->get_order_by_invoice($orderNumber);

            if ($orderInfo) {
                $clientInfo = $this->Client->get_by_id($orderInfo->client_id);
            }
        } else if ($orderType == "NS") {
            $content = "Thank you for your order! Your order " . $orderNumber . " has been placed successfully. We\'ll notify you once it\'s completed."
                        . " Service - Notary Service";
            $orderInfo = $this->NotaryServiceOrder->get_order_by_invoice($orderNumber);

            if ($orderInfo) {
                $clientInfo = $this->Client->get_by_id($orderInfo->client_id);
            }
        } else if ($orderType == "CS") {
            $content = "Thank you for your order! Your order " . $orderNumber . " has been placed successfully. We\'ll notify you once it\'s completed."
                        . " Track your order here: https://www.dashboard.itranslator.lk/#/app/cs-order-requests"
                        . " Service - Company Sectrial Service";
            $orderInfo = $this->CSOrder->get_order_details($orderNumber);

            if ($orderInfo) {
                $clientInfo = $this->Client->get_by_id($orderInfo->client);
            }
        } else if ($orderType == "LG") {
            $content = "Thank you for your order! Your order " . $orderNumber . " has been placed successfully. We\'ll notify you once it\'s completed."
                        . " Track your order here: https://www.dashboard.itranslator.lk/#/app/legal-advice-requests"
                        . " Service - Legal Advice Service";
            $orderInfo = $this->LegalAdviceOrder->Get_DetailsByOrderId($orderNumber);

            if ($orderInfo) {
                $clientInfo = $this->Client->get_by_id($orderInfo->Client_ID);
            }
        } else {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Order Type");
        }

        if ($clientInfo) {
            $smsResponse = $this->sendCode("", $clientInfo->mobile_number, $content);

            if ($smsResponse["status"] == "success" && $smsResponse['result'] == "sent") {

                $smsData['clientId'] = $clientInfo->id;
                $smsData['messageId'] = $smsResponse['msg_id'];
                $smsData['verifyCode'] = "N/A";
                $smsData['content'] = $content;
                $smsData['createTime'] = $this->AppHelper->day_time();

                $smsLog = $this->SMSModel->add_log($smsData);

                if ($smsLog) {
                    return $this->AppHelper->responseMessageHandle(1, "Message Sent Successfully.");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occued.");
                }
            } else {
                return $this->AppHelper->responseMessageHandle(0, "Error Occued." . $smsResponse['status']);
            }
        } else {
            /*
                if order number didnt set any service it does not return correct client info thats why returning invalid 
                order number in here.
            */
            return $this->AppHelper->responseMessageHandle(0, "Invalid Order ID");
        }

    }

    public function sendForgotPasswordVerificationCode(Request $request) {
        $mobileNumber = (is_null($request->mobileNumber) || empty($request->mobileNumber)) ? "" : $request->mobileNumber;
        
        if ($mobileNumber == "") {
            return $this->AppHelper->responseMessageHandle(0, "Mobile Number is required.");
        }

        $clientInfo = $this->Client->get_by_mobile($mobileNumber);

        if ($clientInfo) {
            $authRef = $this->AppHelper->generate_ref(4);
            $content = "Your Verification Code is " . $authRef . ". Put this code into your verification box and submit within 5 minutes";
            $smsResponse = $this->sendCode($clientInfo->email, $mobileNumber, $content);

            if ($smsResponse["status"] == "success" && $smsResponse['result'] == "sent") {

                $smsData['clientId'] = $clientInfo->id;
                $smsData['messageId'] = $smsResponse['msg_id'];
                $smsData['verifyCode'] = $authRef;
                $smsData['content'] = $content;
                $smsData['createTime'] = $this->AppHelper->day_time();

                $smsLog = $this->SMSModel->add_log($smsData);

                if ($smsLog) {
                    return $this->AppHelper->responseMessageHandle(1, "Message Sent Successfully.");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occued.");
                }
            } else {
                return $this->AppHelper->responseMessageHandle(0, "Error Occued." . $smsResponse['status']);
            }
        } else {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Phone Number.");
        }
    }

    public function verifyFrgotVerificationCode(Request $request) {
        $verify_code = (is_null($request->verifyCode) || empty($request->verifyCode)) ? "" : $request->verifyCode;

        if ($verify_code == "") {
            return $this->AppHelper->responseMessageHandle(0, "Enter Verify Code");
        }

        $smsInfo = $this->SMSModel->get_by_code($verify_code);

        if ($smsInfo && ($smsInfo->code == $verify_code)) {
            return $this->AppHelper->responseMessageHandle(1, "Code Matched.");
        } else {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Verify Code");
        }
    }

    public function verifyRegisterCode(Request $request) {
        $verify_code = (is_null($request->verifyCode) || empty($request->verifyCode)) ? "" : $request->verifyCode;

        if ($verify_code == "") {
            return $this->AppHelper->responseMessageHandle(0, "Enter Verify Code");
        }

        $smsInfo = $this->SMSModel->get_by_code($verify_code);

        if ($smsInfo && ($smsInfo->code == $verify_code)) {
            $this->Client->verify_account($smsInfo->client_id);
            return $this->AppHelper->responseMessageHandle(1, "Account Verified Successfully.");
        } else {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Verify Code");
        }
    }

    private function sendCode($clientName, $mobile, $content) {
        // Create a new Guzzle client
        $client = new GClient();

        $data = [
            'user_id' => self::USER_ID,
            'api_key' => self::API_KEY,
            'sender_id' => "My Demo sms",
            'to' => $mobile,
            'message' => $content
            // Add other key-value pairs as needed
        ];

        try {
            // Make the POST request
            $response = $client->post(self::API_URL, [
                'form_params' => $data,
            ]);

            // Get the response body
            $responseBody = $response->getBody()->getContents();

            // Decode JSON response to array if needed
            $responseArray = json_decode($responseBody, true);

            // Return the response or do something with it
            return $responseArray;

        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
