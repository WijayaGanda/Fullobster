<?php
/**
 * Test Script untuk API IoT Endpoint
 * 
 * Script ini untuk mensimulasikan pengiriman data dari sensor IoT
 * ke endpoint API yang telah dibuat.
 */

// URL API endpoint (ganti dengan URL hosting Anda)
$base_url = 'http://localhost:8000'; // Untuk testing lokal
// $base_url = 'https://yourdomain.com'; // Untuk production

$api_url = $base_url . '/api/iot/data';

// Fungsi untuk mengirim data ke API
function sendSensorData($url, $data) {
    $json_data = json_encode($data);
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => $json_data,
        ],
    ];
    
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        return ['error' => 'Failed to send data'];
    }
    
    return json_decode($result, true);
}

// Generate random sensor data untuk testing
function generateRandomSensorData() {
    return [
        'suhu' => round(rand(200, 350) / 10, 1),    // 20.0 - 35.0°C
        'ph' => round(rand(60, 85) / 10, 1),        // 6.0 - 8.5 pH
        'do' => round(rand(40, 80) / 10, 1),        // 4.0 - 8.0 mg/L
        'tds' => rand(100, 500),                    // 100 - 500 ppm
        'amonia' => round(rand(1, 30) / 10, 2)      // 0.10 - 3.00 mg/L
    ];
}

echo "🚀 Testing IoT API Endpoints\n";
echo "==============================\n\n";

// Test 1: Send single sensor data
echo "📊 Test 1: Sending single sensor data...\n";
$test_data = generateRandomSensorData();

echo "Data to send:\n";
foreach ($test_data as $key => $value) {
    echo "  $key: $value\n";
}
echo "\n";

$response = sendSensorData($api_url, $test_data);
echo "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n\n";

// Test 2: Send multiple data points
echo "📈 Test 2: Sending multiple data points...\n";
for ($i = 1; $i <= 5; $i++) {
    echo "Sending data point #$i...\n";
    $test_data = generateRandomSensorData();
    $response = sendSensorData($api_url, $test_data);
    
    if (isset($response['success'])) {
        echo "✅ Data point #$i sent successfully\n";
    } else {
        echo "❌ Failed to send data point #$i\n";
        echo "Error: " . json_encode($response) . "\n";
    }
    
    sleep(2); // Wait 2 seconds between requests
}

echo "\n";

// Test 3: Get latest data
echo "📥 Test 3: Getting latest data...\n";
$latest_url = $base_url . '/api/iot/latest';
$latest_data = file_get_contents($latest_url);

if ($latest_data) {
    $latest_json = json_decode($latest_data, true);
    echo "Latest data: " . json_encode($latest_json, JSON_PRETTY_PRINT) . "\n\n";
} else {
    echo "❌ Failed to get latest data\n\n";
}

// Test 4: Send data dengan nilai extreme untuk test classification
echo "⚠️  Test 4: Sending extreme values for classification test...\n";
$extreme_data = [
    'suhu' => 35.5,    // High temperature
    'ph' => 9.2,       // High pH
    'do' => 2.0,       // Low dissolved oxygen
    'tds' => 600,      // High TDS
    'amonia' => 5.0    // High ammonia
];

echo "Extreme data to send:\n";
foreach ($extreme_data as $key => $value) {
    echo "  $key: $value\n";
}

$response = sendSensorData($api_url, $extreme_data);
echo "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n\n";

echo "🏁 Testing completed!\n";
echo "==============================\n";
echo "💡 Cara menggunakan:\n";
echo "1. Pastikan Laravel server sudah running (php artisan serve)\n";
echo "2. Ganti \$base_url dengan URL hosting Anda untuk production\n";
echo "3. Jalankan script ini: php test_iot_api.php\n";
echo "4. Cek dashboard di browser untuk melihat data real-time\n\n";

echo "📡 API Endpoints yang tersedia:\n";
echo "POST /api/iot/data        - Menerima data dari sensor\n";
echo "GET  /api/iot/latest      - Ambil data terbaru\n";
echo "GET  /api/iot/all         - Ambil semua data\n";
echo "POST /api/iot/test        - Endpoint test untuk development\n";
?>