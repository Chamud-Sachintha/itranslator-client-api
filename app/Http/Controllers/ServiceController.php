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

    public function getPriceByServiceIdAndDeliveryTime(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $serviceId = (is_null($request->serviceId) || empty($request->serviceId)) ? "" : $request->serviceId;
        $deliveryTimeType = (is_null($request->deliveryTimeType) || empty($request->deliveryTimeType)) ? "" : $request->deliveryTimeType;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($serviceId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Service Id is required.");
        } else if ($deliveryTimeType == "") {
            return $this->AppHelper->responseMessageHandle(0, "Delivery Time Type is required.");
        } else {

            try {
                $serviceInfo = $this->Service->find_by_service_id($serviceId);

                $servicePrice = 0;
                if ($deliveryTimeType == 1) {
                    $servicePrice = $serviceInfo->price_1;
                } else if ($deliveryTimeType == 2) {
                    $servicePrice = $serviceInfo->price_2;
                } else if ($deliveryTimeType == 3) {
                    $servicePrice = $serviceInfo->price_3;
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Delivery Time Type.");
                }

                $data = array();
                $data['servicePrice'] = $servicePrice;

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $data);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
