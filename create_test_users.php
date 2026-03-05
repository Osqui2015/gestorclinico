<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "Creando usuarios de prueba...\n\n";

// Admin
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

// Secretaria
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

// Doctor
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

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🌐 Accede a: http://localhost:8000\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
