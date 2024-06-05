<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Client;
use App\Models\SMSModel;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GClient;

class SMSModelController extends Controller
{
    private $SMSModel;
    private $Client;
    private $AppHelper;

    public const API_URL = "http://sender.marxhal.com/api/v2/send.php";
    public const USER_ID = "105547";
    public const API_KEY = "bntz7067pk4iw3fm6";

    public function __construct()
    {
        $this->Client = new Client();
        $this->SMSModel = new SMSModel();
        $this->AppHelper = new AppHelper();
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
            $this->Client->verify_account();
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
