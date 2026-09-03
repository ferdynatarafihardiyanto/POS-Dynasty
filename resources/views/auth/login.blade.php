<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dynasty Cafe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 100%; max-width: 400px; padding: 2rem; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="card login-card border-0">
        <div class="text-center mb-4">
            <h1 class="fw-bold">☕</h1>
            <h3 class="fw-bold mt-2">Dynasty Cafe</h3>
            <p class="text-muted">Login Backoffice / Kasir</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2 border-0 shadow-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ url('/login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Email Account</label>
                <input type="email" name="email" class="form-control form-control-lg bg-light" value="{{ old('email') }}" required autofocus placeholder="admin@cafe.test">
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control form-control-lg bg-light" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-dark btn-lg w-100 fw-bold shadow-sm">MASUK SISTEM</button>
        </form>
    </div>
</body>
</html>
