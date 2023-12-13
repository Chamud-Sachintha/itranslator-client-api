<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainNotaryServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_name',
        'create_time'
    ];

    public function find_by_id($categoryId) {
        $map['id'] = $categoryId;

        return $this->where($map)->first();
    }

    public function find_all() {
        return $this->all();
    }
}
