<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'currency')) {
                $table->string('currency', 3)->default('EUR')->after('email');
            }
        });

        Schema::table('budgets', function (Blueprint $table) {
            if (Schema::hasColumn('budgets', 'currency')) {
                $table->dropColumn('currency');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            if (! Schema::hasColumn('budgets', 'currency')) {
                $table->string('currency', 3)->default('EUR')->after('amount');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'currency')) {
                $table->dropColumn('currency');
            }
        });
    }
};
