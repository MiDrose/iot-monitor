<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IoT Monitor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#0a0e1a;color:#e2e8f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px}
        body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse at 30% 20%,rgba(59,130,246,0.1),transparent 50%),radial-gradient(ellipse at 70% 80%,rgba(139,92,246,0.08),transparent 50%);pointer-events:none}
        .auth-card{position:relative;background:rgba(15,23,42,0.7);border:1px solid rgba(255,255,255,0.06);border-radius:24px;padding:40px;max-width:420px;width:100%;backdrop-filter:blur(16px);box-shadow:0 8px 40px rgba(0,0,0,0.4)}
        .logo{text-align:center;margin-bottom:32px}
        .logo-icon{width:56px;height:56px;background:linear-gradient(135deg,#3b82f6,#06b6d4);border-radius:14px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;box-shadow:0 4px 20px rgba(59,130,246,0.35)}
        .logo-icon svg{width:28px;height:28px;color:#fff}
        .logo h1{font-size:1.5rem;font-weight:800;letter-spacing:-0.03em;background:linear-gradient(135deg,#f1f5f9,#3b82f6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .logo p{font-size:0.85rem;color:#64748b;margin-top:4px}
        .form-group{margin-bottom:18px}
        .form-group label{display:block;font-size:0.8rem;font-weight:600;color:#94a3b8;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em}
        .form-group input{width:100%;padding:12px 16px;background:rgba(30,41,59,0.7);border:1px solid rgba(255,255,255,0.08);border-radius:10px;color:#e2e8f0;font-size:0.95rem;font-family:'Inter',sans-serif;outline:none;transition:all 0.2s}
        .form-group input:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,0.15)}
        .form-group input::placeholder{color:#475569}
        .checkbox-row{display:flex;align-items:center;gap:8px;margin-bottom:20px}
        .checkbox-row input[type="checkbox"]{width:16px;height:16px;accent-color:#3b82f6}
        .checkbox-row label{font-size:0.85rem;color:#94a3b8;cursor:pointer}
        .btn-submit{width:100%;padding:14px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;transition:all 0.2s;font-family:'Inter',sans-serif;box-shadow:0 4px 15px rgba(59,130,246,0.4)}
        .btn-submit:hover{box-shadow:0 6px 25px rgba(59,130,246,0.6);transform:translateY(-1px)}
        .error-box{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);border-radius:10px;padding:12px 16px;margin-bottom:20px}
        .error-box p{color:#ef4444;font-size:0.85rem}
        .auth-footer{text-align:center;margin-top:24px;font-size:0.85rem;color:#64748b}
        .auth-footer a{color:#3b82f6;text-decoration:none;font-weight:600}
        .auth-footer a:hover{text-decoration:underline}
    </style>
</head>
<body>
<div class="auth-card">
    <div class="logo">
        <div class="logo-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"/></svg>
        </div>
        <h1>IoT Monitor</h1>
        <p>Masuk ke akun Anda</p>
    </div>

    @if($errors->any())
    <div class="error-box">
        @foreach($errors->all() as $error)
        <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('login.submit') }}">
        @csrf
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>
        <div class="checkbox-row">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Ingat saya</label>
        </div>
        <button type="submit" class="btn-submit">Masuk</button>
    </form>

    <div class="auth-footer">
        Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a>
    </div>
</div>
</body>
</html>
