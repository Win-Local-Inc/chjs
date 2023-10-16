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
            $table->id('subscription_id');
            $table->foreignUuid('workspace_id')->constrained('workspaces', 'workspace_id');
            $table->foreignUuid('user_id')->constrained('users', 'user_id');

            // [PROMO, SOLO,Entrepreneur , Franchisor, Distributor]
            $table->foreignId('product_id')->constrained('chjs_products', 'product_id')->cascadeOnDelete();
            $table->string('product_handle', 52);

            // the main subscribed plan [ sharecard, sharecard pro, etc..]
            //            $table->foreignId('component_id')->nullable()->constrained('chjs_component', 'component_id');
            $table->string('component_handle')->nullable(); // leave it for now, later maybe mysql trigger

            //            $table->string('product_price_handle', 52);
            $table->string('status', 18)->default(SubscriptionStatus::Active->value);
            $table->string('payment_collection_method', 10)->default(PaymentCollectionMethod::Automatic->value);
            $table->enum('subscription_interval', SubscriptionInterval::values())->default(SubscriptionInterval::Month->value);
            //            $table->unsignedMediumInteger('subscription_price_in_cents')->default(0);//total revenue
            $table->unsignedMediumInteger('total_revenue_in_cents')->default(0);
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
