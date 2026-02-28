<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Exception;

class HealthCheckController extends Controller
{
    /**
     * Perform a comprehensive health check of the application and its dependencies.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $databaseStatus = ['status' => 'ok', 'message' => 'Database connected successfully.'];
        try {
            DB::connection()->getPdo();
        } catch (Exception $e) {
            $databaseStatus = ['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()];
        }

        $cacheStatus = ['status' => 'ok', 'message' => 'Cache connected successfully.'];
        try {
            // Attempt a simple cache operation
            Cache::put('health_check_key', 'test_value', 10); // Store for 10 seconds
            if (Cache::get('health_check_key') !== 'test_value') {
                throw new Exception('Cache read failed or value mismatch.');
            }
            Cache::forget('health_check_key');
        } catch (Exception $e) {
            $cacheStatus = ['status' => 'error', 'message' => 'Cache operation failed: ' . $e->getMessage()];
        }

        $overallStatus = 'ok';
        $httpStatus = 200;

        if ($databaseStatus['status'] === 'error' || $cacheStatus['status'] === 'error') {
            $overallStatus = 'degraded'; // Or 'error' if you want a stricter definition
            $httpStatus = 503;
        }

        return response()->json([
            'status' => $overallStatus,
            'dependencies' => [
                'database' => $databaseStatus,
                'cache' => $cacheStatus,
            ],
            'timestamp' => now()->toIso8601String(),
        ], $httpStatus);
    }
}