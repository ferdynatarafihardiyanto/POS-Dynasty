#!/bin/bash
set -e

echo "=================================================="
echo "🚀 [POS DYNASTY] Memulai Pembaruan Sistem..."
echo "=================================================="

# 1. Tarik kode terbaru dari GitHub
echo "📥 [1/4] Menarik kode terbaru dari GitHub (origin main)..."
git pull origin main

# 2. Periksa apakah menggunakan docker-compose atau docker compose
if command -v docker-compose &> /dev/null; then
    DOCKER_CMD="docker-compose"
else
    DOCKER_CMD="docker compose"
fi

# 3. Jalankan container dengan volume terbaru
echo "🐳 [2/5] Memperbarui container Docker..."
$DOCKER_CMD up -d

# 4. Install dependensi Composer terbaru (seperti barryvdh/laravel-dompdf untuk PDF)
echo "📦 [3/5] Memperbarui paket dependensi Composer di server..."
$DOCKER_CMD exec -T app composer install --no-dev --optimize-autoloader --no-interaction || true

# 5. Jalankan migrasi database otomatis
echo "🗄️ [4/5] Menjalankan migrasi database otomatis..."
$DOCKER_CMD exec -T app php artisan migrate --force

# 6. Bersihkan cache aplikasi agar fitur & logo langsung muncul
echo "🧹 [5/5] Membersihkan cache aplikasi..."
$DOCKER_CMD exec -T app php artisan optimize:clear

# 7. Restart container app agar package PHP baru dimuat oleh PHP-FPM
echo "🔄 Merestart service app..."
$DOCKER_CMD restart app

echo "=================================================="
echo "✅ [POS DYNASTY] Selesai! Aplikasi berhasil diupdate."
echo "=================================================="
