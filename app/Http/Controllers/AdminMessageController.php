<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\AdminMessage;
use App\Models\AdminOrderAssign;
use App\Models\AdminUser;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\NotaryServiceOrder;
use Illuminate\Support\Facades\Mail;

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
        $this->NotaryServiceOrder = new NotaryServiceOrder();
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
                $prefix = substr($invoiceNo, 0, 2);
                if ($prefix === "NS") {
                    $order = $this->NotaryServiceOrder->get_order_by_invoice(($invoiceNo));
                } else if ($prefix === "TR") {
                    $order = $this->Order->get_order_by_invoice($invoiceNo);
                }else{

                }
               

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
                $prefix = substr($invoiceNo, 0, 2);
                if ($prefix === "NS") {
                    $order = $this->NotaryServiceOrder->get_order_by_invoice(($invoiceNo));
                } else if ($prefix === "TR") {
                    $order = $this->Order->get_order_by_invoice($invoiceNo);
                }else{

                }
               

                if ($order) {
                    $messageList = $this->AdminMessage->get_messages_by_order_id($order->id);

                    $dataList = array();
                    foreach ($messageList as $key => $value) {

                        $dataList[$key]['toUser'] = $this->findUser($value->sent_to);
                        $dataList[$key]['fromUser'] = $this->findUser($value->sent_from);
                        $dataList[$key]['avatar'] = $this->findAvatar($value->sent_from);
                        $dataList[$key]['message'] = $value->message;
                        $dataList[$key]['time'] = $value->create_time;
                    }

                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    private function findAvatar($uid) {
        $avatar = null;
        $admin = $this->AdminUser->find_by_id($uid);

        if ($admin) {
            $avatar = $this->AppHelper->encodeImage('itlogo.png');
        } else {
            $avatar = $this->AppHelper->encodeImage('chat_avatar_client.jpg');
        }

        return $avatar;
    }

    private function findUser($uid) {

        $userName = null;
        $admin = $this->AdminUser->find_by_id($uid);

        if ($admin) {
            $userName = $admin->first_name . " " . $admin->last_name;
        } else {
            $client = $this->Client->get_by_id($uid);
            $userName = $client->full_name;
        }

        return $userName;
    }

    
}
