<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('debts', function (Blueprint $table) {
            $table->date('agreement_formalized_on')->nullable()->after('agreement_date');
            $table->date('agreement_end_on')->nullable()->after('agreement_formalized_on');
        });

        DB::table('debts')
            ->whereNotNull('agreement_date')
            ->whereNull('agreement_formalized_on')
            ->update([
                'agreement_formalized_on' => DB::raw('agreement_date'),
            ]);
    }

    public function down(): void
    {
        Schema::table('debts', function (Blueprint $table) {
            $table->dropColumn(['agreement_formalized_on', 'agreement_end_on']);
        });
    }
};
