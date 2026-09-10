#!/bin/bash
set -e

echo "=================================================="
echo "🚀 [POS DYNASTY] Memulai Pembaruan Sistem..."
echo "=================================================="

# 1. Periksa apakah menggunakan docker-compose atau docker compose
if command -v docker-compose &> /dev/null; then
    DOCKER_CMD="docker-compose"
else
    DOCKER_CMD="docker compose"
fi

# 2. Tarik kode terbaru dari GitHub
echo "📥 [1/6] Menarik kode terbaru dari GitHub (origin main)..."
git pull origin main

# 3. Jalankan container dengan volume terbaru
echo "🐳 [2/6] Memastikan container Docker berjalan..."
$DOCKER_CMD up -d

# 4. Install dependensi Composer terbaru (termasuk paket DomPDF)
echo "📦 [3/6] Memperbarui paket dependensi Composer di server..."
$DOCKER_CMD exec -T app composer install --no-dev --optimize-autoloader --no-interaction || true

# 5. Jalankan migrasi database otomatis
echo "🗄️ [4/6] Menjalankan migrasi database otomatis..."
$DOCKER_CMD exec -T app php artisan migrate --force

# 6. Bersihkan cache aplikasi agar perubahan langsung aktif
echo "🧹 [5/6] Membersihkan cache aplikasi..."
$DOCKER_CMD exec -T app php artisan optimize:clear

# 7. Pastikan permission folder storage & cache aman
$DOCKER_CMD exec -T app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache || true
$DOCKER_CMD exec -T app chmod -R 775 /var/www/storage /var/www/bootstrap/cache || true

# 8. Restart container app agar package PHP baru dimuat oleh PHP-FPM
echo "🔄 [6/6] Merestart service app..."
$DOCKER_CMD restart app

echo "=================================================="
echo "✅ [POS DYNASTY] Selesai! Aplikasi berhasil diupdate."
echo "=================================================="
