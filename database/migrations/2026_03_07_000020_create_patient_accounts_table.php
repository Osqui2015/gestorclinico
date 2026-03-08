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
    Schema::create('patient_accounts', function (Blueprint $table) {
      $table->id();
      $table->foreignId('patient_id')->unique()->constrained('patients')->onDelete('cascade');

      // Saldo actual
      $table->decimal('balance', 12, 2)->default(0); // Positivo = acreedor, Negativo = deudor
      $table->decimal('total_charged', 12, 2)->default(0); // Total facturado
      $table->decimal('total_paid', 12, 2)->default(0); // Total pagado
      $table->decimal('total_credits', 12, 2)->default(0); // Créditos otorgados

      // Estado de cuenta
      $table->enum('status', ['active', 'suspended', 'blocked'])->default('active');

      // Control de pagos
      $table->enum('payment_status', ['current', 'overdue', 'in_arrears'])->default('current');
      $table->date('last_payment_date')->nullable();
      $table->integer('days_overdue')->default(0);

      // Intereses
      $table->decimal('accrued_interest', 12, 2)->default(0); // Intereses devengados
      $table->decimal('interest_rate', 5, 2)->default(0); // Tasa de interés mensual

      $table->timestamps();
      $table->softDeletes();

      // Índices
      $table->index('patient_id');
      $table->index('status');
      $table->index('payment_status');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('patient_accounts');
  }
};
