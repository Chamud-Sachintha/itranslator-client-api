<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /*
        'payment_status'                     0- pending 1- paid 2 - gateway pending
        'order_status',                      0- pending 1- taken 2- processing 3- complete
        'bank_slip',                         if not it will blank
        'delivery_time_type',                
        'delivery_method',
        'payment_type',                      1- Bank Deposit 2- online payment
    */

    protected $fillable = [
        'invoice_no',
        'client_id',
        'payment_status', 
        'order_status',
        'bank_slip',
        'delivery_time_type',
        'delivery_method',
        'payment_type',
        'total_amount',
        'create_time',
    ];

    public function add_log($orderDetails) {
        $map['invoice_no'] = $orderDetails['invoiceNo'];
        $map['client_id'] = $orderDetails['clientId'];
        $map['payment_status'] = $orderDetails['paymentStatus'];
        $map['order_status'] = $orderDetails['orderStatus'];

        $map['bank_slip'] = $orderDetails['bankSlip'];

        $map['delivery_time_type'] = $orderDetails['deliveryTimeType'];
        $map['delivery_method'] = $orderDetails['deliveryMethod'];
        $map['payment_type'] = $orderDetails['paymentMethod'];
        $map['total_amount'] = $orderDetails['totalAmount'];
        $map['create_time'] = $orderDetails['createTime'];

        return $this->create($map);
    }

    public function gateway_add_log($orderDetails) {
        $map['invoice_no'] = $orderDetails['invoiceNo'];
        $map['client_id'] = $orderDetails['clientId'];
        $map['payment_status'] = $orderDetails['paymentStatus'];
        $map['order_status'] = $orderDetails['orderStatus'];
        $map['delivery_time_type'] = $orderDetails['deliveryTimeType'];
        $map['delivery_method'] = $orderDetails['deliveryMethod'];
        $map['payment_type'] = $orderDetails['paymentMethod'];
        $map['total_amount'] = $orderDetails['totalAmount'];
        $map['create_time'] = $orderDetails['createTime'];

        return $this->create($map);
    }

    public function get_order_by_uid($uid) {
        $map['client_id'] = $uid;

        return $this->where($map)->first();
    }

    public function get_order_requests($uid) {
        $map['client_id'] = $uid;

        return $this->where($map)->whereNotIn('order_status', [3])->get();
    }

    public function get_order_by_invoice($invoiceNo) {
        $map['invoice_no'] = $invoiceNo;

        return $this->where($map)->first();
    }

    public function update_order_pay($orderPayInfo) {
        $map['id'] = $orderPayInfo['orderId'];
        $map1['payment_status'] = $orderPayInfo['paymentStatus'];

        return $this->where($map)->update($map1);
    }
}
