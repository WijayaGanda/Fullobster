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
        // Get data from database
        $monitorings = Monitorings::latest()
            ->limit(100) // Last 100 records for chart performance
            ->get()
            ->reverse() // Show in chronological order
            ->values();

        $data = $monitorings->map(function($monitoring, $index) {
            return [
                'index' => $index,
                'date' => $monitoring->created_at->format('Y-m-d H:i:s'),
                'ph' => floatval($monitoring->ph),
                'amonia' => floatval($monitoring->amonia),
                'suhu' => floatval($monitoring->suhu),
                'do' => floatval($monitoring->do)
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
            'amonia' => 'required|numeric',
            'suhu' => 'required|numeric',
            'do' => 'required|numeric'
        ]);

        $ph = $request->input('ph');
        $amonia = $request->input('amonia');
        $suhu = $request->input('suhu');
        $do = $request->input('do');

        try {
            // Path ke script Python
            $pythonScript = base_path('app/Services/ClassificationService.py');
            
            // Cari Python executable
            $pythonPath = $this->findPythonPath();
            
            // Jalankan script Python
            $command = sprintf(
                '%s "%s" %f %f %f %f',
                $pythonPath,
                $pythonScript,
                $ph,
                $amonia,
                $suhu,
                $do
            );
            
            $output = shell_exec($command);
            
            if ($output) {
                $result = json_decode($output, true);
                return response()->json($result);
            } else {
                // Fallback: klasifikasi sederhana berdasarkan threshold
                return response()->json($this->simpleClassification($ph, $amonia, $suhu, $do));
            }
            
        } catch (\Exception $e) {
            // Fallback ke klasifikasi sederhana jika Python tidak tersedia
            return response()->json($this->simpleClassification($ph, $amonia, $suhu, $do));
        }
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

    private function simpleClassification($ph, $amonia, $suhu, $do)
    {
        // Klasifikasi sederhana berdasarkan threshold
        // Range optimal untuk budidaya ikan:
        // pH: 6.5 - 7.5
        // Amonia: < 0.05 mg/L
        // Suhu: 23 - 26 °C
        // DO: > 3.5 mg/L
        
        $needsDrain = false;
        $reasons = [];

        if ($ph < 6.3 || $ph > 7.7) {
            $needsDrain = true;
            $reasons[] = 'pH tidak optimal';
        }

        if ($amonia > 0.06) {
            $needsDrain = true;
            $reasons[] = 'Amonia tinggi';
        }

        if ($suhu < 21 || $suhu > 28) {
            $needsDrain = true;
            $reasons[] = 'Suhu tidak ideal';
        }

        if ($do < 2.5) {
            $needsDrain = true;
            $reasons[] = 'Oksigen terlarut rendah';
        }

        return [
            'classification' => $needsDrain ? 1 : 0,
            'confidence' => null,
            'reasons' => $reasons,
            'method' => 'simple_threshold',
            'input' => [
                'ph' => $ph,
                'amonia' => $amonia,
                'suhu' => $suhu,
                'do' => $do
            ]
        ];
    }
}
