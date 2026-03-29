<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('initial_balance', 15, 2)->default(0);
            $table->string('currency', 3)->default('BRL');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_accounts');
    }
};
