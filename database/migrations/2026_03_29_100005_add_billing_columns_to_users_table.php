<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
            $table->foreignUuid('plan_id')->nullable()->after('is_admin')->constrained('plans')->nullOnDelete();
            $table->timestamp('premium_expires_at')->nullable()->after('plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn(['is_admin', 'plan_id', 'premium_expires_at']);
        });
    }
};
