<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentMenu extends Model
{
    protected $table = 'parentmenu';

    protected $fillable = ['parentcode', 'parentname', 'controllername', 'mainroute', 'icon', 'position', 'status'];

    public function submenus()
    {
        return $this->hasMany(SubMenu::class, 'menuparentcode', 'parentcode')->orderBy('position');
    }

    public function submenuUrl(SubMenu $submenu): string
    {
        $route = trim((string) $this->mainroute, '/');
        $method = trim((string) $submenu->method, '/');

        if ($route === '' || $method === '' || $method === '#' || str_starts_with(strtolower($method), strtolower($route) . '/')) {
            return $method ?: '#';
        }

        return $route . '/' . $method;
    }
}
