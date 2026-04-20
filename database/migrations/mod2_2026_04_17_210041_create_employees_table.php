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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position')->nullable();         // cargo
            $table->string('dui')->nullable()->unique();    // documento
            $table->string('isss')->nullable();              // ISSS
            $table->string('afp')->nullable();              // AFP
            $table->decimal('base_salary', 10, 2);
            $table->enum('pay_frequency', ['Semanal', 'Quincenal', 'Mensual'])->default('Mensual');
            $table->enum('payment_method', ['Efectivo', 'Transferencia'])->default('Efectivo');
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('hire_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emplloyees');
    }
};
