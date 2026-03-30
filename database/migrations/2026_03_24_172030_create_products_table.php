<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 30)->nullable(); // SKU / internal code
            $table->string('brand')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable(); // Product category (makanan, minuman, kosmetik, etc.)
            $table->enum('halal_status', ['compliant', 'at_risk', 'non_compliant', 'pending'])->default('pending');
            $table->unsignedTinyInteger('halal_health_score')->default(0); // 0-100
            $table->date('halal_status_checked_at')->nullable();
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'halal_status']);
            $table->index(['facility_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};