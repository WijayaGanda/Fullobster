<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Monitorings extends Model
{
    use HasFactory;

    protected $table = 'monitoring';

    protected $fillable = [
        'suhu',
        'ph',
        'do',
        'tds',
        'amonia',
        'status',
        'sensor_data',
        'measured_at'
    ];

    protected $casts = [
        'suhu' => 'decimal:2',
        'ph' => 'decimal:2',
        'do' => 'decimal:2',
        'tds' => 'decimal:2',
        'amonia' => 'decimal:3',
        'sensor_data' => 'array',
        'measured_at' => 'datetime'
    ];

    protected $dates = [
        'measured_at'
    ];

    public function getFormattedMeasuredAtAttribute()
    {
        return $this->measured_at->format('d/m/Y H:i:s');
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('measured_at', 'desc');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('measured_at', today());
    }

    public function scopeByDateRange($query, $start, $end)
    {
        return $query->whereBetween('measured_at', [$start, $end]);
    }
}
