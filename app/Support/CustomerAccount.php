<?php

namespace App\Support;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\SaleReturn;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class CustomerAccount
{
    public function customer(): ?Customer
    {
        $session = session('storefront.customer');

        if (! is_array($session)) {
            return null;
        }

        $email = (string) ($session['email'] ?? '');

        if ($email === '') {
            return null;
        }

        return Customer::query()->where('email', $email)->first();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function orderSummaries(?Customer $customer): array
    {
        if ($customer === null) {
            return [];
        }

        return $this->orders($customer)
            ->map(fn (Order $order): array => $this->summarizeOrder($order))
            ->all();
    }

    /**
     * @return Collection<int, Order>
     */
    public function orders(Customer $customer): Collection
    {
        return $customer->orders()
            ->with(['payments', 'returns'])
            ->withSum('items', 'quantity')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    public function order(Customer $customer, string $id): Order
    {
        $order = $this->ownedOrderQuery($customer, $id)
            ->with([
                'items.variant.product.images',
                'items.variant.product.brandRecord',
                'payments',
                'returns',
            ])
            ->first();

        abort_if($order === null, 404);

        return $order;
    }

    /**
     * @return list<array{id: string, label: string, total: float, currency: string}>
     */
    public function returnableOrderOptions(Customer $customer): array
    {
        return $this->orders($customer)
            ->filter(fn (Order $order): bool => $this->orderIsReturnable($order))
            ->map(fn (Order $order): array => [
                'id' => $order->order_number,
                'label' => $order->order_number.' · '.($order->created_at?->format('d.m.Y') ?? ''),
                'total' => (float) $order->total_amount,
                'currency' => filled($order->currency) ? $order->currency : 'TRY',
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function returnSummaries(?Customer $customer): array
    {
        if ($customer === null) {
            return [];
        }

        return $this->returns($customer)
            ->map(fn (SaleReturn $return): array => $this->summarizeReturn($return))
            ->all();
    }

    /**
     * @return Collection<int, SaleReturn>
     */
    public function returns(Customer $customer): Collection
    {
        return $customer->returns()
            ->with('order')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    public function returnRequest(Customer $customer, string $id): SaleReturn
    {
        $return = $this->ownedReturnQuery($customer, $id)
            ->with([
                'order',
                'items.orderItem.variant.product.images',
                'items.orderItem.variant.product.brandRecord',
            ])
            ->first();

        abort_if($return === null, 404);

        return $return;
    }

    /**
     * @return array<string, mixed>
     */
    public function summarizeOrder(Order $order): array
    {
        $return = $this->latestReturn($order);

        return [
            'id' => $order->order_number,
            'number' => $order->order_number,
            'date' => $order->created_at?->format('d.m.Y') ?? '',
            'item_count' => (int) ($order->items_sum_quantity ?? $order->items->sum('quantity')),
            'total' => (float) $order->total_amount,
            'currency' => filled($order->currency) ? $order->currency : 'TRY',
            'payment_method' => $this->paymentMethod($order),
            'payment_label' => $this->paymentLabel($this->paymentMethod($order)),
            'status' => $this->orderStatusLabel($order->status),
            'status_key' => $this->orderStatusKey($order->status),
            'return_status' => $return?->status ?? 'none',
            'return_status_label' => $this->returnStatusLabel($return?->status),
            'returnable' => $this->orderIsReturnable($order),
            'href' => route('account.orders.show', $order->order_number),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function detailOrder(Order $order): array
    {
        $summary = $this->summarizeOrder($order);
        $return = $this->latestReturn($order);

        return [
            ...$summary,
            'ordered_at' => $order->created_at?->toIso8601String(),
            'subtotal' => (float) $order->subtotal,
            'discount' => (float) $order->discount_amount,
            'shipping' => (float) $order->shipping_amount,
            'tax' => (float) $order->tax_amount,
            'items' => $order->items
                ->map(fn (OrderItem $item): array => $this->mapOrderItem($item))
                ->all(),
            'return_request' => $return === null ? null : [
                'id' => $return->return_number,
                'status' => $return->status,
                'href' => route('account.returns.show', $return->return_number),
            ],
            'reasons' => $this->reasonOptions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function summarizeReturn(SaleReturn $return): array
    {
        return [
            'id' => $return->return_number,
            'number' => $return->return_number,
            'order_number' => $return->order?->order_number ?? '',
            'date' => $return->created_at?->format('d.m.Y') ?? '',
            'reason' => (string) $return->reason,
            'reason_label' => $this->reasonLabel((string) $return->reason),
            'amount' => (float) $return->total_amount,
            'currency' => filled($return->order?->currency) ? $return->order->currency : 'TRY',
            'status' => $return->status,
            'status_label' => $this->returnStatusLabel($return->status),
            'href' => route('account.returns.show', $return->return_number),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function detailReturn(SaleReturn $return): array
    {
        return [
            ...$this->summarizeReturn($return),
            'requested_at' => $return->created_at?->toIso8601String(),
            'notes' => (string) ($return->notes ?? ''),
            'admin_notes' => (string) ($return->admin_notes ?? ''),
            'admin_action' => $this->adminActionLabel($return),
            'result' => $this->returnStatusLabel($return->status),
            'items' => $return->items
                ->map(function ($item): array {
                    $orderItem = $item->orderItem;

                    if ($orderItem === null) {
                        return [
                            'name' => '',
                            'image' => '',
                            'brand' => '',
                            'variant' => '',
                            'size' => '',
                            'color' => '',
                            'quantity' => (int) $item->quantity,
                            'unit_price' => (float) $item->unit_price,
                            'discount' => 0.0,
                            'total' => (float) $item->total_price,
                        ];
                    }

                    return $this->mapOrderItem($orderItem, (int) $item->quantity, (float) $item->unit_price, (float) $item->total_price);
                })
                ->all(),
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function reasonOptions(): array
    {
        return collect(SaleReturn::customerReasons())
            ->map(fn (string $reason): array => [
                'value' => $reason,
                'label' => $this->reasonLabel($reason),
            ])
            ->all();
    }

    public function orderIsReturnable(Order $order): bool
    {
        if (! in_array($order->status, ['completed', 'delivered', 'shipped', 'processing', 'confirmed'], true)) {
            return false;
        }

        return $this->latestBlockingReturn($order) === null;
    }

    private function latestReturn(Order $order): ?SaleReturn
    {
        return $order->returns
            ->sortByDesc(fn (SaleReturn $return): int => $return->created_at?->getTimestamp() ?? 0)
            ->first();
    }

    private function latestBlockingReturn(Order $order): ?SaleReturn
    {
        return $order->returns->first(
            fn (SaleReturn $return): bool => in_array($return->status, ['pending', 'completed'], true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function mapOrderItem(OrderItem $item, ?int $quantity = null, ?float $unitPrice = null, ?float $total = null): array
    {
        $product = $item->variant?->product;
        $image = $product?->images->first()?->image_url ?? '';
        $brand = $product?->brandRecord?->name ?? $product?->brand ?? '';
        $color = (string) ($item->color ?? '');
        $size = (string) ($item->size ?? '');

        return [
            'name' => $item->product_name,
            'image' => $image,
            'brand' => $brand,
            'variant' => trim($color.' / '.$size, ' /'),
            'size' => $size,
            'color' => $color,
            'quantity' => $quantity ?? (int) $item->quantity,
            'unit_price' => $unitPrice ?? (float) $item->unit_price,
            'discount' => (float) $item->discount_amount,
            'total' => $total ?? (float) $item->total_price,
        ];
    }

    private function paymentMethod(Order $order): string
    {
        $payment = $order->payments->first(
            fn (Payment $payment): bool => $payment->status === 'completed',
        );

        if ($payment instanceof Payment) {
            return (string) $payment->payment_method;
        }

        $notes = (string) $order->notes;

        if (str_contains($notes, '/')) {
            return (string) Str::afterLast($notes, '/');
        }

        return '';
    }

    private function paymentLabel(string $method): string
    {
        return match ($method) {
            'card' => __('storefront.checkout.card'),
            'paypal' => __('storefront.checkout.paypal'),
            'cash' => __('storefront.account.payment_cash'),
            'online' => __('storefront.account.payment_online'),
            'other' => __('storefront.account.payment_other'),
            '' => '—',
            default => $method,
        };
    }

    private function orderStatusKey(string $status): string
    {
        return match ($status) {
            'completed', 'delivered' => 'delivered',
            'in_transit', 'shipped' => 'in_transit',
            default => $status,
        };
    }

    private function orderStatusLabel(string $status): string
    {
        return match ($status) {
            'delivered' => __('storefront.account.status_delivered'),
            'in_transit', 'shipped' => __('storefront.account.status_in_transit'),
            'completed' => __('storefront.account.status_completed'),
            'cancelled' => __('storefront.account.status_cancelled'),
            'returned' => __('storefront.account.status_returned'),
            'partially_returned' => __('storefront.account.status_partially_returned'),
            default => __('storefront.account.status_pending'),
        };
    }

    private function returnStatusLabel(?string $status): string
    {
        return match ($status) {
            'pending' => __('storefront.account.status_pending'),
            'approved' => __('storefront.account.status_approved'),
            'rejected' => __('storefront.account.status_rejected'),
            'completed' => __('storefront.account.status_completed'),
            default => __('storefront.account.return_none'),
        };
    }

    private function reasonLabel(string $reason): string
    {
        $key = 'storefront.account.reason_'.$reason;

        return trans()->has($key) ? __($key) : $reason;
    }

    private function adminActionLabel(SaleReturn $return): string
    {
        return match ($return->status) {
            'pending' => __('storefront.account.admin_pending'),
            'approved' => __('storefront.account.admin_approved'),
            'rejected' => __('storefront.account.admin_rejected'),
            'completed' => __('storefront.account.admin_completed'),
            default => __('storefront.account.return_none'),
        };
    }

    /**
     * @return HasMany<Order, Customer>
     */
    private function ownedOrderQuery(Customer $customer, string $id): HasMany
    {
        return $customer->orders()->where(function ($query) use ($id): void {
            $query->where('order_number', $id);

            if (Str::isUuid($id)) {
                $query->orWhere('id', $id);
            }
        });
    }

    /**
     * @return HasMany<SaleReturn, Customer>
     */
    private function ownedReturnQuery(Customer $customer, string $id): HasMany
    {
        return $customer->returns()->where(function ($query) use ($id): void {
            $query->where('return_number', $id);

            if (Str::isUuid($id)) {
                $query->orWhere('id', $id);
            }
        });
    }
}
