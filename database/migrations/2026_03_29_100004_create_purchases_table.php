<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->unsignedInteger('amount_cents');
            $table->timestamp('paid_at');
            $table->timestamp('expires_at')->nullable();
            $table->string('gateway', 32)->default('fake');
            $table->string('external_payment_id')->nullable()->unique();
            $table->timestamps();

            $table->index(['user_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
