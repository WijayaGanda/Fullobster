<?php

namespace App\Http\Controllers;

use App\Models\Monitorings;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class IoTController extends Controller
{
    /**
     * Menerima data dari IoT device
     */
    public function receiveData(Request $request): JsonResponse
    {
        try {
            // Validasi data yang dikirim IoT
            $validator = Validator::make($request->all(), [
                'suhu' => 'required|numeric',
                'ph' => 'required|numeric',
                'do' => 'required|numeric',
                'tds' => 'required|numeric',
                'amonia' => 'nullable|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data',
                    'errors' => $validator->errors()
                ], 400);
            }

            $data = $validator->validated();
            
            // Klasifikasi otomatis
            $status = $this->classifyWaterQuality($data);
            
            // Simpan ke database
            $monitoring = Monitorings::create([
                'suhu' => $data['suhu'],
                'ph' => $data['ph'],
                'do' => $data['do'],
                'tds' => $data['tds'],
                'amonia' => $data['amonia'] ?? 0,
                'status' => $status,
                'sensor_data' => $request->all(),
                'measured_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data received successfully',
                'data' => [
                    'id' => $monitoring->id,
                    'status' => $status,
                    'timestamp' => $monitoring->measured_at->toISOString()
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('IoT Data Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Klasifikasi kualitas air
     */
    private function classifyWaterQuality(array $data): string
    {
        $issues = [];
        
        if ($data['ph'] < 6.3 || $data['ph'] > 7.7) {
            $issues[] = 'pH tidak optimal';
        }
        
        if (isset($data['amonia']) && $data['amonia'] > 0.06) {
            $issues[] = 'Amonia tinggi';
        }
        
        if ($data['suhu'] < 21 || $data['suhu'] > 28) {
            $issues[] = 'Suhu tidak optimal';
        }
        
        if ($data['do'] < 2.5) {
            $issues[] = 'Oksigen rendah';
        }
        
        if ($data['tds'] > 500) {
            $issues[] = 'TDS tinggi';
        }

        return empty($issues) ? 'Layak' : 'Kurang Layak';
    }

    /**
     * Get data terbaru untuk dashboard
     */
    public function getLatestData(): JsonResponse
    {
        $latestData = Monitorings::latest('measured_at')->first();
        
        if (!$latestData) {
            return response()->json([
                'success' => false,
                'message' => 'No data available'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'ph' => (float) $latestData->ph,
                'amonia' => (float) $latestData->amonia,
                'suhu' => (float) $latestData->suhu,
                'do' => (float) $latestData->do,
                'tds' => (float) $latestData->tds,
                'status' => $latestData->status,
                'measured_at' => $latestData->measured_at->format('d/m/Y H:i:s')
            ]
        ]);
    }

    /**
     * Get semua data untuk dashboard (dengan paginasi)
     */
    public function getAllData(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 50);
        $data = Monitorings::latest('measured_at')
                    ->limit($limit)
                    ->get()
                    ->map(function($item, $index) {
                        return [
                            'index' => $index,
                            'ph' => (float) $item->ph,
                            'amonia' => (float) $item->amonia,
                            'suhu' => (float) $item->suhu,
                            'do' => (float) $item->do,
                            'tds' => (float) $item->tds,
                            'status' => $item->status,
                            'measured_at' => $item->measured_at->format('d/m/Y H:i:s')
                        ];
                    });

        return response()->json($data);
    }

    /**
     * Test endpoint untuk simulasi data IoT
     */
    public function sendTestData(): JsonResponse
    {
        // Data simulasi untuk testing
        $testData = [
            'suhu' => round(rand(220, 280) / 10, 1),
            'ph' => round(rand(60, 80) / 10, 1),
            'do' => round(rand(25, 65) / 10, 1),
            'tds' => round(rand(200, 600), 0),
            'amonia' => round(rand(10, 80) / 1000, 3)
        ];

        $request = new Request($testData);
        return $this->receiveData($request);
    }
}