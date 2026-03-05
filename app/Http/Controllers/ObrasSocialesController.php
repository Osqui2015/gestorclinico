<?php

namespace App\Http\Controllers;

use App\Services\ObrasSocialesService;
use Illuminate\Http\Request;

class ObrasSocialesController extends Controller
{
  protected ObrasSocialesService $service;

  public function __construct(ObrasSocialesService $service)
  {
    $this->service = $service;
  }

  /**
   * Buscar obras sociales
   * GET /api/obras-sociales/search?q=term
   */
  public function search(Request $request)
  {
    $query = $request->query('q', '');
    $limit = $request->query('limit', 20);

    if (empty($query)) {
      return response()->json([
        'success' => false,
        'message' => 'Se requiere un término de búsqueda',
        'data' => []
      ], 400);
    }

    $results = $this->service->search($query, min((int)$limit, 50));

    return response()->json([
      'success' => true,
      'count' => count($results),
      'data' => $results
    ]);
  }

  /**
   * Obtener obra social por RNOS
   * GET /api/obras-sociales/{rnos}
   */
  public function show(string $rnos)
  {
    $obra_social = $this->service->getByRnos($rnos);

    if (!$obra_social) {
      return response()->json([
        'success' => false,
        'message' => 'Obra social no encontrada',
        'data' => null
      ], 404);
    }

    return response()->json([
      'success' => true,
      'data' => $obra_social
    ]);
  }

  /**
   * Obtener obras sociales por provincia
   * GET /api/obras-sociales/provincia/{provincia}
   */
  public function byProvincia(string $provincia)
  {
    $results = $this->service->getByProvincia($provincia);

    return response()->json([
      'success' => true,
      'count' => count($results),
      'data' => $results
    ]);
  }

  /**
   * Obtener lista de provincias
   * GET /api/obras-sociales/provincias
   */
  public function provincias()
  {
    $provincias = $this->service->getProvincias();

    return response()->json([
      'success' => true,
      'count' => count($provincias),
      'data' => $provincias
    ]);
  }
}
