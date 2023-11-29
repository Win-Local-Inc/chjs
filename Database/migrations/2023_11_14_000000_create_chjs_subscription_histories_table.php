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

        Schema::create('chjs_subscription_histories', function (Blueprint $table) {
            $table->id('id');
            $table->uuid('workspace_id');
            $table->unsignedBigInteger('subscription_id');
            $table->string('action', 100);
            $table->string('status', 100);
            $table->json('data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chjs_subscription_histories');
    }
};
