<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('chjs_metafields', function (Blueprint $table) {
            $table->id('id');
            $table->string('key', 20)->index();
            $table->string('value');
            $table->string('sha1_hash', 40)->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chjs_metafields');
    }
};
