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
        Schema::create('chargify_coupons', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();

            $table->foreignId('chargify_product_family_id')
                ->references('id')
                ->on('chargify_product_families')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->string('name', 100)->index();
            $table->string('code', 100)->collation('utf8_bin')->index();
            $table->boolean('stackable')->default(false);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('amount_in_cents')->nullable();
            $table->decimal('percentage', 7, 4, true)->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chargify_coupons');
    }
};
