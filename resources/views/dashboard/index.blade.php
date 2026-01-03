<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Pemantauan Kualitas Air Tawar</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.2em;
            opacity: 0.9;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            color: #666;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-card .unit {
            color: #999;
            font-size: 0.9em;
        }

        .stat-card.ph .value { color: #3b82f6; }
        .stat-card.amonia .value { color: #10b981; }
        .stat-card.suhu .value { color: #f59e0b; }
        .stat-card.do .value { color: #8b5cf6; }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }

        .chart-container h2 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .chart-wrapper {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .chart-wrapper canvas {
            max-width: 100%;
            height: 100% !important;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .classification-panel {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
            text-align: center;
        }

        .classification-panel h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .classification-result {
            padding: 30px;
            border-radius: 12px;
            font-size: 1.5em;
            font-weight: bold;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .classification-result.drain {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            animation: alertPulse 2s infinite;
        }

        .classification-result.warning {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
            animation: warningPulse 3s infinite;
        }

        .classification-result.maintain {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .classification-result.pending {
            background: #f3f4f6;
            color: #6b7280;
        }

        @keyframes alertPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        @keyframes warningPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.01); opacity: 0.95; }
        }

        .classification-details {
            margin-top: 15px;
            padding: 15px;
            background: rgba(0,0,0,0.05);
            border-radius: 8px;
            font-size: 0.9em;
        }

        .info-panel {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-item .label {
            color: #666;
            font-weight: 600;
        }

        .info-item .value {
            color: #333;
            font-weight: bold;
            font-size: 1.1em;
        }

        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #10b981;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .control-panel {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
            transform: scale(1.05);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
            transform: scale(1.05);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .footer-banner {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-top: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .footer-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
            background-size: 200% 100%;
            animation: gradientMove 3s ease infinite;
        }

        @keyframes gradientMove {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .footer-banner h3 {
            color: #333;
            font-size: 1.5em;
            margin-bottom: 10px;
        }

        .footer-banner p {
            color: #666;
            font-size: 1.1em;
            margin-bottom: 20px;
        }

        .landing-btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1em;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .landing-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }

        .landing-btn i {
            margin-left: 10px;
            transition: margin-left 0.3s ease;
        }

        .landing-btn:hover i {
            margin-left: 15px;
        }

        .footer-info {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
            color: #999;
            font-size: 0.9em;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.7);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: white;
            margin: 3% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 1200px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            padding: 25px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.8em;
        }

        .close {
            color: white;
            font-size: 35px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            line-height: 1;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .close:hover,
        .close:focus {
            background: rgba(255,255,255,0.2);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 30px;
        }

        .modal-chart-wrapper {
            position: relative;
            height: 500px;
            margin-bottom: 20px;
            width: 100%;
        }

        .modal-chart-wrapper canvas {
            max-width: 100%;
            height: 100% !important;
        }

        .chart-container canvas {
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .chart-container:hover canvas {
            transform: scale(1.02);
        }

        .modal-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .modal-info-item {
            text-align: center;
        }

        .modal-info-item .label {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 5px;
        }

        .modal-info-item .value {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
        }

        /* Responsive Design untuk Mobile */
        @media screen and (max-width: 768px) {
            body {
                padding: 10px;
            }

            .header h1 {
                font-size: 1.5em;
            }

            .header p {
                font-size: 0.9em;
            }

            .stats-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }

            .stat-card {
                padding: 15px;
            }

            .stat-card h3 {
                font-size: 0.75em;
            }

            .stat-card .value {
                font-size: 1.8em;
            }

            .stat-card .unit {
                font-size: 0.8em;
            }

            .charts-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .chart-container {
                padding: 20px 15px;
            }

            .chart-container h2 {
                font-size: 1.2em;
            }

            .chart-wrapper {
                height: 250px;
            }

            .control-panel {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }

            .control-panel > div {
                width: 100%;
            }

            .control-panel > div:first-child {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .control-panel > div:last-child {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start !important;
            }

            .btn {
                width: 100%;
                padding: 12px 20px;
                font-size: 0.95em;
            }

            .info-item {
                width: 100%;
                justify-content: space-between;
            }

            .info-item .label,
            .info-item .value {
                font-size: 0.9em;
            }

            .classification-panel {
                padding: 20px 15px;
            }

            .classification-panel h2 {
                font-size: 1.3em;
            }

            .classification-result {
                padding: 20px;
                font-size: 1.2em;
            }

            .classification-details {
                font-size: 0.85em;
            }

            .info-panel {
                padding: 15px;
                flex-direction: column;
                gap: 10px;
            }

            .footer-banner {
                padding: 20px 15px;
            }

            .footer-banner h3 {
                font-size: 1.2em;
            }

            .footer-banner p {
                font-size: 0.95em;
            }

            .landing-btn {
                padding: 12px 30px;
                font-size: 0.95em;
            }

            .footer-info {
                font-size: 0.8em;
            }

            .modal-content {
                width: 95%;
                margin: 5% auto;
            }

            .modal-header {
                padding: 20px 15px;
            }

            .modal-header h2 {
                font-size: 1.3em;
            }

            .modal-body {
                padding: 15px;
            }

            .modal-chart-wrapper {
                height: 300px;
            }

            .modal-info {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
                padding: 15px;
            }

            .modal-info-item .label {
                font-size: 0.8em;
            }

            .modal-info-item .value {
                font-size: 1.2em;
            }
        }

        /* Responsive Design untuk layar sangat kecil (< 480px) */
        @media screen and (max-width: 480px) {
            .header h1 {
                font-size: 1.2em;
            }

            .header p {
                font-size: 0.8em;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .stat-card .value {
                font-size: 2em;
            }

            .chart-wrapper {
                height: 200px;
            }

            .classification-result {
                font-size: 1em;
                padding: 15px;
            }

            .modal-info {
                grid-template-columns: 1fr;
            }

            .landing-btn {
                padding: 10px 20px;
                font-size: 0.9em;
            }
        }

        /* Responsive untuk landscape mode di mobile */
        @media screen and (max-width: 768px) and (orientation: landscape) {
            .stats-container {
                grid-template-columns: repeat(4, 1fr);
            }

            .charts-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌊 Dashboard Pemantauan Kualitas Air Tawar</h1>
            <p>Sistem Monitoring Real-time untuk Budidaya Lobster Air Tawar</p>
        </div>

        <div class="control-panel" style="display: flex; align-items: center; justify-content: space-between; gap: 30px;">
            <div style="display: flex; gap: 15px;">
                <button id="startBtn" class="btn btn-primary">▶ Mulai Monitoring</button>
                <button id="stopBtn" class="btn btn-secondary" disabled>⏸ Pause Monitoring</button>
            </div>
            <div style="display: flex; gap: 30px; align-items: center;">
                <div class="info-item">
                    <span class="status-indicator"></span>
                    <span class="label">Status:</span>
                    <span class="value" id="statusValue">Standby</span>
                </div>
                <div class="info-item">
                    <span class="label">Waktu Update:</span>
                    <span class="value" id="lastUpdateValue">-</span>
                </div>
                <div class="info-item">
                    <span class="label">Sumber Data:</span>
                    <span class="value" id="dataSourceInfo">Loading...</span>
                </div>
            </div>
        </div>

        <div class="stats-container">
            <div class="stat-card ph">
                <h3>pH Air</h3>
                <div class="value" id="phValue">-</div>
                <div class="unit">pH Level</div>
            </div>
            <div class="stat-card amonia">
                <h3>TDS</h3>
                <div class="value" id="amoniaValue">-</div>
                <div class="unit">mg/L</div>
            </div>
            <div class="stat-card suhu">
                <h3>Suhu</h3>
                <div class="value" id="suhuValue">-</div>
                <div class="unit">°C</div>
            </div>
            <div class="stat-card do">
                <h3>Oksigen Terlarut</h3>
                <div class="value" id="doValue">-</div>
                <div class="unit">mg/L</div>
            </div>
        </div>

        <div class="classification-panel">
            <h2>Hasil Klasifikasi</h2>
            <div class="classification-result pending" id="classificationResult">
                <div>Menunggu data...</div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-container" onclick="openChartModal('ph')"> 
                <h2>🧪 pH Air</h2>
                <div class="chart-wrapper">
                    <canvas id="phChart"></canvas>
                </div>
            </div>
            <div class="chart-container" onclick="openChartModal('amonia')">
                <h2>☢️ TDS (ppm)</h2>
                <div class="chart-wrapper">
                    <canvas id="amoniaChart"></canvas>
                </div>
            </div>
            <div class="chart-container" onclick="openChartModal('suhu')">
                <h2>🌡️ Suhu (°C)</h2>
                <div class="chart-wrapper">
                    <canvas id="suhuChart"></canvas>
                </div>
            </div>
            <div class="chart-container" onclick="openChartModal('do')">
                <h2>💨 Oksigen Terlarut (mg/L)</h2>
                <div class="chart-wrapper">
                    <canvas id="doChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Modal untuk menampilkan grafik -->
        <div id="chartModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modalTitle">Grafik Detail</h2>
                    <span class="close" onclick="closeChartModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="modal-chart-wrapper">
                        <canvas id="modalChart"></canvas>
                    </div>
                    <div class="modal-info">
                        <div class="modal-info-item">
                            <div class="label">Nilai Terkini</div>
                            <div class="value" id="modalCurrentValue">-</div>
                        </div>
                        <div class="modal-info-item">
                            <div class="label">Rata-rata</div>
                            <div class="value" id="modalAvgValue">-</div>
                        </div>
                        <div class="modal-info-item">
                            <div class="label">Nilai Tertinggi</div>
                            <div class="value" id="modalMaxValue">-</div>
                        </div>
                        <div class="modal-info-item">
                            <div class="label">Nilai Terendah</div>
                            <div class="value" id="modalMinValue">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-banner">
            <h3>👋 Tentang Kami</h3>
            <p>Ingin tahu lebih banyak tentang tim pengembang di balik dashboard ini?</p>
            <a href="{{ url('/') }}" class="landing-btn">
                🎉 Kunjungi Landing Page Kami <i>→</i>
            </a>
            <div class="footer-info">
                <p>👨‍💻 Dikembangkan oleh Tim Capstone Project Semester 7 | © 2025 Fullobster</p>
            </div>
        </div>

    </div>

    <script>
        let phChart, amoniaChart, suhuChart, doChart, modalChart;
        let allData = [];
        let currentIndex = 0;
        let intervalId = null;
        let countdownId = null;
        let countdown = 60;
        const maxDataPoints = 20; // Maksimal titik yang ditampilkan di grafik
        let currentModalType = null; // Menyimpan tipe chart yang sedang ditampilkan di modal

        // Data untuk setiap chart
        const phData = { labels: [], data: [] };
        const amoniaData = { labels: [], data: [] };
        const suhuData = { labels: [], data: [] };
        const doData = { labels: [], data: [] };

        function createChart(canvasId, label, color, data) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            return new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: label,
                        data: data.data,
                        borderColor: color,
                        backgroundColor: color.replace('rgb', 'rgba').replace(')', ', 0.1)'),
                        tension: 0.4,
                        borderWidth: 3,
                        fill: true,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    resizeDelay: 0,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: window.innerWidth < 768 ? 8 : 15,
                                font: {
                                    size: window.innerWidth < 768 ? 10 : 12,
                                    weight: 'bold'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: window.innerWidth < 768 ? 8 : 12,
                            titleFont: {
                                size: window.innerWidth < 768 ? 12 : 14
                            },
                            bodyFont: {
                                size: window.innerWidth < 768 ? 11 : 13
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: {
                                    size: window.innerWidth < 768 ? 9 : 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: {
                                    size: window.innerWidth < 768 ? 8 : 11
                                },
                                maxRotation: window.innerWidth < 768 ? 90 : 45,
                                minRotation: window.innerWidth < 768 ? 45 : 45,
                                autoSkip: true,
                                maxTicksLimit: window.innerWidth < 768 ? 6 : 10
                            }
                        }
                    }
                }
            });
        }

        function initCharts() {
            phChart = createChart('phChart', 'pH Level', '#3b82f6', phData);
            amoniaChart = createChart('amoniaChart', 'TDS (ppm)', '#10b981', amoniaData);
            suhuChart = createChart('suhuChart', 'Suhu (°C)', '#f59e0b', suhuData);
            doChart = createChart('doChart', 'DO (mg/L)', '#8b5cf6', doData);
        }

        function loadAllData() {
            fetch('http://195.88.211.90/~fullobst/api/dashboard/data')
                .then(response => response.json())
                .then(data => {
                    allData = data;
                    console.log('Data loaded:', allData.length, 'records');
                    
                    // Check if we have IoT data vs CSV data
                    checkDataSource();
                })
                .catch(error => {
                    console.error('Error loading data:', error);
                    alert('Gagal memuat data. Silakan refresh halaman.');
                });
        }

        function checkDataSource() {
            // Cek apakah ada data di database IoT
            fetch('http://195.88.211.90/~fullobst/api/iot/latest')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        document.getElementById('dataSourceInfo').textContent = 'Mode: Database IoT Ready';
                        console.log('IoT database has data available:', data.data);
                    } else {
                        document.getElementById('dataSourceInfo').textContent = 'Mode: CSV Fallback (No IoT Data)';
                        console.log('No IoT data found, will use CSV as fallback');
                    }
                })
                .catch(error => {
                    console.error('Error checking data source:', error);
                    document.getElementById('dataSourceInfo').textContent = 'Mode: CSV Fallback (Database Error)';
                });
        }

        function startRealTimeMode() {
            console.log('Starting real-time IoT mode');
            
            // Auto-load latest data every 30 seconds
            setInterval(() => {
                loadLatestIoTData();
            }, 2000);
            
            // Load initial latest data
            loadLatestIoTData();
        }

        function loadLatestIoTData() {
            fetch('http://195.88.211.90/~fullobst/api/iot/latest')
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        const latestRecord = data[data.length - 1];
                        addRealTimeData(latestRecord);
                    }
                })
                .catch(error => {
                    console.error('Error loading latest IoT data:', error);
                });
        }

        function addDatabaseDataToChart(data) {
            const now = new Date();
            const timeLabel = now.toLocaleTimeString('id-ID');
            
            // Add to chart data arrays
            phData.labels.push(timeLabel);
            phData.data.push(parseFloat(data.ph));
            
            amoniaData.labels.push(timeLabel);
            amoniaData.data.push(parseFloat(data.tds));
            
            suhuData.labels.push(timeLabel);
            suhuData.data.push(parseFloat(data.suhu));
            
            doData.labels.push(timeLabel);
            doData.data.push(parseFloat(data.do));
            
            // Keep only last 20 data points for performance
            if (phData.labels.length > maxDataPoints) {
                phData.labels.shift();
                phData.data.shift();
                amoniaData.labels.shift();
                amoniaData.data.shift();
                suhuData.labels.shift();
                suhuData.data.shift();
                doData.labels.shift();
                doData.data.shift();
            }
            
            // Update charts with smooth animation
            phChart.update('active');
            amoniaChart.update('active');
            suhuChart.update('active');
            doChart.update('active');

            // Update modal chart jika sedang terbuka
            updateModalChart();

            // Otomatis jalankan klasifikasi setelah data ditambahkan
            const classificationData = {
                ph: parseFloat(data.ph),
                tds: parseFloat(data.tds),
                suhu: parseFloat(data.suhu),
                do: parseFloat(data.do)
            };
            classifyData(classificationData);
        }

        function updateDatabaseStats(data) {
            document.getElementById('phValue').textContent = parseFloat(data.ph).toFixed(2);
            document.getElementById('amoniaValue').textContent = parseFloat(data.tds).toFixed(2);
            document.getElementById('suhuValue').textContent = parseFloat(data.suhu).toFixed(1);
            document.getElementById('doValue').textContent = parseFloat(data.do).toFixed(2);
            
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID');
            document.getElementById('lastUpdateValue').textContent = timeString;
        }

        function updateChart(data) {
            const label = `#${currentIndex + 1}`;
            
            // Update semua chart
            phData.labels.push(label);
            phData.data.push(data.ph);
            
            amoniaData.labels.push(label);
            amoniaData.data.push(data.tds);
            
            suhuData.labels.push(label);
            suhuData.data.push(data.suhu);
            
            doData.labels.push(label);
            doData.data.push(data.do);

            // Batasi jumlah data yang ditampilkan
            if (phData.labels.length > maxDataPoints) {
                phData.labels.shift();
                phData.data.shift();
                amoniaData.labels.shift();
                amoniaData.data.shift();
                suhuData.labels.shift();
                suhuData.data.shift();
                doData.labels.shift();
                doData.data.shift();
            }

            // Update semua chart
            phChart.update('none');
            amoniaChart.update('none');
            suhuChart.update('none');
            doChart.update('none');

            // Update modal chart jika sedang terbuka
            updateModalChart();

            // Otomatis jalankan klasifikasi setelah data ditambahkan
            classifyData(data);
        }

        function classifyData(data) {
            // Kirim data ke backend untuk klasifikasi
            fetch('/api/dashboard/classify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    ph: data.ph,
                    tds: data.tds,
                    suhu: data.suhu,
                    do: data.do
                })
            })
            .then(response => response.json())
            .then(result => {
                updateClassificationResult(result);
            })
            .catch(error => {
                console.error('Error classifying data:', error);
            });
        }

        function updateClassificationResult(result) {
            const resultDiv = document.getElementById('classificationResult');
            
            // Label: 0 = Kurang Layak, 1 = Layak, 2 = Tidak Layak
            if (result.classification === 2 || result.classification === 'TIDAK LAYAK' || result.classification === 'KURAS') {
                resultDiv.className = 'classification-result drain';
                resultDiv.innerHTML = `
                    <div>⚠️ TIDAK LAYAK - PERLU DIKURAS ⚠️</div>
                    <div class="classification-details">
                        Kualitas air tidak optimal. Disarankan untuk segera melakukan pengurasan kolam.
                        ${result.reasons ? '<br><br><strong>Alasan:</strong><br>' + result.reasons.join('<br>') : ''}
                    </div>
                `;
            } else if (result.classification === 0 || result.classification === 'KURANG LAYAK') {
                resultDiv.className = 'classification-result warning';
                resultDiv.innerHTML = `
                    <div>⚠️ KURANG LAYAK</div>
                    <div class="classification-details">
                        Kualitas air dalam zona warning. Lanjutkan monitoring rutin dan perhatikan perubahan parameter.
                        ${result.reasons ? '<br><br><strong>Parameter yang perlu diperhatikan:</strong><br>' + result.reasons.join('<br>') : ''}
                    </div>
                `;
            } else {
                resultDiv.className = 'classification-result maintain';
                resultDiv.innerHTML = `
                    <div>✅ LAYAK - TIDAK PERLU DIKURAS</div>
                    <div class="classification-details">
                        Kualitas air dalam kondisi baik. Lanjutkan monitoring rutin.
                    </div>
                `;
            }
        }

        function updateStats(data) {
            document.getElementById('phValue').textContent = data.ph.toFixed(2);
            document.getElementById('amoniaValue').textContent = data.tds.toFixed(2);
            document.getElementById('suhuValue').textContent = data.suhu.toFixed(1);
            document.getElementById('doValue').textContent = data.do.toFixed(1);
            
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID');
            document.getElementById('lastUpdateValue').textContent = timeString;
        }

        function loadLatestDatabaseData() {
            fetch('http://195.88.211.90/~fullobst/api/iot/latest')
                .then(response => response.json())
                .then(result => {
                    if (result.success && result.data) {
                        const data = result.data;
                        addDatabaseDataToChart(data);
                        updateDatabaseStats(data);
                        classifyData(data); // Klasifikasi otomatis
                        
                        // Reset countdown
                        countdown = 30; // 30 detik untuk database mode
                        
                        console.log('Database data updated:', data);
                    } else {
                        console.log('No database data available');
                        document.getElementById('dataSourceInfo').textContent = 'Mode: Menunggu Data IoT...';
                    }
                })
                .catch(error => {
                    console.error('Error loading database data:', error);
                    document.getElementById('dataSourceInfo').textContent = 'Mode: Error Database';
                });
        }

        function updateCountdown() {
            countdown--;
            
            // Update waktu real-time setiap detik
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID');
            document.getElementById('lastUpdateValue').textContent = timeString;
            
            if (countdown <= 0) {
                countdown = 30; // 30 detik untuk database mode
            }
        }

        function startMonitoring() {
            document.getElementById('statusValue').textContent = 'Monitoring Aktif - Database';
            document.getElementById('startBtn').disabled = true;
            document.getElementById('stopBtn').disabled = false;
            document.getElementById('dataSourceInfo').textContent = 'Mode: Real-time Database';
            
            // Load data pertama dari database langsung
            loadLatestDatabaseData();
            
            // Set interval untuk update data dari database setiap 30 detik
            intervalId = setInterval(loadLatestDatabaseData, 2000);
            
            // Set interval untuk countdown setiap 1 detik
            countdownId = setInterval(updateCountdown, 1000);
        }

        function stopMonitoring() {
            if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;
            }
            
            if (countdownId) {
                clearInterval(countdownId);
                countdownId = null;
            }
            
            document.getElementById('statusValue').textContent = 'Pause';
            document.getElementById('startBtn').disabled = false;
            document.getElementById('stopBtn').disabled = true;
            document.getElementById('dataSourceInfo').textContent = 'Mode: Monitoring Dihentikan';
        }

        function resetMonitoring() {
            stopMonitoring();
            
            countdown = 30; // Reset ke 30 detik untuk database mode
            
            // Clear semua array data
            while (phData.labels.length > 0) {
                phData.labels.pop();
                phData.data.pop();
            }
            while (amoniaData.labels.length > 0) {
                amoniaData.labels.pop();
                amoniaData.data.pop();
            }
            while (suhuData.labels.length > 0) {
                suhuData.labels.pop();
                suhuData.data.pop();
            }
            while (doData.labels.length > 0) {
                doData.labels.pop();
                doData.data.pop();
            }
            
            // Force update semua chart
            phChart.update('active');
            amoniaChart.update('active');
            suhuChart.update('active');
            doChart.update('active');

            // Update modal chart jika sedang terbuka
            updateModalChart();
            
            // Reset nilai statistik
            document.getElementById('phValue').textContent = '-';
            document.getElementById('amoniaValue').textContent = '-';
            document.getElementById('suhuValue').textContent = '-';
            document.getElementById('doValue').textContent = '-';
            document.getElementById('statusValue').textContent = 'Standby';
            document.getElementById('dataPointValue').textContent = '0 Data Point';
            document.getElementById('dataSourceInfo').textContent = 'Mode: Reset';
            
            // Reset classification result
            const resultDiv = document.getElementById('classificationResult');
            resultDiv.className = 'classification-result pending';
            resultDiv.innerHTML = '<div>Menunggu data dari database...</div>';
            
            console.log('Dashboard telah direset untuk mode database');
        }

        // Event Listeners
        document.getElementById('startBtn').addEventListener('click', startMonitoring);
        document.getElementById('stopBtn').addEventListener('click', stopMonitoring);

        // Modal Functions
        function openChartModal(type) {
            currentModalType = type;
            const modal = document.getElementById('chartModal');
            
            // Set title dan warna berdasarkan tipe
            const titles = {
                'ph': '🧪 pH Air - Detail View',
                'amonia': '☢️ TDS (ppm) - Detail View',
                'suhu': '🌡️ Suhu (°C) - Detail View',
                'do': '💨 Oksigen Terlarut (mg/L) - Detail View'
            };
            
            const colors = {
                'ph': '#3b82f6',
                'amonia': '#10b981',
                'suhu': '#f59e0b',
                'do': '#8b5cf6'
            };
            
            document.getElementById('modalTitle').textContent = titles[type];
            
            // Buat chart di modal dengan referensi data yang sama
            createModalChart(type, colors[type]);
            
            // Update info statistics
            updateModalInfo(type);
            
            // Tampilkan modal
            modal.style.display = 'block';
        }

        function closeChartModal() {
            const modal = document.getElementById('chartModal');
            modal.style.display = 'none';
            
            // Hancurkan chart modal
            if (modalChart) {
                modalChart.destroy();
                modalChart = null;
            }
            
            currentModalType = null;
        }

        function createModalChart(type, color) {
            // Hancurkan chart lama jika ada
            if (modalChart) {
                modalChart.destroy();
            }
            
            // Ambil data berdasarkan tipe
            const chartData = getChartDataByType(type);
            
            const ctx = document.getElementById('modalChart').getContext('2d');
            modalChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: getTitleByType(type),
                        data: chartData.data,
                        borderColor: color,
                        backgroundColor: color.replace('rgb', 'rgba').replace(')', ', 0.1)'),
                        tension: 0.4,
                        borderWidth: 3,
                        fill: true,
                        pointRadius: 6,
                        pointHoverRadius: 9,
                        pointBackgroundColor: color,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    resizeDelay: 0,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: window.innerWidth < 768 ? 10 : 20,
                                font: {
                                    size: window.innerWidth < 768 ? 11 : 14,
                                    weight: 'bold'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.9)',
                            padding: window.innerWidth < 768 ? 10 : 15,
                            titleFont: {
                                size: window.innerWidth < 768 ? 13 : 16,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: window.innerWidth < 768 ? 12 : 14
                            },
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.08)',
                                lineWidth: 1
                            },
                            ticks: {
                                font: {
                                    size: window.innerWidth < 768 ? 10 : 13
                                },
                                padding: window.innerWidth < 768 ? 5 : 10
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(0, 0, 0, 0.08)',
                                lineWidth: 1
                            },
                            ticks: {
                                font: {
                                    size: window.innerWidth < 768 ? 9 : 12
                                },
                                maxRotation: window.innerWidth < 768 ? 90 : 45,
                                minRotation: window.innerWidth < 768 ? 45 : 45,
                                padding: window.innerWidth < 768 ? 5 : 10,
                                autoSkip: true,
                                maxTicksLimit: window.innerWidth < 768 ? 6 : 10
                            }
                        }
                    }
                }
            });
        }

        function getChartDataByType(type) {
            const dataMap = {
                'ph': phData,
                'amonia': amoniaData,
                'suhu': suhuData,
                'do': doData
            };
            return dataMap[type];
        }

        function getTitleByType(type) {
            const titles = {
                'ph': 'pH Level',
                'amonia': 'TDS (ppm)',
                'suhu': 'Suhu (°C)',
                'do': 'DO (mg/L)'
            };
            return titles[type];
        }

        function updateModalInfo(type) {
            const chartData = getChartDataByType(type);
            const data = chartData.data;
            
            if (data.length === 0) {
                document.getElementById('modalCurrentValue').textContent = '-';
                document.getElementById('modalAvgValue').textContent = '-';
                document.getElementById('modalMaxValue').textContent = '-';
                document.getElementById('modalMinValue').textContent = '-';
                return;
            }
            
            const current = data[data.length - 1];
            const avg = data.reduce((a, b) => a + b, 0) / data.length;
            const max = Math.max(...data);
            const min = Math.min(...data);
            
            const unit = type === 'ph' ? '' : (type === 'suhu' ? '°C' : 'mg/L');
            
            document.getElementById('modalCurrentValue').textContent = current.toFixed(2) + ' ' + unit;
            document.getElementById('modalAvgValue').textContent = avg.toFixed(2) + ' ' + unit;
            document.getElementById('modalMaxValue').textContent = max.toFixed(2) + ' ' + unit;
            document.getElementById('modalMinValue').textContent = min.toFixed(2) + ' ' + unit;
        }

        function updateModalChart() {
            // Update modal chart jika sedang terbuka
            if (currentModalType && modalChart) {
                const chartData = getChartDataByType(currentModalType);
                modalChart.data.labels = chartData.labels;
                modalChart.data.datasets[0].data = chartData.data;
                modalChart.update('none');
                
                // Update info statistics
                updateModalInfo(currentModalType);
            }
        }

        // Tutup modal ketika klik di luar modal
        window.onclick = function(event) {
            const modal = document.getElementById('chartModal');
            if (event.target == modal) {
                closeChartModal();
            }
        }

        // Fungsi classify yang dapat dipanggil dari luar
        function classify() {
            if (phData.data.length === 0) {
                alert('Tidak ada data untuk diklasifikasi. Mulai monitoring terlebih dahulu.');
                return;
            }
            
            const lastIndex = phData.data.length - 1;
            const data = {
                ph: phData.data[lastIndex],
                amonia: amoniaData.data[lastIndex],
                suhu: suhuData.data[lastIndex],
                do: doData.data[lastIndex]
            };
            
            classifyData(data);
        }

        // Initialize
        window.addEventListener('load', () => {
            initCharts();
            loadAllData();
        });

        // Handle window resize untuk responsiveness chart
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                // Destroy dan recreate semua chart dengan konfigurasi responsif
                if (phChart) {
                    phChart.destroy();
                    phChart = createChart('phChart', 'pH Level', '#3b82f6', phData);
                }
                if (amoniaChart) {
                    amoniaChart.destroy();
                    amoniaChart = createChart('amoniaChart', 'TDS (ppm)', '#10b981', amoniaData);
                }
                if (suhuChart) {
                    suhuChart.destroy();
                    suhuChart = createChart('suhuChart', 'Suhu (°C)', '#f59e0b', suhuData);
                }
                if (doChart) {
                    doChart.destroy();
                    doChart = createChart('doChart', 'DO (mg/L)', '#8b5cf6', doData);
                }
                
                // Update modal chart jika sedang terbuka
                if (currentModalType && modalChart) {
                    const colors = {
                        'ph': '#3b82f6',
                        'amonia': '#10b981',
                        'suhu': '#f59e0b',
                        'do': '#8b5cf6'
                    };
                    createModalChart(currentModalType, colors[currentModalType]);
                }
            }, 300); // Debounce resize event
        });
    </script>
</body>
</html>
