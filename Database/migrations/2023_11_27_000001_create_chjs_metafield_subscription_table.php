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

        Schema::create('chjs_metafield_subscription', function (Blueprint $table) {
            $table->foreignUuid('workspace_id')
                ->constrained('chjs_subscriptions', 'workspace_id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('metafield_id')
                ->constrained('chjs_metafields', 'id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->primary(['workspace_id', 'metafield_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chjs_metafield_subscription');
    }
};
