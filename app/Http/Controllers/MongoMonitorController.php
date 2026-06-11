<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MongoMonitorController extends Controller
{
    // Sólo estas colecciones pueden vaciarse desde la UI
    const TRUNCATABLE = ['logs'];
    // Límite gratuito de MongoDB Atlas en bytes (512 MB)
    const FREE_TIER_LIMIT_BYTES = 512 * 1024 * 1024;

    public function index()
    {
        $activePage = 'mongo_monitor';
        return view('mongo_monitor.index', compact('activePage'));
    }

    public function truncateCollection(string $collection)
    {
        if (!in_array($collection, self::TRUNCATABLE, true)) {
            return response()->json(['success' => false, 'message' => 'Operación no permitida para esta colección.'], 403);
        }

        try {
            $result = DB::connection('mongodb')
                ->getMongoDB()
                ->selectCollection($collection)
                ->deleteMany([]);

            return response()->json([
                'success'  => true,
                'deleted'  => $result->getDeletedCount(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function stats()
    {
        try {
            $conn     = DB::connection('mongodb');
            $database = $conn->getMongoDB();
            $dbName   = $conn->getDatabaseName();

            // Estadísticas generales de la base de datos
            $dbStats = $database->command(['dbStats' => 1, 'scale' => 1])->toArray()[0];

            $dataSize    = (int) ($dbStats['dataSize']    ?? 0);
            $storageSize = (int) ($dbStats['storageSize'] ?? 0);
            $indexSize   = (int) ($dbStats['indexSize']   ?? 0);
            $totalSize   = $storageSize + $indexSize;

            // Estadísticas por colección
            $collections = [];
            foreach ($database->listCollections() as $collInfo) {
                $collName  = $collInfo->getName();
                $collStats = $database->command(['collStats' => $collName, 'scale' => 1])->toArray()[0];

                $collections[] = [
                    'name'         => $collName,
                    'count'        => (int) ($collStats['count']       ?? 0),
                    'data_size'    => (int) ($collStats['size']        ?? 0),
                    'storage_size' => (int) ($collStats['storageSize'] ?? 0),
                    'index_size'   => (int) ($collStats['totalIndexSize'] ?? 0),
                    'total_size'   => (int) (($collStats['storageSize'] ?? 0) + ($collStats['totalIndexSize'] ?? 0)),
                    'avg_obj_size' => (int) ($collStats['avgObjSize']  ?? 0),
                ];
            }

            // Ordenar por tamaño total descendente
            usort($collections, fn($a, $b) => $b['total_size'] <=> $a['total_size']);

            return response()->json([
                'success'    => true,
                'db_name'    => $dbName,
                'summary'    => [
                    'data_size'    => $dataSize,
                    'storage_size' => $storageSize,
                    'index_size'   => $indexSize,
                    'total_size'   => $totalSize,
                    'limit_bytes'  => self::FREE_TIER_LIMIT_BYTES,
                    'used_percent' => round(($totalSize / self::FREE_TIER_LIMIT_BYTES) * 100, 2),
                ],
                'collections' => $collections,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
