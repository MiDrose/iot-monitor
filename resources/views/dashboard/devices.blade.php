@extends('layouts.app')

@section('title', 'Perangkat')
@section('page-title', 'Perangkat IoT')
@section('page-subtitle', 'Kelola perangkat sensor yang terhubung')

@section('content')
<div class="devices-page">
    {{-- API Info Card --}}
    <div class="card card-api-info" id="card-api-info">
        <div class="card-header">
            <div class="card-icon api">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 11a9 9 0 0 1 9 9"/>
                    <path d="M4 4a16 16 0 0 1 16 16"/>
                    <circle cx="5" cy="19" r="1"/>
                </svg>
            </div>
            <div class="card-label">
                <span class="label-text">Endpoint API</span>
                <span class="label-unit">Untuk koneksi ESP32</span>
            </div>
        </div>
        <div class="api-info-content">
            <div class="api-endpoint">
                <span class="method-badge">POST</span>
                <code id="api-url">{{ url('/api/sensor-data') }}</code>
                <button class="btn-copy" onclick="copyToClipboard('api-url')" title="Salin URL" id="btn-copy-url">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                </button>
            </div>
            <div class="api-payload">
                <span class="payload-label">Contoh Payload JSON:</span>
<pre><code id="api-payload-code">{
    "device_id": "ESP32-001",
    "temperature": 28.5,
    "humidity": 65.3,
    "api_key": "YOUR_API_KEY"
}</code></pre>
            </div>
        </div>
    </div>

    {{-- Device Cards --}}
    <div class="devices-grid">
        @forelse($devices as $device)
        <div class="card card-device" id="device-{{ $device->id }}">
            <div class="device-header">
                <div class="device-avatar {{ $device->isOnline() ? 'online' : 'offline' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                        <path d="M16 7V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v3"/>
                    </svg>
                </div>
                <div class="device-title">
                    <h3>{{ $device->name }}</h3>
                    <span class="device-id-tag">{{ $device->device_id }}</span>
                </div>
                <div class="device-badge {{ $device->isOnline() ? 'online' : 'offline' }}">
                    <div class="badge-dot"></div>
                    <span>{{ $device->isOnline() ? 'Online' : 'Offline' }}</span>
                </div>
            </div>

            <div class="device-details">
                <div class="detail-item">
                    <span class="detail-label">📍 Lokasi</span>
                    <span class="detail-value">{{ $device->location ?: '-' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">📊 Total Data</span>
                    <span class="detail-value">{{ number_format($device->sensor_readings_count) }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">⏱️ Terakhir Online</span>
                    <span class="detail-value">{{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Belum pernah' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">🔑 API Key</span>
                    <div class="api-key-display">
                        <code class="api-key-masked" id="api-key-{{ $device->id }}">{{ substr($device->api_key, 0, 8) }}...{{ substr($device->api_key, -8) }}</code>
                        <button class="btn-copy btn-sm" onclick="copyToClipboard('api-key-full-{{ $device->id }}')" title="Salin API Key">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                            </svg>
                        </button>
                        <span class="hidden" id="api-key-full-{{ $device->id }}">{{ $device->api_key }}</span>
                    </div>
                </div>
            </div>

            {{-- Edit Form --}}
            <form method="POST" action="{{ route('devices.update', $device) }}" class="device-form">
                @csrf
                @method('PUT')
                <div class="form-row">
                    <div class="form-group">
                        <label for="device-name-{{ $device->id }}">Nama Perangkat</label>
                        <input type="text" name="name" id="device-name-{{ $device->id }}" class="input-field" value="{{ $device->name }}" required>
                    </div>
                    <div class="form-group">
                        <label for="device-location-{{ $device->id }}">Lokasi</label>
                        <input type="text" name="location" id="device-location-{{ $device->id }}" class="input-field" value="{{ $device->location }}" placeholder="Contoh: Ruang Server">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ $device->is_active ? 'checked' : '' }} class="checkbox-input">
                            <span class="checkbox-custom"></span>
                            <span>Perangkat Aktif</span>
                        </label>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="btn-save-{{ $device->id }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Simpan
                    </button>
                    <form method="POST" action="{{ route('devices.regenerateKey', $device) }}" style="display:inline" onsubmit="return confirm('Yakin ingin generate ulang API Key? Key lama akan tidak berlaku.')">
                        @csrf
                        <button type="submit" class="btn btn-warning" id="btn-regen-{{ $device->id }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <polyline points="23 4 23 10 17 10"/>
                                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                            </svg>
                            Regenerate Key
                        </button>
                    </form>
                </div>
            </form>
        </div>
        @empty
        <div class="card card-empty">
            <div class="empty-state large">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="80" height="80">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 7V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v3"/>
                </svg>
                <h3>Belum Ada Perangkat</h3>
                <p>Perangkat akan otomatis terdaftar saat ESP32 pertama kali mengirim data ke API.</p>
                <div class="empty-steps">
                    <div class="step">
                        <span class="step-num">1</span>
                        <span>Upload kode ke ESP32</span>
                    </div>
                    <div class="step">
                        <span class="step-num">2</span>
                        <span>Hubungkan ke WiFi</span>
                    </div>
                    <div class="step">
                        <span class="step-num">3</span>
                        <span>ESP32 akan otomatis terdaftar</span>
                    </div>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyToClipboard(elementId) {
        const el = document.getElementById(elementId);
        const text = el.textContent || el.innerText;

        navigator.clipboard.writeText(text.trim()).then(() => {
            // Show toast
            const toast = document.createElement('div');
            toast.className = 'toast toast-success';
            toast.textContent = '✓ Disalin ke clipboard!';
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('show');
            }, 10);

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 2000);
        }).catch(() => {
            // Fallback
            const range = document.createRange();
            range.selectNode(el);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
            document.execCommand('copy');
            window.getSelection().removeAllRanges();
        });
    }
</script>
@endpush
