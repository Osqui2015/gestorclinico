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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // Usuario que realizó la acción
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('user_name')->nullable();
            $table->string('user_role')->nullable();

            // Modelo y recurso auditado
            $table->string('model')->nullable(); // Ej: 'Prescription', 'User', 'Patient'
            $table->unsignedBigInteger('model_id')->nullable();

            // Acción realizada
            $table->enum('action', [
                'created',
                'updated',
                'deleted',
                'viewed',
                'downloaded',
                'signed',
                'verified',
                'dispensed',
                'annulled',
                'restored',
                'exported'
            ]);

            // Descripción de la acción
            $table->text('description')->nullable();

            // Datos antes y después (para cambios)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Información de la solicitud
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->string('method', 10)->nullable(); // GET, POST, PUT, DELETE
            $table->text('url')->nullable();

            // Campos específicos para ReNaPDiS
            $table->string('cuir')->nullable();
            $table->boolean('renapdis_relevant')->default(false);

            // Control
            $table->timestamps();
            $table->index('user_id');
            $table->index('model');
            $table->index('model_id');
            $table->index('action');
            $table->index('created_at');
            $table->index('cuir');
            $table->fullText(['description']); // Para búsquedas textuales
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
