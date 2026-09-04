<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories') || Schema::hasColumn('categories', 'brand_id')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table): void {
            $table->uuid('brand_id')->nullable();
            $table->index('brand_id');
            $table->foreign('brand_id')->references('id')->on('brands')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('categories') || ! Schema::hasColumn('categories', 'brand_id')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropForeign(['brand_id']);
            $table->dropColumn('brand_id');
        });
    }
};
