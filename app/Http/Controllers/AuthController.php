<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailService;

class AuthController extends Controller
{
    private $AppHelper;
    private $Client;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Client = new Client();
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

            if ($user && Hash::check($password, $user['password'])) {
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

                if (!empty($checkEmail)) {
                    return $this->AppHelper->responseMessageHandle(0, "Email Already Exist.");
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
            
                    Mail::to('hashanmirinda10@gmail.com')->send(new MailService($details));
            
                  
                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $client);
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
