<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $table = 'sales';
    protected $primaryKey = 'sale_id';
    protected $fillable = [
        'user_id',
        'payment_id',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'payment_status',
        'sale_date',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'payment_id');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class, 'sale_id', 'sale_id');
    }
}
