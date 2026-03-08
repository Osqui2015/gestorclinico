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
        Schema::create('bed_cleaning_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bed_id')->constrained('beds')->onDelete('cascade');
            $table->foreignId('cleaned_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('started_at')->nullable(); // Inicio de limpieza
            $table->timestamp('completed_at'); // Fin de limpieza
            $table->enum('cleaning_type', [
                'routine',          // Limpieza de rutina
                'deep',             // Limpieza profunda
                'discharge',        // Limpieza después de alta
                'disinfection'      // Desinfección especial
            ])->default('routine');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Índices
            $table->index('bed_id');
            $table->index('completed_at');
            $table->index(['bed_id', 'completed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bed_cleaning_logs');
    }
};
