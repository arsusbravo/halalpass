<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('halal_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->string('sh_number'); // Sertifikat Halal number
            $table->enum('issuing_body', ['LPH', 'MUI', 'FOREIGN_HCB']);
            $table->string('issuing_body_name')->nullable(); // e.g. "LPPOM MUI", "JAKIM", etc.
            $table->date('issue_date')->nullable();
            $table->date('expiry_date');
            $table->string('document_path')->nullable(); // Storage path to uploaded PDF/image
            $table->string('original_filename')->nullable();
            $table->enum('status', ['valid', 'expiring_soon', 'expired', 'missing'])->default('valid');
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['ingredient_id', 'status']);
            $table->index('expiry_date');
            $table->index('sh_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('halal_certificates');
    }
};