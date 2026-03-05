<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Load environment values from a non-dot file if the host blocks .env uploads.
$envFile = __DIR__ . '/../../env.infinityfree';
if (is_readable($envFile)) {
  $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) {
      continue;
    }
    [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
    $key = trim($key);
    $value = trim($value);
    if ($key === '') {
      continue;
    }
    $value = trim($value, " \"'");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
    putenv($key . '=' . $value);
  }
}

// Determine if the application is in maintenance mode...
// NOTA: En InfinityFree, los archivos de Laravel están 2 niveles arriba de htdocs
if (file_exists($maintenance = __DIR__ . '/../../storage/framework/maintenance.php')) {
  require $maintenance;
}

// Register the Composer autoloader...
// CAMBIADO: ../ por ../../ porque estamos en htdocs y Laravel está 2 niveles arriba
require __DIR__ . '/../../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
// CAMBIADO: ../ por ../../ porque estamos en htdocs y Laravel está 2 niveles arriba
$app = require_once __DIR__ . '/../../bootstrap/app.php';

$app->handleRequest(Request::capture());
