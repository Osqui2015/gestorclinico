<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'specialty',
        'dni',
        'license_number',
        'phone',
        'address',
        'professional_id',
        'allowed_modules',
        // Campos ReNaPDiS
        'matricula_nacional',
        'matricula_provincial',
        'provincia_matricula',
        'consultorio_direccion',
        'consultorio_telefono',
        'cuil',
        'validado_refeps',
        'fecha_validacion_refeps',
        'firma_electronica_habilitada',
        'firma_electronica_metodo',
        'firma_digital_certificado',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'allowed_modules' => 'array',
            'validado_refeps' => 'boolean',
            'fecha_validacion_refeps' => 'datetime',
            'firma_electronica_habilitada' => 'boolean',
        ];
    }

    /**
     * Get all appointments for this doctor
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    /**
     * Get all medical records created by this doctor
     */
    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'doctor_id');
    }

    /**
     * Get doctor schedules
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class, 'doctor_id');
    }

    /**
     * Get doctor exceptions (vacations, etc.)
     */
    public function exceptions(): HasMany
    {
        return $this->hasMany(DoctorException::class, 'doctor_id');
    }

    /**
     * Get active schedules
     */
    public function activeSchedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class, 'doctor_id')->where('is_active', true);
    }

    /**
     * Check if doctor is available on a given date
     */
    public function isAvailableOn(\DateTime $date): bool
    {
        // Check if there's an exception for this day
        $hasException = $this->exceptions()
            ->where('exception_date', $date->format('Y-m-d'))
            ->where('is_all_day', true)
            ->exists();

        return !$hasException;
    }

    /**
     * Check if user is a doctor
     */
    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a secretary
     */
    public function isSecretary(): bool
    {
        return $this->role === 'secretary';
    }

    /**
     * Check if user is pharmacy staff.
     */
    public function isPharmacy(): bool
    {
        return $this->role === 'pharmacy';
    }

    /**
     * Check if user is operating room manager.
     */
    public function isOperatingRoomManager(): bool
    {
        return $this->role === 'operating_room_manager';
    }

    /**
     * Operations assigned as doctor.
     */
    public function operationsAsDoctor(): HasMany
    {
        return $this->hasMany(Operation::class, 'doctor_id');
    }

    /**
     * Operations created by this user.
     */
    public function operationsCreated(): HasMany
    {
        return $this->hasMany(Operation::class, 'created_by');
    }

    /**
     * Operations updated by this user.
     */
    public function operationsUpdated(): HasMany
    {
        return $this->hasMany(Operation::class, 'updated_by');
    }

    /**
     * Pre-admissions assigned to this secretary.
     */
    public function preAdmissionsAssigned(): HasMany
    {
        return $this->hasMany(PreAdmission::class, 'secretary_id');
    }

    /**
     * Hospitalizations where this doctor is responsible.
     */
    public function hospitalizationsAsDoctor(): HasMany
    {
        return $this->hasMany(Hospitalization::class, 'doctor_id');
    }

    /**
     * Check if user is nurse/hospitalization staff.
     */
    public function isNurse(): bool
    {
        return $this->role === 'nurse';
    }

    /**
     * Check if user is maintenance staff.
     */
    public function isMaintenance(): bool
    {
        return $this->role === 'maintenance';
    }

    /**
     * Check if user is paramedic staff.
     */
    public function isParamedic(): bool
    {
        return $this->role === 'paramedic';
    }

    /**
     * Check if user has access to a specific module.
     */
    public function hasModuleAccess(string $module): bool
    {
        // Admin always has access to all modules
        if ($this->isAdmin()) {
            return true;
        }

        // If no modules are specified, allow all (backward compatibility)
        if (empty($this->allowed_modules)) {
            return true;
        }

        return in_array($module, $this->allowed_modules);
    }

    /**
     * Get all allowed modules for this user.
     */
    public function getAllowedModules(): array
    {
        // Admin has access to everything
        if ($this->isAdmin()) {
            return [
                'patients',
                'appointments',
                'calendar',
                'reports',
                'schedules',
                'pharmacy_requests',
                'operations',
                'hospitalizations',
                'pre_admissions',
                'emergency',
                'accounting',
                'maintenance',
                'paramedic',
                'admin',
            ];
        }

        return $this->allowed_modules ?? [];
    }
}
