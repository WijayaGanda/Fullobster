# Dashboard Pemantauan Kualitas Air Tawar

Dashboard monitoring real-time untuk pemantauan kualitas air budidaya dengan klasifikasi AI otomatis.

## Fitur Utama

### 📊 Monitoring Real-time
- 4 Grafik terpisah untuk setiap parameter:
  - pH Air
  - Amonia (mg/L)
  - Suhu (°C)
  - Oksigen Terlarut/DO (mg/L)

### 🤖 Klasifikasi AI Otomatis
- Klasifikasi otomatis saat data baru terbaca
- Menggunakan model Decision Tree yang sudah ditraining
- Menentukan apakah kolam perlu dikuras atau tidak

### ⏱️ Update Otomatis
- Data berpindah otomatis setiap 60 detik
- Countdown timer untuk update berikutnya
- Kontrol manual (Start, Pause, Reset)

## Instalasi

### 1. Persyaratan
- PHP >= 8.1
- Composer
- Python 3.x (untuk klasifikasi AI)
- Laravel 11.x

### 2. Install Dependencies PHP
```bash
composer install
```

### 3. Install Dependencies Python
```bash
pip install numpy scikit-learn
```

### 4. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Jalankan Server
```bash
php artisan serve
```

### 6. Akses Dashboard
Buka browser: `http://127.0.0.1:8000/dashboard`

## Cara Menggunakan

1. **Klik "▶ Mulai Monitoring"** untuk memulai
2. Data akan muncul langsung dan update setiap 60 detik
3. **Panel Klasifikasi AI** akan menampilkan:
   - ⚠️ **PERLU DIKURAS** - Jika kualitas air tidak optimal
   - ✅ **TIDAK PERLU DIKURAS** - Jika kualitas air masih baik
4. Gunakan **"⏸ Pause"** untuk menghentikan sementara
5. Gunakan **"🔄 Reset"** untuk mulai dari awal

## Struktur Data

Data CSV harus memiliki format:
```csv
Date;pH;amonia;suhu;do
01/01/2023;7.02;0.05;24.2;3.8
```

## Model Klasifikasi

Dashboard menggunakan:
1. **Model Decision Tree** (Primary) - File: `data/datatraining/model_decision_tree.pkl`
2. **Simple Threshold** (Fallback) - Jika model tidak tersedia

### Threshold Klasifikasi:
- pH: 6.3 - 7.7 (optimal)
- Amonia: < 0.06 mg/L (aman)
- Suhu: 21 - 28 °C (ideal)
- DO: > 2.5 mg/L (cukup)

## File Penting

- `app/Http/Controllers/DashboardController.php` - Backend controller
- `app/Services/ClassificationService.py` - Script Python untuk klasifikasi
- `resources/views/dashboard/index.blade.php` - Frontend dashboard
- `data/datamentah/dataset_dummy.csv` - Data mentah
- `data/datatraining/model_decision_tree.pkl` - Model ML

## Troubleshooting

### Python tidak terdeteksi
Pastikan Python sudah terinstall dan ada di PATH:
```bash
python --version
```

Atau edit `DashboardController.php` untuk menambahkan path Python yang benar.

### Model tidak bisa dimuat
Sistem akan otomatis menggunakan klasifikasi threshold sederhana sebagai fallback.

## Pengembangan Selanjutnya

- [ ] Export data hasil monitoring
- [ ] Notifikasi email/WhatsApp saat perlu dikuras
- [ ] Historical data analysis
- [ ] Multiple tank monitoring
- [ ] Integration dengan IoT sensors

## Lisensi

Capstone Project - Semester 7

---
Dibuat dengan ❤️ untuk budidaya perairan yang lebih baik
