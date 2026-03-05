<?php

namespace App\Observers;

use App\Models\Prescription;
use App\Services\AuditService;
use Illuminate\Support\Facades\Log;

class PrescriptionObserver
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Handle the Prescription "created" event.
     */
    public function created(Prescription $prescription): void
    {
        try {
            $this->auditService->logCreate(
                'Prescription',
                $prescription->id,
                $prescription->getAttributes(),
                $prescription->cuir
            );

            Log::info('Auditoría registrada: Receta creada', [
                'prescription_id' => $prescription->id,
                'cuir' => $prescription->cuir,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al registrar auditoría de creación', [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Prescription "updated" event.
     */
    public function updated(Prescription $prescription): void
    {
        try {
            $changes = $prescription->getChanges();

            // Solo registrar si hay cambios reales
            if (count($changes) > 0) {
                $this->auditService->logUpdate(
                    'Prescription',
                    $prescription->id,
                    $prescription->getOriginal(),
                    $prescription->getAttributes(),
                    $prescription->cuir
                );

                Log::info('Auditoría registrada: Receta actualizada', [
                    'prescription_id' => $prescription->id,
                    'cuir' => $prescription->cuir,
                    'changes' => array_keys($changes),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error al registrar auditoría de actualización', [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Prescription "deleted" event.
     */
    public function deleted(Prescription $prescription): void
    {
        try {
            // Usar SoftDelete en realidad, así que esto se llama cuando está en papelera
            $this->auditService->logDelete(
                'Prescription',
                $prescription->id,
                $prescription->getAttributes(),
                $prescription->cuir
            );

            Log::info('Auditoría registrada: Receta eliminada (soft delete)', [
                'prescription_id' => $prescription->id,
                'cuir' => $prescription->cuir,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al registrar auditoría de eliminación', [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Prescription "restored" event.
     */
    public function restored(Prescription $prescription): void
    {
        try {
            $this->auditService->log(
                'restored',
                'Prescription',
                $prescription->id,
                'Receta restaurada desde papelera',
                null,
                null,
                $prescription->cuir
            );

            Log::info('Auditoría registrada: Receta restaurada', [
                'prescription_id' => $prescription->id,
                'cuir' => $prescription->cuir,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al registrar auditoría de restauración', [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Prescription "force deleted" event.
     */
    public function forceDeleted(Prescription $prescription): void
    {
        try {
            $this->auditService->log(
                'permanently_deleted',
                'Prescription',
                $prescription->id,
                'Receta eliminada permanentemente (requiere investigación)',
                $prescription->getAttributes(),
                null,
                $prescription->cuir
            );

            Log::warning('Auditoría registrada: Receta eliminada permanentemente', [
                'prescription_id' => $prescription->id,
                'cuir' => $prescription->cuir,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al registrar auditoría de eliminación permanente', [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
