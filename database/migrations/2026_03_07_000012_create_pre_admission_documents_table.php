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
    Schema::create('pre_admission_documents', function (Blueprint $table) {
      $table->id();
      $table->foreignId('pre_admission_id')->constrained('pre_admissions')->onDelete('cascade');
      $table->foreignId('required_document_id')->constrained('required_documents')->onDelete('cascade');
      $table->enum('status', [
        'pending',
        'uploaded',
        'verified',
        'rejected',
        'not_applicable'
      ])->default('pending');

      $table->string('file_path')->nullable()->comment('Ruta del archivo subido');
      $table->string('original_filename')->nullable();
      $table->unsignedBigInteger('file_size')->nullable()->comment('Tamaño en bytes');
      $table->timestamp('uploaded_at')->nullable();
      $table->timestamp('verified_at')->nullable();
      $table->text('verification_notes')->nullable();
      $table->text('rejection_reason')->nullable();

      $table->timestamps();
      $table->softDeletes();

      $table->unique(['pre_admission_id', 'required_document_id'], 'pad_pre_req_unique');
      $table->index('pre_admission_id');
      $table->index('status');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('pre_admission_documents');
  }
};
