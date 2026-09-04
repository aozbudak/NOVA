<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories') || ! Schema::hasColumn('categories', 'brand_id')) {
            return;
        }

        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::table('categories', function (Blueprint $table): void {
                $table->dropColumn('brand_id');
            });
        });
    }

    public function down(): void
    {
        //
    }
};
