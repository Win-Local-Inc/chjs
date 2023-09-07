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
        Schema::create('chargify_payment_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();

            $table->foreignId('chargify_customer_id')
                ->references('id')
                ->on('chargify_customers')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->string('masked_card_number', 40)->nullable()->collation('utf8_bin');
            $table->boolean('is_default')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chargify_payment_profiles');
    }
};
