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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // quien la procesó
            $table->date('pay_date');
            $table->enum('period_type', ['Semanal', 'Quincenal', 'Mensual']);
            $table->decimal('total_gross', 10, 2)->default(0);   // salario bruto total
            $table->decimal('total_isss', 10, 2)->default(0);    // descuento ISSS empleado
            $table->decimal('total_afp', 10, 2)->default(0);     // descuento AFP empleado
            $table->decimal('total_renta', 10, 2)->default(0);   // retención ISR
            $table->decimal('total_net', 10, 2)->default(0);     // a pagar
            $table->timestamps();
        });
        Schema::create('payroll_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('gross_salary', 10, 2);
            $table->decimal('isss_deduction', 10, 2)->default(0);   // 3% empleado
            $table->decimal('afp_deduction', 10, 2)->default(0);    // 7.25% empleado
            $table->decimal('renta_deduction', 10, 2)->default(0);  // ISR según tabla
            $table->decimal('net_salary', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('payroll_lines');
    }
};
