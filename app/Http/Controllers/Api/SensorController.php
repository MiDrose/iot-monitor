<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\SensorReading;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SensorController extends Controller
{
    /**
     * POST /api/sensor-data
     * Receive sensor data from ESP32.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:50',
            'temperature' => 'required|numeric|between:-50,100',
            'humidity' => 'required|numeric|between:0,100',
            'api_key' => 'required|string',
        ]);

        // Find or create device
        $device = Device::where('device_id', $validated['device_id'])->first();

        if (!$device) {
            // Auto-register new device
            $device = Device::create([
                'device_id' => $validated['device_id'],
                'name' => 'Device ' . $validated['device_id'],
                'location' => 'Belum diatur',
                'api_key' => $validated['api_key'],
                'is_active' => true,
            ]);
        }

        // Verify API key
        if ($device->api_key !== $validated['api_key']) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key',
            ], 401);
        }

        // Check if device is active
        if (!$device->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Device is deactivated',
            ], 403);
        }

        // Store sensor reading
        $reading = SensorReading::create([
            'device_id' => $device->id,
            'temperature' => $validated['temperature'],
            'humidity' => $validated['humidity'],
            'ip_address' => $request->ip(),
        ]);

        // Update device last seen
        $device->update(['last_seen_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Data received successfully',
            'data' => [
                'id' => $reading->id,
                'temperature' => $reading->temperature,
                'humidity' => $reading->humidity,
                'recorded_at' => $reading->created_at->toISOString(),
            ],
        ], 201);
    }

    /**
     * GET /api/sensor-data/latest
     * Get latest readings for all devices or a specific device.
     */
    public function latest(Request $request): JsonResponse
    {
        $deviceId = $request->query('device_id');

        if ($deviceId) {
            $device = Device::where('device_id', $deviceId)->first();
            if (!$device) {
                return response()->json(['success' => false, 'message' => 'Device not found'], 404);
            }

            $reading = $device->sensorReadings()->latest()->first();
            return response()->json([
                'success' => true,
                'data' => $reading ? [
                    'device_id' => $device->device_id,
                    'device_name' => $device->name,
                    'location' => $device->location,
                    'temperature' => $reading->temperature,
                    'humidity' => $reading->humidity,
                    'recorded_at' => $reading->created_at->toISOString(),
                    'is_online' => $device->isOnline(),
                ] : null,
            ]);
        }

        // All devices' latest readings
        $devices = Device::where('is_active', true)->with('latestReading')->get();
        $data = $devices->map(function ($device) {
            $reading = $device->latestReading;
            return [
                'device_id' => $device->device_id,
                'device_name' => $device->name,
                'location' => $device->location,
                'temperature' => $reading?->temperature,
                'humidity' => $reading?->humidity,
                'recorded_at' => $reading?->created_at?->toISOString(),
                'is_online' => $device->isOnline(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * GET /api/sensor-data/history
     * Get historical sensor data with optional filters.
     */
    public function history(Request $request): JsonResponse
    {
        $query = SensorReading::with('device');

        // Filter by device
        if ($request->query('device_id')) {
            $device = Device::where('device_id', $request->query('device_id'))->first();
            if ($device) {
                $query->where('device_id', $device->id);
            }
        }

        // Filter by date range
        if ($request->query('start_date')) {
            $query->where('created_at', '>=', $request->query('start_date'));
        }
        if ($request->query('end_date')) {
            $query->where('created_at', '<=', $request->query('end_date'));
        }

        // Limit and order
        $limit = min((int) ($request->query('limit', 100)), 500);
        $readings = $query->orderBy('created_at', 'desc')->limit($limit)->get();

        $data = $readings->map(function ($reading) {
            return [
                'id' => $reading->id,
                'device_id' => $reading->device->device_id,
                'temperature' => $reading->temperature,
                'humidity' => $reading->humidity,
                'recorded_at' => $reading->created_at->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $data->count(),
            'data' => $data,
        ]);
    }

    /**
     * GET /api/devices
     * Get all registered devices.
     */
    public function devices(): JsonResponse
    {
        $devices = Device::withCount('sensorReadings')->get();

        $data = $devices->map(function ($device) {
            return [
                'id' => $device->id,
                'device_id' => $device->device_id,
                'name' => $device->name,
                'location' => $device->location,
                'is_active' => $device->is_active,
                'is_online' => $device->isOnline(),
                'last_seen_at' => $device->last_seen_at?->toISOString(),
                'total_readings' => $device->sensor_readings_count,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * GET /api/statistics
     * Get device statistics (min, max, avg).
     */
    public function statistics(Request $request): JsonResponse
    {
        $deviceId = $request->query('device_id');
        $period = $request->query('period', 'today'); // today, week, month

        $query = SensorReading::query();

        if ($deviceId) {
            $device = Device::where('device_id', $deviceId)->first();
            if ($device) {
                $query->where('device_id', $device->id);
            }
        }

        switch ($period) {
            case 'week':
                $query->where('created_at', '>=', now()->subWeek());
                break;
            case 'month':
                $query->where('created_at', '>=', now()->subMonth());
                break;
            case 'today':
            default:
                $query->where('created_at', '>=', now()->startOfDay());
                break;
        }

        $stats = $query->selectRaw('
            MIN(temperature) as temp_min,
            MAX(temperature) as temp_max,
            AVG(temperature) as temp_avg,
            MIN(humidity) as hum_min,
            MAX(humidity) as hum_max,
            AVG(humidity) as hum_avg,
            COUNT(*) as total_readings
        ')->first();

        return response()->json([
            'success' => true,
            'period' => $period,
            'data' => [
                'temperature' => [
                    'min' => round($stats->temp_min, 1),
                    'max' => round($stats->temp_max, 1),
                    'avg' => round($stats->temp_avg, 1),
                ],
                'humidity' => [
                    'min' => round($stats->hum_min, 1),
                    'max' => round($stats->hum_max, 1),
                    'avg' => round($stats->hum_avg, 1),
                ],
                'total_readings' => $stats->total_readings,
            ],
        ]);
    }
}
