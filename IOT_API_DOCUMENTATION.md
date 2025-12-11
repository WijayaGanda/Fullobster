# API Dokumentasi - IoT Sensor Data

## Overview
API ini digunakan untuk menerima dan mengelola data sensor IoT untuk monitoring kualitas air. API menyediakan endpoint untuk menerima data sensor secara real-time dan menampilkannya di dashboard.

## Base URL
- **Development**: `http://localhost:8000`
- **Production**: `https://yourdomain.com`

## Authentication
Saat ini API tidak menggunakan authentication, tetapi untuk production disarankan menambahkan API key atau token.

## Endpoints

### 1. Menerima Data Sensor
**POST** `/api/iot/data`

Endpoint untuk menerima data dari sensor IoT dan menyimpannya ke database.

#### Request Body
```json
{
    "suhu": 28.5,
    "ph": 7.2,
    "do": 6.8,
    "tds": 320,
    "amonia": 1.5
}
```

#### Parameters
| Parameter | Type    | Required | Description                    |
|-----------|---------|----------|--------------------------------|
| suhu      | float   | Yes      | Suhu air dalam Celsius         |
| ph        | float   | Yes      | Tingkat pH air (6.0-8.5)      |
| do        | float   | Yes      | Dissolved Oxygen (mg/L)        |
| tds       | integer | Yes      | Total Dissolved Solids (ppm)   |
| amonia    | float   | Yes      | Kadar amonia (mg/L)            |

#### Response Success (201)
```json
{
    "success": true,
    "message": "Data received and stored successfully",
    "data": {
        "id": 123,
        "suhu": 28.5,
        "ph": 7.2,
        "do": 6.8,
        "tds": 320,
        "amonia": 1.5,
        "created_at": "2025-01-27T10:30:00.000000Z"
    },
    "classification": {
        "result": "maintain",
        "recommendation": "Kualitas air baik, lanjutkan monitoring"
    }
}
```

#### Response Error (422)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "suhu": ["Suhu field is required"],
        "ph": ["pH must be between 0 and 14"]
    }
}
```

### 2. Ambil Data Terbaru
**GET** `/api/iot/latest`

Mengambil 10 data sensor terbaru untuk ditampilkan di dashboard.

#### Response (200)
```json
[
    {
        "id": 123,
        "suhu": 28.5,
        "ph": 7.2,
        "do": 6.8,
        "tds": 320,
        "amonia": 1.5,
        "created_at": "2025-01-27T10:30:00.000000Z"
    },
    // ... 9 data lainnya
]
```

### 3. Ambil Semua Data
**GET** `/api/iot/all`

Mengambil semua data sensor dengan pagination.

#### Query Parameters
| Parameter | Type    | Default | Description           |
|-----------|---------|---------|----------------------|
| page      | integer | 1       | Nomor halaman        |
| per_page  | integer | 50      | Data per halaman     |

#### Response (200)
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 123,
            "suhu": 28.5,
            "ph": 7.2,
            "do": 6.8,
            "tds": 320,
            "amonia": 1.5,
            "created_at": "2025-01-27T10:30:00.000000Z"
        }
        // ... data lainnya
    ],
    "total": 500,
    "per_page": 50,
    "last_page": 10
}
```

### 4. Test Endpoint
**POST** `/api/iot/test`

Endpoint khusus untuk development, mengirim data sample ke database.

#### Response (201)
```json
{
    "success": true,
    "message": "Test data sent successfully",
    "data": {
        // ... sample data
    }
}
```

## Klasifikasi Kualitas Air

Sistem secara otomatis mengklasifikasikan kualitas air berdasarkan parameter:

### Kriteria "DRAIN" (Perlu Ganti Air):
- **Suhu**: > 30°C
- **pH**: < 6.5 atau > 8.0
- **DO**: < 5.0 mg/L
- **Amonia**: > 2.0 mg/L

### Kriteria "MAINTAIN" (Kondisi Baik):
- Semua parameter dalam rentang normal

## Error Codes

| Status Code | Description                |
|-------------|----------------------------|
| 200         | Success                    |
| 201         | Created                    |
| 400         | Bad Request                |
| 422         | Validation Error           |
| 500         | Internal Server Error      |

## Example Code

### Arduino/ESP32 Example
```cpp
#include <WiFi.h>
#include <HTTPClient.h>

void sendSensorData(float suhu, float ph, float doValue, int tds, float amonia) {
    HTTPClient http;
    http.begin("https://yourdomain.com/api/iot/data");
    http.addHeader("Content-Type", "application/json");
    
    String jsonData = "{";
    jsonData += "\"suhu\":" + String(suhu) + ",";
    jsonData += "\"ph\":" + String(ph) + ",";
    jsonData += "\"do\":" + String(doValue) + ",";
    jsonData += "\"tds\":" + String(tds) + ",";
    jsonData += "\"amonia\":" + String(amonia);
    jsonData += "}";
    
    int httpResponseCode = http.POST(jsonData);
    
    if(httpResponseCode > 0) {
        String response = http.getString();
        Serial.println("Response: " + response);
    }
    
    http.end();
}
```

### Python Example
```python
import requests
import json

def send_sensor_data(suhu, ph, do_value, tds, amonia):
    url = "https://yourdomain.com/api/iot/data"
    data = {
        "suhu": suhu,
        "ph": ph,
        "do": do_value,
        "tds": tds,
        "amonia": amonia
    }
    
    response = requests.post(url, json=data)
    
    if response.status_code == 201:
        print("Data sent successfully:", response.json())
    else:
        print("Error:", response.json())

# Usage
send_sensor_data(28.5, 7.2, 6.8, 320, 1.5)
```

## Monitoring Dashboard

Dashboard secara otomatis akan:
1. Mendeteksi data IoT real-time vs data CSV
2. Auto-refresh setiap 30 detik untuk data IoT
3. Menampilkan klasifikasi otomatis
4. Menunjukkan grafik real-time untuk 4 parameter

## Rate Limiting

Untuk production, disarankan menambahkan rate limiting:
- Maximum 60 requests per minute per IP
- Maximum 1000 requests per hour per API key

## Security

Untuk production, implementasikan:
1. API Key authentication
2. HTTPS only
3. Input validation dan sanitization
4. Rate limiting
5. CORS configuration

## Support

Untuk pertanyaan teknis atau bug report, hubungi tim development Fullobster.