<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\SensorReading;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a sample device
        $device = Device::create([
            'device_id' => 'ESP32-001',
            'name' => 'Sensor Ruang Utama',
            'location' => 'Ruang Server',
            'api_key' => 'iot-monitor-secret-key-2024',
            'is_active' => true,
            'last_seen_at' => now(),
        ]);

        // Generate 24 hours of sample data (every 10 minutes)
        $startTime = now()->subHours(24);

        for ($i = 0; $i < 144; $i++) {
            $time = $startTime->copy()->addMinutes($i * 10);

            // Simulate realistic temperature (25-32°C with daily cycle)
            $hour = $time->hour;
            $baseTemp = 27;
            $tempVariation = sin(($hour - 6) * M_PI / 12) * 3; // peaks at noon
            $temp = $baseTemp + $tempVariation + (mt_rand(-10, 10) / 10);

            // Simulate realistic humidity (50-75% inverse to temperature)
            $baseHum = 62;
            $humVariation = -sin(($hour - 6) * M_PI / 12) * 8;
            $hum = $baseHum + $humVariation + (mt_rand(-20, 20) / 10);

            SensorReading::create([
                'device_id' => $device->id,
                'temperature' => round($temp, 2),
                'humidity' => round(max(30, min(90, $hum)), 2),
                'ip_address' => '192.168.1.100',
                'created_at' => $time,
                'updated_at' => $time,
            ]);
        }
    }
}
