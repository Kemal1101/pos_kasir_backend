<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $primaryKey = 'product_id';
    protected $fillable = [
        'categories_id',
        'name',
        'description',
        'cost_price',
        'selling_price',
        'product_images',
        'stock',
        'barcode',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'categories_id', 'categories_id');
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'product_id', 'product_id');
    }
}
