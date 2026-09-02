<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addSlug('customers');
        $this->addSlug('suppliers');
        $this->addSlug('users');
    }

    public function down(): void
    {
        $this->dropSlug('customers');
        $this->dropSlug('suppliers');
        $this->dropSlug('users');
    }

    private function addSlug(string $table): void
    {
        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'slug')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->string('slug', 180)->nullable()->unique();
        });
    }

    private function dropSlug(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'slug')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->dropUnique(['slug']);
            $blueprint->dropColumn('slug');
        });
    }
};
