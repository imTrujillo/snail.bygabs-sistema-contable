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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_document_id')->nullable()->constrained()->nullOnDelete();
            $table->date('purchase_date');
            $table->float('exempt_amount')->default(0);
            $table->float('non_taxable_amount')->default(0);
            $table->float('taxable_amount')->default(0);
            $table->float('credit_fiscal')->default(0);
            $table->float('total_amount')->default(0);
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('document_number')->nullable(); // ✅ faltaba
            $table->string('notes')->nullable();           // ✅ nullable
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
