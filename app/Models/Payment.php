<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';
    protected $primaryKey = 'payment_id';
    protected $fillable = ['payment_method'];

    public function sales()
    {
        return $this->hasMany(Sale::class, 'payment_id', 'payment_id');
    }
}
