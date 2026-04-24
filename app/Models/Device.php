<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Device extends Model
{
    protected $fillable = [
        'device_id',
        'name',
        'location',
        'api_key',
        'is_active',
        'last_seen_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    /**
     * Get all sensor readings for this device.
     */
    public function sensorReadings(): HasMany
    {
        return $this->hasMany(SensorReading::class);
    }

    /**
     * Get the latest sensor reading.
     */
    public function latestReading()
    {
        return $this->hasOne(SensorReading::class)->latestOfMany();
    }

    /**
     * Check if device is online (sent data in last 2 minutes).
     */
    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->diffInMinutes(now()) < 2;
    }

    /**
     * Generate a new API key.
     */
    public static function generateApiKey(): string
    {
        return Str::random(64);
    }
}
