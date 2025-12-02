<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    public function createPayment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'subtotal' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $orderId = 'ORDER-' . time() . '-' . Str::random(6);
            $subtotal = $request->subtotal;
            $discount = $request->discount_amount ?? 0;
            $tax = $request->tax_amount ?? 0;
            $totalAmount = $subtotal - $discount + $tax;

            // Create Payment record
            $payment = Payment::create([
                'order_id' => $orderId,
                'gross_amount' => $totalAmount,
                'transaction_status' => 'pending',
                'metadata' => $request->items
            ]);

            // Create Sale record
            $sale = Sale::create([
                'user_id' => $request->user_id,
                'payment_id' => $payment->payment_id,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'total_amount' => $totalAmount,
                'payment_status' => 'unpaid',
                'sale_date' => now()
            ]);

            // Get user details
            $user = $sale->user;

            // Create Midtrans transaction
            $snapToken = $this->midtransService->createTransaction([
                'order_id' => $orderId,
                'gross_amount' => $totalAmount,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->phone ?? '',
                'items' => $request->items
            ]);

            // Update payment with snap token
            $payment->update(['snap_token' => $snapToken]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'sale_id' => $sale->sale_id,
                    'order_id' => $orderId,
                    'snap_token' => $snapToken,
                    'total_amount' => $totalAmount
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function handleNotification(Request $request)
    {
        try {
            $notification = $request->all();
            $orderId = $notification['order_id'];
            $transactionStatus = $notification['transaction_status'];
            $fraudStatus = $notification['fraud_status'] ?? 'accept';

            $payment = Payment::where('order_id', $orderId)->firstOrFail();

            // Update payment
            $payment->update([
                'transaction_id' => $notification['transaction_id'] ?? null,
                'payment_type' => $notification['payment_type'] ?? null,
                'transaction_status' => $transactionStatus
            ]);

            // Update sale payment status
            $paymentStatus = 'unpaid';

            if ($transactionStatus == 'capture') {
                $paymentStatus = ($fraudStatus == 'accept') ? 'paid' : 'pending';
            } elseif ($transactionStatus == 'settlement') {
                $paymentStatus = 'paid';
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                $paymentStatus = 'failed';
            } elseif ($transactionStatus == 'pending') {
                $paymentStatus = 'pending';
            }

            Sale::where('payment_id', $payment->payment_id)
                ->update(['payment_status' => $paymentStatus]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function checkStatus($orderId)
    {
        try {
            $payment = Payment::where('order_id', $orderId)->firstOrFail();
            $status = (object) $this->midtransService->getTransactionStatus($orderId);

            $sale = $payment->sales()->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $orderId,
                    'transaction_status' => $status->transaction_status ?? 'unknown',
                    'payment_type' => $status->payment_type ?? null,
                    'payment_status' => $sale?->payment_status ?? 'unpaid'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
