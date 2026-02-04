<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'symbol',
        'exchange_rate',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:4',
    ];

    /**
     * Get the products that use this currency as base currency.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'currency_id');
    }

    /**
     * Get the product prices in this currency.
     */
    public function productPrices(): HasMany
    {
        return $this->hasMany(ProductPrice::class, 'currency_id');
    }
}
