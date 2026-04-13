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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->string('category');
            $table->float('amount')->default(0);
            $table->dateTime('expense_date');
            $table->string('paid_with');
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->enum('document_type', ['FCF', 'CCF'])->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('supplier_nrc')->nullable();
            $table->float('iva_amount')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
