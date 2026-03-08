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
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->string('bed_number', 20); // Número de cama en la habitación
            $table->enum('status', [
                'available',        // Disponible y limpia
                'occupied',         // Ocupada
                'pending_cleaning', // Libre pero necesita limpieza
                'cleaning',         // En proceso de limpieza
                'maintenance'       // En mantenimiento
            ])->default('available');
            $table->enum('bed_type', [
                'standard',         // Cama estándar
                'intensive_care',   // Cuidados intensivos
                'isolation',        // Aislamiento
                'pediatric',        // Pediátrica
                'psychiatric'       // Psiquiátrica
            ])->default('standard');
            $table->text('observations')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Índices
            $table->unique(['room_id', 'bed_number'], 'bed_room_number_unique');
            $table->index('status');
            $table->index(['status', 'bed_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};
