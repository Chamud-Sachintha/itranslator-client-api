<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\CSService;
use App\Models\LegalAdvice;
use App\Models\NotaryServiceOrder;
use App\Models\Order;
use Illuminate\Http\Request;

class ExportInvoiceController extends Controller
{

    private $TranslateOrderInfoModel;
    private $NotaryOrderInfoModel;
    private $CSOrderInfoModel;
    private $LegalServiceOrderInfoModel;
    private $AppHelper;

    public function __construct()
    {
        $this->TranslateOrderInfoModel = new Order();
        $this->NotaryOrderInfoModel = new NotaryServiceOrder();
        $this->CSOrderInfoModel = new CSService();
        $this->LegalServiceOrderInfoModel = new LegalAdvice();
        $this->AppHelper = new AppHelper();
    }

    public function exportInvoiceAsPDF(Request $request) {
        $invoiceNo = (is_null($request->invoiceNo) || empty($request->invoiceNo)) ? "" : $request->invoiceNo;
        $clientName = (is_null($request->clientName) || empty($request->clientName)) ? "" : $request->clientName;
        $address = (is_null($request->address) || empty($request->address)) ? "" : $request->address;
        $mobileNumber = (is_null($request->mobileNumber) || empty($request->mobileNumber)) ? "" : $request->mobileNumber;
        $deliveryMethod = (is_null($request->deliveryMethod) || empty($request->deliveryMethod)) ? "" : $request->deliveryMethod;
        $valueObjArray = (is_null($request->valueObjModel) || empty($request->valueObjModel)) ? "" : $request->valueObjModel;

        if ($invoiceNo == "" || $clientName == "" || $address == "" || $mobileNumber == "") {
            return $this->AppHelper->responseMessageHandle(0, "Required Fields are Missig.");
        }   

        $orderTypeExt = explode("-", $invoiceNo);
        $orderInfo = null;

        if ($orderTypeExt == "TR") {
            $orderInfo = $this->TranslateOrderInfoModel->get_order_by_invoice($invoiceNo);
        } else if ($orderTypeExt == "NS") {
            $orderInfo = $this->NotaryOrderInfoModel->get_order_by_invoice($invoiceNo);
        } else if ($orderTypeExt == "CS") {
            $orderInfo = $this->CSOrderInfoModel->get_order_details($invoiceNo);
        } else if ($orderTypeExt == "LG") {
            $orderInfo = $this->LegalServiceOrderInfoModel->Get_DetailsByOrderId($invoiceNo);
        } else {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Order Type");
        }

        
    }
}
