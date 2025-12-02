<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'order_id',
        'transaction_id',
        'payment_type',
        'gross_amount',
        'transaction_status',
        'snap_token',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'gross_amount' => 'decimal:2'
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class, 'payment_id', 'payment_id');
    }
}
