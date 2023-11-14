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
        Schema::create('chjs_products', function (Blueprint $table) {
            $table->id('product_id');
            $table->string('product_handle', 52)->unique();
            $table->string('product_name', 52);
            $table->boolean('product_is_active')->default(IsActive::Active->value);
            $table->boolean('children_self_payment')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chjs_products');
    }
};
