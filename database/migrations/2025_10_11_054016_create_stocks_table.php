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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();

            // 🔹 Fund reference
            $table->foreignId('fund_id')
                ->constrained('funds')
                ->cascadeOnDelete();

            // 🔹 Category reference
            $table->foreignId('category_id')
                ->constrained('catagories') // (তোমার DB তে টেবিলের নাম catagories)
                ->cascadeOnDelete();

            // 🔹 Subcategory (Item) reference
            $table->foreignId('item_id')
                ->constrained('subcategories')
                ->cascadeOnDelete();

            // 🔹 Current stock quantity
            $table->decimal('qty', 15, 2)->default(0)->comment('Current available stock quantity');

            $table->timestamps();

            // 🔹 Unique constraint: prevent duplicate stock rows per fund+category+item
            $table->unique(['fund_id', 'category_id', 'item_id'], 'fund_cat_item_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
