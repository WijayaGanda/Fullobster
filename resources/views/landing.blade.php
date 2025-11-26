<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Fullobster - Dashboard Monitoring Kualitas Air</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            background: #0f172a;
            color: #e2e8f0;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -250px;
            right: -250px;
            animation: float 6s ease-in-out infinite;
        }

        .hero::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -150px;
            left: -150px;
            animation: float 8s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .hero-content {
            text-align: center;
            z-index: 1;
            padding: 20px;
            max-width: 1200px;
        }

        .hero h1 {
            font-size: 4em;
            font-weight: 700;
            color: white;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            animation: slideDown 1s ease-out;
        }

        .hero p {
            font-size: 1.5em;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 30px;
            animation: slideUp 1s ease-out;
        }

        .hero-btn {
            display: inline-block;
            padding: 15px 40px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1em;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: slideUp 1.2s ease-out;
        }

        .hero-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.4);
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Project Section */
        .project-section {
            padding: 100px 20px;
            background: #1e293b;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 3em;
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-subtitle {
            text-align: center;
            font-size: 1.2em;
            color: #94a3b8;
            margin-bottom: 60px;
        }

        .project-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .project-card {
            background: #0f172a;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            transition: all 0.3s ease;
            border: 1px solid #334155;
        }

        .project-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.3);
            border-color: #667eea;
        }

        .project-icon {
            font-size: 3em;
            margin-bottom: 20px;
        }

        .project-card h3 {
            font-size: 1.8em;
            margin-bottom: 15px;
            color: #e2e8f0;
        }

        .project-card p {
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .project-features {
            list-style: none;
            padding: 0;
        }

        .project-features li {
            padding: 8px 0;
            color: #cbd5e1;
            position: relative;
            padding-left: 25px;
        }

        .project-features li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #10b981;
            font-weight: bold;
        }

        /* Team Section */
        .team-section {
            padding: 100px 20px;
            background: #0f172a;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-top: 60px;
        }

        .team-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
        }

        .team-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .team-card:hover::before {
            transform: translateY(0);
        }

        .team-card:hover {
            transform: translateY(-10px) scale(1.05);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.5);
        }

        .team-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: white;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4em;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
        }

        .team-card h3 {
            font-size: 1.5em;
            margin-bottom: 10px;
            color: white;
            position: relative;
            z-index: 1;
        }

        .team-role {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1em;
            margin-bottom: 15px;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }

        .team-card p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9em;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }

        /* Tech Stack Section */
        .tech-section {
            padding: 100px 20px;
            background: #1e293b;
        }

        .tech-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-top: 60px;
        }

        .tech-item {
            background: #0f172a;
            padding: 20px 30px;
            border-radius: 50px;
            border: 2px solid #334155;
            transition: all 0.3s ease;
            font-weight: 600;
            color: #e2e8f0;
        }

        .tech-item:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        /* Footer */
        .footer {
            background: #0f172a;
            padding: 40px 20px;
            text-align: center;
            border-top: 1px solid #334155;
        }

        .footer p {
            color: #94a3b8;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5em;
            }

            .hero p {
                font-size: 1.2em;
            }

            .section-title {
                font-size: 2em;
            }

            .team-grid, .project-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Scroll Indicator */
        .scroll-indicator {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            animation: bounce 2s infinite;
        }

        .scroll-indicator span {
            display: block;
            width: 30px;
            height: 50px;
            border: 2px solid white;
            border-radius: 25px;
            position: relative;
        }

        .scroll-indicator span::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 50%;
            width: 6px;
            height: 6px;
            margin-left: -3px;
            background: white;
            border-radius: 50%;
            animation: scroll 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
            40% { transform: translateX(-50%) translateY(-10px); }
            60% { transform: translateX(-50%) translateY(-5px); }
        }

        @keyframes scroll {
            0% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(20px); }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>🌊 Fullobster</h1>
            <p>Dashboard Monitoring Kualitas Air Tawar Berbasis Machine Learning</p>
            <a href="/dashboard" class="hero-btn">Lihat Dashboard →</a>
        </div>
        <div class="scroll-indicator">
            <span></span>
        </div>
    </section>

    <!-- Project Description Section -->
    <section class="project-section">
        <div class="container">
            <h2 class="section-title">Tentang Proyek</h2>
            <p class="section-subtitle">Sistem monitoring real-time untuk budidaya lobster air tawar</p>
            
            <div class="project-grid">
                <div class="project-card">
                    <div class="project-icon">📊</div>
                    <h3>Monitoring Real-time</h3>
                    <p>Pemantauan kualitas air secara real-time dengan update otomatis setiap 60 detik</p>
                    <ul class="project-features">
                        <li>4 Parameter Utama (pH, Amonia, Suhu, DO)</li>
                        <li>Grafik Interaktif Terpisah</li>
                        <li>Visualisasi Data Real-time</li>
                        <li>Auto-refresh Dashboard</li>
                    </ul>
                </div>

                <div class="project-card">
                    <div class="project-icon">🤖</div>
                    <h3>Klasifikasi AI</h3>
                    <p>Sistem klasifikasi otomatis menggunakan Machine Learning untuk menentukan kualitas air</p>
                    <ul class="project-features">
                        <li>Decision Tree Algorithm</li>
                        <li>Prediksi Waktu Kuras</li>
                        <li>Klasifikasi Otomatis</li>
                        <li>Akurasi Tinggi</li>
                    </ul>
                </div>

                <div class="project-card">
                    <div class="project-icon">💧</div>
                    <h3>Smart Analysis</h3>
                    <p>Analisis cerdas untuk kesehatan kolam budidaya lobster air tawar</p>
                    <ul class="project-features">
                        <li>Threshold-based Detection</li>
                        <li>Alert System</li>
                        <li>Data History</li>
                        <li>Rekomendasi Aksi</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <h2 class="section-title">Tim Pengembang</h2>
            <p class="section-subtitle">Mahasiswa Semester 7 - Capstone Project</p>
            
            <div class="team-grid">
                <div class="team-card">
                    <div class="team-avatar">👨‍💻</div>
                    <h3>Developer 1</h3>
                    <p class="team-role">Full Stack Developer</p>
                    <p>Bertanggung jawab atas pengembangan backend dan integrasi sistem dengan fokus pada Laravel framework dan API development.</p>
                </div>

                <div class="team-card">
                    <div class="team-avatar">👩‍💻</div>
                    <h3>Developer 2</h3>
                    <p class="team-role">Frontend Developer</p>
                    <p>Mengembangkan antarmuka pengguna yang intuitif dan responsive dengan fokus pada user experience dan visualisasi data.</p>
                </div>

                <div class="team-card">
                    <div class="team-avatar">👨‍🔬</div>
                    <h3>Developer 3</h3>
                    <p class="team-role">Data Scientist</p>
                    <p>Mengembangkan model Machine Learning untuk klasifikasi kualitas air dan analisis data menggunakan Python dan scikit-learn.</p>
                </div>

                <div class="team-card">
                    <div class="team-avatar">👩‍🔬</div>
                    <h3>Developer 4</h3>
                    <p class="team-role">System Analyst</p>
                    <p>Merancang arsitektur sistem, melakukan testing, dan memastikan integrasi yang sempurna antara semua komponen aplikasi.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tech Stack Section -->
    <section class="tech-section">
        <div class="container">
            <h2 class="section-title">Teknologi yang Digunakan</h2>
            <p class="section-subtitle">Stack teknologi modern untuk performa optimal</p>
            
            <div class="tech-grid">
                <div class="tech-item">🐘 PHP Laravel 11</div>
                <div class="tech-item">🐍 Python 3.11</div>
                <div class="tech-item">📊 Chart.js</div>
                <div class="tech-item">🤖 Scikit-learn</div>
                <div class="tech-item">🎨 Blade Template</div>
                <div class="tech-item">🔢 NumPy</div>
                <div class="tech-item">📈 Decision Tree</div>
                <div class="tech-item">💾 CSV Data Processing</div>
                <div class="tech-item">🌐 RESTful API</div>
                <div class="tech-item">⚡ Real-time Updates</div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Team Fullobster - Capstone Project Semester 7</p>
            <p>Dashboard Monitoring Kualitas Air Tawar untuk Budidaya Lobster</p>
        </div>
    </footer>

    <script>
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        // Scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.project-card, .team-card, .tech-item').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(50px)';
            el.style.transition = 'all 0.6s ease';
            observer.observe(el);
        });
    </script>
</body>
</html>
