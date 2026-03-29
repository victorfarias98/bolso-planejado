<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $freeId = DB::table('plans')->where('slug', 'free')->value('id');
        if ($freeId === null) {
            return;
        }

        DB::table('users')->whereNull('plan_id')->update(['plan_id' => $freeId]);
    }

    public function down(): void
    {
        //
    }
};
