<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SensorReading;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard home page - real-time monitoring.
     */
    public function index()
    {
        $devices = Device::where('is_active', true)->with('latestReading')->get();

        // Today's statistics
        $todayStats = SensorReading::where('created_at', '>=', now()->startOfDay())
            ->selectRaw('
                MIN(temperature) as temp_min,
                MAX(temperature) as temp_max,
                AVG(temperature) as temp_avg,
                MIN(humidity) as hum_min,
                MAX(humidity) as hum_max,
                AVG(humidity) as hum_avg,
                COUNT(*) as total_readings
            ')
            ->first();

        // Recent readings for chart (last 24 hours, sampled)
        $chartData = SensorReading::where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at', 'asc')
            ->get(['temperature', 'humidity', 'created_at'])
            ->map(function ($r) {
                return [
                    'time' => $r->created_at->format('H:i'),
                    'temperature' => (float) $r->temperature,
                    'humidity' => (float) $r->humidity,
                ];
            });

        return view('dashboard.index', compact('devices', 'todayStats', 'chartData'));
    }

    /**
     * History page - view historical data.
     */
    public function history(Request $request)
    {
        $devices = Device::where('is_active', true)->get();

        $query = SensorReading::with('device')->orderBy('created_at', 'desc');

        if ($request->query('device_id')) {
            $device = Device::where('device_id', $request->query('device_id'))->first();
            if ($device) {
                $query->where('device_id', $device->id);
            }
        }

        if ($request->query('start_date')) {
            $query->where('created_at', '>=', $request->query('start_date'));
        }
        if ($request->query('end_date')) {
            $query->where('created_at', '<=', $request->query('end_date') . ' 23:59:59');
        }

        $readings = $query->paginate(25)->withQueryString();

        return view('dashboard.history', compact('devices', 'readings'));
    }

    /**
     * Devices page - manage IoT devices.
     */
    public function devices()
    {
        $devices = Device::withCount('sensorReadings')->get();
        return view('dashboard.devices', compact('devices'));
    }

    /**
     * Update device info.
     */
    public function updateDevice(Request $request, Device $device)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'location' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $device->update($validated);

        return redirect()->route('devices')->with('success', 'Perangkat berhasil diperbarui!');
    }

    /**
     * Regenerate API key for a device.
     */
    public function regenerateApiKey(Device $device)
    {
        $device->update(['api_key' => Device::generateApiKey()]);

        return redirect()->route('devices')->with('success', 'API Key berhasil di-generate ulang!');
    }

    /**
     * Export sensor data to CSV.
     */
    public function export(Request $request)
    {
        $query = SensorReading::with('device')->orderBy('created_at', 'desc');

        if ($request->query('device_id')) {
            $device = Device::where('device_id', $request->query('device_id'))->first();
            if ($device) {
                $query->where('device_id', $device->id);
            }
        }

        if ($request->query('start_date')) {
            $query->where('created_at', '>=', $request->query('start_date'));
        }
        if ($request->query('end_date')) {
            $query->where('created_at', '<=', $request->query('end_date') . ' 23:59:59');
        }

        $readings = $query->limit(5000)->get();
        $filename = 'sensor_data_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($readings) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Device ID', 'Nama Device', 'Lokasi', 'Suhu (°C)', 'Kelembaban (%)', 'Waktu']);

            foreach ($readings as $reading) {
                fputcsv($file, [
                    $reading->id,
                    $reading->device->device_id,
                    $reading->device->name,
                    $reading->device->location,
                    $reading->temperature,
                    $reading->humidity,
                    $reading->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
