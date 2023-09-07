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
        Schema::create('chargify_product_price_points', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();

            $table->foreignId('chargify_product_id')
                ->references('id')
                ->on('chargify_products')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->string('name', 100)->index();
            $table->string('handle', 100)->nullable()->collation('utf8_bin')->index();
            $table->string('type', 10)->default('catalog')->collation('utf8_bin')->index();
            $table->unsignedBigInteger('price_in_cents');
            $table->unsignedInteger('interval');
            $table->string('interval_unit', 10)->collation('utf8_bin');
            $table->unsignedBigInteger('trial_price_in_cents')->nullable();
            $table->unsignedInteger('trial_interval')->nullable();
            $table->string('trial_interval_unit', 10)->collation('utf8_bin')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chargify_product_price_points');
    }
};
