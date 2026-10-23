<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubMenu extends Model
{
    protected $table = 'submenu';

    protected $fillable = ['menuparentcode', 'submenucode', 'submenuname', 'method', 'icon', 'position', 'status'];

    public function parent()
    {
        return $this->belongsTo(ParentMenu::class, 'menuparentcode', 'parentcode');
    }
}
