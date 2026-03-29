<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurrence_series', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('financial_account_id')->constrained('financial_accounts')->cascadeOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('type', 16);
            $table->decimal('amount', 15, 2);
            $table->unsignedTinyInteger('day_of_month');
            $table->date('start_on');
            $table->date('end_on')->nullable();
            $table->unsignedInteger('max_occurrences')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurrence_series');
    }
};
