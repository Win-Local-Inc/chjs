<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('users')) {

            Schema::table('users', function (Blueprint $table) {
    //            $table->integer('chargify_id')->nullable()->index();
                if (!Schema::hasColumn('users', 'chargify_id')) {
                    $table->integer('chargify_id')->nullable()->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'chargify_id')) {
                    $table->dropColumn('chargify_id');
                }
            });
        }
    }
};
