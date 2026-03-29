<?php

use App\Models\Category;
use App\Models\CategoryGoal;
use App\Models\Debt;
use App\Models\PlanHistory;
use App\Models\RecurrenceSeries;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->foreignUuid('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        DB::transaction(function (): void {
            $legacy = Category::query()->whereNull('user_id')->get();
            if ($legacy->isEmpty()) {
                return;
            }

            $users = User::query()->get();
            if ($users->isEmpty()) {
                Category::query()->whereNull('user_id')->delete();

                return;
            }

            /** @var array<string, array<string, string>> $map legacy_id => [ user_id => new_category_id ] */
            $map = [];

            foreach ($users as $user) {
                foreach ($legacy as $old) {
                    $new = Category::query()->create([
                        'user_id' => $user->id,
                        'slug' => $old->slug,
                        'name' => $old->name,
                    ]);
                    $map[$old->id][$user->id] = $new->id;
                }
            }

            foreach (Transaction::query()->whereNotNull('category_id')->cursor() as $t) {
                $newId = $map[$t->category_id][$t->user_id] ?? null;
                if ($newId !== null) {
                    $t->category_id = $newId;
                    $t->save();
                }
            }

            foreach (RecurrenceSeries::query()->whereNotNull('category_id')->cursor() as $r) {
                $newId = $map[$r->category_id][$r->user_id] ?? null;
                if ($newId !== null) {
                    $r->category_id = $newId;
                    $r->save();
                }
            }

            foreach (CategoryGoal::query()->cursor() as $g) {
                $newId = $map[$g->category_id][$g->user_id] ?? null;
                if ($newId !== null) {
                    $g->category_id = $newId;
                    $g->save();
                }
            }

            foreach (Debt::query()->whereNotNull('category_id')->cursor() as $d) {
                $newId = $map[$d->category_id][$d->user_id] ?? null;
                if ($newId !== null) {
                    $d->category_id = $newId;
                    $d->save();
                }
            }

            foreach (PlanHistory::query()->whereNotNull('category_id')->cursor() as $p) {
                $newId = $map[$p->category_id][$p->user_id] ?? null;
                if ($newId !== null) {
                    $p->category_id = $newId;
                    $p->save();
                }
            }

            Category::query()->whereNull('user_id')->delete();
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->foreignUuid('user_id')->nullable(false)->change();
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->unique(['user_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'slug']);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->unique('slug');
        });
    }
};
