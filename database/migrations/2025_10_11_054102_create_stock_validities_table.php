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
        Schema::create('stock_validities', function (Blueprint $table) {
            $table->id();

            // 🔹 Relationship with stocks
            $table->foreignId('stock_id')
                ->constrained('stocks')
                ->cascadeOnDelete();

            // 🔹 Validity period (e.g. product expiry or validity)
            $table->date('validity_start')->nullable()->comment('Product validity start date');
            $table->date('validity_end')->nullable()->comment('Product validity end date');

            // 🔹 Warranty period
            $table->date('warranty_start')->nullable()->comment('Warranty start date');
            $table->date('warranty_end')->nullable()->comment('Warranty end date');

            $table->timestamps();

            // 🔹 Index for faster queries
            $table->index(['stock_id', 'validity_end', 'warranty_end'], 'idx_stock_validity_warranty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_validities');
    }
};
