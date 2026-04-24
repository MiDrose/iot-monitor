<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorReading extends Model
{
    protected $fillable = [
        'device_id',
        'temperature',
        'humidity',
        'ip_address',
    ];

    protected $casts = [
        'temperature' => 'decimal:2',
        'humidity' => 'decimal:2',
    ];

    /**
     * Get the device that owns this reading.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Scope: filter by device.
     */
    public function scopeByDevice($query, $deviceId)
    {
        return $query->whereHas('device', function ($q) use ($deviceId) {
            $q->where('device_id', $deviceId);
        });
    }

    /**
     * Scope: filter by date range.
     */
    public function scopeByDateRange($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }
}
