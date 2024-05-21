<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalAdvice extends Model
{
    use HasFactory;

    protected $fillable = [
        'Client_ID',
        'OrderNo',
        'Message',
        'A_Admin_ID',
        'create_time',
        'UploadFiles',
        'Status'
       
    ];

    public function submit_Details($LegalMessage) {
    
        $map['Client_ID'] = $LegalMessage['Client_Id'];
        $map['OrderNo'] =$LegalMessage['OrderNo'];
        $map['Message'] = $LegalMessage['Message'];
        $map['UploadFiles'] = $LegalMessage['FileName'];
        $map['create_time'] = $LegalMessage['createtime'];
        
       // DD($LegalMessage);
        return $this->create($map);
    }

    public function Get_Details($id){
        $query = $this->where('Client_ID', $id)
        ->where('Status', '=', 1)
        ->get();

            return $query;
    }

    public function Get_DetailsByOrderId($OrderNo){
        $query = $this->where('OrderNo', $OrderNo)->get();

            return $query;
    }

    public function GetClientID($OrderNo){

        $query = $this->where('OrderNo', '=', $OrderNo)->get();

            return $query;

    }
}
