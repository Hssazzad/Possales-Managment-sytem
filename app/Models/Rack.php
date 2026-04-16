<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rack extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'location',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function shelves()
    {
        return $this->hasMany(Shelf::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
