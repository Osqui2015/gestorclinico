<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Roles gestionables desde administración.
     */
    protected $manageableRoles = [
        'doctor' => '👨‍⚕️ Médico',
        'secretary' => '🗂️ Secretaría de Turnos',
        'admin' => '🔐 Administrador',
        'operating_room_manager' => '🏥 Encargado de Quirófano',
        'pharmacy' => '💊 Farmacia',
        'nurse' => '🩺 Enfermería',
        'emergency' => '🚑 Guardia / Emergencias',
        'accountant' => '💼 Contabilidad',
        'maintenance' => '🔧 Mantenimiento',
        'paramedic' => '🚐 Paramédico / Ambulancia',
    ];

    /**
     * Especialidades médicas disponibles
     */
    protected $specialties = [
        'Cardiología',
        'Dermatología',
        'Endocrinología',
        'Gastroenterología',
        'Geriatría',
        'Ginecología',
        'Hematología',
        'Infectología',
        'Neumología',
        'Neurología',
        'Oftalmología',
        'Oncología',
        'Otorrinolaringología',
        'Pediatría',
        'Psiquiatría',
        'Reumatología',
        'Traumatología',
        'Urología',
        'Medicina General',
        'Medicina Interna',
    ];

    /**
     * Módulos disponibles del sistema
     */
    protected $availableModules = [
        [
            'id' => 'patients',
            'label' => 'Pacientes',
            'icon' => '👥',
            'description' => 'Ver y gestionar pacientes',
        ],
        [
            'id' => 'appointments',
            'label' => 'Citas',
            'icon' => '📅',
            'description' => 'Gestionar citas médicas',
        ],
        [
            'id' => 'calendar',
            'label' => 'Calendario',
            'icon' => '📆',
            'description' => 'Vista de calendario de citas',
        ],
        [
            'id' => 'reports',
            'label' => 'Reportes',
            'icon' => '📊',
            'description' => 'Ver reportes y estadísticas',
        ],
        [
            'id' => 'schedules',
            'label' => 'Horarios',
            'icon' => '🕐',
            'description' => 'Gestionar horarios médicos',
        ],
        [
            'id' => 'pharmacy_requests',
            'label' => 'Solicitudes Farmacia',
            'icon' => '💊',
            'description' => 'Solicitudes a farmacia',
        ],
        [
            'id' => 'operations',
            'label' => 'Quirófanos',
            'icon' => '🏥',
            'description' => 'Gestionar operaciones quirúrgicas',
        ],
        [
            'id' => 'hospitalizations',
            'label' => 'Internación',
            'icon' => '🛏️',
            'description' => 'Gestionar internaciones',
        ],
        [
            'id' => 'pre_admissions',
            'label' => 'Pre-Internación',
            'icon' => '🟢',
            'description' => 'Gestionar pre-internaciones',
        ],
        [
            'id' => 'emergency',
            'label' => 'Emergencias',
            'icon' => '🚑',
            'description' => 'Módulo de guardia/emergencias',
        ],
        [
            'id' => 'accounting',
            'label' => 'Contabilidad',
            'icon' => '💼',
            'description' => 'Gestión contable y financiera',
        ],
        [
            'id' => 'maintenance',
            'label' => 'Mantenimiento',
            'icon' => '🔧',
            'description' => 'Gestión de mantenimiento',
        ],
        [
            'id' => 'paramedic',
            'label' => 'Paramédicos',
            'icon' => '🚐',
            'description' => 'Gestión de traslados',
        ],
    ];

    /**
     * Display users managed by admin.
     */
    public function index()
    {
        $users = User::whereIn('role', array_keys($this->manageableRoles))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'specialties' => $this->specialties,
        ]);
    }

    /**
     * Show form to create new user
     */
    public function create()
    {
        return Inertia::render('Admin/Users/Create', [
            'specialties' => $this->specialties,
            'availableModules' => $this->availableModules,
            'availableRoles' => $this->getAvailableRoles(),
        ]);
    }

    /**
     * Store new user.
     */
    public function store(Request $request)
    {
        $moduleIds = array_column($this->availableModules, 'id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dni' => 'required|string|unique:users|max:20',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'specialty' => 'nullable|string|max:255|required_if:role,doctor',
            'license_number' => 'nullable|string|max:100|required_if:role,doctor',
            'professional_id' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role' => 'required|in:' . implode(',', array_keys($this->manageableRoles)),
            'allowed_modules' => 'nullable|array',
            'allowed_modules.*' => 'string|in:' . implode(',', $moduleIds),
        ]);

        User::create(array_merge($validated, [
            'password' => Hash::make($validated['password']),
        ]));

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado exitosamente');
    }

    /**
     * Edit user.
     */
    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'availableModules' => $this->availableModules,
            'availableRoles' => $this->getAvailableRoles(),
            'user' => $user,
            'specialties' => $this->specialties,
        ]);
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user)
    {
        $moduleIds = array_column($this->availableModules, 'id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dni' => 'required|string|unique:users,dni,' . $user->id . '|max:20',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'specialty' => 'nullable|string|max:255|required_if:role,doctor',
            'license_number' => 'nullable|string|max:100|required_if:role,doctor',
            'professional_id' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'allowed_modules' => 'nullable|array',
            'allowed_modules.*' => 'string|in:' . implode(',', $moduleIds),
            'role' => 'required|in:' . implode(',', array_keys($this->manageableRoles)),
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado exitosamente');
    }

    /**
     * Delete user.
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->back()
                ->with('error', 'No puedes eliminar tu propia cuenta');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado exitosamente');
    }

    /**
     * Role options consumidas por el frontend.
     *
     * @return array<int, array{value: string, label: string}>
     */
    protected function getAvailableRoles(): array
    {
        $roles = [];

        foreach ($this->manageableRoles as $value => $label) {
            $roles[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $roles;
    }
}
