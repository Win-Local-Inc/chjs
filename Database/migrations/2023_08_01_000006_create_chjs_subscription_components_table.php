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
        Schema::create('chjs_subscription_components', function (Blueprint $table) {
            $table->primary(['subscription_id', 'component_id']);
            $table->foreignId('subscription_id')->constrained('chjs_subscriptions', 'subscription_id')->cascadeOnDelete();
            $table->foreignId('component_id')->constrained('chjs_components', 'component_id')->cascadeOnDelete();
            $table->string('component_handle', 52)->index();
            $table->string('component_price_handle', 52)->index();
            $table->foreignId('component_price_id')->index();
            $table->unsignedMediumInteger('subscription_component_price')->default(0);
            $table->unsignedMediumInteger('subscription_component_quantity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chjs_subscription_components');
    }
};
