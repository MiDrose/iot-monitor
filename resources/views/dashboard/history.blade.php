@extends('layouts.app')

@section('title', 'Riwayat Data')
@section('page-title', 'Riwayat Data')
@section('page-subtitle', 'Lihat data historis sensor suhu dan kelembaban')

@section('content')
<div class="history-page">
    {{-- Filters --}}
    <div class="card card-filters" id="card-filters">
        <form method="GET" action="{{ route('history') }}" class="filter-form">
            <div class="filter-group">
                <label for="filter-device">Perangkat</label>
                <select name="device_id" id="filter-device" class="input-field">
                    <option value="">Semua Perangkat</option>
                    @foreach($devices as $device)
                    <option value="{{ $device->device_id }}" {{ request('device_id') == $device->device_id ? 'selected' : '' }}>
                        {{ $device->name }} ({{ $device->device_id }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label for="filter-start">Tanggal Mulai</label>
                <input type="date" name="start_date" id="filter-start" class="input-field" value="{{ request('start_date') }}">
            </div>
            <div class="filter-group">
                <label for="filter-end">Tanggal Akhir</label>
                <input type="date" name="end_date" id="filter-end" class="input-field" value="{{ request('end_date') }}">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary" id="btn-filter">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    Filter
                </button>
                <a href="{{ route('history') }}" class="btn btn-secondary" id="btn-reset">Reset</a>
                <a href="{{ route('export', request()->query()) }}" class="btn btn-accent" id="btn-export">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Export CSV
                </a>
            </div>
        </form>
    </div>

    {{-- History Chart --}}
    <div class="card card-chart" id="card-history-chart">
        <div class="card-header">
            <div class="card-icon chart">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/>
                </svg>
            </div>
            <div class="card-label">
                <span class="label-text">Grafik Data</span>
                <span class="label-unit">{{ $readings->total() }} data ditemukan</span>
            </div>
        </div>
        <div class="chart-wrapper">
            <canvas id="historyChart" height="250"></canvas>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card card-table" id="card-data-table">
        <div class="card-header">
            <div class="card-icon table-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <line x1="3" y1="9" x2="21" y2="9"/>
                    <line x1="3" y1="15" x2="21" y2="15"/>
                    <line x1="9" y1="3" x2="9" y2="21"/>
                </svg>
            </div>
            <div class="card-label">
                <span class="label-text">Tabel Data Sensor</span>
                <span class="label-unit">Halaman {{ $readings->currentPage() }} dari {{ $readings->lastPage() }}</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="sensor-data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Perangkat</th>
                        <th>Lokasi</th>
                        <th>🌡️ Suhu (°C)</th>
                        <th>💧 Kelembaban (%)</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($readings as $index => $reading)
                    <tr>
                        <td class="td-num">{{ $readings->firstItem() + $index }}</td>
                        <td>
                            <span class="device-tag">{{ $reading->device->device_id }}</span>
                        </td>
                        <td>{{ $reading->device->location }}</td>
                        <td>
                            <span class="temp-value {{ $reading->temperature > 35 || $reading->temperature < 18 ? 'alert' : '' }}">
                                {{ number_format($reading->temperature, 1) }}°C
                            </span>
                        </td>
                        <td>
                            <span class="hum-value {{ $reading->humidity > 80 || $reading->humidity < 30 ? 'alert' : '' }}">
                                {{ number_format($reading->humidity, 1) }}%
                            </span>
                        </td>
                        <td class="td-time">
                            <span class="time-date">{{ $reading->created_at->format('d M Y') }}</span>
                            <span class="time-clock">{{ $reading->created_at->format('H:i:s') }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="td-empty">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48">
                                    <path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"/>
                                </svg>
                                <p>Belum ada data sensor</p>
                                <span class="empty-hint">Data akan muncul setelah ESP32 mengirim data</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($readings->hasPages())
        <div class="pagination-wrapper">
            {{ $readings->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Build chart from table data (reversed to show oldest first)
    const readings = @json($readings->items());
    const reversed = [...readings].reverse();

    const historyCtx = document.getElementById('historyChart').getContext('2d');

    const hGradientTemp = historyCtx.createLinearGradient(0, 0, 0, 250);
    hGradientTemp.addColorStop(0, 'rgba(239, 68, 68, 0.2)');
    hGradientTemp.addColorStop(1, 'rgba(239, 68, 68, 0.0)');

    const hGradientHum = historyCtx.createLinearGradient(0, 0, 0, 250);
    hGradientHum.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
    hGradientHum.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

    new Chart(historyCtx, {
        type: 'line',
        data: {
            labels: reversed.map(r => {
                const d = new Date(r.created_at);
                return d.toLocaleString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
            }),
            datasets: [
                {
                    label: 'Suhu (°C)',
                    data: reversed.map(r => parseFloat(r.temperature)),
                    borderColor: '#ef4444',
                    backgroundColor: hGradientTemp,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#ef4444',
                },
                {
                    label: 'Kelembaban (%)',
                    data: reversed.map(r => parseFloat(r.humidity)),
                    borderColor: '#3b82f6',
                    backgroundColor: hGradientHum,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#3b82f6',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: { color: '#94a3b8', usePointStyle: true, padding: 20 }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    titleColor: '#e2e8f0',
                    bodyColor: '#94a3b8',
                    cornerRadius: 12,
                    padding: 14,
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.04)' },
                    ticks: { color: '#64748b', font: { size: 10 }, maxTicksLimit: 10 },
                    border: { display: false },
                },
                y: {
                    grid: { color: 'rgba(255,255,255,0.04)' },
                    ticks: { color: '#64748b', font: { size: 11 } },
                    border: { display: false },
                }
            }
        }
    });
</script>
@endpush
