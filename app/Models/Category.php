<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'brand_id',
        'variations',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'variations' => 'array',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function hasVariation($type)
    {
        return in_array($type, $this->variations ?? []);
    }
}
