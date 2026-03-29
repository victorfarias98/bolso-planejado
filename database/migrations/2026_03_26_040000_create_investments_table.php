<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 160);
            $table->string('investment_type', 30);
            $table->decimal('current_amount', 14, 2);
            $table->decimal('monthly_contribution', 14, 2)->default(0);
            $table->decimal('monthly_return_rate', 8, 4)->default(0); // percentage (e.g. 0.80 = 0.8%)
            $table->unsignedTinyInteger('contribution_day')->default(5);
            $table->string('currency', 3)->default('BRL');
            $table->decimal('target_amount', 14, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
