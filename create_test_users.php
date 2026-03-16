<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "Creando usuarios de prueba para todos los roles...\n\n";

// 1. Admin
$admin = User::updateOrCreate(
    ['email' => 'admin@gestor.com'],
    [
        'name' => 'Administrador Sistema',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
        'dni' => '12345678',
    ]
);
echo "✅ Admin creado\n";
echo "   Email: admin@gestor.com\n";
echo "   Pass:  admin123\n\n";

// 2. Doctor
$doctor = User::updateOrCreate(
    ['email' => 'doctor@gestor.com'],
    [
        'name' => 'Dr. Juan Pérez',
        'password' => Hash::make('doctor123'),
        'role' => 'doctor',
        'specialty' => 'Medicina General',
        'dni' => '34567890',
        'phone' => '261-7654321',
        'matricula_nacional' => 'MN123456',
        'matricula_provincial' => 'MP789012',
        'provincia_matricula' => 'Mendoza',
    ]
);
echo "✅ Doctor creado\n";
echo "   Email: doctor@gestor.com\n";
echo "   Pass:  doctor123\n\n";

// 3. Secretaria
$secretary = User::updateOrCreate(
    ['email' => 'secretaria@gestor.com'],
    [
        'name' => 'María González',
        'password' => Hash::make('secretaria123'),
        'role' => 'secretary',
        'dni' => '23456789',
        'phone' => '261-4567890',
    ]
);
echo "✅ Secretaria creada\n";
echo "   Email: secretaria@gestor.com\n";
echo "   Pass:  secretaria123\n\n";

// 4. Farmacia
$pharmacy = User::updateOrCreate(
    ['email' => 'farmacia@gestor.com'],
    [
        'name' => 'Ana Martínez',
        'password' => Hash::make('farmacia123'),
        'role' => 'pharmacy',
        'dni' => '45678901',
        'phone' => '261-5551234',
    ]
);
echo "✅ Farmacia creado\n";
echo "   Email: farmacia@gestor.com\n";
echo "   Pass:  farmacia123\n\n";

// 5. Encargado de Quirófano
$operating = User::updateOrCreate(
    ['email' => 'quirofano@gestor.com'],
    [
        'name' => 'Carlos Ruiz',
        'password' => Hash::make('quirofano123'),
        'role' => 'operating_room_manager',
        'dni' => '56789012',
        'phone' => '261-5552345',
    ]
);
echo "✅ Encargado de Quirófano creado\n";
echo "   Email: quirofano@gestor.com\n";
echo "   Pass:  quirofano123\n\n";

// 6. Enfermera
$nurse = User::updateOrCreate(
    ['email' => 'enfermera@gestor.com'],
    [
        'name' => 'Laura Fernández',
        'password' => Hash::make('enfermera123'),
        'role' => 'nurse',
        'dni' => '67890123',
        'phone' => '261-5553456',
    ]
);
echo "✅ Enfermera creada\n";
echo "   Email: enfermera@gestor.com\n";
echo "   Pass:  enfermera123\n\n";

// 7. Emergencias
$emergency = User::updateOrCreate(
    ['email' => 'emergencia@gestor.com'],
    [
        'name' => 'Dr. Roberto Sánchez',
        'password' => Hash::make('emergencia123'),
        'role' => 'emergency',
        'specialty' => 'Emergencias',
        'dni' => '78901234',
        'phone' => '261-5554567',
    ]
);
echo "✅ Emergencias fue  creado\n";
echo "   Email: emergencia@gestor.com\n";
echo "   Pass:  emergencia123\n\n";

// 8. Contador
$accountant = User::updateOrCreate(
    ['email' => 'contador@gestor.com'],
    [
        'name' => 'Patricia López',
        'password' => Hash::make('contador123'),
        'role' => 'accountant',
        'dni' => '89012345',
        'phone' => '261-5555678',
    ]
);
echo "✅ Contador creado\n";
echo "   Email: contador@gestor.com\n";
echo "   Pass:  contador123\n\n";

// 9. Mantenimiento
$maintenance = User::updateOrCreate(
    ['email' => 'mantenimiento@gestor.com'],
    [
        'name' => 'Miguel Torres',
        'password' => Hash::make('mantenimiento123'),
        'role' => 'maintenance',
        'dni' => '90123456',
        'phone' => '261-5556789',
    ]
);
echo "✅ Mantenimiento creado\n";
echo "   Email: mantenimiento@gestor.com\n";
echo "   Pass:  mantenimiento123\n\n";

// 10. Paramédico
$paramedic = User::updateOrCreate(
    ['email' => 'paramedico@gestor.com'],
    [
        'name' => 'Jorge Ramírez',
        'password' => Hash::make('paramedico123'),
        'role' => 'paramedic',
        'dni' => '01234567',
        'phone' => '261-5557890',
    ]
);
echo "✅ Paramédico creado\n";
echo "   Email: paramedico@gestor.com\n";
echo "   Pass:  paramedico123\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✨ ¡Todos los usuarios han sido creados exitosamente!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🌐 Accede a: http://localhost:8000/login\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
