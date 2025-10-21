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
    $table->unsignedInteger('fund_id');
    $table->unsignedInteger('supplier_id');
    $table->string('memo_no', 64)->nullable();
    $table->date('date');
    $table->unsignedInteger('initial_qty');
    $table->unsignedBigInteger('user_id');
    $table->timestamps();

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
