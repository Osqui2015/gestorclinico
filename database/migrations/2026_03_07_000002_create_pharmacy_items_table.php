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
        Schema::create('pharmacy_items', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del item
            $table->enum('type', ['medication', 'instrument', 'supply']); // Tipo de item
            $table->text('description')->nullable();
            $table->string('code')->unique(); // Código interno
            $table->string('laboratory')->nullable(); // Laboratorio (para medicamentos)
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->integer('current_stock')->default(0);
            $table->integer('minimum_stock')->default(10); // Stock mínimo para alertas
            $table->integer('reorder_point')->default(20); // Punto de reorden
            $table->string('unit_measurement')->default('unidad'); // unidad, caja, frasco, etc.
            $table->date('expiration_date')->nullable(); // Fecha de vencimiento
            $table->string('batch_number')->nullable(); // Número de lote
            $table->boolean('requires_sterilization')->default(false); // Para instrumentos
            $table->date('last_sterilization_date')->nullable();
            $table->date('next_sterilization_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indices
            $table->index('type');
            $table->index('status');
            $table->index('expiration_date');
            $table->index('current_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_items');
    }
};
