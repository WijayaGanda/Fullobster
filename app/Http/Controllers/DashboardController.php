<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Monitorings;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index');
    }

    public function getData(Request $request)
    {
        // Check if we have IoT data in database
        $iotDataExists = Monitorings::count() > 0;
        
        if ($iotDataExists && !$request->has('use_csv')) {
            return $this->getIoTData($request);
        }
        
        return $this->getCSVData($request);
    }

    private function getIoTData(Request $request)
    {
        // Get data from database - urut dari lama ke baru (chronological order)
        $monitorings = Monitorings::orderBy('id', 'asc')
            ->limit(100) // Last 100 records for chart performance
            ->get();

        $data = $monitorings->map(function($monitoring, $index) {
            return [
                'index' => $index,
                'date' => $monitoring->created_at ? $monitoring->created_at->format('Y-m-d H:i:s') : '',
                'ph' => floatval($monitoring->ph ?? 0),
                'amonia' => floatval($monitoring->tds ?? 0), // Gunakan TDS sebagai amonia
                'suhu' => floatval($monitoring->suhu ?? 0),
                'do' => floatval($monitoring->do ?? 0)
            ];
        });

        return response()->json($data);
    }

    private function getCSVData(Request $request)
    {
        $csvPath = base_path('data/datamentah/dataset_dummy.csv');
        
        if (!File::exists($csvPath)) {
            return response()->json(['error' => 'Data file not found'], 404);
        }

        $data = [];
        $file = fopen($csvPath, 'r');
        
        // Skip header
        $header = fgetcsv($file, 0, ';');
        
        // Read all data
        $rowIndex = 0;
        while (($row = fgetcsv($file, 0, ';')) !== false) {
            $data[] = [
                'index' => $rowIndex,
                'date' => $row[0],
                'ph' => floatval($row[1]),
                'amonia' => floatval($row[2]),
                'suhu' => floatval($row[3]),
                'do' => floatval($row[4])
            ];
            $rowIndex++;
        }
        
        fclose($file);
        
        return response()->json($data);
    }

    public function getDataByIndex(Request $request, $index)
    {
        $csvPath = base_path('data/datamentah/dataset_dummy.csv');
        
        if (!File::exists($csvPath)) {
            return response()->json(['error' => 'Data file not found'], 404);
        }

        $file = fopen($csvPath, 'r');
        
        // Skip header
        fgetcsv($file, 0, ';');
        
        // Read until we reach the desired index
        $currentIndex = 0;
        $result = null;
        
        while (($row = fgetcsv($file, 0, ';')) !== false) {
            if ($currentIndex == $index) {
                $result = [
                    'index' => $currentIndex,
                    'date' => $row[0],
                    'ph' => floatval($row[1]),
                    'amonia' => floatval($row[2]),
                    'suhu' => floatval($row[3]),
                    'do' => floatval($row[4])
                ];
                break;
            }
            $currentIndex++;
        }
        
        fclose($file);
        
        if ($result) {
            return response()->json($result);
        } else {
            return response()->json(['error' => 'Index not found'], 404);
        }
    }

    public function classify(Request $request)
    {
        $request->validate([
            'ph' => 'required|numeric',
            'amonia' => 'required|numeric',  // Sebenarnya TDS, tapi nama variable tetap amonia untuk backward compatibility
            'suhu' => 'required|numeric',
            'do' => 'required|numeric'
        ]);

        $ph = $request->input('ph');
        $tds = $request->input('amonia');  // Di database ini TDS, bukan amonia
        $suhu = $request->input('suhu');
        $do = $request->input('do');

        try {
            // Gunakan Flask API untuk classification dengan ML model (cepat karena model sudah loaded)
            $ch = curl_init('https://flask-fullobster.azurewebsites.net/classify');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'ph' => $ph,
                'tds' => $tds,
                'suhu' => $suhu,
                'do' => $do
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2); // 2 detik timeout
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); // 1 detik untuk koneksi
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            // Jika Flask API berhasil, gunakan hasilnya
            if ($httpCode === 200 && $response) {
                $result = json_decode($response, true);
                $result['method'] = 'flask_ml_model'; // Tandai bahwa menggunakan Flask
                return response()->json($result);
            }
            
            // Jika gagal, fallback ke PHP simple classification
            \Log::warning('Flask API not available, using PHP fallback', [
                'http_code' => $httpCode,
                'error' => $error
            ]);
            
        } catch (\Exception $e) {
            // Fallback ke PHP simple classification jika ada error
            \Log::error('Error calling Flask API: ' . $e->getMessage());
        }
        
        // Fallback: PHP Simple Classification (tetap cepat dan akurat)
        $result = $this->simpleClassification($ph, $tds, $suhu, $do);
        return response()->json($result);
    }

    private function findPythonPath()
    {
        // Try common Python paths
        $possiblePaths = [
            'python',
            'python3',
            'C:\\Python39\\python.exe',
            'C:\\Python310\\python.exe',
            'C:\\Python311\\python.exe',
            'C:\\Python312\\python.exe',
            'C:\\Users\\' . get_current_user() . '\\AppData\\Local\\Programs\\Python\\Python39\\python.exe',
            'C:\\Users\\' . get_current_user() . '\\AppData\\Local\\Programs\\Python\\Python310\\python.exe',
            'C:\\Users\\' . get_current_user() . '\\AppData\\Local\\Programs\\Python\\Python311\\python.exe',
            'C:\\Users\\' . get_current_user() . '\\AppData\\Local\\Programs\\Python\\Python312\\python.exe',
        ];

        foreach ($possiblePaths as $path) {
            $test = shell_exec($path . ' --version 2>&1');
            if (strpos($test, 'Python') !== false) {
                return $path;
            }
        }

        return 'python'; // Default fallback
    }

    private function simpleClassification($ph, $tds, $suhu, $do)
    {
        // Klasifikasi dengan 3 kategori
        // Label: 0 = Kurang Layak, 1 = Layak, 2 = Tidak Layak
        // 
        // Range Layak:
        // - Suhu: 23-25, pH: 6.5-7.8, DO: 4-6, TDS: 50-400
        //
        // Range Kurang Layak (warning zone):
        // - Suhu: 21-22 atau 26-27
        // - pH: 6.0-6.4 atau 7.9-8.5
        // - DO: 2.5-3.9 atau 6.1-7
        // - TDS: 400-600 atau < 50
        //
        // Range Tidak Layak (perlu kuras):
        // - Suhu: < 21 atau > 27
        // - pH: < 6.0 atau > 8.5
        // - DO: < 2.5 atau > 7
        // - TDS: > 600
        
        $classification = 1; // Default: Layak
        $reasons = [];
        $notSuitableCount = 0;
        $lessSuitableCount = 0;

        // Cek pH
        if ($ph < 6.0 || $ph > 8.5) {
            $notSuitableCount++;
            $reasons[] = "pH tidak layak ({$ph}) - Range layak: 6.5-7.8";
        } elseif (($ph >= 6.0 && $ph < 6.5) || ($ph > 7.8 && $ph <= 8.5)) {
            $lessSuitableCount++;
            $reasons[] = "pH kurang layak ({$ph}) - Range layak: 6.5-7.8";
        }

        // Cek TDS
        if ($tds > 600) {
            $notSuitableCount++;
            $reasons[] = "TDS tidak layak ({$tds} mg/L) - Range layak: 50-400 mg/L";
        } elseif ($tds < 50 || ($tds > 400 && $tds <= 600)) {
            $lessSuitableCount++;
            $reasons[] = "TDS kurang layak ({$tds} mg/L) - Range layak: 50-400 mg/L";
        }

        // Cek Suhu
        if ($suhu < 21 || $suhu > 27) {
            $notSuitableCount++;
            $reasons[] = "Suhu tidak layak ({$suhu}°C) - Range layak: 23-25°C";
        } elseif (($suhu >= 21 && $suhu < 23) || ($suhu > 25 && $suhu <= 27)) {
            $lessSuitableCount++;
            $reasons[] = "Suhu kurang layak ({$suhu}°C) - Range layak: 23-25°C";
        }

        // Cek DO
        if ($do < 2.5 || $do > 7) {
            $notSuitableCount++;
            $reasons[] = "DO tidak layak ({$do} mg/L) - Range layak: 4-6 mg/L";
        } elseif (($do >= 2.5 && $do < 4) || ($do > 6 && $do <= 7)) {
            $lessSuitableCount++;
            $reasons[] = "DO kurang layak ({$do} mg/L) - Range layak: 4-6 mg/L";
        }
        
        // Tentukan klasifikasi berdasarkan jumlah parameter
        if ($notSuitableCount > 0) {
            $classification = 2; // Tidak Layak - perlu kuras
        } elseif ($lessSuitableCount > 0) {
            $classification = 0; // Kurang Layak - monitoring rutin
        }

        return [
            'classification' => $classification,
            'confidence' => null,
            'reasons' => $reasons,
            'method' => 'simple_threshold',
            'input' => [
                'ph' => $ph,
                'tds' => $tds,
                'suhu' => $suhu,
                'do' => $do
            ]
        ];
    }
}
