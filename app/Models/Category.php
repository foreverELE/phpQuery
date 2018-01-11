<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;
    //表名
    protected $table = 'categories';

    protected $fillable = [
        'name_jp',
        'name_cn',
        'yh_category_id',
        'yh_parent_category_id',
        'child_category_num',
    ];

}
