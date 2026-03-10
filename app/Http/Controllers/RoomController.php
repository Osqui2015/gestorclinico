<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Room;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RoomController extends Controller
{
    use AuthorizesRequests;

    /**
     * Return authenticated user as App\Models\User.
     */
    private function authUser(): \App\Models\User
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user instanceof \App\Models\User) {
            abort(401, 'Usuario no autenticado.');
        }

        return $user;
    }

    /**
     * Mostrar configuración de habitaciones y camas
     */
    public function settings()
    {
        $user = $this->authUser();

        $rooms = Room::with(['beds' => function ($query) {
            $query->orderBy('bed_number');
        }])
            ->withCount([
                'beds as total_beds',
                'activeBeds as active_beds',
                'availableBeds as available_beds',
                'occupiedBeds as occupied_beds'
            ])
            ->orderBy('floor')
            ->orderBy('name')
            ->get();

        $stats = [
            'total_rooms' => Room::count(),
            'active_rooms' => Room::where('is_active', true)->count(),
            'total_beds' => Bed::count(),
            'active_beds' => Bed::where('is_active', true)->count(),
            'available_beds' => Bed::where('status', Bed::STATUS_AVAILABLE)->where('is_active', true)->count(),
            'occupied_beds' => Bed::where('status', Bed::STATUS_OCCUPIED)->count(),
        ];

        return Inertia::render('Hospitalization/RoomsSettings', [
            'rooms' => $rooms,
            'stats' => $stats,
            'roomTypes' => $this->getRoomTypes(),
            'bedTypes' => $this->getBedTypes(),
            'permissions' => [
                'canManage' => $user->isAdmin(),
            ],
        ]);
    }

    /**
     * Crear una nueva habitación
     */
    public function store(Request $request)
    {
        $this->authorize('create', Room::class);

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:rooms,name',
            'code' => 'nullable|string|max:20|unique:rooms,code',
            'room_type' => 'required|string|in:' . implode(',', array_keys($this->getRoomTypes())),
            'floor' => 'required|integer|min:0|max:50',
            'wing' => 'nullable|string|max:50',
            'max_beds' => 'required|integer|min:1|max:20',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['is_active'] = true;

        DB::transaction(function () use ($validated) {
            $room = Room::create($validated);

            // Crear las camas automáticamente
            for ($i = 1; $i <= $validated['max_beds']; $i++) {
                Bed::create([
                    'room_id' => $room->id,
                    'bed_number' => $i,
                    'bed_type' => $this->mapRoomTypeToBedType($validated['room_type']),
                    'status' => Bed::STATUS_AVAILABLE,
                    'is_active' => true,
                ]);
            }
        });

        return redirect()->route('rooms.settings')->with('success', 'Habitación creada exitosamente');
    }

    /**
     * Actualizar una habitación
     */
    public function update(Request $request, Room $room)
    {
        $this->authorize('update', $room);

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:rooms,name,' . $room->id,
            'code' => 'nullable|string|max:20|unique:rooms,code,' . $room->id,
            'room_type' => 'required|string|in:' . implode(',', array_keys($this->getRoomTypes())),
            'floor' => 'required|integer|min:0|max:50',
            'wing' => 'nullable|string|max:50',
            'max_beds' => 'required|integer|min:1|max:20',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($room, $validated) {
            $room->update($validated);

            // Ajustar camas si cambió max_beds
            $currentBedCount = $room->beds()->count();
            $targetBedCount = $validated['max_beds'];

            if ($targetBedCount > $currentBedCount) {
                // Crear camas adicionales
                for ($i = $currentBedCount + 1; $i <= $targetBedCount; $i++) {
                    Bed::create([
                        'room_id' => $room->id,
                        'bed_number' => $i,
                        'bed_type' => $this->mapRoomTypeToBedType($validated['room_type']),
                        'status' => Bed::STATUS_AVAILABLE,
                        'is_active' => true,
                    ]);
                }
            } elseif ($targetBedCount < $currentBedCount) {
                // Desactivar camas excedentes que estén disponibles
                $room->beds()
                    ->where('status', Bed::STATUS_AVAILABLE)
                    ->where('bed_number', '>', $targetBedCount)
                    ->update(['is_active' => false]);
            }
        });

        return back()->with('success', 'Habitación actualizada exitosamente');
    }

    /**
     * Eliminar (desactivar) una habitación
     */
    public function destroy(Room $room)
    {
        $this->authorize('delete', $room);

        // Verificar que no haya camas ocupadas
        $occupiedBedsCount = $room->occupiedBeds()->count();

        if ($occupiedBedsCount > 0) {
            return back()->with('error', 'No se puede desactivar una habitación con camas ocupadas');
        }

        DB::transaction(function () use ($room) {
            // Desactivar todas las camas
            $room->beds()->update(['is_active' => false]);

            // Desactivar habitación
            $room->update(['is_active' => false]);
        });

        return back()->with('success', 'Habitación desactivada exitosamente');
    }

    /**
     * Obtener tipos de habitación
     */
    private function getRoomTypes(): array
    {
        return [
            Room::TYPE_STANDARD => 'Estándar',
            Room::TYPE_INTENSIVE_CARE => 'Terapia Intensiva',
            Room::TYPE_ISOLATION => 'Aislamiento',
            Room::TYPE_PEDIATRIC => 'Pediátrica',
            Room::TYPE_PSYCHIATRIC => 'Psiquiátrica',
            Room::TYPE_RECOVERY => 'Recuperación',
        ];
    }

    /**
     * Obtener tipos de cama
     */
    private function getBedTypes(): array
    {
        return [
            Bed::TYPE_STANDARD => 'Estándar',
            Bed::TYPE_INTENSIVE_CARE => 'Terapia Intensiva',
            Bed::TYPE_ISOLATION => 'Aislamiento',
            Bed::TYPE_PEDIATRIC => 'Pediátrica',
            Bed::TYPE_PSYCHIATRIC => 'Psiquiátrica',
        ];
    }

    /**
     * Mapear tipo de habitación a tipo de cama
     */
    private function mapRoomTypeToBedType(string $roomType): string
    {
        $map = [
            Room::TYPE_STANDARD => Bed::TYPE_STANDARD,
            Room::TYPE_INTENSIVE_CARE => Bed::TYPE_INTENSIVE_CARE,
            Room::TYPE_ISOLATION => Bed::TYPE_ISOLATION,
            Room::TYPE_PEDIATRIC => Bed::TYPE_PEDIATRIC,
            Room::TYPE_PSYCHIATRIC => Bed::TYPE_PSYCHIATRIC,
            Room::TYPE_RECOVERY => Bed::TYPE_STANDARD,
        ];

        return $map[$roomType] ?? Bed::TYPE_STANDARD;
    }
}
