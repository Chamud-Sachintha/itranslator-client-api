<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Client;
use App\Models\CSService;
use App\Models\LegalAdvice;
use App\Models\NotaryServiceOrder;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ExportInvoiceController extends Controller
{

    private $TranslateOrderInfoModel;
    private $NotaryOrderInfoModel;
    private $CSOrderInfoModel;
    private $LegalServiceOrderInfoModel;
    private $AppHelper;
    private $ClientInfo;

    public function __construct()
    {
        $this->TranslateOrderInfoModel = new Order();
        $this->NotaryOrderInfoModel = new NotaryServiceOrder();
        $this->CSOrderInfoModel = new CSService();
        $this->LegalServiceOrderInfoModel = new LegalAdvice();
        $this->AppHelper = new AppHelper();
        $this->ClientInfo = new Client();
    }

    public function exportInvoiceAsPDF(Request $request) {
        $invoiceNo = (is_null($request->invoiceNo) || empty($request->invoiceNo)) ? "" : $request->invoiceNo;
        $deliveryMethod = (is_null($request->deliveryMethod) || empty($request->deliveryMethod)) ? "" : $request->deliveryMethod;
        $deliveryTimeType = (is_null($request->deliveryTimeType) || empty($request->deliveryTimeType)) ? "" : $request->deliveryTimeType;
        $paymentMethod = (is_null($request->paymentMethod) || empty($request->paymentMethod)) ? "" : $request->paymentMethod;
        $valueObjArray = (is_null($request->valueObjModel) || empty($request->valueObjModel)) ? "" : $request->valueObjModel;

        // client info 

        $fullName = (is_null($request->fullName) || empty($request->fullName)) ? "" : $request->fullName;
        $address = (is_null($request->address) || empty($request->address)) ? "" : $request->address;
        $mobileNumber = (is_null($request->mobileNumber) || empty($request->mobileNumber)) ? "" : $request->mobileNumber;

        if ($invoiceNo == "") {
            return $this->AppHelper->responseMessageHandle(0, "Required Fields are Missig.");
        }   

        $orderTypeExt = explode("-", $invoiceNo);
        $orderInfo = null; $clientInfo = null;

        $deliveryType = null;
        $jsonArray = json_decode(json_encode($valueObjArray));

        if ($deliveryMethod == "2") {
            $deliveryType = "By Hand";
        } else if ($deliveryMethod == "3") {
            $deliveryType = "By Courier";
        } else if ($deliveryMethod == "4") {
            $deliveryType = "By Speed Post";
        } else {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Delivery Type");
        }

        $dataList = [
            "invoiceNo" => $invoiceNo,
            "clientName" => $fullName,
            "address" => $address,
            "mobileNumber" => $mobileNumber,
            "deliveryType" => $deliveryType,
            "documentObjectArray" => $valueObjArray
        ];

        $fileName = "waybill";
        $pdf = Pdf::loadView('pdf-templates.invoice', array('data' => $dataList))->setPaper('a4', 'portrait');

        return $pdf->stream($fileName.'.pdf');
    }
}
