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
        Schema::create('chargify_subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();

            $table->foreignId('chargify_product_price_point_id')
                ->nullable()
                ->references('id')
                ->on('chargify_product_price_points')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->uuid('chargify_subscription_group_id')
                ->nullable()
                ->index(); 
            $table->foreign('chargify_subscription_group_id')
                ->references('id')
                ->on('chargify_subscription_groups')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->uuid('user_id')
                ->nullable()
                ->index(); 
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->uuid('workspace_id')
                ->nullable()
                ->index(); 
            $table->foreign('workspace_id')
                ->references('workspace_id')
                ->on('workspaces')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->string('state', 20)->collation('utf8_bin');
            $table->unsignedBigInteger('balance_in_cents')->nullable();
            $table->unsignedBigInteger('total_revenue_in_cents')->nullable();
            $table->unsignedBigInteger('product_price_in_cents')->nullable();

            $table->timestamp('current_period_ends_at')->nullable();
            $table->timestamp('trial_ended_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chargify_subscriptions');
    }
};
