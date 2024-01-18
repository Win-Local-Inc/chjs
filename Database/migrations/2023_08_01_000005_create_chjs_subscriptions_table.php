<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WinLocalInc\Chjs\Enums\PaymentCollectionMethod;
use WinLocalInc\Chjs\Enums\SubscriptionInterval;
use WinLocalInc\Chjs\Enums\SubscriptionStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('chjs_subscriptions', function (Blueprint $table) {
            $table->uuid('workspace_id')->primary();
            $table->foreignUuid('user_id')->constrained('users', 'user_id');
            $table->unsignedBigInteger('subscription_id')->index();

            $table->string('product_price_handle', 52)->index();
            $table->string('product_handle', 52)->index();

            //\WinLocalInc\Chjs\Enums\MainComponent::names();
            $table->string('component')->index()->nullable();
            $table->string('component_handle')->index()->nullable();

            $table->string('status', 18)->default(SubscriptionStatus::Active->value);
            $table->string('payment_collection_method', 10)->default(PaymentCollectionMethod::Automatic->value);
            $table->enum('subscription_interval', SubscriptionInterval::values())->default(SubscriptionInterval::Month->value);
            $table->unsignedBigInteger('total_revenue_in_cents')->default(0);
            $table->unsignedMediumInteger('product_price_in_cents')->default(0);
            //            $table->boolean('self_payment')->default(1);
            $table->timestamp('next_billing_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chjs_subscriptions');
    }
};
