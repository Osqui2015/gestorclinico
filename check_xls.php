<?php
$path = 'storage/app/listadoSSSalud.xls';
$lines = file($path, FILE_IGNORE_NEW_LINES);

echo "Total de líneas: " . count($lines) . "\n\n";

// Buscar cabecera
$headerIdx = null;
foreach ($lines as $idx => $line) {
    if (stripos(trim($line), 'rnas') === 0) {
        $headerIdx = $idx;
        echo "Cabecera encontrada en línea $idx\n";
        break;
    }
}

if ($headerIdx !== null) {
    $headerLine = $lines[$headerIdx];
    $columns = str_getcsv($headerLine, "\t");
    echo "\n=== Columnas (total: " . count($columns) . ") ===\n";
    foreach ($columns as $i => $col) {
        echo "Col $i: '" . trim($col) . "'\n";
    }
    
    echo "\n=== Primera fila de datos ===\n";
    if (isset($lines[$headerIdx + 1])) {
        $firstData = str_getcsv($lines[$headerIdx + 1], "\t");
        foreach ($firstData as $i => $val) {
            $colName = isset($columns[$i]) ? trim($columns[$i]) : "col$i";
            echo "$colName: " . substr($val, 0, 50) . "\n";
        }
    }
}
