<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'model',
        'model_id',
        'action',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'method',
        'url',
        'cuir',
        'renapdis_relevant',
    ];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
        'renapdis_relevant' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para filtrar logs por modelo
     */
    public function scopeForModel($query, string $model, ?int $modelId = null)
    {
        $query->where('model', $model);

        if ($modelId) {
            $query->where('model_id', $modelId);
        }

        return $query;
    }

    /**
     * Scope para filtrar logs de ReNaPDiS
     */
    public function scopeReNaPDiSRelevant($query)
    {
        return $query->where('renapdis_relevant', true);
    }

    /**
     * Scope para filtrar por acción
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope para filtrar por usuario
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope para filtrar por CUIR
     */
    public function scopeByCUIR($query, string $cuir)
    {
        return $query->where('cuir', $cuir);
    }
}
