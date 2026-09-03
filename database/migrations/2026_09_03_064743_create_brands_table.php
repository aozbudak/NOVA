<?php

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('name', 150);
                $table->string('slug', 180)->unique();
                $table->text('description')->nullable();
                $table->string('logo', 500)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index('name');
                $table->index('is_active');
            });
        }

        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'brand_id')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->uuid('brand_id')->nullable();
                $table->index('brand_id');
                $table->foreign('brand_id')->references('id')->on('brands')->nullOnDelete();
            });
        }

        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'vat_rate')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->decimal('vat_rate', 5, 2)->default(20);
            });
        }

        $this->backfillBrands();
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'brand_id')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropForeign(['brand_id']);
                $table->dropColumn('brand_id');
            });
        }

        if (Schema::hasTable('products') && Schema::hasColumn('products', 'vat_rate')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropColumn('vat_rate');
            });
        }

        Schema::dropIfExists('brands');
    }

    private function backfillBrands(): void
    {
        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'brand_id')) {
            return;
        }

        Product::query()
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->pluck('brand')
            ->unique()
            ->each(function (string $name): void {
                $slug = Str::slug($name) ?: 'brand';
                $base = $slug;
                $suffix = 2;

                while (Brand::query()->where('slug', $slug)->where('name', '!=', $name)->exists()) {
                    $slug = $base.'-'.$suffix;
                    $suffix++;
                }

                $brand = Brand::query()->firstOrCreate(
                    ['name' => $name],
                    [
                        'slug' => $slug,
                        'is_active' => true,
                    ],
                );

                Product::query()
                    ->where('brand', $name)
                    ->whereNull('brand_id')
                    ->update(['brand_id' => $brand->id]);
            });
    }
};
