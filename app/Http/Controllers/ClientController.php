<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    private $User;
    private $AppHelper;

    public function __construct()
    {   
        $this->User = new Client();
        $this->AppHelper = new AppHelper();
    }

    public function getProfileInfo(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $client = $this->User->find_by_token($request_token);

                if ($client) {
                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $client);    
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Token");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
