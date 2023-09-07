<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chargify_subscription_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignId('chargify_customer_id')
                ->constrained(
                    table: 'chargify_customers',
                    indexName: 'chargify_subscription_groups_to_chargify_customer_foreign'
                )
                ->restrictOnUpdate()
                ->restrictOnDelete();

            /**
             * No Foreign key as chargify_subscriptions has restrict connection to this table
             */
            $table->unsignedBigInteger('primary_subscription_id');

            $table->string('state', 20)->collation('utf8_bin')->default('active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chargify_subscription_groups');
    }
};
