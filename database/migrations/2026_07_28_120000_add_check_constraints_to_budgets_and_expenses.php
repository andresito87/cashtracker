<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enforce non-negative amounts at database level via SQL CHECK constraints
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE budgets ADD CONSTRAINT budgets_amount_check CHECK (amount >= 0)');
            DB::statement('ALTER TABLE expenses ADD CONSTRAINT expenses_amount_check CHECK (amount >= 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE budgets DROP CONSTRAINT IF EXISTS budgets_amount_check');
            DB::statement('ALTER TABLE expenses DROP CONSTRAINT IF EXISTS expenses_amount_check');
        }
    }
};
