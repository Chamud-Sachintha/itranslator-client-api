<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{

    private $AppHelper;
    private $Service;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Service = new Service();
    }

    public function getTranslateServiceList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else {

            try {
                $allServiceList = $this->Service->query_all();

                $serviceList = array();
                foreach ($allServiceList as $key => $value) {
                    $serviceList[$key]['serviceId'] = $value['id'];
                    $serviceList[$key]['serviceName'] = $value['service_name'];
                    $serviceList[$key]['firstPrice'] = $value['price_1'];
                    $serviceList[$key]['secondPrice'] = $value['price_2'];
                    $serviceList[$key]['thirdPrice'] = $value['price_3'];
                    $serviceList[$key]['description'] = $value['description'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $serviceList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
