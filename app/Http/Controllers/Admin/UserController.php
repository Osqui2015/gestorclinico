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
     * Display users managed by admin.
     */
    public function index()
    {
        $users = User::whereIn('role', ['doctor', 'admin', 'secretary', 'pharmacy', 'operating_room_manager', 'nurse', 'emergency', 'accountant', 'maintenance', 'paramedic'])
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
        ]);
    }

    /**
     * Store new user.
     */
    public function store(Request $request)
    {
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
            'role' => 'required|in:doctor,admin,secretary,pharmacy,operating_room_manager,nurse,emergency,accountant,maintenance,paramedic',
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
            'user' => $user,
            'specialties' => $this->specialties,
        ]);
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dni' => 'required|string|unique:users,dni,' . $user->id . '|max:20',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'specialty' => 'nullable|string|max:255|required_if:role,doctor',
            'license_number' => 'nullable|string|max:100|required_if:role,doctor',
            'professional_id' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role' => 'required|in:doctor,admin,secretary,pharmacy,operating_room_manager,nurse,emergency,accountant,maintenance,paramedic',
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
}
