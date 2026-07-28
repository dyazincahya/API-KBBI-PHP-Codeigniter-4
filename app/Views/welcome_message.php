<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=device-width, initial-scale=1.0">
    <title>Unofficial API KBBI 2026</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-color: #0b0f19;
            --card-bg: rgba(20, 26, 46, 0.6);
            --card-border: rgba(255, 255, 255, 0.08);
            --primary: #3b82f6;
            --primary-glow: rgba(59, 130, 246, 0.15);
            --accent: #10b981;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --code-bg: #060913;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem 1.5rem;
            overflow-x: hidden;
            position: relative;
        }

        /* Subtle ambient backgrounds */
        body::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, var(--primary-glow) 0%, transparent 70%);
            top: 10%;
            left: -10%;
            z-index: 0;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.08) 0%, transparent 70%);
            bottom: 10%;
            right: -10%;
            z-index: 0;
            pointer-events: none;
        }

        .container {
            max-width: 1200px;
            width: 100%;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
        }

        .header {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }

        .logo {
            width: 100px;
            height: auto;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        h1 {
            font-size: 2.25rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ffffff 40%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.025em;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 1rem;
            font-weight: 400;
            max-width: 500px;
            line-height: 1.5;
        }

        /* Glassmorphism Card Grid */
        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            width: 100%;
            padding: 2.25rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .tabs {
            display: flex;
            gap: 0.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            padding-bottom: 0.75rem;
        }

        .tab-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-family: inherit;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 0.5rem 1rem;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .tab-btn.active {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.08);
        }

        .tab-btn:hover:not(.active) {
            color: var(--text-main);
            background-color: rgba(255, 255, 255, 0.03);
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease forwards;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Visual View layout */
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        @media (min-width: 640px) {
            .meta-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .meta-section {
            background-color: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.03);
            border-radius: 16px;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .section-title {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--primary);
            font-weight: 600;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.92rem;
            padding: 0.25rem 0;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.04);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--text-muted);
        }

        .info-value {
            font-weight: 500;
            color: var(--text-main);
        }

        .badge-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-top: 0.25rem;
        }

        .badge {
            background-color: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.04);
            border-radius: 6px;
            padding: 0.25rem 0.6rem;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-main);
        }

        .badge.primary {
            background-color: rgba(59, 130, 246, 0.1);
            border-color: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }

        /* JSON/Code block layout */
        pre {
            background-color: var(--code-bg);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 1.25rem;
            overflow-x: auto;
            font-family: 'Fira Code', monospace;
            font-size: 0.88rem;
            line-height: 1.6;
            color: #38bdf8;
        }

        .json-key {
            color: #f43f5e;
        }

        .json-string {
            color: #10b981;
        }

        .json-number {
            color: #fbbf24;
        }

        .json-boolean {
            color: #3b82f6;
        }

        .json-null {
            color: #6b7280;
        }

        /* API Sandbox section */
        .api-sandbox {
            background-color: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.03);
            border-radius: 16px;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            width: 100%;
        }

        .input-group {
            display: flex;
            gap: 0.5rem;
        }

        input {
            flex: 1;
            background-color: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            color: #ffffff;
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.25);
        }

        button.btn {
            background-color: var(--primary);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-family: inherit;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        button.btn:hover {
            opacity: 0.95;
            box-shadow: 0 0 12px rgba(59, 130, 246, 0.4);
        }

        a.link {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        a.link:hover {
            color: #60a5fa;
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            font-size: 0.82rem;
            color: var(--text-muted);
            margin-top: 1rem;
        }

        /* Setup status styles */
        .status-container {
            width: 100%;
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 1.5rem;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            display: flex;
            flex-direction: column;
            gap: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .status-header {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        @media (min-width: 640px) {
            .status-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .status-card {
            background-color: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-label {
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .status-badge {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            text-transform: uppercase;
        }

        .status-badge.success {
            background-color: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.25);
            color: #34d399;
        }

        .status-badge.danger {
            background-color: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #f87171;
        }

        .alert-notice {
            background-color: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.18);
            color: #f87171;
            border-radius: 12px;
            padding: 1rem;
            font-size: 0.88rem;
            line-height: 1.5;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .alert-title {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.05em;
        }

        .btn-try-api {
            width: 100%;
            background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 100%);
            color: #ffffff;
            text-decoration: none;
            text-align: center;
            display: block;
            padding: 1rem;
            border-radius: 14px;
            font-weight: 600;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.25);
            margin-top: 0.5rem;
        }

        .btn-try-api:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(59, 130, 246, 0.4);
            opacity: 0.95;
        }
    </style>
</head>

<body>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <img class="logo" src="https://raw.githubusercontent.com/dyazincahya/API-KBBI-PHP-Codeigniter-4/main/kbbi.webp" alt="Logo KBBI">
            <h1>API KBBI 2026</h1>
            <p class="subtitle">Unofficial API Kamus Besar Bahasa Indonesia (KBBI) dengan mekanisme scraping HTML cerdas.</p>
        </div>

        <!-- Status Setup -->
        <div class="status-container">
            <div class="status-header">
                <span><i class="fa-solid fa-server"></i> Status Konfigurasi Server</span>
            </div>
            <div class="status-grid">
                <div class="status-card">
                    <span class="status-label"><i class="fa-solid fa-file-code"></i> File .env</span>
                    <span class="status-badge <?= $envExists ? 'success' : 'danger' ?>">
                        <?= $envExists ? 'Terbaca' : 'Belum Ada' ?>
                    </span>
                </div>
                <div class="status-card">
                    <span class="status-label"><i class="fa-solid fa-key"></i> GeoNode API Key</span>
                    <span class="status-badge <?= $geonodeSetup ? 'success' : 'danger' ?>">
                        <?= $geonodeSetup ? 'Terkonfigurasi' : 'Belum Disetup' ?>
                    </span>
                </div>
            </div>

            <?php if (!$envExists || !$geonodeSetup): ?>
                <div class="alert-notice">
                    <span class="alert-title">⚠️ Wajib Dikonfigurasi</span>
                    <span>Anda harus menduplikasi file <code>env</code> menjadi <code>.env</code> di root folder, dan memasukkan setidaknya satu GeoNode API Key yang valid agar fitur bypass limit WAF KBBI dapat berjalan.</span>
                </div>
            <?php else: ?>
                <a href="/kbbi?search=demokrasi" target="_blank" class="btn-try-api">
                    <i class="fa-solid fa-circle-play"></i> Coba API
                </a>
            <?php endif; ?>
        </div>

        <!-- Main Card -->
        <div class="card">
            <!-- Navigation tabs -->
            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab('visual')"><i class="fa-solid fa-chart-pie"></i> Dashboard</button>
                <button class="tab-btn" onclick="switchTab('json')"><i class="fa-solid fa-code"></i> Raw JSON</button>
                <button class="tab-btn" onclick="switchTab('sandbox')"><i class="fa-solid fa-flask"></i> Coba API (Sandbox)</button>
            </div>

            <!-- Tab 1: Visual View -->
            <div id="tab-visual" class="tab-content active">
                <div class="meta-grid">
                    <!-- API Info -->
                    <div class="meta-section">
                        <div class="section-title"><i class="fa-solid fa-circle-info"></i> Informasi API</div>
                        <div class="info-row">
                            <span class="info-label">Nama API</span>
                            <span class="info-value">API KBBI 2026</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Sumber Data</span>
                            <span class="info-value"><a class="link" href="https://kbbi.kemendikdasmen.go.id" target="_blank">kbbi.kemendikdasmen.go.id</a></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Metode</span>
                            <span class="info-value">HTML Parsing</span>
                        </div>
                    </div>

                    <!-- Tech Specs -->
                    <div class="meta-section">
                        <div class="section-title"><i class="fa-solid fa-laptop-code"></i> Teknologi & Spek</div>
                        <div class="info-row">
                            <span class="info-label">Bahasa</span>
                            <span class="info-value">PHP <?= esc($phpVersion) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Framework</span>
                            <span class="info-value">CodeIgniter <?= esc($ciVersion) ?></span>
                        </div>
                        <div>
                            <span class="info-label" style="font-size: 0.92rem;">Library Aktif:</span>
                            <div class="badge-list">
                                <span class="badge">CURL</span>
                                <span class="badge">DOMDocument</span>
                                <span class="badge">DOMXPath</span>
                                <span class="badge primary">GeoNodeScraperAPI</span>
                            </div>
                        </div>
                    </div>

                    <!-- Author -->
                    <div class="meta-section" style="grid-column: span 1;">
                        <div class="section-title"><i class="fa-solid fa-user-tie"></i> Penulis</div>
                        <div class="info-row">
                            <span class="info-label">Nama</span>
                            <span class="info-value">Kang Cahya</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Blog</span>
                            <span class="info-value"><a class="link" href="https://kang-cahya.com" target="_blank">kang-cahya.com</a></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">GitHub</span>
                            <span class="info-value"><a class="link" href="https://github.com/dyazincahya" target="_blank">@dyazincahya</a></span>
                        </div>
                    </div>

                    <!-- Quick Start Links -->
                    <div class="meta-section">
                        <div class="section-title"><i class="fa-solid fa-circle-nodes"></i> Endpoint Tersedia</div>
                        <div class="info-row">
                            <span class="info-label">Informasi API</span>
                            <span class="info-value"><a class="link" href="/kbbi" target="_blank">/kbbi</a></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Pencarian Kata (URI)</span>
                            <span class="info-value"><a class="link" href="/kbbi/search/demokrasi" target="_blank">/kbbi/search/{kata}</a></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Pencarian Kata (Query)</span>
                            <span class="info-value"><a class="link" href="/kbbi?search=demokrasi" target="_blank">/kbbi?search={kata}</a></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Raw JSON View -->
            <div id="tab-json" class="tab-content">
                <pre id="json-renderer"></pre>
            </div>

            <!-- Tab 3: API Sandbox -->
            <div id="tab-sandbox" class="tab-content">
                <div class="api-sandbox">
                    <div class="section-title"><i class="fa-solid fa-magnifying-glass"></i> Uji Pencarian Kata</div>
                    <div class="input-group">
                        <input type="text" id="sandbox-input" placeholder="Masukkan kata kunci pencarian (misal: demokrasi)" value="demokrasi" onkeydown="if(event.key === 'Enter') testSearch()">
                        <button class="btn" onclick="testSearch()">Cari Kata</button>
                    </div>
                    <pre id="sandbox-output" style="max-height: 300px; color: #a5b4fc;">Hasil uji pencarian akan muncul di sini...</pre>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            &copy; 2026 Kang Cahya. Dibuat dengan CodeIgniter 4 & PHP 8.
        </div>
    </div>

    <script>
        const jsonMetadata = {
            "api": {
                "name": "API KBBI 2026",
                "source": "https://kbbi.kemendikdasmen.go.id",
                "method": "HTML Parsing"
            },
            "technology": {
                "lang": "PHP <?= esc($phpVersion) ?>",
                "framework": "CodeIgniter <?= esc($ciVersion) ?>",
                "library": [
                    "CURL",
                    "DOMDocument",
                    "DOMXPath",
                    "GeoNodeScraperAPI"
                ]
            },
            "author": {
                "name": "Kang Cahya",
                "blog": "https://kang-cahya.com",
                "github": "https://github.com/dyazincahya"
            }
        };

        // Syntax highlighting for JSON
        function syntaxHighlight(json) {
            if (typeof json !== 'string') {
                json = JSON.stringify(json, undefined, 4);
            }
            json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+-]?\d+)?)/g, function(match) {
                let cls = 'json-number';
                if (/^"/.test(match)) {
                    if (/:$/.test(match)) {
                        cls = 'json-key';
                    } else {
                        cls = 'json-string';
                    }
                } else if (/true|false/.test(match)) {
                    cls = 'json-boolean';
                } else if (/null/.test(match)) {
                    cls = 'json-null';
                }
                return '<span class="' + cls + '">' + match + '</span>';
            });
        }

        // Initialize raw JSON view
        document.getElementById('json-renderer').innerHTML = syntaxHighlight(jsonMetadata);

        // Tab switcher
        function switchTab(tabId) {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });

            event.target.classList.add('active');
            document.getElementById(`tab-${tabId}`).classList.add('active');
        }

        // Test API Search function
        function testSearch() {
            const word = document.getElementById('sandbox-input').value.trim();
            const outputElement = document.getElementById('sandbox-output');

            if (!word) {
                outputElement.innerHTML = 'Silakan masukkan kata kunci pencarian.';
                return;
            }

            outputElement.innerHTML = 'Sedang mencari data...';

            fetch(`/kbbi/search/${encodeURIComponent(word)}`)
                .then(response => response.json())
                .then(data => {
                    outputElement.innerHTML = syntaxHighlight(data);
                })
                .catch(error => {
                    outputElement.innerHTML = 'Error mengambil data: ' + error.message;
                });
        }
    </script>
</body>

</html>