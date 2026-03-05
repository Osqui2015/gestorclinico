<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ObrasSocialesService;

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🔍 Probando búsqueda de Obras Sociales\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$service = new ObrasSocialesService();

// Test 1: Buscar "Sancor"
echo "📋 Búsqueda: 'Sancor'\n";
$results = $service->search('Sancor', 5);
echo "   Total encontrado: " . count($results) . "\n";
foreach ($results as $os) {
  echo "   - {$os['name']} (Código: {$os['code']})\n";
}
echo "\n";

// Test 2: Buscar "OSDE"
echo "📋 Búsqueda: 'OSDE'\n";
$results = $service->search('OSDE', 5);
echo "   Total encontrado: " . count($results) . "\n";
foreach ($results as $os) {
  echo "   - {$os['name']} (Código: {$os['code']})\n";
}
echo "\n";

// Test 3: Buscar por código
echo "📋 Búsqueda: '208'\n";
$results = $service->search('208', 5);
echo "   Total encontrado: " . count($results) . "\n";
foreach ($results as $os) {
  echo "   - {$os['name']} (Código: {$os['code']})\n";
}
echo "\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Test completado\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
