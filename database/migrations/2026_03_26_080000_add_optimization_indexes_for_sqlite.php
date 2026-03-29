<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_accounts', function (Blueprint $table): void {
            $table->index(['user_id', 'name'], 'idx_financial_accounts_user_name');
        });

        Schema::table('investments', function (Blueprint $table): void {
            $table->index(['user_id', 'is_active'], 'idx_investments_user_active');
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->index(['user_id', 'financial_account_id', 'status', 'occurred_on'], 'idx_tx_user_acc_status_date');
            $table->index(['user_id', 'recurrence_series_id', 'status', 'occurred_on'], 'idx_tx_user_rec_status_date');
            $table->index(['recurrence_series_id', 'status', 'occurred_on'], 'idx_tx_rec_status_date');
        });

        Schema::table('recurrence_series', function (Blueprint $table): void {
            $table->index(['user_id', 'is_active', 'financial_account_id'], 'idx_recurrence_user_active_acc');
        });
    }

    public function down(): void
    {
        Schema::table('financial_accounts', function (Blueprint $table): void {
            $table->dropIndex('idx_financial_accounts_user_name');
        });

        Schema::table('investments', function (Blueprint $table): void {
            $table->dropIndex('idx_investments_user_active');
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropIndex('idx_tx_user_acc_status_date');
            $table->dropIndex('idx_tx_user_rec_status_date');
            $table->dropIndex('idx_tx_rec_status_date');
        });

        Schema::table('recurrence_series', function (Blueprint $table): void {
            $table->dropIndex('idx_recurrence_user_active_acc');
        });
    }
};

