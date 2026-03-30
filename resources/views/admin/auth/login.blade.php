<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — eHealthFinder</title>
<meta name="robots" content="noindex,nofollow">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'Inter', sans-serif;
    background: #0f172a;
    color: #f1f5f9;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-image: radial-gradient(ellipse at 20% 20%, rgba(79,70,229,0.15) 0%, transparent 50%),
                      radial-gradient(ellipse at 80% 80%, rgba(16,185,129,0.08) 0%, transparent 50%);
}
.login-card {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 24px;
    padding: 2.5rem;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.5);
}
.login-logo {
    text-align: center;
    margin-bottom: 2rem;
}
.login-logo .icon {
    width: 64px; height: 64px;
    background: linear-gradient(135deg, #4f46e5, #818cf8);
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem;
    margin: 0 auto 1rem;
    box-shadow: 0 8px 25px rgba(79,70,229,0.4);
}
.login-logo h1 { font-size: 1.5rem; font-weight: 800; }
.login-logo p { color: #94a3b8; font-size: 0.85rem; margin-top: 0.3rem; }

.form-group { margin-bottom: 1.25rem; }
.form-label { display: block; font-size: 0.8rem; font-weight: 600; color: #94a3b8; margin-bottom: 0.5rem; }
.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    background: rgba(255,255,255,0.06);
    border: 1px solid #334155;
    border-radius: 10px;
    color: #f1f5f9;
    font-size: 0.9rem;
    font-family: 'Inter', sans-serif;
    transition: border-color 0.2s;
}
.form-control:focus { outline: none; border-color: #4f46e5; background: rgba(79,70,229,0.08); }
.btn-login {
    width: 100%;
    padding: 0.85rem;
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    font-family: 'Inter', sans-serif;
    transition: all 0.2s;
    box-shadow: 0 4px 15px rgba(79,70,229,0.3);
}
.btn-login:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(79,70,229,0.45); }
.alert-error { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #f87171; padding: 0.75rem 1rem; border-radius: 10px; font-size: 0.85rem; margin-bottom: 1.25rem; }
.site-link { text-align: center; margin-top: 1.5rem; }
.site-link a { color: #64748b; font-size: 0.8rem; text-decoration: none; }
.site-link a:hover { color: #94a3b8; }
</style>
</head>
<body>
<div class="login-card">
    <div class="login-logo">
        <div class="icon">🏥</div>
        <h1>eHealthFinder</h1>
        <p>Admin Control Panel</p>
    </div>

    @if($errors->any())
        <div class="alert-error">{{ $errors->first('login') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.login.post') }}">
        @csrf
        <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="admin@ehealthfinder.com" required autofocus>
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-login">🔐 Sign In to Admin Panel</button>
    </form>

    <div class="site-link">
        <a href="{{ url('/') }}">← Back to eHealthFinder</a>
    </div>
</div>
</body>
</html>
