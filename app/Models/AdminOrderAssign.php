<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminOrderAssign extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'admin_id',
        'create_time'
    ];

    public function get_by_invoice_id($invoiceId) {
        $map['invoice_no'] = $invoiceId;

        return $this->where($map)->first();
    }
}
