<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubNotaryServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'main_category_id',
        'sub_category_name',
        'create_time'
    ];

    public function find_by_id($subCategoryCode) {
        $map['id'] = $subCategoryCode;

        return $this->where($map)->first();
    }

    public function find_by_main_code($catInfo) {
        $map['id'] = $catInfo['id'];
        $map['main_category_id'] = $catInfo['mainCatId'];

        return $this->where($map)->first();
    }

    public function get_all_by_main_cate_code($mainCategoryCode) {
        $map['main_category_id'] = $mainCategoryCode;

        return $this->where($map)->get();
    }
}
