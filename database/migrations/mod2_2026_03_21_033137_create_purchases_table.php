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
            $table->foreignId('tax_document_id')->constrained()->cascadeOnDelete();
            $table->dateTime('purchase_date');
            $table->float('exempt_amount');
            $table->float('non_taxable_amount');
            $table->float('taxable_amount');
            $table->float('credit_fiscal');
            $table->float('total_amount');
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('notes');
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
