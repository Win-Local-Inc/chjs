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
        if( app()->runningUnitTests() &&  (!Schema::hasTable('workspaces')) )
        {
            Schema::create('workspaces', function (Blueprint $table) {
                $table->uuid('workspace_id')->primary();
                $table->string('workspace_name');
                $table->string('owner_id')->nullable();
                $table->timestamps();
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('workspaces')) {
            Schema::dropIfExists('workspaces');
        }
    }
};
