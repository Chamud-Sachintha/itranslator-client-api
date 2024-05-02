<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CSService extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_index',
        'invoice_no',
        'client',
        'json_value',
        'total_amount',
        'payment_type',
        'bank_slip',
        'payment_status',
        'order_status',
        'is_customer_completed',
        'create_time'
    ];

    public function add_log($info) {
        $map['service_index'] = $info['serviceIndex'];
        $map['invoice_no'] = $info['invoiceNo'];
        $map['client'] = $info['clientId'];
        $map['json_value'] = $info['jsonValue'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }

    public function get_order_requests($clientId) {
        $query = $this->where('client', $clientId)
        ->where('is_customer_complete', '!=', 1)
        ->get();

            return $query;
    }

    public function get_complete_order_requests($clientId) {
       
        $query = $this->where('client', $clientId)
        ->where('is_customer_complete', '=', 1)
        ->get();

            return $query;
    }
}
