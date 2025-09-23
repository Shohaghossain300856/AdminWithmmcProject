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
        Schema::create('subcategories', function (Blueprint $table) {
     $table->id();

            // Header fields (same for all rows in one submission)
            $table->string('memo_no');                // e.g. MEMO-...
            $table->date('date');
            $table->unsignedBigInteger('fund_id');
            $table->unsignedBigInteger('categorie_id');

            // Optional: store grand totals on every row (duplicate but handy)
            $table->decimal('total_budget', 15, 2)->default(0);
            $table->decimal('total_pending', 15, 2)->default(0);
            $table->decimal('total_balance', 15, 2)->default(0);

            // Row fields
            $table->unsignedInteger('sn');            // serial number from UI
            $table->decimal('budget', 15, 2)->default(0);
            $table->decimal('revised', 15, 2)->default(0);
            $table->decimal('disbursement', 15, 2)->default(0);
            $table->decimal('withdrawal', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('expense_pending', 15, 2)->default(0);
            $table->decimal('actual_expense', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('rate', 8, 2)->default(0);

            $table->timestamps();

            // Useful indexes
            $table->index(['memo_no']);
            $table->index(['fund_id', 'categorie_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subcategories');
    }
};
