<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'token',
        'login_time',
        'flag',
        'create_time'
    ];

    public function find_by_id($id) {
        $map['id'] = $id;

        return $this->where($map)->first();
    }
}
