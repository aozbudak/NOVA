<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        $this->replaceCheck('orders', 'chk_orders_status', "CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'confirmed'::character varying, 'processing'::character varying, 'shipped'::character varying, 'delivered'::character varying, 'completed'::character varying, 'returned'::character varying, 'cancelled'::character varying])::text[])))");
        $this->replaceCheck('payments', 'chk_payments_method', "CHECK (((payment_method)::text = ANY ((ARRAY['cash'::character varying, 'card'::character varying, 'online'::character varying, 'other'::character varying])::text[])))");
        $this->replaceCheck('cash_transactions', 'chk_cash_transactions_type', "CHECK (((transaction_type)::text = ANY ((ARRAY['sale'::character varying, 'refund'::character varying, 'expense'::character varying, 'income'::character varying, 'cash_in'::character varying, 'cash_out'::character varying, 'opening'::character varying, 'closing'::character varying])::text[])))");
        $this->replaceCheck('cash_transactions', 'chk_cash_transactions_amount', "CHECK (((amount <> (0)::numeric) OR ((transaction_type)::text = ANY ((ARRAY['opening'::character varying, 'closing'::character varying])::text[]))))");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        $this->replaceCheck('orders', 'chk_orders_status', "CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'confirmed'::character varying, 'processing'::character varying, 'shipped'::character varying, 'delivered'::character varying, 'cancelled'::character varying])::text[])))");
        $this->replaceCheck('payments', 'chk_payments_method', "CHECK (((payment_method)::text = ANY ((ARRAY['cash'::character varying, 'card'::character varying, 'online'::character varying])::text[])))");
        $this->replaceCheck('cash_transactions', 'chk_cash_transactions_type', "CHECK (((transaction_type)::text = ANY ((ARRAY['sale'::character varying, 'refund'::character varying, 'expense'::character varying, 'income'::character varying, 'cash_in'::character varying, 'cash_out'::character varying])::text[])))");
        $this->replaceCheck('cash_transactions', 'chk_cash_transactions_amount', 'CHECK ((amount > (0)::numeric))');
    }

    private function replaceCheck(string $table, string $name, string $definition): void
    {
        if (! Schema::hasTable($table) || ! $this->hasConstraint($table, $name)) {
            return;
        }

        DB::statement('ALTER TABLE '.$table.' DROP CONSTRAINT '.$name);
        DB::statement('ALTER TABLE '.$table.' ADD CONSTRAINT '.$name.' '.$definition);
    }

    private function hasConstraint(string $table, string $name): bool
    {
        return DB::selectOne(
            'select 1 as present from pg_constraint where conname = ? and conrelid = ?::regclass',
            [$name, $table],
        ) !== null;
    }
};
