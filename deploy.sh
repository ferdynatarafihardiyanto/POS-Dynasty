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
echo "🐳 [2/4] Memperbarui container Docker..."
$DOCKER_CMD up -d

# 4. Jalankan migrasi database otomatis
echo "🗄️ [3/4] Menjalankan migrasi database otomatis..."
$DOCKER_CMD exec -T app php artisan migrate --force

# 5. Bersihkan cache aplikasi agar fitur & logo langsung muncul
echo "🧹 [4/4] Membersihkan cache aplikasi..."
$DOCKER_CMD exec -T app php artisan optimize:clear

echo "=================================================="
echo "✅ [POS DYNASTY] Selesai! Aplikasi berhasil diupdate."
echo "=================================================="
