<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('ingredients')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code', 30)->nullable(); // Internal ingredient code
            $table->enum('type', ['simple', 'composite'])->default('simple');
            $table->string('origin_country', 2)->nullable(); // ISO 3166-1 alpha-2
            $table->string('brand')->nullable();
            $table->string('manufacturer')->nullable();
            $table->enum('category', [
                'bahan_baku',       // Raw material
                'bahan_tambahan',   // Additive
                'bahan_penolong',   // Processing aid
            ])->default('bahan_baku');
            $table->enum('halal_risk_level', [
                'no_risk',      // Naturally halal, no cert needed (egg, water, fresh vegetables)
                'low_risk',     // Likely halal but cert recommended (refined sugar, table salt)
                'medium_risk',  // Processed, cert required (MSG, coloring, preservatives)
                'high_risk',    // Animal-derived or critical, cert mandatory (gelatin, enzymes, emulsifiers)
            ])->default('medium_risk');
            $table->string('sh_number')->nullable();
            $table->date('cert_issue_date')->nullable();
            $table->date('cert_expiry_date')->nullable();
            $table->text('specifications')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};