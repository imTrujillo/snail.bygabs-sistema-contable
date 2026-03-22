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
        Schema::create('tax_documents', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['FCF', 'CCF', 'NC_CCF', 'ND_CCF', 'NC_FCF']);
            $table->string('series');
            $table->integer('correlative_number');
            $table->string('document_number');
            $table->string('issue_date');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('reference_id');
            $table->enum('reference_type', ['sale', 'purchase']);
            $table->float('exempt_amount')->default(0);
            $table->float('non_taxable_amount')->default(0);
            $table->float('taxable_amount')->default(0);
            $table->float('iva_amount')->default(0);
            $table->float('total_amount')->default(0);
            $table->boolean('is_voided')->nullable();
            $table->dateTime('voided_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_documents');
    }
};
