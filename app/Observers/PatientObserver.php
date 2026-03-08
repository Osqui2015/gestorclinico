<?php

namespace App\Observers;

use App\Models\Patient;
use App\Models\PatientAccount;

class PatientObserver
{
  /**
   * Handle the Patient "created" event.
   */
  public function created(Patient $patient): void
  {
    // Crear automáticamente cuenta corriente para el paciente
    PatientAccount::create([
      'patient_id' => $patient->id,
      'balance' => 0,
      'status' => 'active',
      'payment_status' => 'current',
    ]);
  }

  /**
   * Handle the Patient "updated" event.
   */
  public function updated(Patient $patient): void
  {
    //
  }

  /**
   * Handle the Patient "deleted" event.
   */
  public function deleted(Patient $patient): void
  {
    // Soft delete de la cuenta también
    if ($patient->account) {
      $patient->account->delete();
    }
  }

  /**
   * Handle the Patient "restored" event.
   */
  public function restored(Patient $patient): void
  {
    // Restaurar la cuenta also
    if ($patient->account) {
      $patient->account->restore();
    }
  }
}
