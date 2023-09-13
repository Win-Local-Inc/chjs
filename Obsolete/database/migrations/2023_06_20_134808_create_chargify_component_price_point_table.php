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
        Schema::create('chargify_component_price_points', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();

            $table->foreignId('chargify_component_id')
                ->references('id')
                ->on('chargify_components')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->string('name', 100)->index();
            $table->string('handle', 100)->nullable()->index();
            $table->string('type', 10)->default('catalog')->collation('utf8_bin')->index();
            $table->string('pricing_scheme', 20)->collation('utf8_bin');
            $table->json('prices')->nullable();

            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chargify_component_price_points');
    }
};
