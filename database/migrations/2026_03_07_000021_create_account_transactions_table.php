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
    Schema::create('account_transactions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('patient_account_id')->constrained('patient_accounts')->onDelete('cascade');
      $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

      // Tipo de transacción
      $table->enum('type', [
        'charge',           // Cobro (consulta, estudio, internación)
        'payment',          // Pago
        'credit',           // Crédito otorgado
        'write_off',        // Condonación
        'interest',         // Intereses
        'adjustment',       // Ajuste
        'refund'           // Devolución
      ]);

      $table->string('concept'); // Descripción: "Consulta Dr. García", "Pago en efectivo", etc.
      $table->text('description')->nullable();

      // Montos
      $table->decimal('amount', 12, 2);
      $table->decimal('balance_after', 12, 2); // Saldo después de la transacción

      // Referencias
      $table->string('reference_type')->nullable(); // appointment, invoice, hospitalization
      $table->unsignedBigInteger('reference_id')->nullable(); // ID del appointment/invoice/etc

      // Comprobante
      $table->string('voucher_number')->nullable(); // Número de recibo, factura, etc.
      $table->date('transaction_date');

      // Método de pago (si es pago)
      $table->enum('payment_method', [
        'cash',            // Efectivo
        'check',           // Cheque
        'transfer',        // Transferencia
        'credit_card',     // Tarjeta de crédito
        'debit_card',      // Tarjeta de débito
        'promissory_note', // Pagaré
        'credit',          // Crédito hospitalario
        'insurance',       // Obra social
        'other'
      ])->nullable();

      // Información adicional
      $table->text('notes')->nullable();

      $table->timestamps();
      $table->softDeletes();

      // Índices
      $table->index('patient_account_id');
      $table->index('created_by');
      $table->index('type');
      $table->index('transaction_date');
      $table->index(['reference_type', 'reference_id']);
      $table->index(['patient_account_id', 'transaction_date']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('account_transactions');
  }
};
