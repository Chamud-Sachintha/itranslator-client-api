<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnePayGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'order_id',
        'reference',
        'amount',
        'status', // 0 - pending 1- paid
        'create_time'
    ];

    public function add_log($paymentInfo) {
        $map['client_id'] = $paymentInfo['clientId'];
        $map['order_id'] = $paymentInfo['orderId'];
        $map['reference'] = $paymentInfo['reference'];
        $map['amount'] = $paymentInfo['amount'];
        $map['status'] = $paymentInfo['status'];
        $map['create_time'] = $paymentInfo['createTime'];

        return $this->create($map);
    }

    public function get_order_by_ref($ref) {
        $map['reference'] = $ref;

        return $this->where($map)->first();
    }

    public function update_payment_log($paymentInfo) {
        $map['reference'] = $paymentInfo['reference'];
        $map1['status'] = 1;

        return $this->where($map)->update($map1);
    }
}
