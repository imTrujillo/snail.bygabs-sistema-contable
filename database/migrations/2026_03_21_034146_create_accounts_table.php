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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->enum('type', ['Activo', 'Pasivo', 'Patrimonio', 'Ingreso', 'Costo', 'Gasto']);
            $table->enum('subtype', ['Corriente', 'No Corriente', 'Operativo', 'Administrativo', 'Venta', 'Financiero', 'No Operativo']);
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_group');
            $table->boolean('is_default');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
