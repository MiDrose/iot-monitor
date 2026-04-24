@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Monitoring suhu dan kelembaban secara real-time')

@section('content')
<div class="dashboard-grid">
    {{-- Live Gauges Section --}}
    <div class="card card-gauge" id="card-temperature">
        <div class="card-header">
            <div class="card-icon temperature">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"/>
                </svg>
            </div>
            <div class="card-label">
                <span class="label-text">Suhu Ruangan</span>
                <span class="label-unit">Celsius</span>
            </div>
        </div>
        <div class="gauge-container">
            <svg class="gauge-svg" viewBox="0 0 200 120">
                <defs>
                    <linearGradient id="tempGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" style="stop-color:#3b82f6"/>
                        <stop offset="50%" style="stop-color:#10b981"/>
                        <stop offset="100%" style="stop-color:#ef4444"/>
                    </linearGradient>
                </defs>
                <path class="gauge-bg" d="M 20 100 A 80 80 0 0 1 180 100" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="12" stroke-linecap="round"/>
                <path class="gauge-fill" id="temp-gauge-fill" d="M 20 100 A 80 80 0 0 1 180 100" fill="none" stroke="url(#tempGradient)" stroke-width="12" stroke-linecap="round" stroke-dasharray="251.2" stroke-dashoffset="251.2"/>
            </svg>
            <div class="gauge-value">
                <span class="value-number" id="current-temp">--</span>
                <span class="value-unit">°C</span>
            </div>
        </div>
        <div class="card-footer">
            <div class="stat-mini">
                <span class="stat-label">Min</span>
                <span class="stat-value" id="temp-min">{{ $todayStats->temp_min ? number_format($todayStats->temp_min, 1) : '--' }}°</span>
            </div>
            <div class="stat-mini">
                <span class="stat-label">Rata-rata</span>
                <span class="stat-value" id="temp-avg">{{ $todayStats->temp_avg ? number_format($todayStats->temp_avg, 1) : '--' }}°</span>
            </div>
            <div class="stat-mini">
                <span class="stat-label">Max</span>
                <span class="stat-value" id="temp-max">{{ $todayStats->temp_max ? number_format($todayStats->temp_max, 1) : '--' }}°</span>
            </div>
        </div>
    </div>

    <div class="card card-gauge" id="card-humidity">
        <div class="card-header">
            <div class="card-icon humidity">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                </svg>
            </div>
            <div class="card-label">
                <span class="label-text">Kelembaban</span>
                <span class="label-unit">Persen</span>
            </div>
        </div>
        <div class="gauge-container">
            <svg class="gauge-svg" viewBox="0 0 200 120">
                <defs>
                    <linearGradient id="humGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" style="stop-color:#f59e0b"/>
                        <stop offset="50%" style="stop-color:#06b6d4"/>
                        <stop offset="100%" style="stop-color:#3b82f6"/>
                    </linearGradient>
                </defs>
                <path class="gauge-bg" d="M 20 100 A 80 80 0 0 1 180 100" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="12" stroke-linecap="round"/>
                <path class="gauge-fill" id="hum-gauge-fill" d="M 20 100 A 80 80 0 0 1 180 100" fill="none" stroke="url(#humGradient)" stroke-width="12" stroke-linecap="round" stroke-dasharray="251.2" stroke-dashoffset="251.2"/>
            </svg>
            <div class="gauge-value">
                <span class="value-number" id="current-hum">--</span>
                <span class="value-unit">%</span>
            </div>
        </div>
        <div class="card-footer">
            <div class="stat-mini">
                <span class="stat-label">Min</span>
                <span class="stat-value" id="hum-min">{{ $todayStats->hum_min ? number_format($todayStats->hum_min, 1) : '--' }}%</span>
            </div>
            <div class="stat-mini">
                <span class="stat-label">Rata-rata</span>
                <span class="stat-value" id="hum-avg">{{ $todayStats->hum_avg ? number_format($todayStats->hum_avg, 1) : '--' }}%</span>
            </div>
            <div class="stat-mini">
                <span class="stat-label">Max</span>
                <span class="stat-value" id="hum-max">{{ $todayStats->hum_max ? number_format($todayStats->hum_max, 1) : '--' }}%</span>
            </div>
        </div>
    </div>

    {{-- Device Status Cards --}}
    <div class="card card-status" id="card-device-status">
        <div class="card-header">
            <div class="card-icon status">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12.55a11 11 0 0 1 14.08 0"/>
                    <path d="M1.42 9a16 16 0 0 1 21.16 0"/>
                    <path d="M8.53 16.11a6 6 0 0 1 6.95 0"/>
                    <circle cx="12" cy="20" r="1"/>
                </svg>
            </div>
            <div class="card-label">
                <span class="label-text">Status Perangkat</span>
                <span class="label-unit">ESP32</span>
            </div>
        </div>
        <div class="status-list">
            @forelse($devices as $device)
            <div class="device-status-item">
                <div class="device-info">
                    <span class="device-name">{{ $device->name }}</span>
                    <span class="device-location">{{ $device->location }}</span>
                </div>
                <div class="device-badge {{ $device->isOnline() ? 'online' : 'offline' }}">
                    <div class="badge-dot"></div>
                    <span>{{ $device->isOnline() ? 'Online' : 'Offline' }}</span>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 7V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v3"/>
                </svg>
                <p>Belum ada perangkat terhubung</p>
                <span class="empty-hint">Hubungkan ESP32 ke API untuk memulai</span>
            </div>
            @endforelse
        </div>
    </div>

    <div class="card card-stats" id="card-today-stats">
        <div class="card-header">
            <div class="card-icon stats">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="20" x2="18" y2="10"/>
                    <line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6" y1="20" x2="6" y2="14"/>
                </svg>
            </div>
            <div class="card-label">
                <span class="label-text">Statistik Hari Ini</span>
                <span class="label-unit">{{ now()->format('d M Y') }}</span>
            </div>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon temp-icon">🌡️</div>
                <div class="stat-details">
                    <span class="stat-title">Total Data</span>
                    <span class="stat-number" id="total-readings">{{ $todayStats->total_readings ?? 0 }}</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-details">
                    <span class="stat-title">Perangkat Aktif</span>
                    <span class="stat-number">{{ $devices->count() }}</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏱️</div>
                <div class="stat-details">
                    <span class="stat-title">Update Terakhir</span>
                    <span class="stat-number stat-time" id="last-update">--:--</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🔔</div>
                <div class="stat-details">
                    <span class="stat-title">Status</span>
                    <span class="stat-number stat-ok" id="status-text">Normal</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart Section --}}
    <div class="card card-chart" id="card-chart">
        <div class="card-header">
            <div class="card-icon chart">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/>
                </svg>
            </div>
            <div class="card-label">
                <span class="label-text">Grafik 24 Jam Terakhir</span>
                <span class="label-unit">Suhu & Kelembaban</span>
            </div>
            <div class="chart-legend">
                <span class="legend-item legend-temp"><span class="legend-dot"></span> Suhu</span>
                <span class="legend-item legend-hum"><span class="legend-dot"></span> Kelembaban</span>
            </div>
        </div>
        <div class="chart-wrapper">
            <canvas id="sensorChart" height="300"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Chart Data from server
    const chartData = @json($chartData);

    // Initialize Chart.js
    const ctx = document.getElementById('sensorChart').getContext('2d');

    const gradientTemp = ctx.createLinearGradient(0, 0, 0, 300);
    gradientTemp.addColorStop(0, 'rgba(239, 68, 68, 0.3)');
    gradientTemp.addColorStop(1, 'rgba(239, 68, 68, 0.0)');

    const gradientHum = ctx.createLinearGradient(0, 0, 0, 300);
    gradientHum.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
    gradientHum.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

    const sensorChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(d => d.time),
            datasets: [
                {
                    label: 'Suhu (°C)',
                    data: chartData.map(d => d.temperature),
                    borderColor: '#ef4444',
                    backgroundColor: gradientTemp,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#ef4444',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                },
                {
                    label: 'Kelembaban (%)',
                    data: chartData.map(d => d.humidity),
                    borderColor: '#3b82f6',
                    backgroundColor: gradientHum,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#3b82f6',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    titleColor: '#e2e8f0',
                    bodyColor: '#94a3b8',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    cornerRadius: 12,
                    padding: 14,
                    titleFont: { weight: '600', size: 13 },
                    bodyFont: { size: 12 },
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            return `  ${label}: ${context.parsed.y}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false },
                    ticks: { color: '#64748b', font: { size: 11 }, maxTicksLimit: 12 },
                    border: { display: false },
                },
                y: {
                    grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false },
                    ticks: { color: '#64748b', font: { size: 11 } },
                    border: { display: false },
                }
            }
        }
    });

    // Gauge animation
    function updateGauge(elementId, value, min, max) {
        const totalArc = 251.2;
        const percent = Math.max(0, Math.min(1, (value - min) / (max - min)));
        const offset = totalArc - (totalArc * percent);
        const el = document.getElementById(elementId);
        if (el) {
            el.style.transition = 'stroke-dashoffset 1s ease-out';
            el.setAttribute('stroke-dashoffset', offset);
        }
    }

    // Polling for real-time data
    function fetchLatestData() {
        fetch('/api/sensor-data/latest')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data && data.data.length > 0) {
                    const latest = data.data[0];

                    // Update temperature
                    const tempEl = document.getElementById('current-temp');
                    const humEl = document.getElementById('current-hum');

                    if (latest.temperature !== null) {
                        tempEl.textContent = parseFloat(latest.temperature).toFixed(1);
                        updateGauge('temp-gauge-fill', parseFloat(latest.temperature), 0, 50);
                    }

                    if (latest.humidity !== null) {
                        humEl.textContent = parseFloat(latest.humidity).toFixed(1);
                        updateGauge('hum-gauge-fill', parseFloat(latest.humidity), 0, 100);
                    }

                    // Update last update time
                    if (latest.recorded_at) {
                        const dt = new Date(latest.recorded_at);
                        document.getElementById('last-update').textContent = dt.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                    }

                    // Update status
                    const statusEl = document.getElementById('status-text');
                    const temp = parseFloat(latest.temperature);
                    const hum = parseFloat(latest.humidity);

                    if (temp > 35 || temp < 18 || hum > 80 || hum < 30) {
                        statusEl.textContent = '⚠️ Peringatan';
                        statusEl.className = 'stat-number stat-warn';
                    } else {
                        statusEl.textContent = '✅ Normal';
                        statusEl.className = 'stat-number stat-ok';
                    }

                    // Update chart
                    const now = new Date();
                    const timeLabel = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');

                    if (sensorChart.data.labels[sensorChart.data.labels.length - 1] !== timeLabel) {
                        sensorChart.data.labels.push(timeLabel);
                        sensorChart.data.datasets[0].data.push(parseFloat(latest.temperature));
                        sensorChart.data.datasets[1].data.push(parseFloat(latest.humidity));

                        // Keep only last 144 data points (24h at 10-min intervals)
                        if (sensorChart.data.labels.length > 144) {
                            sensorChart.data.labels.shift();
                            sensorChart.data.datasets[0].data.shift();
                            sensorChart.data.datasets[1].data.shift();
                        }

                        sensorChart.update('none');
                    }
                }
            })
            .catch(err => console.warn('Fetch error:', err));
    }

    // Initial load
    fetchLatestData();

    // Poll every 10 seconds
    setInterval(fetchLatestData, 10000);

    // Animate gauges on load with initial data
    @if($devices->isNotEmpty() && $devices->first()->latestReading)
        @php $lr = $devices->first()->latestReading; @endphp
        setTimeout(() => {
            document.getElementById('current-temp').textContent = '{{ number_format($lr->temperature, 1) }}';
            document.getElementById('current-hum').textContent = '{{ number_format($lr->humidity, 1) }}';
            updateGauge('temp-gauge-fill', {{ $lr->temperature }}, 0, 50);
            updateGauge('hum-gauge-fill', {{ $lr->humidity }}, 0, 100);
        }, 500);
    @endif
</script>
@endpush
