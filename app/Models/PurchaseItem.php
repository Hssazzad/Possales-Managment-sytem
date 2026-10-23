<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'variation_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    /**
     * ??? ????????? (Purchase) ????? ?? ??????? ????
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * ?? ??????? ??? ????????????
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * ??? ?????/???? (Variation) ?????
     */
    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class);
    }
}