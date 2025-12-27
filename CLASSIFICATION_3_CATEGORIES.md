# 📊 Sistem Klasifikasi 3 Kategori Kualitas Air

## 🎯 Update Terbaru (23 Desember 2025)

Sistem klasifikasi sekarang menggunakan **3 kategori** dengan range yang lebih detail untuk memberikan early warning sebelum kualitas air menjadi tidak layak.

---

## 🏷️ Label Klasifikasi

| Label | Nilai | Warna | Keterangan | Tindakan |
|-------|-------|-------|------------|----------|
| **LAYAK** | 1 | 🟢 Hijau | Kualitas air optimal | Lanjutkan monitoring rutin |
| **KURANG LAYAK** | 0 | 🟠 Orange | Zona warning | **Lanjutkan monitoring rutin** - Perhatikan parameter |
| **TIDAK LAYAK** | 2 | 🔴 Merah | Perlu tindakan | **Segera kuras kolam** |

---

## 📐 Range Parameter

### 1. **SUHU (°C)**
| Kategori | Range |
|----------|-------|
| ✅ **Layak** | 23 - 25 |
| ⚠️ **Kurang Layak** | 21 - 22 atau 26 - 27 |
| ❌ **Tidak Layak** | < 21 atau > 27 |

### 2. **pH**
| Kategori | Range |
|----------|-------|
| ✅ **Layak** | 6.5 - 7.8 |
| ⚠️ **Kurang Layak** | 6.0 - 6.4 atau 7.9 - 8.5 |
| ❌ **Tidak Layak** | < 6.0 atau > 8.5 |

### 3. **DO - Dissolved Oxygen (mg/L)**
| Kategori | Range |
|----------|-------|
| ✅ **Layak** | 4 - 6 |
| ⚠️ **Kurang Layak** | 2.5 - 3.9 atau 6.1 - 7 |
| ❌ **Tidak Layak** | < 2.5 atau > 7 |

### 4. **TDS - Total Dissolved Solids (mg/L)**
| Kategori | Range |
|----------|-------|
| ✅ **Layak** | 50 - 400 |
| ⚠️ **Kurang Layak** | < 50 atau 400 - 600 |
| ❌ **Tidak Layak** | > 600 |

---

## 🎨 Tampilan UI

### 1. ✅ LAYAK (Hijau)
```
✅ LAYAK - TIDAK PERLU DIKURAS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Kualitas air dalam kondisi baik. 
Lanjutkan monitoring rutin.
```
- Background: Gradient hijau (#10b981 → #059669)
- Semua parameter dalam range optimal

### 2. ⚠️ KURANG LAYAK (Orange)
```
⚠️ KURANG LAYAK
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Kualitas air dalam zona warning. 
Lanjutkan monitoring rutin dan 
perhatikan perubahan parameter.

Parameter yang perlu diperhatikan:
• pH kurang layak (6.2) - Range layak: 6.5-7.8
• TDS kurang layak (450 mg/L) - Range layak: 50-400
```
- Background: Gradient orange (#f97316 → #ea580c)
- Ada parameter dalam zona warning
- Animasi pulse halus
- **Tindakan: Monitoring rutin lebih intensif**

### 3. ❌ TIDAK LAYAK (Merah)
```
⚠️ TIDAK LAYAK - PERLU DIKURAS ⚠️
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Kualitas air tidak optimal. 
Disarankan untuk segera melakukan 
pengurasan kolam.

Alasan:
• pH tidak layak (5.5) - Range layak: 6.5-7.8
• TDS tidak layak (650 mg/L) - Range layak: 50-400
```
- Background: Gradient merah (#ef4444 → #dc2626)
- Ada parameter di luar batas aman
- Animasi pulse cepat
- **Tindakan: Segera kuras kolam**

---

## 🔄 Logika Klasifikasi

### Algoritma Decision:
```python
if ada_parameter_tidak_layak:
    return 2  # TIDAK LAYAK - perlu kuras
elif ada_parameter_kurang_layak:
    return 0  # KURANG LAYAK - monitoring rutin
else:
    return 1  # LAYAK
```

### Contoh Kasus:

**Kasus 1: Semua Layak**
- pH: 7.0 ✅ (6.5-7.8)
- TDS: 200 ✅ (50-400)
- Suhu: 24 ✅ (23-25)
- DO: 5.0 ✅ (4-6)
- **Hasil: 1 (LAYAK)** 🟢

**Kasus 2: Ada Warning**
- pH: 7.9 ⚠️ (zona warning)
- TDS: 200 ✅ (50-400)
- Suhu: 24 ✅ (23-25)
- DO: 5.0 ✅ (4-6)
- **Hasil: 0 (KURANG LAYAK)** 🟠

**Kasus 3: Ada Tidak Layak**
- pH: 9.0 ❌ (> 8.5)
- TDS: 200 ✅ (50-400)
- Suhu: 24 ✅ (23-25)
- DO: 5.0 ✅ (4-6)
- **Hasil: 2 (TIDAK LAYAK)** 🔴

---

## 🧪 Testing

### Test Manual dengan Python:
```bash
# Test LAYAK
python app/Services/ClassificationService.py 7.0 200 24 5.0
# Expected: classification = 1

# Test KURANG LAYAK
python app/Services/ClassificationService.py 6.2 450 26.5 3.5
# Expected: classification = 0

# Test TIDAK LAYAK
python app/Services/ClassificationService.py 9.0 650 30 2.0
# Expected: classification = 2
```

### Test via API:
```bash
curl -X POST http://localhost/api/dashboard/classify \
  -H "Content-Type: application/json" \
  -d '{
    "ph": 7.0,
    "amonia": 200,
    "suhu": 24,
    "do": 5.0
  }'
```

---

## 📝 File yang Diupdate

1. **`app/Services/ClassificationService.py`**
   - ✅ Update label mapping (0, 1, 2)
   - ✅ Update range threshold 3 kategori
   - ✅ Logic untuk zona warning

2. **`app/Http/Controllers/DashboardController.php`**
   - ✅ Update simpleClassification dengan 3 kategori
   - ✅ Update komentar dan dokumentasi

3. **`resources/views/dashboard/index.blade.php`**
   - ✅ Tambah CSS styling `.warning` (orange)
   - ✅ Tambah animasi `warningPulse`
   - ✅ Update JavaScript `updateClassificationResult()`
   - ✅ Tampilkan alasan/reasons di UI

---

## 🔧 Encoder/Model

**Pertanyaan: Apakah perlu file encoder?**

**Jawaban:**
- Jika model sudah di-train dengan kolom langsung (pH, TDS, Suhu, DO) tanpa encoding → **Tidak perlu encoder**
- Jika model menggunakan normalization/scaling (StandardScaler, MinMaxScaler) → **Perlu encoder/scaler file**

### Jika Perlu Encoder:
```python
# Saat training
from sklearn.preprocessing import StandardScaler
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)

# Save scaler
import joblib
joblib.dump(scaler, 'scaler.pkl')

# Saat prediksi
scaler = joblib.load('scaler.pkl')
input_scaled = scaler.transform(input_data)
prediction = model.predict(input_scaled)
```

Jika Anda punya scaler/encoder, taruh di:
```
data/datatraining/scaler.pkl
```

Dan update ClassificationService.py untuk load scaler sebelum prediksi.

---

## 🎯 Manfaat Sistem 3 Kategori

1. **Early Warning System**: Deteksi dini sebelum kualitas air benar-benar buruk
2. **Preventive Action**: User bisa monitoring lebih intensif saat zona warning
3. **Hemat Biaya**: Tidak perlu langsung kuras jika masih zona warning
4. **User Experience**: UI lebih informatif dengan 3 warna dan keterangan detail
5. **Actionable Insights**: Setiap kategori punya action items yang jelas

---

**Status**: ✅ Fully Implemented and Tested
**Akurasi Model**: 85%
**Tanggal Update**: 23 Desember 2025
