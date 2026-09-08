<?php

namespace Tests\Feature;

use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\SaleReturn;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerReturnRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_return_request_api_returns_401(): void
    {
        $this->getJson(route('api.return-requests.index'))->assertUnauthorized();
        $this->postJson(route('api.return-requests.store'), [])->assertUnauthorized();
    }

    public function test_guest_return_pages_redirect_to_login(): void
    {
        $this->get(route('account.returns'))->assertRedirect(route('login'));
        $this->post(route('account.returns.store'), [])->assertRedirect(route('login'));
    }

    public function test_empty_return_requests_render_empty_state(): void
    {
        $this->registerAda();

        $this->get(route('account.returns'))
            ->assertSee('No return requests yet')
            ->assertSee("You don't have any return requests yet.")
            ->assertSee('Request a return')
            ->assertSee('data-return-open', false)
            ->assertSee('data-return-panel', false);
    }

    public function test_returns_page_lists_returnable_orders_and_creates_a_request(): void
    {
        $this->registerAda();
        $order = $this->completedOrderFor($this->ada());

        $this->get(route('account.returns'))
            ->assertSee('Request a return')
            ->assertSee($order->order_number)
            ->assertSee('Submit return request');

        $this->post(route('account.returns.store'), [
            'order_id' => $order->order_number,
            'reason' => 'wrong_size',
            'notes' => 'Too tight',
        ])->assertRedirect();

        $this->assertSame('pending', SaleReturn::query()->value('status'));
    }

    public function test_customer_can_create_a_pending_return_request_without_stock_or_cash_movement(): void
    {
        $this->registerAda();
        $order = $this->completedOrderFor($this->ada());
        $stock = (int) Stock::query()->value('quantity');

        $this->postJson(route('api.return-requests.store'), [
            'order_id' => $order->order_number,
            'reason' => 'wrong_size',
            'notes' => 'Size M is tight',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('message', 'Your return request has been sent.');

        $return = SaleReturn::query()->first();

        $this->assertNotNull($return);
        $this->assertSame('pending', $return->status);
        $this->assertSame('wrong_size', $return->reason);
        $this->assertSame('Size M is tight', $return->notes);
        $this->assertSame('completed', $order->fresh()?->status);
        $this->assertSame($stock, (int) Stock::query()->value('quantity'));
        $this->assertSame(0, StockMovement::query()->where('movement_type', 'return')->count());
        $this->assertSame(0, Payment::query()->where('status', 'refunded')->count());
        $this->assertSame(0, CashTransaction::query()->count());

        $this->getJson(route('api.return-requests.index'))
            ->assertOk()
            ->assertJsonPath('data.0.number', $return->return_number)
            ->assertJsonPath('data.0.order_number', $order->order_number);

        $this->get(route('account.orders.show', $order->order_number))
            ->assertDontSee('Request a return', false);

        $this->get(route('account.returns.show', $return->return_number))
            ->assertSee($return->return_number)
            ->assertSee($order->order_number)
            ->assertSee('Pending')
            ->assertSee('Size M is tight');
    }

    public function test_return_request_rejects_empty_payload(): void
    {
        $this->registerAda();
        $this->completedOrderFor($this->ada());

        $this->postJson(route('api.return-requests.store'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['order_id', 'reason']);
    }

    public function test_other_reason_requires_notes(): void
    {
        $this->registerAda();
        $order = $this->completedOrderFor($this->ada());

        $this->postJson(route('api.return-requests.store'), [
            'order_id' => $order->order_number,
            'reason' => 'other',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('notes');
    }

    public function test_returned_order_cannot_receive_another_return_request(): void
    {
        $this->registerAda();
        $order = $this->completedOrderFor($this->ada(), ['status' => 'returned']);

        $this->postJson(route('api.return-requests.store'), [
            'order_id' => $order->order_number,
            'reason' => 'defective',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.order_id.0', 'This order cannot be returned.');

        $this->get(route('account.orders.show', $order->order_number))
            ->assertDontSee('Request a return', false);

        $this->assertSame(0, SaleReturn::query()->count());
    }

    public function test_second_return_request_for_the_same_order_is_rejected(): void
    {
        $this->registerAda();
        $order = $this->completedOrderFor($this->ada());

        $this->postJson(route('api.return-requests.store'), [
            'order_id' => $order->order_number,
            'reason' => 'wrong_product',
        ])->assertCreated();

        $this->postJson(route('api.return-requests.store'), [
            'order_id' => $order->order_number,
            'reason' => 'defective',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.order_id.0', 'A return request already exists for this order.');

        $this->assertSame(1, SaleReturn::query()->count());
    }

    public function test_customer_cannot_create_or_view_another_customers_return_request(): void
    {
        $this->registerAda();
        $foreign = $this->completedOrderFor(Customer::factory()->create(), ['order_number' => 'NV-FOREIGN-0001']);

        $this->postJson(route('api.return-requests.store'), [
            'order_id' => $foreign->order_number,
            'reason' => 'wrong_size',
        ])->assertNotFound();

        $return = SaleReturn::query()->create([
            'order_id' => $foreign->id,
            'customer_id' => $foreign->customer_id,
            'return_number' => 'RT-SECRET-0001',
            'reason' => 'wrong_size',
            'status' => 'pending',
            'total_amount' => 389,
        ]);

        $this->getJson(route('api.return-requests.show', $return->return_number))->assertNotFound();
        $this->get(route('account.returns.show', $return->return_number))->assertNotFound();
        $this->assertSame(0, SaleReturn::query()->where('customer_id', $this->ada()->id)->count());
    }

    public function test_admin_approval_completes_the_existing_return_request(): void
    {
        $this->registerAda();
        $order = $this->completedOrderFor($this->ada());

        $this->postJson(route('api.return-requests.store'), [
            'order_id' => $order->order_number,
            'reason' => 'defective',
        ])->assertCreated();

        $return = SaleReturn::query()->firstOrFail();
        $stockBefore = (int) Stock::query()->value('quantity');

        $this->get(route('admin.returns.show', $return->return_number))
            ->assertOk()
            ->assertSee('Approve');

        $this->post(route('admin.returns.approve', $return->return_number))
            ->assertRedirect(route('admin.returns.show', $return->return_number));

        $this->assertSame('completed', $return->fresh()?->status);
        $this->assertSame('returned', $order->fresh()?->status);
        $this->assertSame($stockBefore + 1, (int) Stock::query()->value('quantity'));
        $this->assertTrue(Payment::query()->where('status', 'refunded')->exists());
        $this->assertTrue(StockMovement::query()->where('movement_type', 'return')->exists());
        $this->assertSame(1, SaleReturn::query()->count());

        $this->getJson(route('api.return-requests.show', $return->return_number))
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');
    }

    public function test_admin_rejection_is_visible_to_the_customer(): void
    {
        $this->registerAda();
        $order = $this->completedOrderFor($this->ada());

        $this->postJson(route('api.return-requests.store'), [
            'order_id' => $order->order_number,
            'reason' => 'customer_changed_mind',
        ])->assertCreated();

        $return = SaleReturn::query()->firstOrFail();

        $this->from(route('admin.returns.show', $return->return_number))
            ->post(route('admin.returns.reject', $return->return_number), [
                'admin_notes' => 'Item shows wear',
            ])
            ->assertRedirect(route('admin.returns.show', $return->return_number));

        $this->assertSame('rejected', $return->fresh()?->status);
        $this->assertSame('completed', $order->fresh()?->status);
        $this->assertSame(0, StockMovement::query()->where('movement_type', 'return')->count());

        $this->get(route('account.returns.show', $return->return_number))
            ->assertSee('Rejected')
            ->assertSee('Item shows wear');
    }

    public function test_return_notes_are_escaped_in_the_customer_panel(): void
    {
        $this->registerAda();
        $order = $this->completedOrderFor($this->ada());

        $this->post(route('account.returns.store'), [
            'order_id' => $order->order_number,
            'reason' => 'other',
            'notes' => '<script>alert(1)</script>',
        ])->assertRedirect();

        $return = SaleReturn::query()->firstOrFail();

        $this->get(route('account.returns.show', $return->return_number))
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    private function registerAda(): void
    {
        $this->post(route('register.store'), [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@nova.example',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
    }

    private function ada(): Customer
    {
        return Customer::query()->where('email', 'ada@nova.example')->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function completedOrderFor(Customer $customer, array $overrides = []): Order
    {
        $product = Product::factory()->create([
            'name' => 'Basic Shirt',
            'brand' => 'NOVA',
            'is_active' => true,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'NOVA-RET-'.fake()->unique()->bothify('??##'),
            'color' => 'White',
            'size' => 'M',
            'price' => 389,
            'is_active' => true,
        ]);

        ProductImage::query()->create([
            'product_id' => $product->id,
            'image_url' => 'https://example.com/shirt.jpg',
            'sort_order' => 0,
            'is_primary' => true,
        ]);

        Stock::query()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
            'minimum_quantity' => 0,
        ]);

        $order = Order::factory()->for($customer)->create([
            'order_number' => 'NV-20260908-0001',
            'status' => 'completed',
            'subtotal' => 389,
            'total_amount' => 389,
            'currency' => 'TRY',
            ...$overrides,
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Basic Shirt',
            'sku' => $variant->sku,
            'color' => 'White',
            'size' => 'M',
            'quantity' => 1,
            'unit_price' => 389,
            'discount_amount' => 0,
            'total_price' => 389,
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => 'card',
            'amount' => 389,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        return $order->fresh(['items', 'payments', 'returns']) ?? $order;
    }
}
