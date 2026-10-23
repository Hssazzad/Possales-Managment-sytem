<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'text',
        'code',
        'url',
        'icon',
        'controller_name',
        'main_route',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order');
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public static function generateCode(?int $parentId = null): string
    {
        if (! $parentId) {
            for ($number = 10; static::where('code', $number)->exists(); $number += 10) {
            }

            return (string) $number;
        }

        $parent = static::findOrFail($parentId);
        $prefix = $parent->code ?: (string) $parent->id;

        for ($number = 1; static::where('code', $prefix . str_pad((string) $number, 2, '0', STR_PAD_LEFT))->exists(); $number++) {
        }

        return $prefix . str_pad((string) $number, 2, '0', STR_PAD_LEFT);
    }
}
