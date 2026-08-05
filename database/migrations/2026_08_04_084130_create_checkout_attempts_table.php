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
        Schema::create('checkout_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan');
            $table->string('idempotency_key')->nullable();
            $table->string('stripe_session_id')->nullable();
            $table->string('stripe_url')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            // Replay the same idempotency key returns the stored session.
            $table->index(['user_id', 'idempotency_key']);
            // Prevent duplicate live attempts for the same user+plan+session.
            $table->unique(['user_id', 'stripe_session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkout_attempts');
    }
};
