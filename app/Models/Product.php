<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'currency_id',
        'tax_cost',
        'manufacturing_cost',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'tax_cost' => 'decimal:2',
        'manufacturing_cost' => 'decimal:2',
    ];

    /**
     * Get the base currency for this product.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    /**
     * Get all prices for this product in different currencies.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class, 'product_id');
    }
}
