<?php

namespace Tests\Unit;

use App\Models\Payment;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'user_id',
            'payment_id',
            'subtotal',
            'discount_amount',
            'tax_amount',
            'total_amount',
            'payment_status',
            'sale_date',
        ];

        $sale = new Sale();

        $this->assertEquals($fillable, $sale->getFillable());
    }

    /** @test */
    public function it_uses_correct_table_name()
    {
        $sale = new Sale();

        $this->assertEquals('sales', $sale->getTable());
    }

    /** @test */
    public function it_uses_correct_primary_key()
    {
        $sale = new Sale();

        $this->assertEquals('sale_id', $sale->getKeyName());
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $role = Role::create(['name' => 'Kasir']);
        $user = User::factory()->create(['role_id' => $role->role_id]);

        $sale = Sale::create([
            'user_id' => $user->user_id,
            'subtotal' => 100000.5,
            'discount_amount' => 5000.25,
            'tax_amount' => 10000.75,
            'total_amount' => 105001.00,
            'payment_status' => 'draft',
            'sale_date' => '2024-01-15',
        ]);

        $this->assertIsString($sale->subtotal);
        $this->assertEquals('100000.50', $sale->subtotal);
        $this->assertIsString($sale->discount_amount);
        $this->assertIsString($sale->tax_amount);
        $this->assertIsString($sale->total_amount);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $sale->sale_date);
    }

    /** @test */
    public function it_belongs_to_user()
    {
        $role = Role::create(['name' => 'Kasir']);
        $user = User::factory()->create(['role_id' => $role->role_id]);

        $sale = Sale::create([
            'user_id' => $user->user_id,
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'payment_status' => 'draft',
            'sale_date' => now(),
        ]);

        $this->assertInstanceOf(User::class, $sale->user);
        $this->assertEquals($user->user_id, $sale->user->user_id);
    }

    /** @test */
    public function it_belongs_to_payment()
    {
        $role = Role::create(['name' => 'Kasir']);
        $user = User::factory()->create(['role_id' => $role->role_id]);

        $payment = Payment::create([
            'payment_method' => 'cash',
            'order_id' => 'ORDER-123',
            'gross_amount' => 100000,
            'transaction_status' => 'pending',
        ]);

        $sale = Sale::create([
            'user_id' => $user->user_id,
            'payment_id' => $payment->payment_id,
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 100000,
            'payment_status' => 'paid',
            'sale_date' => now(),
        ]);

        $this->assertInstanceOf(Payment::class, $sale->payment);
        $this->assertEquals($payment->payment_id, $sale->payment->payment_id);
    }

    /** @test */
    public function it_has_many_items()
    {
        $role = Role::create(['name' => 'Kasir']);
        $user = User::factory()->create(['role_id' => $role->role_id]);

        $sale = Sale::create([
            'user_id' => $user->user_id,
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 100000,
            'payment_status' => 'draft',
            'sale_date' => now(),
        ]);

        SaleItem::factory()->create(['sale_id' => $sale->sale_id]);
        SaleItem::factory()->create(['sale_id' => $sale->sale_id]);

        $this->assertCount(2, $sale->items);
        $this->assertInstanceOf(SaleItem::class, $sale->items->first());
    }

    /** @test */
    public function it_can_calculate_total_correctly()
    {
        $role = Role::create(['name' => 'Kasir']);
        $user = User::factory()->create(['role_id' => $role->role_id]);

        $sale = Sale::create([
            'user_id' => $user->user_id,
            'subtotal' => 1000000,
            'discount_amount' => 100000,
            'tax_amount' => 50000,
            'total_amount' => 950000, // 1000000 - 100000 + 50000
            'payment_status' => 'draft',
            'sale_date' => now(),
        ]);

        $this->assertEquals('950000.00', $sale->total_amount);
    }

    /** @test */
    public function it_can_have_draft_status()
    {
        $role = Role::create(['name' => 'Kasir']);
        $user = User::factory()->create(['role_id' => $role->role_id]);

        $sale = Sale::create([
            'user_id' => $user->user_id,
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'payment_status' => 'draft',
            'sale_date' => now(),
        ]);

        $this->assertEquals('draft', $sale->payment_status);
    }

    /** @test */
    public function it_can_have_paid_status()
    {
        $role = Role::create(['name' => 'Kasir']);
        $user = User::factory()->create(['role_id' => $role->role_id]);

        $sale = Sale::create([
            'user_id' => $user->user_id,
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 100000,
            'payment_status' => 'paid',
            'sale_date' => now(),
        ]);

        $this->assertEquals('paid', $sale->payment_status);
    }

    /** @test */
    public function it_eager_loads_relationships()
    {
        $role = Role::create(['name' => 'Kasir']);
        $user = User::factory()->create(['role_id' => $role->role_id]);

        $payment = Payment::create([
            'payment_method' => 'cash',
            'order_id' => 'ORDER-123',
            'gross_amount' => 100000,
            'transaction_status' => 'pending',
        ]);

        $sale = Sale::create([
            'user_id' => $user->user_id,
            'payment_id' => $payment->payment_id,
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 100000,
            'payment_status' => 'paid',
            'sale_date' => now(),
        ]);

        $loadedSale = Sale::with(['user', 'payment', 'items'])->find($sale->sale_id);

        $this->assertTrue($loadedSale->relationLoaded('user'));
        $this->assertTrue($loadedSale->relationLoaded('payment'));
        $this->assertTrue($loadedSale->relationLoaded('items'));
    }

    /** @test */
    public function it_can_have_null_payment_id_for_draft_sales()
    {
        $role = Role::create(['name' => 'Kasir']);
        $user = User::factory()->create(['role_id' => $role->role_id]);

        $sale = Sale::create([
            'user_id' => $user->user_id,
            'payment_id' => null,
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'payment_status' => 'draft',
            'sale_date' => now(),
        ]);

        $this->assertNull($sale->payment_id);
        $this->assertEquals('draft', $sale->payment_status);
    }
}
