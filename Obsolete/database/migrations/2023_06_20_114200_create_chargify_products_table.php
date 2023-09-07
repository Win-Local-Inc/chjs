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
        Schema::create('chargify_products', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();

            $table->foreignId('chargify_product_family_id')
                ->references('id')
                ->on('chargify_product_families')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->string('name', 100)->index();
            $table->string('handle', 100)->collation('utf8_bin')->index();
            $table->boolean('require_credit_card')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chargify_products');
    }
};
