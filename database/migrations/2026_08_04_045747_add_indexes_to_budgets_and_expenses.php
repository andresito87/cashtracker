<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add explicit indexes on the foreign key columns used by the most
     * frequent queries. PostgreSQL (unlike MySQL/InnoDB) does not
     * automatically index the columns of a foreign key constraint.
     */
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index('budget_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['budget_id']);
        });
    }
};
