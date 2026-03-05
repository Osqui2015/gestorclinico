<?php

namespace App\Observers;

use App\Models\Audit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditableObserver
{
  protected function createAudit($model, string $event)
  {
    try {
      Audit::create([
        'auditable_type' => get_class($model),
        'auditable_id' => $model->getKey(),
        'user_id' => Auth::id(),
        'event' => $event,
        'old_values' => $model->getOriginal(),
        'new_values' => $model->getAttributes(),
        'ip_address' => Request::ip(),
        'user_agent' => Request::header('User-Agent'),
      ]);
    } catch (\Throwable $e) {
      // Swallow to avoid breaking app flow if auditing fails
    }
  }

  public function created($model)
  {
    $this->createAudit($model, 'created');
  }

  public function updated($model)
  {
    $this->createAudit($model, 'updated');
  }

  public function deleted($model)
  {
    $this->createAudit($model, 'deleted');
  }

  public function restored($model)
  {
    $this->createAudit($model, 'restored');
  }
}
