<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('returns', function (Blueprint $table): void {
            if (! Schema::hasColumn('returns', 'notes')) {
                $table->text('notes')->nullable()->after('reason');
            }

            if (! Schema::hasColumn('returns', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('notes');
            }

            if (! Schema::hasColumn('returns', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('completed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('returns', function (Blueprint $table): void {
            $columns = array_values(array_filter([
                Schema::hasColumn('returns', 'notes') ? 'notes' : null,
                Schema::hasColumn('returns', 'admin_notes') ? 'admin_notes' : null,
                Schema::hasColumn('returns', 'rejected_at') ? 'rejected_at' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
