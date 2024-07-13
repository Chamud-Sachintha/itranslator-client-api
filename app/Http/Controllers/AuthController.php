<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Client;
use App\Models\SMSModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailService;

class AuthController extends Controller
{
    private $AppHelper;
    private $Client;
    private $SMSModel;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Client = new Client();
        $this->SMSModel = new SMSModel();
    }

    public function authenticateUser(Request $request) {

        $emailAddress = (is_null($request->username) || empty($request->username)) ? "" : $request->username;
        $password = (is_null($request->password) || empty($request->password)) ? "" : $request->password;

        if ($emailAddress == "") {
            return $this->AppHelper->responseMessageHandle(0, "Email Address is Required.");
        } else if ($password == "") {
            return $this->AppHelper->responseMessageHandle(0, "Password is Required.");
        } else {
            $user = $this->Client->verify_email($emailAddress);

            if ($user == null) {
                $user = $this->Client->get_by_mobile($emailAddress);
            }

            if ($user && Hash::check($password, $user['password'])) {

                if ($user['sms_auth'] == 0) {
                    return $this->AppHelper->responseMessageHandle(0, "Please Verify Your Account.");
                }

                $loginInfo['id'] = $user['id'];
                $loginInfo['fullName'] = $user['full_name'];
                $loginInfo['email'] = $user['email'];

                $token = $this->AppHelper->generateAuthToken($user);

                $loginInfo['userRole'] = $user['flag'];

                $tokenInfo = array();
                $tokenInfo['token'] = $token;
                $tokenInfo['loginTime'] = $this->AppHelper->day_time();
                $token_time = $this->Client->update_login_token($user['id'], $tokenInfo);

                return $this->AppHelper->responseEntityHandle(1, "Operation Successfully.", $loginInfo, $token);
            } else {
                return $this->AppHelper->responseMessageHandle(0, "Invalid Credentials");
            }
        }
        
    }

    public function signUpNewUser(Request $request) {

        $fullname = (is_null($request->fullName) || empty($request->fullName)) ? "" : $request->fullName;
        $emailAddress = (is_null($request->emailAddress) || empty($request->emailAddress)) ? "" : $request->emailAddress;
        $nicNumber = (is_null($request->nicNumber) || empty($request->nicNumber)) ? "" : $request->nicNumber;
        $address = (is_null($request->address) || empty($request->address)) ? "" : $request->address;
        $mobileNumber = (is_null($request->mobileNumber) || empty($request->mobileNumber)) ? "" : $request->mobileNumber;
        $birthDate = (is_null($request->birthDate) || empty($request->birthDate)) ? "" : $request->birthDate;
        $password = (is_null($request->password) || empty($request->password)) ? "" : $request->password;

        if ($fullname == "") {
            return $this->AppHelper->responseMessageHandle(0, "Full Name is required.");
        } else if ($emailAddress == "") {
            return $this->AppHelper->responseMessageHandle(0, "Email Address is required.");
        } else if ($nicNumber == "") {
            return $this->AppHelper->responseMessageHandle(0, "NIC Number is required.");
        } else if ($address == "") {
            return $this->AppHelper->responseMessageHandle(0, "Address is required.");
        } else if ($mobileNumber == "") {
            return $this->AppHelper->responseMessageHandle(0, "Mobile Number is required.");
        } else if ($birthDate == "") {
            return $this->AppHelper->responseMessageHandle(0, "Birth Date is required.");
        } else if ($password == "") {
            return $this->AppHelper->responseMessageHandle(0, "Password is required.");
        } else {

            try {

                $checkEmail = $this->Client->verify_email($emailAddress);
                $checkMobile = $this->Client->get_by_mobile($mobileNumber);

                if (!empty($checkEmail)) {
                    return $this->AppHelper->responseMessageHandle(0, "Email Already Exist.");
                }

                if (!empty($checkMobile)) {
                    return $this->AppHelper->responseMessageHandle(0, "Mobile Already Exist.");
                }

                $clientInfo = array();
                $clientInfo['fullName'] = $fullname;
                $clientInfo['emailAddress'] = $emailAddress;
                $clientInfo['nicNumber'] = $nicNumber;
                $clientInfo['address'] = $address;
                $clientInfo['mobileNumber'] = $mobileNumber;
                $clientInfo['birthDate'] = strtotime($birthDate);
                $clientInfo['password'] = Hash::make($password);
                $clientInfo['createTime'] = $this->AppHelper->get_date_and_time();

                $client = $this->Client->add_log($clientInfo);
                   // dd( $client);
                if ($client) {
                    $details = [
                        'full_name' => $client->full_name ,
                        'created_at' => $client->created_at ,
                        'bodyType' => '1' 
                        
                    ];
            
                    Mail::to($emailAddress)->send(new MailService($details));
            
                  
                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $client);
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function changeClientPassword(Request $request) {

        $authCode = (is_null($request->authCode) || empty($request->authCode)) ? "" : $request->authCode;
        $newPassword = (is_null($request->newPassword) || empty($request->newPassword)) ? "" : $request->newPassword;

        if ($authCode == "" || $newPassword == "") {
            return $this->AppHelper->responseMessageHandle(0, "All Fields are Required.");
        } else {
            $smsLogInfo = $this->SMSModel->get_by_code($authCode);

            if ($smsLogInfo) {
                $newPasswordInfo['newPassword'] = Hash::make($newPassword);
                $newPasswordInfo['clientId'] = $smsLogInfo->client_id;

                $resp = $this->Client->update_password($newPasswordInfo);

                if ($resp) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Successfully.");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } else {
                return $this->AppHelper->responseMessageHandle(0, "Invalid Client Id");
            }
        }
    }

    public function getprofiledetails(Request $request){
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Full Name is required.");
        }
        else{
            $client = $this->Client->find_by_token($request->token);
           
            if ($client) {
                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $client);
            } else {
                return $this->AppHelper->responseMessageHandle(0, "Error Occured");
            }
        }

    }

    public function updateprofile(Request $request){
        $request_token = (is_null($request->token) || empty($request->token))? "" : $request->token;
        $fullname = (is_null($request->UserName) || empty($request->UserName)) ? "" : $request->UserName;
        $emailAddress = (is_null($request->Email) || empty($request->Email)) ? "" : $request->Email;
        $nicNumber = (is_null($request->NIC) || empty($request->NIC)) ? "" : $request->NIC;
        $address = (is_null($request->Address) || empty($request->Address)) ? "" : $request->Address;
        $mobileNumber = (is_null($request->Phone) || empty($request->Phone)) ? "" : $request->Phone;
        $birthDate = (is_null($request->Birthday) || empty($request->Birthday)) ? "" : $request->Birthday;
       
        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Full Name is required.");
        }
        else{

            $client = $this->Client->find_by_token($request->token);
                
            $clientInfo = array();
          
            $clientInfo['fullName'] = $fullname;
            $clientInfo['emailAddress'] = $emailAddress;
            $clientInfo['nicNumber'] = $nicNumber;
            $clientInfo['address'] = $address;
            $clientInfo['mobileNumber'] = $mobileNumber;
            $clientInfo['birthDate'] = strtotime($birthDate);
           // $clientInfo['updated_at'] = $this->AppHelper->get_date_and_time();
            
            $client = $this->Client->update_log( $client->id, $clientInfo);
            if ($client) {
                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $client );
                } 
                else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured");
                    }
        }
    }

    public function resetPasswordprofile(Request $request){
        $request_token = (is_null($request->token) || empty($request->token))? "" : $request->token;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Full Name is required.");
        }
        else{

            $changedpass = Hash::make($request->newpass);
            $client = $this->Client->find_by_token($request->token);
            $newPasswordInfo['newPassword'] = $changedpass ;
            $newPasswordInfo['clientId'] = $client->id;
            $client = $this->Client->update_password( $newPasswordInfo);

        }
    }
}
