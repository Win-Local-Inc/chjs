<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WinLocalInc\Chjs\Enums\IsActive;
use WinLocalInc\Chjs\Enums\SubscriptionInterval;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chjs_product_prices', function (Blueprint $table) {
            $table->id('product_price_id');
            $table->foreignId('product_id')->constrained('chjs_products', 'product_id')->cascadeOnDelete();
            $table->string('product_handle', 52);
            $table->string('product_price_handle', 52)->unique();
            $table->string('product_price_name', 52);
            $table->char('product_price_interval', 5)->default(SubscriptionInterval::Month->value);
            $table->unsignedMediumInteger('product_price_in_cents')->default(0);
            $table->boolean('product_price_is_active')->default(IsActive::Active->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chjs_product_prices');
    }
};
