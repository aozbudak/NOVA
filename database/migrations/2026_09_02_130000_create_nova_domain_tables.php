<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->create('roles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $this->create('permissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name', 150)->unique();
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $this->create('role_user', function (Blueprint $table): void {
            $table->uuid('role_id');
            $table->uuid('user_id');
            $table->timestamp('created_at')->useCurrent();
            $table->primary(['role_id', 'user_id']);
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        $this->create('permission_role', function (Blueprint $table): void {
            $table->uuid('permission_id');
            $table->uuid('role_id');
            $table->primary(['permission_id', 'role_id']);
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });

        $this->create('categories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('parent_id')->nullable();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            $table->string('image', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete();
            $table->index('is_active');
            $table->index('parent_id');
        });

        $this->create('products', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('category_id');
            $table->string('name');
            $table->string('slug', 280)->unique();
            $table->text('description')->nullable();
            $table->string('brand', 150)->nullable();
            $table->decimal('base_price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->string('currency', 3)->default('TRY');
            $table->boolean('is_new')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('catalog_code')->nullable()->unique();
            $table->json('attributes')->nullable();
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
            $table->index('brand');
            $table->index('category_id');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('is_new');
        });

        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'catalog_code')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->unsignedInteger('catalog_code')->nullable()->unique();
            });
        }

        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'attributes')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->json('attributes')->nullable();
            });
        }

        $this->create('product_variants', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->string('sku', 100)->unique();
            $table->string('barcode', 100)->nullable()->unique();
            $table->string('color', 100)->nullable();
            $table->string('size', 50)->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->index('product_id');
            $table->index('barcode');
            $table->index('color');
            $table->index('size');
            $table->index('is_active');
        });

        $this->create('product_images', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->string('image_url', 1000);
            $table->string('alt_text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->index('product_id');
            $table->index('sort_order');
        });

        $this->create('stocks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('product_variant_id')->unique();
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('minimum_quantity')->default(5);
            $table->timestamp('updated_at')->useCurrent();
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->cascadeOnDelete();
            $table->index('quantity');
        });

        $this->create('stock_movements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('product_variant_id');
            $table->uuid('user_id')->nullable();
            $table->string('movement_type', 50);
            $table->integer('quantity');
            $table->string('reference_type', 100)->nullable();
            $table->uuid('reference_id')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->restrictOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('created_at');
            $table->index('movement_type');
            $table->index('user_id');
            $table->index('product_variant_id');
            $table->index(['reference_type', 'reference_id']);
        });

        $this->create('customers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable()->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('email');
            $table->index('phone');
            $table->index(['first_name', 'last_name']);
        });

        $this->create('customer_addresses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('customer_id');
            $table->string('title', 100);
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('phone', 30)->nullable();
            $table->string('city', 100);
            $table->string('district', 100);
            $table->text('address_line');
            $table->string('postal_code', 20)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->index('city');
            $table->index('customer_id');
        });

        $this->create('carts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('customer_id')->unique();
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
        });

        $this->create('cart_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('cart_id');
            $table->uuid('product_variant_id');
            $table->integer('quantity')->default(1);
            $table->timestamps();
            $table->unique(['cart_id', 'product_variant_id']);
            $table->foreign('cart_id')->references('id')->on('carts')->cascadeOnDelete();
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->restrictOnDelete();
            $table->index('cart_id');
            $table->index('product_variant_id');
        });

        $this->create('wishlists', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('customer_id')->unique();
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
        });

        $this->create('wishlist_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('wishlist_id');
            $table->uuid('product_id');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['wishlist_id', 'product_id']);
            $table->foreign('wishlist_id')->references('id')->on('wishlists')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->index('product_id');
        });

        $this->create('orders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('order_number', 50)->unique();
            $table->uuid('customer_id')->nullable();
            $table->string('status', 50)->default('pending');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('shipping_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('TRY');
            $table->json('shipping_address')->nullable();
            $table->json('billing_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->index('created_at');
            $table->index('customer_id');
            $table->index('status');
            $table->index('total_amount');
        });

        $this->create('order_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('order_id');
            $table->uuid('product_variant_id')->nullable();
            $table->string('product_name');
            $table->string('sku', 100)->nullable();
            $table->string('color', 100)->nullable();
            $table->string('size', 50)->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2);
            $table->timestamps();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->nullOnDelete();
            $table->index('order_id');
            $table->index('product_variant_id');
        });

        $this->create('payments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('order_id');
            $table->string('payment_method', 50);
            $table->decimal('amount', 12, 2);
            $table->string('status', 50)->default('pending');
            $table->string('transaction_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->index('order_id');
            $table->index('paid_at');
            $table->index('status');
        });

        $this->create('returns', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('order_id');
            $table->uuid('customer_id')->nullable();
            $table->string('return_number', 50)->unique();
            $table->text('reason')->nullable();
            $table->string('status', 50)->default('pending');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->foreign('order_id')->references('id')->on('orders')->restrictOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->index('created_at');
            $table->index('customer_id');
            $table->index('order_id');
            $table->index('status');
        });

        $this->create('return_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('return_id');
            $table->uuid('order_item_id');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->foreign('return_id')->references('id')->on('returns')->cascadeOnDelete();
            $table->foreign('order_item_id')->references('id')->on('order_items')->restrictOnDelete();
            $table->index('order_item_id');
            $table->index('return_id');
        });

        $this->create('exchanges', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('return_id');
            $table->uuid('old_product_variant_id');
            $table->uuid('new_product_variant_id');
            $table->integer('quantity')->default(1);
            $table->decimal('price_difference', 12, 2)->default(0);
            $table->string('status', 50)->default('pending');
            $table->timestamps();
            $table->foreign('return_id')->references('id')->on('returns')->cascadeOnDelete();
            $table->foreign('old_product_variant_id')->references('id')->on('product_variants')->restrictOnDelete();
            $table->foreign('new_product_variant_id')->references('id')->on('product_variants')->restrictOnDelete();
            $table->index('return_id');
            $table->index('old_product_variant_id');
            $table->index('new_product_variant_id');
        });

        $this->create('suppliers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('company_name');
            $table->string('contact_name', 150)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('tax_number', 50)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('company_name');
            $table->index('is_active');
            $table->index('tax_number');
        });

        $this->create('cash_registers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index('is_active');
        });

        $this->create('cash_transactions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('cash_register_id');
            $table->uuid('user_id')->nullable();
            $table->string('transaction_type', 50);
            $table->decimal('amount', 12, 2);
            $table->string('reference_type', 100)->nullable();
            $table->uuid('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('cash_register_id')->references('id')->on('cash_registers')->restrictOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('created_at');
            $table->index('cash_register_id');
            $table->index('transaction_type');
            $table->index('user_id');
            $table->index(['reference_type', 'reference_id']);
        });

        $this->create('audit_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('action', 100);
            $table->string('auditable_type', 150)->nullable();
            $table->uuid('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('action');
            $table->index('created_at');
            $table->index('user_id');
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'catalog_code')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropUnique(['catalog_code']);
                $table->dropColumn('catalog_code');
            });
        }

        if (Schema::hasTable('products') && Schema::hasColumn('products', 'attributes')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropColumn('attributes');
            });
        }
    }

    private function create(string $table, Closure $callback): void
    {
        if (! Schema::hasTable($table)) {
            Schema::create($table, $callback);
        }
    }
};
