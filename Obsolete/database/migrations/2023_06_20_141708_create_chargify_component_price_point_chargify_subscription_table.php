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
        Schema::create('chargify_component_price_point_chargify_subscription', function (Blueprint $table) {

            $table->foreignId('chargify_component_price_point_id')
                ->constrained(
                    table: 'chargify_component_price_points',
                    indexName: 'chargify_component_price_point_to_subscription_foreign'
                )
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('chargify_subscription_id')
                ->constrained(
                    table: 'chargify_subscriptions',
                    indexName: 'chargify_subscription_to_component_price_point_foreign'
                )
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->primary(['chargify_component_price_point_id', 'chargify_subscription_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chargify_component_price_point_chargify_subscription');
    }
};
