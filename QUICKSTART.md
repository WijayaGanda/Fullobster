# Quick Start - Dashboard Monitoring Kualitas Air

## 🚀 Langkah Cepat

### 1. Start Server
```bash
php artisan serve
```

### 2. Buka Browser
```
http://127.0.0.1:8000/dashboard
```

### 3. Klik "▶ Mulai Monitoring"
- Data akan muncul langsung
- Klasifikasi AI **otomatis** berjalan
- Update setiap 60 detik

## 📊 Yang Akan Anda Lihat

### 4 Grafik Terpisah:
1. **pH Air** - Warna Biru
2. **Amonia** - Warna Hijau  
3. **Suhu** - Warna Orange
4. **DO (Oksigen)** - Warna Ungu

### Panel Klasifikasi (Otomatis):
- 🔴 **PERLU DIKURAS** - Jika ada masalah
- 🟢 **TIDAK PERLU DIKURAS** - Jika aman

## ⚙️ Kontrol

- **▶ Mulai** - Mulai monitoring
- **⏸ Pause** - Jeda sementara  
- **🔄 Reset** - Ulang dari awal

## 🧪 Test Klasifikasi

Jalankan test script:
```bash
python app\Services\TestClassification.py
```

## 📋 Threshold Klasifikasi

| Parameter | Aman | Perlu Kuras |
|-----------|------|-------------|
| pH | 6.3-7.7 | < 6.3 atau > 7.7 |
| Amonia | ≤ 0.06 | > 0.06 mg/L |
| Suhu | 21-28°C | < 21 atau > 28°C |
| DO | ≥ 2.5 | < 2.5 mg/L |

## 🐛 Troubleshooting

**Dashboard tidak muncul?**
- Cek server: `php artisan serve`
- Refresh browser

**Klasifikasi tidak update?**
- Normal jika menggunakan fallback threshold
- Check console browser (F12) untuk error

**Python error?**
- Pastikan Python terinstall: `python --version`
- Install dependencies: `pip install numpy scikit-learn`

## 📞 Bantuan

Lihat dokumentasi lengkap:
- `UPDATE_SUMMARY.md` - Detail lengkap perubahan
- `DASHBOARD_README.md` - Dokumentasi pengguna

---
✨ **Selamat monitoring!** ✨
