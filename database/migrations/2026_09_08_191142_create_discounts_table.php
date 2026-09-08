<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->string('type', 20);
            $table->decimal('value', 12, 2);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['is_active', 'starts_at', 'ends_at']);
            $table->index('type');
        });

        Schema::create('discount_product', function (Blueprint $table): void {
            $table->uuid('discount_id');
            $table->uuid('product_id');
            $table->primary(['discount_id', 'product_id']);
            $table->foreign('discount_id')->references('id')->on('discounts')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->index('product_id');
        });

        Schema::create('discount_product_variant', function (Blueprint $table): void {
            $table->uuid('discount_id');
            $table->uuid('product_variant_id');
            $table->primary(['discount_id', 'product_variant_id']);
            $table->foreign('discount_id')->references('id')->on('discounts')->cascadeOnDelete();
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->cascadeOnDelete();
            $table->index('product_variant_id');
        });

        Schema::create('discount_category', function (Blueprint $table): void {
            $table->uuid('discount_id');
            $table->uuid('category_id');
            $table->primary(['discount_id', 'category_id']);
            $table->foreign('discount_id')->references('id')->on('discounts')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            $table->index('category_id');
        });

        Schema::create('discount_brand', function (Blueprint $table): void {
            $table->uuid('discount_id');
            $table->uuid('brand_id');
            $table->primary(['discount_id', 'brand_id']);
            $table->foreign('discount_id')->references('id')->on('discounts')->cascadeOnDelete();
            $table->foreign('brand_id')->references('id')->on('brands')->cascadeOnDelete();
            $table->index('brand_id');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->string('discount_name', 150)->nullable()->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn('discount_name');
        });

        Schema::dropIfExists('discount_brand');
        Schema::dropIfExists('discount_category');
        Schema::dropIfExists('discount_product_variant');
        Schema::dropIfExists('discount_product');
        Schema::dropIfExists('discounts');
    }
};
