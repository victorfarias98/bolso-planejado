<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->uuid('recurrence_series_id')->nullable()->after('category_id');
            $table->foreign('recurrence_series_id')
                ->references('id')
                ->on('recurrence_series')
                ->nullOnDelete();
            $table->index(['recurrence_series_id', 'occurred_on']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropForeign(['recurrence_series_id']);
            $table->dropIndex(['recurrence_series_id', 'occurred_on']);
            $table->dropColumn('recurrence_series_id');
        });
    }
};
