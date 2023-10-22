<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WinLocalInc\Chjs\Enums\IsActive;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chjs_component_prices', function (Blueprint $table) {
            $table->id('component_price_id');
            $table->foreignId('component_id')->constrained('chjs_components', 'component_id')->cascadeOnDelete();
            $table->string('component_handle', 52);
            $table->string('component_price_handle', 52)->unique();
            $table->string('component_price_name', 52);
            $table->string('component_price_scheme', 24);
            $table->string('component_price_type', 32);
            $table->unsignedMediumInteger('component_price_in_cents')->default(0);
            $table->boolean('component_price_is_active')->default(IsActive::Active->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chjs_component_prices');
    }
};
