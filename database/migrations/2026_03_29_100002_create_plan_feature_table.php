<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_feature', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->foreignUuid('feature_id')->constrained('features')->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('limit_int')->nullable();
            $table->timestamps();

            $table->unique(['plan_id', 'feature_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_feature');
    }
};
