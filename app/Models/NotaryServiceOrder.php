<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaryServiceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'invoice_no',
        'main_category',
        'sub_category',
        'description_of_service',
        'doc_1',
        'doc_2',
        'doc_3',
        'date_of_signing',
        'start_date',
        'end_date',
        'value',
        'monthly_rent',
        'advance_amount',
        'village_officer_number',
        'devisional_sec',
        'local_gov',
        'district',
        'land_reg_office',
        'notary_person_json',
        'total_amt',
        'payment_status',
        'order_status',
        'is_customer_complete',
        'payment_type',
        'bank_slip',
        'create_time',
        'modified_time'
    ];

    public function add_log($notaryOrderInfo) {
        $map['client_id'] = $notaryOrderInfo['clientId'];
        $map['invoice_no'] = $notaryOrderInfo['invoiceNo'];
        $map['main_category'] = $notaryOrderInfo['mainCategory'];
        $map['sub_category'] = $notaryOrderInfo['subCategory'];
        $map['description_of_service'] = $notaryOrderInfo['descriptionOfService'];
        $map['doc_1'] = $notaryOrderInfo['firstDoc'];
        $map['doc_2'] = $notaryOrderInfo['secondDoc'];
        $map['doc_3'] = $notaryOrderInfo['thirdDoc'];
        $map['date_of_signing'] = $notaryOrderInfo['dateOfSigning'];
        $map['start_date'] = $notaryOrderInfo['startDate'];
        $map['end_date'] = $notaryOrderInfo['endDate'];
        $map['value'] = $notaryOrderInfo['value'];
        $map['monthly_rent'] = $notaryOrderInfo['monthlyRent'];
        $map['advance_amount'] = $notaryOrderInfo['advanceAmount'];
        $map['village_officer_number'] = $notaryOrderInfo['von'];
        $map['devisional_sec'] = $notaryOrderInfo['divisionalSec'];
        $map['local_gov'] = $notaryOrderInfo['localGov'];
        $map['district'] = $notaryOrderInfo['district'];
        $map['land_reg_office'] = $notaryOrderInfo['lro'];
        $map['notary_person_json'] = $notaryOrderInfo['notaryPersonJson'];
        $map['payment_status'] = $notaryOrderInfo['paymentStatus'];
        $map['order_status'] = $notaryOrderInfo['orderStatus'];
        $map['create_time'] = $notaryOrderInfo['createTime'];
        $map['modified_time'] = $notaryOrderInfo['modifiedTime'];

        return $this->create($map);
    }

    public function get_order_requests($clientId) {
        $map['client_id'] = $clientId;

        return $this->where($map)->get();
    }

    public function submit_bank_slip($bankSlipInfo) {
        $map['invoice_no'] = $bankSlipInfo['invoiceNo'];
        $map1['bank_slip'] = $bankSlipInfo['bankSlip'];

        return $this->where($map)->update($map1);
    }

    public function get_order_by_invoice($invoiceNo) {
        $map['invoice_no'] = $invoiceNo;

        return $this->where($map)->first();
    }

    public function update_order_status_client($orderInfo) {
        $map['invoice_no'] = $orderInfo['invoiceNo'];
        $map1['is_customer_complete'] = $orderInfo['orderStatus'];

        return $this->where($map)->update($map1);
    }
}
