<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sjph_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->string('version', 20)->default('1.0');
            $table->enum('status', ['draft', 'in_review', 'approved', 'archived'])->default('draft');

            // The 11 SJPH criteria stored as JSON sections
            // Each can be filled incrementally via the wizard
            $table->text('kebijakan_halal')->nullable();           // 1. Halal Policy
            $table->text('tim_manajemen_halal')->nullable();       // 2. Halal Management Team
            $table->text('pelatihan_edukasi')->nullable();         // 3. Training & Education
            $table->text('bahan')->nullable();                     // 4. Materials
            $table->text('produk')->nullable();                    // 5. Products
            $table->text('fasilitas_produksi')->nullable();        // 6. Production Facilities
            $table->text('prosedur_aktivitas_kritis')->nullable(); // 7. Critical Activity Procedures
            $table->text('kemampuan_telusur')->nullable();         // 8. Traceability
            $table->text('penanganan_produk_tidak_halal')->nullable(); // 9. Non-Halal Product Handling
            $table->text('audit_internal')->nullable();            // 10. Internal Audit
            $table->text('kaji_ulang_manajemen')->nullable();      // 11. Management Review

            $table->string('document_path')->nullable(); // Path to generated PDF
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'facility_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sjph_documents');
    }
};