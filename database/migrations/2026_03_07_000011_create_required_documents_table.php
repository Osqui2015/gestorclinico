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
    Schema::create('required_documents', function (Blueprint $table) {
      $table->id();
      $table->string('name')->comment('Ej: Cédula de Identidad, Consentimiento Informado');
      $table->string('code')->unique()->comment('Código único: DNI, FORM_CONSENT, etc');
      $table->text('description')->nullable();
      $table->enum('applicability', [
        'all_surgeries',
        'by_operation_type',
        'by_insurance',
        'custom'
      ])->default('all_surgeries');
      $table->boolean('is_mandatory')->default(true);
      $table->text('notes')->nullable();
      $table->boolean('requires_upload')->default(true)->comment('Si necesita archivo adjunto');
      $table->enum('status', ['active', 'inactive'])->default('active');
      $table->timestamps();

      $table->index('code');
      $table->index('status');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('required_documents');
  }
};
