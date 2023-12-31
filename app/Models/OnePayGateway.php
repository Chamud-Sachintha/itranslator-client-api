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
        'create_time'
    ];

    public function add_log($paymentInfo) {
        $map['client_id'] = $paymentInfo['clientId'];
        $map['order_id'] = $paymentInfo['orderId'];
        $map['reference'] = $paymentInfo['reference'];
        $map['amount'] = $paymentInfo['amount'];
        $map['create_time'] = $paymentInfo['createTime'];

        return $this->create($map);
    }
}
