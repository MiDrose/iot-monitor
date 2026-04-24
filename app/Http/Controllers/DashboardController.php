<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SensorReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Get device IDs belonging to the current user.
     */
    private function userDeviceIds()
    {
        return Auth::user()->devices()->pluck('id');
    }

    /**
     * Dashboard home page - real-time monitoring.
     */
    public function index()
    {
        $deviceIds = $this->userDeviceIds();
        $devices = Device::whereIn('id', $deviceIds)->where('is_active', true)->with('latestReading')->get();

        // Today's statistics (only user's devices)
        $todayStats = SensorReading::whereIn('device_id', $deviceIds)
            ->where('created_at', '>=', now()->startOfDay())
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

        // Recent readings for chart (last 24 hours)
        $chartData = SensorReading::whereIn('device_id', $deviceIds)
            ->where('created_at', '>=', now()->subHours(24))
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
        $deviceIds = $this->userDeviceIds();
        $devices = Device::whereIn('id', $deviceIds)->where('is_active', true)->get();

        $query = SensorReading::with('device')
            ->whereIn('device_id', $deviceIds)
            ->orderBy('created_at', 'desc');

        if ($request->query('device_id')) {
            $device = Device::whereIn('id', $deviceIds)->where('device_id', $request->query('device_id'))->first();
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
        $devices = Auth::user()->devices()->withCount('sensorReadings')->get();
        return view('dashboard.devices', compact('devices'));
    }

    /**
     * Add a new device manually (pre-register before ESP32 connects).
     */
    public function addDevice(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:50|unique:devices,device_id',
            'name' => 'required|string|max:100',
            'location' => 'nullable|string|max:100',
        ]);

        $apiKey = Device::generateApiKey();

        Device::create([
            'user_id' => Auth::id(),
            'device_id' => $validated['device_id'],
            'name' => $validated['name'],
            'location' => $validated['location'] ?? 'Belum diatur',
            'api_key' => $apiKey,
            'is_active' => true,
        ]);

        return redirect()->route('devices')->with('success', 'Perangkat berhasil ditambahkan! Gunakan API Key yang tertera di ESP32 Anda.');
    }

    /**
     * Claim an existing unclaimed device (auto-registered by ESP32).
     */
    public function claimDevice(Request $request)
    {
        $validated = $request->validate([
            'claim_device_id' => 'required|string|max:50',
            'claim_api_key' => 'required|string',
        ]);

        $device = Device::where('device_id', $validated['claim_device_id'])
            ->where('api_key', $validated['claim_api_key'])
            ->first();

        if (!$device) {
            return redirect()->route('devices')->withErrors(['claim_device_id' => 'Device ID atau API Key salah.']);
        }

        if ($device->user_id !== null) {
            return redirect()->route('devices')->withErrors(['claim_device_id' => 'Perangkat ini sudah dimiliki user lain.']);
        }

        $device->update(['user_id' => Auth::id()]);

        return redirect()->route('devices')->with('success', 'Perangkat "' . $device->name . '" berhasil diklaim!');
    }

    /**
     * Update device info.
     */
    public function updateDevice(Request $request, Device $device)
    {
        // Ensure user owns this device
        if ($device->user_id !== Auth::id()) {
            abort(403);
        }

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
        if ($device->user_id !== Auth::id()) {
            abort(403);
        }

        $device->update(['api_key' => Device::generateApiKey()]);

        return redirect()->route('devices')->with('success', 'API Key berhasil di-generate ulang!');
    }

    /**
     * Export sensor data to CSV.
     */
    public function export(Request $request)
    {
        $deviceIds = $this->userDeviceIds();

        $query = SensorReading::with('device')
            ->whereIn('device_id', $deviceIds)
            ->orderBy('created_at', 'desc');

        if ($request->query('device_id')) {
            $device = Device::whereIn('id', $deviceIds)->where('device_id', $request->query('device_id'))->first();
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
