<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected static $table = 'chargify_events';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(self::$table, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('event_name', '100')->index();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(self::$table);
    }
};
