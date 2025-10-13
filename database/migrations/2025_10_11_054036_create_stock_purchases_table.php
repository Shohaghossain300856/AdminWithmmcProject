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
        Schema::create('stock_purchases', function (Blueprint $table) {
            $table->id();

            // Foreign key to stocks
            $table->foreignId('stock_id')
                  ->constrained('stocks')
                  ->cascadeOnDelete();

            // Purchase details
            $table->date('date')->nullable();
            $table->string('ref_no', 100)->nullable();

            // Quantity and pricing
            $table->unsignedInteger('purchase_qty')->default(0)->comment('Purchased quantity');
            $table->decimal('unit_price', 15, 2)->default(0)->comment('Per-unit price');

            $table->timestamps();

            // Optional index for faster reporting
            $table->index(['stock_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_purchases');
    }
};
