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
                // 'amonia' => 'nullable|numeric', // Tidak perlu karena menggunakan TDS
                'measured_at' => 'nullable|date' // Tambahan untuk timestamp dari IoT device
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
            
            // Tentukan measured_at: gunakan dari request jika ada, atau gunakan waktu sekarang
            $measuredAt = isset($data['measured_at']) 
                ? Carbon::parse($data['measured_at'])->setTimezone('Asia/Jakarta')
                : now('Asia/Jakarta');
            
            // Simpan ke database
            $monitoring = Monitorings::create([
                'suhu' => $data['suhu'],
                'ph' => $data['ph'],
                'do' => $data['do'],
                'tds' => $data['tds'],
                // 'amonia' => $data['amonia'] ?? 0, // Tidak perlu karena menggunakan TDS
                'status' => $status,
                'sensor_data' => $request->all(),
                'measured_at' => $measuredAt
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data received successfully',
                'data' => [
                    'id' => $monitoring->id,
                    'status' => $status,
                    'timestamp' => $monitoring->measured_at->format('d/m/Y H:i:s'),
                    'timestamp_iso' => $monitoring->measured_at->toISOString(),
                    'timezone' => 'Asia/Jakarta'
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
        
        if (isset($data['tds']) && $data['tds'] > 500) {
            $issues[] = 'TDS/Amonia tinggi';
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
        $latestData = Monitorings::latest('created_at')->first();
        
        if (!$latestData) {
            return response()->json([
                'success' => false,
                'message' => 'No data available',
                'total_records' => Monitorings::count()
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $latestData->id,
                'ph' => (float) ($latestData->ph ?? 0),
                // 'amonia' => (float) ($latestData->tds ?? 0), // Gunakan TDS sebagai amonia
                'suhu' => (float) ($latestData->suhu ?? 0),
                'do' => (float) ($latestData->do ?? 0),
                'tds' => (float) ($latestData->tds ?? 0),
                'status' => $latestData->status ?? '',
                'measured_at' => $latestData->measured_at ? $latestData->measured_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') : '',
                'created_at' => $latestData->created_at ? $latestData->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') : ''
            ],
            'total_records' => Monitorings::count()
        ]);
    }

    /**
     * Get semua data untuk dashboard (dengan paginasi)
     */
    public function getAllData(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 50);
        $data = Monitorings::orderBy('id', 'asc') // Urut dari lama ke baru
                    ->limit($limit)
                    ->get()
                    ->map(function($item, $index) {
                        return [
                            'index' => $index,
                            'id' => $item->id,
                            'ph' => (float) ($item->ph ?? 0),
                            // 'amonia' => (float) ($item->tds ?? 0), // Gunakan TDS sebagai amonia
                            'suhu' => (float) ($item->suhu ?? 0),
                            'do' => (float) ($item->do ?? 0),
                            'tds' => (float) ($item->tds ?? 0),
                            'status' => $item->status ?? '',
                            'measured_at' => $item->measured_at ? $item->measured_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') : null,
                            'created_at' => $item->created_at ? $item->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') : null
                        ];
                    });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total_records' => Monitorings::count(),
            'limit' => $limit
        ]);
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
            'tds' => round(rand(200, 600), 0)
        ];

        $request = new Request($testData);
        return $this->receiveData($request);
    }
}