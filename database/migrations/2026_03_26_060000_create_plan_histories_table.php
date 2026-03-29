<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_histories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('cut_amount', 14, 2)->default(0);
            $table->decimal('extra_investment', 14, 2)->default(0);
            $table->decimal('debt_share_pct', 5, 2)->default(70);
            $table->decimal('simulated_debt_payment', 14, 2)->default(0);
            $table->decimal('simulated_investment_total', 14, 2)->default(0);
            $table->integer('simulated_debt_payoff_months')->nullable();
            $table->string('notes', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_histories');
    }
};
