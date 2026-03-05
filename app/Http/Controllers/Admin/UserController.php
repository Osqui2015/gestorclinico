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
     * Display all doctors
     */
    public function index()
    {
        $doctors = User::whereIn('role', ['doctor', 'admin'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Admin/Users/Index', [
            'users' => $doctors,
            'specialties' => $this->specialties,
        ]);
    }

    /**
     * Show form to create new doctor
     */
    public function create()
    {
        return Inertia::render('Admin/Users/Create', [
            'specialties' => $this->specialties,
        ]);
    }

    /**
     * Store new doctor
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dni' => 'required|string|unique:users|max:20',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'specialty' => 'required|string|max:255',
            'license_number' => 'required|string|max:100',
            'professional_id' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role' => 'required|in:doctor,admin',
        ]);

        User::create(array_merge($validated, [
            'password' => Hash::make($validated['password']),
        ]));

        return redirect()->route('admin.users.index')
            ->with('success', 'Doctor creado exitosamente');
    }

    /**
     * Edit doctor
     */
    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'specialties' => $this->specialties,
        ]);
    }

    /**
     * Update doctor
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dni' => 'required|string|unique:users,dni,' . $user->id . '|max:20',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'specialty' => 'required|string|max:255',
            'license_number' => 'required|string|max:100',
            'professional_id' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role' => 'required|in:doctor,admin',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Doctor actualizado exitosamente');
    }

    /**
     * Delete doctor
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->back()
                ->with('error', 'No puedes eliminar tu propia cuenta');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Doctor eliminado exitosamente');
    }
}
