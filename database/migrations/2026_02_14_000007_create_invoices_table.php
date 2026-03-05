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
    Schema::create('invoices', function (Blueprint $table) {
      $table->id();
      $table->string('invoice_number')->unique();
      $table->foreignId('patient_id')->constrained()->onDelete('restrict');
      $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
      $table->foreignId('health_insurance_id')->nullable()->constrained()->onDelete('set null');
      $table->date('invoice_date');
      $table->decimal('subtotal', 10, 2);
      $table->decimal('discount', 10, 2)->default(0);
      $table->decimal('insurance_coverage', 10, 2)->default(0);
      $table->decimal('total', 10, 2);
      $table->enum('status', ['pending', 'paid', 'partially_paid', 'cancelled'])->default('pending');
      $table->enum('payment_method', ['cash', 'card', 'transfer', 'insurance', 'other'])->nullable();
      $table->timestamp('paid_at')->nullable();
      $table->text('notes')->nullable();
      $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
      $table->timestamps();

      $table->index(['patient_id', 'invoice_date']);
      $table->index(['status', 'invoice_date']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('invoices');
  }
};
