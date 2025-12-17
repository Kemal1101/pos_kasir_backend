<?php

namespace Tests\Unit;

use App\Models\Payment;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'order_id',
            'transaction_id',
            'payment_type',
            'gross_amount',
            'transaction_status',
            'snap_token',
            'metadata'
        ];

        $payment = new Payment();

        $this->assertEquals($fillable, $payment->getFillable());
    }

    /** @test */
    public function it_uses_correct_primary_key()
    {
        $payment = new Payment();

        $this->assertEquals('payment_id', $payment->getKeyName());
    }

    /** @test */
    public function it_casts_metadata_to_array()
    {
        $payment = new Payment();

        $this->assertArrayHasKey('metadata', $payment->getCasts());
        $this->assertEquals('array', $payment->getCasts()['metadata']);
    }

    /** @test */
    public function it_casts_gross_amount_to_decimal()
    {
        $payment = new Payment();

        $this->assertArrayHasKey('gross_amount', $payment->getCasts());
        $this->assertEquals('decimal:2', $payment->getCasts()['gross_amount']);
    }

    /** @test */
    public function it_can_create_payment_with_all_fields()
    {
        $payment = Payment::create([
            'order_id' => 'ORD-12345',
            'transaction_id' => 'TRX-67890',
            'payment_type' => 'credit_card',
            'gross_amount' => 500000.50,
            'transaction_status' => 'pending',
            'snap_token' => 'snap-token-abc123',
            'metadata' => ['bank' => 'BCA', 'card_type' => 'visa'],
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => 'ORD-12345',
            'transaction_id' => 'TRX-67890',
            'payment_type' => 'credit_card',
            'transaction_status' => 'pending',
        ]);

        $this->assertEquals(500000.50, $payment->gross_amount);
        $this->assertIsArray($payment->metadata);
        $this->assertEquals('BCA', $payment->metadata['bank']);
    }

    /** @test */
    public function it_can_create_payment_with_minimal_data()
    {
        $payment = Payment::create([
            'order_id' => 'ORD-001',
            'gross_amount' => 100000,
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => 'ORD-001',
        ]);

        $this->assertNull($payment->transaction_id);
        $this->assertNull($payment->payment_type);
        $this->assertNull($payment->snap_token);
    }

    /** @test */
    public function it_handles_null_metadata()
    {
        $payment = Payment::create([
            'order_id' => 'ORD-002',
            'gross_amount' => 50000,
            'metadata' => null,
        ]);

        $this->assertNull($payment->metadata);
    }

    /** @test */
    public function it_handles_empty_array_metadata()
    {
        $payment = Payment::create([
            'order_id' => 'ORD-003',
            'gross_amount' => 50000,
            'metadata' => [],
        ]);

        $this->assertIsArray($payment->metadata);
        $this->assertEmpty($payment->metadata);
    }

    /** @test */
    public function it_handles_complex_metadata()
    {
        $complexMetadata = [
            'customer' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ],
            'items' => [
                ['name' => 'Product 1', 'qty' => 2],
                ['name' => 'Product 2', 'qty' => 1],
            ],
            'shipping' => [
                'address' => 'Jakarta',
                'method' => 'JNE',
            ],
        ];

        $payment = Payment::create([
            'order_id' => 'ORD-004',
            'gross_amount' => 500000,
            'metadata' => $complexMetadata,
        ]);

        $this->assertEquals($complexMetadata, $payment->metadata);
        $this->assertEquals('John Doe', $payment->metadata['customer']['name']);
        $this->assertCount(2, $payment->metadata['items']);
    }

    /** @test */
    public function it_can_update_payment()
    {
        $payment = Payment::create([
            'order_id' => 'ORD-005',
            'gross_amount' => 100000,
            'transaction_status' => 'pending',
        ]);

        $payment->update([
            'transaction_status' => 'success',
            'transaction_id' => 'TRX-999',
        ]);

        $this->assertDatabaseHas('payments', [
            'payment_id' => $payment->payment_id,
            'transaction_status' => 'success',
            'transaction_id' => 'TRX-999',
        ]);
    }

    /** @test */
    public function it_has_many_sales()
    {
        $payment = Payment::create([
            'order_id' => 'ORD-006',
            'gross_amount' => 100000,
        ]);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $payment->sales()
        );
    }

    /** @test */
    public function it_can_retrieve_associated_sales()
    {
        $role = Role::create(['name' => 'Admin']);
        $user = User::create([
            'role_id' => $role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $payment = Payment::create([
            'order_id' => 'ORD-007',
            'gross_amount' => 500000,
        ]);

        $sale1 = Sale::create([
            'user_id' => $user->user_id,
            'payment_id' => $payment->payment_id,
            'subtotal' => 500000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 500000,
            'payment_status' => 'paid',
            'sale_date' => now(),
        ]);

        $sale2 = Sale::create([
            'user_id' => $user->user_id,
            'payment_id' => $payment->payment_id,
            'subtotal' => 300000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 300000,
            'payment_status' => 'paid',
            'sale_date' => now(),
        ]);

        $payment->refresh();

        $this->assertCount(2, $payment->sales);
        $this->assertTrue($payment->sales->contains($sale1));
        $this->assertTrue($payment->sales->contains($sale2));
    }

    /** @test */
    public function it_handles_zero_gross_amount()
    {
        $payment = Payment::create([
            'order_id' => 'ORD-008',
            'gross_amount' => 0,
        ]);

        $this->assertEquals(0, $payment->gross_amount);
    }

    /** @test */
    public function it_handles_large_gross_amount()
    {
        $payment = Payment::create([
            'order_id' => 'ORD-009',
            'gross_amount' => 99999999.99,
        ]);

        $this->assertEquals(99999999.99, $payment->gross_amount);
    }

    /** @test */
    public function it_handles_different_payment_types()
    {
        $types = ['credit_card', 'bank_transfer', 'ewallet', 'qris', 'cash'];

        foreach ($types as $index => $type) {
            $payment = Payment::create([
                'order_id' => 'ORD-' . ($index + 10),
                'gross_amount' => 100000,
                'payment_type' => $type,
            ]);

            $this->assertEquals($type, $payment->payment_type);
        }
    }

    /** @test */
    public function it_handles_different_transaction_statuses()
    {
        $statuses = ['pending', 'success', 'failed', 'expired', 'cancel'];

        foreach ($statuses as $index => $status) {
            $payment = Payment::create([
                'order_id' => 'ORD-' . ($index + 20),
                'gross_amount' => 100000,
                'transaction_status' => $status,
            ]);

            $this->assertEquals($status, $payment->transaction_status);
        }
    }

    /** @test */
    public function it_has_timestamps()
    {
        $payment = Payment::create([
            'order_id' => 'ORD-030',
            'gross_amount' => 100000,
        ]);

        $this->assertNotNull($payment->created_at);
        $this->assertNotNull($payment->updated_at);
    }

    /** @test */
    public function it_eager_loads_sales()
    {
        $role = Role::create(['name' => 'Admin']);
        $user = User::create([
            'role_id' => $role->role_id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'username' => 'cashier',
            'name' => 'Cashier User',
            'email' => 'cashier@test.com',
            'password' => bcrypt('password'),
        ]);

        $payment = Payment::create([
            'order_id' => 'ORD-031',
            'gross_amount' => 100000,
        ]);

        Sale::create([
            'user_id' => $user->user_id,
            'payment_id' => $payment->payment_id,
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 100000,
            'payment_status' => 'paid',
            'sale_date' => now(),
        ]);

        $paymentWithSales = Payment::with('sales')->find($payment->payment_id);

        $this->assertTrue($paymentWithSales->relationLoaded('sales'));
        $this->assertCount(1, $paymentWithSales->sales);
    }

    /** @test */
    public function it_can_delete_payment()
    {
        $payment = Payment::create([
            'order_id' => 'ORD-032',
            'gross_amount' => 100000,
        ]);

        $paymentId = $payment->payment_id;
        $payment->delete();

        $this->assertDatabaseMissing('payments', [
            'payment_id' => $paymentId,
        ]);
    }
}
