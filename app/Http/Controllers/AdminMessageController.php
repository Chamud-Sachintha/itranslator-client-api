<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\AdminMessage;
use App\Models\AdminOrderAssign;
use App\Models\AdminUser;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminMessageController extends Controller
{
    private $AppHelper;
    private $AdminMessage;
    private $Order;
    private $Client;
    private $AdminUser;
    private $AdminOrderAssign;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Order = new Order();
        $this->AdminMessage = new AdminMessage();
        $this->Client = new Client();
        $this->AdminUser = new AdminUser();
        $this->AdminOrderAssign = new AdminOrderAssign();
    }

    public function sendMessageToAdmin(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $invoiceNo = (is_null($request->invoiceNo) || empty($request->invoiceNo)) ? "" : $request->invoiceNo;
        $message = (is_null($request->message) || empty($request->message)) ? "" : $request->message;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($invoiceNo == "") {
            return $this->AppHelper->responseMessageHandle(0, "Invoice No is Reuirqed.");
        } else if ($message == "") {
            return $this->AppHelper->responseMessageHandle(0, "Message is required.");
        } else {

            try {
                $order = $this->Order->get_order_by_invoice($invoiceNo);

                if ($order) {
                    $adminOrderAssign = $this->AdminOrderAssign->get_by_invoice_id($order->invoice_no);
                    $sentFrom = $this->Client->find_by_token($request_token);
                    $sentTo = $this->AdminUser->find_by_id($adminOrderAssign->admin_id);

                    $messageInfo = array();
                    $messageInfo['orderId'] = $order->id;
                    $messageInfo['sentFrom'] = $sentFrom->id;
                    $messageInfo['sentTo'] = $sentTo->id;
                    $messageInfo['message'] = $message;
                    $messageInfo['createTime'] = $this->AppHelper->get_date_and_time();

                    $resp = $this->AdminMessage->add_log($messageInfo);

                    if ($resp) {
                        return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                    }
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getAdminMessageList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $flag = (is_null($request->flag) || empty($request->flag)) ? "" : $request->flag;
        $invoiceNo = (is_null($request->invoiceNo) || empty($request->invoiceNo)) ? "" : $request->invoiceNo;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($flag == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($invoiceNo == "") {
            return $this->AppHelper->responseMessageHandle(0, "Invoice No is required.");
        } else {

            try {
                $order = $this->Order->get_order_by_invoice($invoiceNo);

                if ($order) {
                    $messages = $this->AdminMessage->get_messages_by_order_id($order->id);

                    $dataList = array();
                    foreach ($messages as $key => $value) {
                        $sentFrom = $this->AdminUser->find_by_id($value['sent_from']);
                        $sentTo = $this->Client->get_by_id($value['sent_to']);

                        $dataList[$key]['orderId'] = $value['order_id'];
                        $dataList[$key]['sentFrom'] = $sentFrom['first_name'] . " " . $sentFrom['last_name'];
                        $dataList[$key]['message'] = $value['message'];
                        $dataList[$key]['createTime'] = $value['create_time'];
                    }

                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
