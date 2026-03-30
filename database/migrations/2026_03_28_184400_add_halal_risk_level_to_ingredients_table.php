<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->enum('halal_risk_level', [
                'no_risk',      // Naturally halal, no cert needed (egg, water, fresh vegetables)
                'low_risk',     // Likely halal but cert recommended (refined sugar, table salt)
                'medium_risk',  // Processed, cert required (MSG, coloring, preservatives)
                'high_risk',    // Animal-derived or critical, cert mandatory (gelatin, enzymes, emulsifiers)
            ])->default('medium_risk')->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn('halal_risk_level');
        });
    }
};