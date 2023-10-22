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
        Schema::create('chjs_components', function (Blueprint $table) {
            $table->id('component_id');
            $table->string('component_handle', 52)->unique();
            $table->string('component_name', 52);
            $table->string('component_entry', 21)->index()->nullable();
            $table->string('component_unit', 12);
            $table->string('component_type', 32);
            $table->boolean('component_is_active')->default(IsActive::Active->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chjs_components');
    }
};
