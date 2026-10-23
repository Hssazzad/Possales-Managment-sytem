<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $table = 'sub_categories';

    public $timestamps = false;

    protected $fillable = [
        'main_code',
        'code',
        'name',
        'amount',
    ];

    public function mainCategory()
    {
        return $this->belongsTo(MainCategory::class, 'main_code', 'code');
    }
}
