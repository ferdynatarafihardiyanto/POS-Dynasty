<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Kasir & Backoffice - Dynasty Cafe</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo.png') }}">

    <!-- Bootstrap 5 CDN & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Google Fonts: Outfit & DM Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-primary: #922c24;
            --brand-dark: #6e1c16;
            --brand-gold: #f59e0b;
            --bg-surface: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background-color: #fcfbfa;
            font-family: 'DM Sans', sans-serif;
            color: var(--text-main);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            padding: 1.5rem;
        }

        /* Ambient Background Atmosphere */
        .ambient-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .ambient-glow-1 {
            position: absolute;
            top: -10%;
            right: -5%;
            width: 550px;
            height: 550px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(146, 44, 36, 0.12) 0%, rgba(146, 44, 36, 0) 70%);
            filter: blur(60px);
        }

        .ambient-glow-2 {
            position: absolute;
            bottom: -15%;
            left: -10%;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.09) 0%, rgba(245, 158, 11, 0) 70%);
            filter: blur(70px);
        }

        /* Subtle Geometric Grid Overlay */
        .grid-pattern {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(146, 44, 36, 0.08) 1px, transparent 1px);
            background-size: 28px 28px;
            opacity: 0.6;
        }

        /* Login Container & Card */
        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 28px;
            border: 1px solid rgba(226, 232, 240, 0.85);
            box-shadow: 
                0 4px 6px -1px rgba(0, 0, 0, 0.03),
                0 20px 40px -12px rgba(146, 44, 36, 0.08),
                0 1px 3px 0 rgba(0, 0, 0, 0.02);
            padding: 2.75rem 2.25rem 2.25rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 2rem 1.5rem 1.75rem;
                border-radius: 22px;
            }
        }

        /* Logo Emblem Container */
        .brand-emblem-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 18px;
            border-radius: 20px;
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 8px 16px -4px rgba(146, 44, 36, 0.06);
            margin-bottom: 1.25rem;
        }

        .brand-logo-img {
            max-height: 52px;
            width: auto;
            max-width: 170px;
            object-fit: contain;
            display: block;
        }

        .brand-logo-fallback {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--brand-primary);
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: -0.5px;
        }

        .brand-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.6rem;
            letter-spacing: -0.5px;
            color: #0f172a;
            margin-bottom: 0.25rem;
        }

        .brand-subtitle {
            font-size: 0.86rem;
            color: var(--text-muted);
            margin-bottom: 0;
        }

        .badge-system {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background-color: #fef2f2;
            color: var(--brand-primary);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.4px;
            padding: 4px 10px;
            border-radius: 9999px;
            border: 1px solid rgba(146, 44, 36, 0.15);
            margin-top: 0.5rem;
        }

        /* Form Inputs */
        .form-label-custom {
            font-size: 0.82rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .input-group-custom {
            position: relative;
            display: flex;
            align-items: center;
            border-radius: 14px;
            background-color: #f8fafc;
            border: 1.5px solid #e2e8f0;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .input-group-custom:focus-within {
            background-color: #ffffff;
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 4px rgba(146, 44, 36, 0.12);
        }

        .input-icon-left {
            padding-left: 14px;
            padding-right: 6px;
            color: #94a3b8;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }

        .input-group-custom:focus-within .input-icon-left {
            color: var(--brand-primary);
        }

        .form-control-custom {
            border: none;
            background: transparent;
            padding: 0.78rem 0.85rem 0.78rem 0.4rem;
            font-size: 0.92rem;
            color: #0f172a;
            width: 100%;
            outline: none;
            box-shadow: none !important;
            font-family: inherit;
        }

        .form-control-custom::placeholder {
            color: #94a3b8;
            font-size: 0.86rem;
        }

        .btn-toggle-password {
            background: transparent;
            border: none;
            color: #94a3b8;
            padding: 0 14px;
            font-size: 1.05rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }

        .btn-toggle-password:hover {
            color: #334155;
        }

        /* Submit Button */
        .btn-login {
            background: linear-gradient(135deg, #922c24 0%, #6e1c16 100%);
            color: #ffffff;
            border: none;
            border-radius: 14px;
            padding: 0.85rem 1.25rem;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            transition: all 0.25s ease;
            box-shadow: 0 8px 18px -4px rgba(146, 44, 36, 0.35);
            margin-top: 0.5rem;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #a33229 0%, #7d1f18 100%);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -4px rgba(146, 44, 36, 0.45);
        }

        .btn-login:active {
            transform: translateY(0);
            box-shadow: 0 4px 10px rgba(146, 44, 36, 0.3);
        }

        /* Security Note / Footer */
        .footer-note {
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            background-color: #22c55e;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
        }
    </style>
</head>
<body>

    <!-- Ambient Lighting & Background -->
    <div class="ambient-bg">
        <div class="grid-pattern"></div>
        <div class="ambient-glow-1"></div>
        <div class="ambient-glow-2"></div>
    </div>

    <!-- Main Container -->
    <div class="login-wrapper">
        <div class="login-card">
            
            <!-- Header Branding -->
            <div class="text-center mb-4">
                <div class="brand-emblem-wrap">
                    <img src="{{ asset('images/Logo.png') }}" 
                         alt="Logo Dynasty Cafe" 
                         class="brand-logo-img" 
                         onerror="this.style.display='none'; document.getElementById('logoFallback').classList.remove('d-none');">
                    <div id="logoFallback" class="brand-logo-fallback d-none">
                        <i class="bi bi-cup-hot-fill"></i> DYNASTY <span>CAFE</span>
                    </div>
                </div>

                <h1 class="brand-title">Dynasty Cafe</h1>
                <p class="brand-subtitle">Portal Akses Point of Sale & Backoffice</p>
                <div class="badge-system">
                    <span class="status-dot"></span> POS ENGINE v2.4 AKTIF
                </div>
            </div>

            <!-- Error Notification Alert -->
            @if($errors->any())
                <div class="alert alert-danger py-2.5 px-3 rounded-3 border-0 d-flex align-items-center gap-2 mb-3 shadow-xs" style="background-color: #fef2f2; color: #991b1b; font-size: 0.85rem;" role="alert">
                    <i class="bi bi-exclamation-octagon-fill fs-6 flex-shrink-0"></i>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('admin.login') }}" autocomplete="on">
                @csrf

                <!-- Email / Username Input -->
                <div class="mb-3">
                    <label class="form-label-custom" for="loginEmail">
                        <span>Email / Username Kasir</span>
                    </label>
                    <div class="input-group-custom">
                        <div class="input-icon-left">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <input type="text" 
                               id="loginEmail"
                               name="email" 
                               class="form-control-custom" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus 
                               placeholder="Masukkan email atau username"
                               autocomplete="username">
                    </div>
                </div>

                <!-- Password Input with Toggle -->
                <div class="mb-4">
                    <label class="form-label-custom" for="loginPassword">
                        <span>Kata Sandi (Password)</span>
                    </label>
                    <div class="input-group-custom">
                        <div class="input-icon-left">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <input type="password" 
                               id="loginPassword"
                               name="password" 
                               class="form-control-custom" 
                               required 
                               placeholder="••••••••"
                               autocomplete="current-password">
                        <button type="button" 
                                class="btn-toggle-password" 
                                id="togglePasswordBtn" 
                                title="Lihat/Sembunyikan Sandi"
                                tabindex="-1">
                            <i class="bi bi-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-login" id="submitLoginBtn">
                    <span>Masuk ke Sistem Kasir</span>
                    <i class="bi bi-arrow-right-short fs-4 lh-1"></i>
                </button>
            </form>

            <!-- Card Footer Note -->
            <div class="footer-note">
                <div class="d-flex align-items-center justify-content-center gap-1.5 mb-1">
                    <i class="bi bi-lock-fill text-muted" style="font-size: 0.7rem;"></i>
                    <span>Koneksi Sesi Terenkripsi & Aman</span>
                </div>
                <div>&copy; {{ date('Y') }} Kedai Kopi Dynasty. All Rights Reserved.</div>
            </div>

        </div>
    </div>

    <!-- Interactive Script for Password Toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('togglePasswordBtn');
            const passInput = document.getElementById('loginPassword');
            const toggleIcon = document.getElementById('togglePasswordIcon');

            if (toggleBtn && passInput && toggleIcon) {
                toggleBtn.addEventListener('click', () => {
                    const isPassword = passInput.type === 'password';
                    passInput.type = isPassword ? 'text' : 'password';
                    toggleIcon.classList.toggle('bi-eye', !isPassword);
                    toggleIcon.classList.toggle('bi-eye-slash', isPassword);
                    passInput.focus();
                });
            }
        });
    </script>
</body>
</html>
