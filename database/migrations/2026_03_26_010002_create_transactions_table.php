<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('financial_account_id')->constrained('financial_accounts')->cascadeOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('type', 16);
            $table->decimal('amount', 15, 2);
            $table->date('occurred_on');
            $table->string('status', 16);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'occurred_on']);
            $table->index(['financial_account_id', 'occurred_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
