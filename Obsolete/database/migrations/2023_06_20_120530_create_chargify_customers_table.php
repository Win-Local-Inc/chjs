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
        Schema::create('chargify_customers', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();

            $table->uuid('user_id')->index();

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->unsignedBigInteger('parent_id')
                ->index()
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chargify_customers');
    }
};
