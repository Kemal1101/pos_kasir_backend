<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $table = 'sale_items';
    protected $primaryKey = 'sale_item_id';
    protected $fillable = [
        'sale_id',
        'product_id',
        'name_product',
        'stock',
        'discount_amount',
        'subtotal',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id', 'sale_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
