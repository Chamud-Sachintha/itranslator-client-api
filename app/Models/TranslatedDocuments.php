<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TranslatedDocuments extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'document',
        'create_time'
    ];

    public function get_doc_list_by_order_id($orderId) {
        $map['order_id'] = $orderId;

        return $this->where($map)->get();
    }
}
