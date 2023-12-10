<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'service_id',
        'json_value',
        'create_time',
        'modified_time'
    ];

    public function add_log($orderItemsDetails) {
        $map['order_id'] = $orderItemsDetails['orderId'];
        $map['service_id'] = $orderItemsDetails['serviceId'];
        $map['json_value'] = $orderItemsDetails['jsonValue'];
        $map['create_time'] = $orderItemsDetails['createTime'];
        $map['modified_time'] = $orderItemsDetails['modifiedTime'];

        return $this->create($map);
    }
}
