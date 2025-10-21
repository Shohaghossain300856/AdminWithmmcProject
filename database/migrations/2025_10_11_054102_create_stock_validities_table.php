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

    $table->unsignedInteger('stock_purchase_id');
    $table->unsignedBigInteger('stock_id'); 

    $table->date('warranty_start')->nullable();
    $table->date('warranty_end')->nullable();
    $table->date('validity_start')->nullable();
    $table->date('validity_end')->nullable();

    $table->timestamps();
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
