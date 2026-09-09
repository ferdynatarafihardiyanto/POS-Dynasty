<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <title>Kedai Dynasty - Dine-in & Mobile Ordering</title>

    <!-- Google Fonts: Plus Jakarta Sans & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @php
        $manifestPath = public_path('build/manifest.json');
        $hasManifest = file_exists($manifestPath);
    @endphp

    @if($hasManifest)
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @else
        <link rel="stylesheet" href="/build/assets/app-CIotbjir.css">
        <script type="module" src="/build/assets/app-CE9_9mHz.js"></script>
    @endif

    <!-- Midtrans Snap JS SDK (Sandbox / Production) -->
    <script 
        src="{{ config('midtrans.snap_js_url') }}" 
        data-client-key="{{ config('midtrans.client_key') }}">
    </script>
</head>

<body class="bg-stone-50 text-stone-900 font-sans antialiased selection:bg-amber-500 selection:text-white min-h-screen">
    <div id="app">
        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100vh; font-family:sans-serif; text-align:center; padding:20px;">
            <div style="font-size:32px; margin-bottom:10px;">☕</div>
            <h2 style="margin:0 0 8px 0; color:#1c1917; font-size:20px; font-weight:700;">Memuat Menu Restoran...</h2>
            <p style="margin:0; color:#6b7280; font-size:14px;">Mohon tunggu sebentar, sedang menyiapkan meja Anda.</p>
        </div>
    </div>
</body>
</html>