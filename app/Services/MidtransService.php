<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function createTransaction($params)
    {
        $transaction = [
            'transaction_details' => [
                'order_id' => $params['order_id'],
                'gross_amount' => $params['gross_amount'],
            ],
            'customer_details' => [
                'first_name' => $params['customer_name'],
                'email' => $params['customer_email'],
                'phone' => $params['customer_phone'] ?? '',
            ],
            'item_details' => $params['items'],
        ];

        return Snap::getSnapToken($transaction);
    }

    public function getTransactionStatus($orderId)
    {
        return Transaction::status($orderId);
    }
}
