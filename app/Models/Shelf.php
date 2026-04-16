<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shelf extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'rack_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function rack()
    {
        return $this->belongsTo(Rack::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
