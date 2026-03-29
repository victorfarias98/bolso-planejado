<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('financial_account_id')->nullable()->constrained('financial_accounts')->nullOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('title');
            $table->string('creditor')->nullable();
            $table->string('debt_type', 32);
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('balance_amount', 15, 2);
            $table->string('currency', 3)->default('BRL');
            $table->string('status', 32);
            $table->date('agreement_date')->nullable();
            $table->boolean('agreement_fulfilled')->default(false);
            $table->unsignedTinyInteger('agreement_day_of_month')->nullable();
            $table->date('agreement_first_due_date')->nullable();
            $table->unsignedInteger('agreement_installment_count')->nullable();
            $table->decimal('agreement_installment_amount', 15, 2)->nullable();
            $table->decimal('agreement_down_payment', 15, 2)->nullable();
            $table->text('agreement_notes')->nullable();
            $table->foreignUuid('recurrence_series_id')->nullable()->constrained('recurrence_series')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
