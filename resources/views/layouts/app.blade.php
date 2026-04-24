<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="IoT Monitoring Suhu dan Kelembaban Real-time - Dashboard">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'IoT Monitor') - Monitoring Suhu & Kelembaban</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    @stack('styles')
</head>
<body>
    <!-- Sidebar Navigation -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"/>
                    </svg>
                </div>
                <div class="logo-text">
                    <span class="logo-title">IoT Monitor</span>
                    <span class="logo-subtitle">Suhu & Kelembaban</span>
                </div>
            </div>
        </div>

        <div class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" id="nav-dashboard">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('history') }}" class="nav-item {{ request()->routeIs('history') ? 'active' : '' }}" id="nav-history">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12,6 12,12 16,14"/>
                </svg>
                <span>Riwayat Data</span>
            </a>
            <a href="{{ route('devices') }}" class="nav-item {{ request()->routeIs('devices') ? 'active' : '' }}" id="nav-devices">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 7V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v3"/>
                </svg>
                <span>Perangkat</span>
            </a>
        </div>

        <div class="sidebar-footer">
            <div class="status-indicator" id="connection-status">
                <div class="status-dot online"></div>
                <span>Sistem Aktif</span>
            </div>
        </div>
    </nav>

    <!-- Mobile Hamburger -->
    <button class="mobile-toggle" id="mobile-toggle" aria-label="Toggle navigation">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- Main Content -->
    <main class="main-content">
        <header class="page-header">
            <div>
                <h1 id="page-title">@yield('page-title', 'Dashboard')</h1>
                <p class="page-subtitle">@yield('page-subtitle', 'Real-time monitoring suhu dan kelembaban')</p>
            </div>
            <div class="header-actions">
                <div class="live-clock" id="live-clock"></div>
            </div>
        </header>

        @if(session('success'))
        <div class="alert alert-success" id="alert-success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        @yield('content')
    </main>

    <!-- Overlay for mobile -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- Base JavaScript -->
    <script>
        // Mobile sidebar toggle
        const mobileToggle = document.getElementById('mobile-toggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        if (mobileToggle) {
            mobileToggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('active');
                mobileToggle.classList.toggle('active');
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                mobileToggle.classList.remove('active');
            });
        }

        // Live clock
        function updateClock() {
            const now = new Date();
            const options = {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
                hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
            };
            document.getElementById('live-clock').textContent = now.toLocaleDateString('id-ID', options);
        }
        updateClock();
        setInterval(updateClock, 1000);

        // Dismiss alerts
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    </script>

    @stack('scripts')
</body>
</html>
