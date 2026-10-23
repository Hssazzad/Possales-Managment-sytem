<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MainCategory extends Model
{
    protected $table = 'main_categories';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
    ];

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class, 'main_code', 'code');
    }
}
