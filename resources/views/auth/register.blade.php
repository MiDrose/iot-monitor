<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - IoT Monitor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#0a0e1a;color:#e2e8f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px}
        body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse at 30% 20%,rgba(59,130,246,0.1),transparent 50%),radial-gradient(ellipse at 70% 80%,rgba(139,92,246,0.08),transparent 50%);pointer-events:none}
        .auth-card{position:relative;background:rgba(15,23,42,0.7);border:1px solid rgba(255,255,255,0.06);border-radius:24px;padding:40px;max-width:420px;width:100%;backdrop-filter:blur(16px);box-shadow:0 8px 40px rgba(0,0,0,0.4)}
        .logo{text-align:center;margin-bottom:32px}
        .logo-icon{width:56px;height:56px;background:linear-gradient(135deg,#10b981,#06b6d4);border-radius:14px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;box-shadow:0 4px 20px rgba(16,185,129,0.35)}
        .logo-icon svg{width:28px;height:28px;color:#fff}
        .logo h1{font-size:1.5rem;font-weight:800;letter-spacing:-0.03em;background:linear-gradient(135deg,#f1f5f9,#10b981);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .logo p{font-size:0.85rem;color:#64748b;margin-top:4px}
        .form-group{margin-bottom:18px}
        .form-group label{display:block;font-size:0.8rem;font-weight:600;color:#94a3b8;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em}
        .form-group input{width:100%;padding:12px 16px;background:rgba(30,41,59,0.7);border:1px solid rgba(255,255,255,0.08);border-radius:10px;color:#e2e8f0;font-size:0.95rem;font-family:'Inter',sans-serif;outline:none;transition:all 0.2s}
        .form-group input:focus{border-color:#10b981;box-shadow:0 0 0 3px rgba(16,185,129,0.15)}
        .form-group input::placeholder{color:#475569}
        .btn-submit{width:100%;padding:14px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;transition:all 0.2s;font-family:'Inter',sans-serif;box-shadow:0 4px 15px rgba(16,185,129,0.4)}
        .btn-submit:hover{box-shadow:0 6px 25px rgba(16,185,129,0.6);transform:translateY(-1px)}
        .error-box{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);border-radius:10px;padding:12px 16px;margin-bottom:20px}
        .error-box p{color:#ef4444;font-size:0.85rem}
        .auth-footer{text-align:center;margin-top:24px;font-size:0.85rem;color:#64748b}
        .auth-footer a{color:#10b981;text-decoration:none;font-weight:600}
        .auth-footer a:hover{text-decoration:underline}
    </style>
</head>
<body>
<div class="auth-card">
    <div class="logo">
        <div class="logo-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
        </div>
        <h1>Buat Akun Baru</h1>
        <p>Daftar untuk mulai monitoring IoT</p>
    </div>

    @if($errors->any())
    <div class="error-box">
        @foreach($errors->all() as $error)
        <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('register.submit') }}">
        @csrf
        <div class="form-group">
            <label for="name">Nama Lengkap</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Nama Anda" required autofocus>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
        </div>
        <div class="form-group">
            <label for="password_confirmation">Konfirmasi Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password" required>
        </div>
        <button type="submit" class="btn-submit">Daftar Sekarang</button>
    </form>

    <div class="auth-footer">
        Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
    </div>
</div>
</body>
</html>
