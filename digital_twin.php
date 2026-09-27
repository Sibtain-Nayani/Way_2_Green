<?php
// digital_twin.php — Way2Green AI Digital Twin Dashboard
// Sophisticated, spatially balanced SaaS-style environmental intelligence dashboard
require_once 'db.php';
require_once 'user_auth.php';
require_once 'services/Weather.php';

$user = get_logged_in_user();

$defaultDest = 'munnar';
$destSlug = strtolower(trim($_GET['dest'] ?? $defaultDest));
$coords = Weather::DESTINATION_COORDS[$destSlug] ?? Weather::DESTINATION_COORDS[$defaultDest];
$destName = $coords['label'];
$destLat  = $coords['lat'];
$destLon  = $coords['lon'];

$initialWeather = null;
try {
    $initialWeather = Weather::getCurrentWeather($destLat, $destLon, $destName);
} catch (Exception $e) {
    $initialWeather = ['status' => 'error'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Twin — Weather Intelligence | Way2Green</title>
    <meta name="description" content="AI-powered Digital Twin for Way2Green: simulate how weather events cascade through sustainable hospitality — transport, hotels, accessibility, eco-operations.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <style>
        /* ── DESIGN TOKENS matching index.php & redesign constraints ──────────────────────── */
        :root {
            /* Updated palette per redesign prompt */
            --seaweed-deepest: #042f1f;
            --seaweed-dark:    #064E3B; /* deep green */
            --seaweed-primary: #073B2A; /* deep green */
            --seaweed-accent:  #29AB87; /* seaweed green */
            --seaweed-mint:    #34d399;
            --seaweed-glow:    rgba(41, 171, 135, 0.4);

            --alice-blue:        #F0F8FF;
            --alice-blue-light:  #f7fbff;
            --alice-blue-dark:   #e1effc;
            --alice-blue-deep:   #cae3fb;
            --alice-blue-card:   #ffffff; /* White content surfaces */
            --alice-blue-border: rgba(190, 220, 248, 0.8);

            --text-on-seaweed:    #ffffff;
            --text-on-alice:      #073B2A;
            --text-muted:         #47695c;
            --text-light-subtle:  rgba(255, 255, 255, 0.85);

            /* Subtle borders & restrained shadows */
            --border-subtle: 1px solid var(--alice-blue-border);
            --shadow-sm: 0 2px 8px rgba(7,59,42,0.04);
            --shadow-md: 0 8px 24px rgba(7,59,42,0.08);

            --radius-sm:   6px;
            --radius-md:   12px;
            --radius-lg:   16px;
            --radius-full: 9999px;
            --font-main:   'Plus Jakarta Sans', sans-serif;
            --transition:  all 0.25s ease;
            
            /* 8px spacing system */
            --sp-1: 8px;
            --sp-2: 16px;
            --sp-3: 24px;
            --sp-4: 32px;
            --sp-5: 40px;
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        html { scroll-behavior:smooth; }
        body {
            font-family: var(--font-main);
            background: var(--alice-blue-light); /* very light blue/green interface bg */
            color: var(--text-on-alice);
            min-height: 100vh;
            line-height: 1.5;
        }

        a { text-decoration: none; color: inherit; }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar { width:6px; }
        ::-webkit-scrollbar-track { background: var(--alice-blue-light); }
        ::-webkit-scrollbar-thumb { background: var(--alice-blue-deep); border-radius:99px; }

        /* ── SITE HEADER ──────────────────────── */
        .site-header {
            position: sticky;
            top: 0;
            z-index: 200;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            border-bottom: var(--border-subtle);
            padding: var(--sp-2) var(--sp-4);
        }
        .nav-inner {
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--sp-3);
        }
        .brand-logo {
            display: flex; align-items: center; gap: var(--sp-1);
            font-size: 1.25rem; font-weight: 800;
            color: var(--seaweed-primary); letter-spacing: -0.02em;
        }
        .brand-icon-box {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, var(--seaweed-primary), var(--seaweed-accent));
            border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1rem;
        }
        .desktop-nav { display: flex; align-items: center; gap: var(--sp-2); }
        .nav-link {
            font-size: 0.9rem; font-weight: 600;
            color: var(--text-muted); padding: var(--sp-1) 12px;
            border-radius: var(--radius-sm); transition: var(--transition);
        }
        .nav-link:hover, .nav-link.active {
            color: var(--seaweed-primary); background: var(--alice-blue);
        }
        .nav-link-twin {
            font-size: 0.9rem; font-weight: 700;
            color: var(--seaweed-accent); padding: var(--sp-1) 12px;
            border-radius: var(--radius-sm);
            background: rgba(41, 171, 135, 0.08);
            transition: var(--transition);
        }
        .nav-link-twin:hover { background: var(--seaweed-accent); color: #fff; }

        /* ── PAGE LAYOUT ─────────────────────────────────────────── */
        .dt-wrapper {
            display: flex;
            gap: var(--sp-3);
            padding: var(--sp-3) var(--sp-4);
            max-width: 1600px;
            margin: 0 auto;
            align-items: flex-start;
        }
        
        /* Main Workspace ~ 75% */
        .dt-main {
            flex: 1;
            min-width: 0; /* prevent flex blowout */
            display: flex;
            flex-direction: column;
            gap: var(--sp-3);
        }

        /* Context / Control Rail ~ 25% */
        .dt-sidebar {
            width: 340px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            gap: var(--sp-2);
        }

        /* ── SHARED COMPONENT STYLES ─────────────────────────────── */
        .card {
            background: var(--alice-blue-card);
            border: var(--border-subtle);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }
        .card-header {
            padding: var(--sp-2) var(--sp-3);
            border-bottom: var(--border-subtle);
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--seaweed-primary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-body {
            padding: var(--sp-3);
        }
        .section-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--seaweed-dark);
            margin-bottom: var(--sp-1);
            display: flex; align-items: center; gap: 6px;
        }
        .text-sm { font-size: 0.8rem; }
        .text-xs { font-size: 0.7rem; }
        .text-muted { color: var(--text-muted); }

        /* ── MAP WORKSPACE ───────────────────────────────────────── */
        .map-wrapper {
            position: relative;
            height: 480px;
            border-radius: var(--radius-md);
            overflow: hidden;
            border: var(--border-subtle);
            box-shadow: var(--shadow-sm);
        }
        #dt-map { width: 100%; height: 100%; }
        .map-overlay-bar {
            position: absolute; top: var(--sp-2); left: var(--sp-2); z-index: 500;
            display: flex; align-items: center; gap: var(--sp-1); pointer-events: none;
        }
        .map-chip {
            background: rgba(255,255,255,0.95);
            border: var(--border-subtle);
            border-radius: var(--radius-sm);
            padding: 6px 12px; font-size: 0.8rem; font-weight: 700;
            color: var(--seaweed-primary); pointer-events: auto;
            backdrop-filter: blur(4px);
            box-shadow: var(--shadow-sm);
            display: flex; align-items: center; gap: 6px;
        }
        
        .sim-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 10px; border-radius: var(--radius-sm);
            font-size: 0.75rem; font-weight: 700;
        }
        .sim-badge-live   { background: rgba(41,171,135,0.15); color: var(--seaweed-accent); border: 1px solid rgba(41,171,135,0.3); }
        .sim-badge-whatif { background: rgba(245,158,11,0.15); color: #d97706; border: 1px solid rgba(245,158,11,0.3); }


        /* ── HEALTH & METRICS ROW ────────────────────────────────── */
        .health-row {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: var(--sp-3);
            align-items: stretch;
        }
        
        .score-card {
            display: flex;
            align-items: center;
            gap: var(--sp-3);
            padding: var(--sp-3);
            background: var(--alice-blue-card);
            border: var(--border-subtle);
            border-radius: var(--radius-md);
            min-width: 280px;
        }
        .score-value-block {
            display: flex; flex-direction: column;
        }
        .score-num { font-size: 3.5rem; font-weight: 800; color: var(--seaweed-primary); line-height: 1; letter-spacing: -0.02em; }
        .score-label { font-size: 1rem; font-weight: 700; color: var(--seaweed-accent); margin-top: 4px;}
        .score-context {
            flex: 1;
        }
        .score-context-item {
            display: flex; justify-content: space-between;
            font-size: 0.8rem; margin-bottom: 4px;
            color: var(--text-muted);
        }
        .score-context-val { font-weight: 600; color: var(--seaweed-primary); }
        
        .status-card {
            display: flex;
            padding: var(--sp-3);
            background: var(--alice-blue-card);
            border: var(--border-subtle);
            border-radius: var(--radius-md);
            gap: var(--sp-4);
            align-items: center;
        }
        .status-block { flex: 1; }
        .status-metric { font-size: 1.5rem; font-weight: 700; color: var(--seaweed-primary); }
        .status-label { font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }

        /* ── CASCADING IMPACTS FLOW ──────────────────────────────── */
        .cascade-flow {
            background: var(--alice-blue-card);
            border: var(--border-subtle);
            border-radius: var(--radius-md);
            padding: var(--sp-3);
            display: flex;
            align-items: center;
            justify-content: space-between;
            overflow-x: auto;
        }
        .cascade-node {
            display: flex; flex-direction: column; align-items: flex-start;
            min-width: 140px;
        }
        .cascade-node-header {
            font-size: 0.75rem; font-weight: 700; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.05em;
            margin-bottom: 4px; display: flex; align-items: center; gap: 4px;
        }
        .cascade-node-value {
            font-size: 1.25rem; font-weight: 700; color: var(--seaweed-primary);
        }
        .cascade-node-sub {
            font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;
        }
        .cascade-arrow {
            color: var(--alice-blue-border);
            font-weight: 300;
            font-size: 1.5rem;
            margin: 0 var(--sp-2);
        }
        
        /* Node states */
        .node-good .cascade-node-value { color: var(--seaweed-accent); }
        .node-warn .cascade-node-value { color: #d97706; }
        .node-alert .cascade-node-value { color: #dc2626; }

        /* ── AI SITUATIONAL BRIEFING ─────────────────────────────── */
        .ai-briefing-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--sp-3);
        }
        .briefing-card {
            background: var(--alice-blue-card);
            border: var(--border-subtle);
            border-left: 4px solid var(--seaweed-accent);
            border-radius: var(--radius-md);
            padding: var(--sp-3);
        }
        .briefing-title {
            font-size: 0.75rem; font-weight: 700; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: var(--sp-2);
        }
        .impact-list { list-style: none; display: flex; flex-direction: column; gap: var(--sp-1); }
        .impact-list li {
            display: flex; align-items: center; justify-content: space-between;
            font-size: 0.85rem; padding-bottom: var(--sp-1); border-bottom: 1px dashed var(--alice-blue-border);
        }
        .impact-list li:last-child { border-bottom: none; padding-bottom: 0; }
        
        .action-list { list-style: none; display: flex; flex-direction: column; gap: var(--sp-1); }
        .action-list li {
            display: flex; align-items: flex-start; gap: var(--sp-1);
            font-size: 0.85rem; color: var(--seaweed-primary); font-weight: 500;
        }
        .action-list li::before {
            content: '→'; color: var(--seaweed-accent); font-weight: 700;
        }

        /* ── SIDEBAR CONTROLS ────────────────────────────────────── */
        
        /* Dest Select */
        .dest-select {
            width: 100%; padding: 10px 12px;
            background: #fff; border: var(--border-subtle);
            border-radius: var(--radius-sm); color: var(--seaweed-primary);
            font-size: 0.9rem; font-family: var(--font-main); font-weight: 600;
            margin-bottom: var(--sp-1); outline: none; cursor: pointer;
        }

        /* Sliders */
        .scenario-group { margin-bottom: var(--sp-2); }
        .scenario-label {
            display: flex; justify-content: space-between; align-items: center;
            font-size: 0.8rem; font-weight: 600; color: var(--text-muted);
            margin-bottom: 6px;
        }
        .scenario-val { color: var(--seaweed-primary); font-weight: 700; }
        input[type=range] { width: 100%; accent-color: var(--seaweed-accent); cursor: pointer; height: 4px; }
        
        .btn-reset {
            width: 100%; padding: 8px; margin-top: var(--sp-1);
            background: #fff; border: 1px solid var(--alice-blue-border);
            border-radius: var(--radius-sm); color: var(--text-muted);
            font-size: 0.8rem; font-family: var(--font-main); cursor: pointer;
            transition: var(--transition); font-weight: 600;
        }
        .btn-reset:hover { background: var(--alice-blue); color: var(--seaweed-primary); }

        /* Social / POI Lists */
        .sidebar-list {
            display: flex; flex-direction: column; gap: var(--sp-1);
            max-height: 240px; overflow-y: auto; padding-right: 4px;
        }
        .list-item {
            padding: var(--sp-1) 0; border-bottom: 1px dashed var(--alice-blue-border);
            display: flex; flex-direction: column; gap: 2px;
        }
        .list-item:last-child { border-bottom: none; }
        .list-title { font-size: 0.8rem; font-weight: 600; color: var(--seaweed-primary); line-height: 1.3;}
        .list-meta { font-size: 0.7rem; color: var(--text-muted); display: flex; gap: 8px; }
        
        .signal-chip {
            font-size: 0.65rem; padding: 2px 6px; border-radius: 4px; font-weight: 700;
        }
        .sig-pos { background: rgba(41,171,135,0.1); color: var(--seaweed-accent); }
        .sig-neg { background: rgba(220,38,38,0.1); color: #dc2626; }
        .sig-neu { background: var(--alice-blue); color: var(--text-muted); }

        /* Helpers */
        .spin {
            display: inline-block; width: 14px; height: 14px;
            border: 2px solid var(--alice-blue-border);
            border-top-color: var(--seaweed-accent);
            border-radius: 50%; animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loader-block { text-align: center; padding: var(--sp-3); color: var(--text-muted); font-size: 0.8rem; }

        /* Responsive */
        @media(max-width: 1100px) {
            .dt-wrapper { flex-direction: column; }
            .dt-sidebar { width: 100%; }
            .health-row { grid-template-columns: 1fr; }
            .ai-briefing-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- ── HEADER NAV ───────────────────────────────────────────── -->
<?php include 'components/navbar.php'; ?>

<div class="dt-wrapper">
    
    <!-- ── MAIN WORKSPACE (~75%) ────────────────────────────── -->
    <div class="dt-main">
        
        <!-- Map -->
        <div class="map-wrapper">
            <div id="dt-map"></div>
            <div class="map-overlay-bar">
                <div class="map-chip">
                    📍 <span id="chipDestName"><?= htmlspecialchars($destName) ?></span>
                </div>
                <div class="map-chip">
                    <span id="simBadge" class="sim-badge sim-badge-live">LIVE</span>
                </div>
                <span id="simLoading" style="display:none; background:rgba(255,255,255,0.9); padding:6px; border-radius:50%;"><span class="spin"></span></span>
            </div>
        </div>

        <!-- Health Metrics Row -->
        <div class="health-row">
            <div class="score-card">
                <div class="score-value-block">
                    <div class="score-num" id="greenScore">—</div>
                    <div class="score-label" id="greenLabel">Loading</div>
                </div>
                <div class="score-context">
                    <div class="score-context-item">
                        <span>Baseline</span>
                        <span class="score-context-val" id="scoreBaseline">—</span>
                    </div>
                    <div class="score-context-item">
                        <span>Variance</span>
                        <span class="score-context-val" id="scoreDelta">—</span>
                    </div>
                </div>
            </div>
            
            <div class="status-card">
                <div class="status-block">
                    <div class="status-label">Weather</div>
                    <div class="status-metric" id="statWeather">—</div>
                    <div class="text-xs text-muted" style="margin-top:2px" id="statTemp">—</div>
                </div>
                <div class="status-block">
                    <div class="status-label">Visibility</div>
                    <div class="status-metric" id="statVis">—</div>
                </div>
                <div class="status-block">
                    <div class="status-label">Solar Eff.</div>
                    <div class="status-metric" id="statSolar">—</div>
                </div>
                <div class="status-block">
                    <div class="status-label">Model Conf.</div>
                    <div class="status-metric" id="statConf">—</div>
                </div>
            </div>
        </div>

        <!-- Cascading Impacts Flow -->
        <div>
            <div class="section-title">Cascading Impacts</div>
            <div class="cascade-flow" id="cascadeFlow">
                <div class="loader-block"><span class="spin"></span> Processing impacts...</div>
            </div>
        </div>

        <!-- AI Situational Briefing -->
        <div class="ai-briefing-grid">
            <div class="briefing-card">
                <div class="briefing-title">Key Impacts</div>
                <ul class="impact-list" id="briefingImpacts">
                    <li><span class="text-muted">Analyzing current situation...</span></li>
                </ul>
            </div>
            <div class="briefing-card" style="border-left-color: var(--seaweed-primary);">
                <div class="briefing-title">AI Recommendations & Actions</div>
                <ul class="action-list" id="briefingActions">
                    <li class="text-muted" style="color:var(--text-muted)">Awaiting data...</li>
                </ul>
                <div style="margin-top: var(--sp-2); font-size: 0.8rem; color: var(--text-muted); border-top: 1px dashed var(--alice-blue-border); padding-top: var(--sp-1);">
                    <strong>Recovery Outlook:</strong> <span id="briefingRecovery">—</span>
                </div>
            </div>
        </div>

    </div><!-- /dt-main -->

    <!-- ── CONTEXT / CONTROL RAIL (~25%) ────────────────────── -->
    <div class="dt-sidebar">
        
        <div class="card">
            <div class="card-header">Target Destination</div>
            <div class="card-body">
                <select id="destSelect" class="dest-select" onchange="selectDestination(this)">
                    <?php foreach (Weather::DESTINATION_COORDS as $slug => $c): ?>
                    <option value="<?= $slug ?>" 
                            data-lat="<?= $c['lat'] ?>" 
                            data-lon="<?= $c['lon'] ?>"
                            data-name="<?= htmlspecialchars($c['label']) ?>"
                            <?= $slug === $destSlug ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['label']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="text-xs text-muted">Select a location to visualize local environmental data and operational digital twin.</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Scenario Controls</div>
            <div class="card-body">
                <div class="scenario-group">
                    <div class="scenario-label"><span>Rainfall</span><span class="scenario-val" id="rainVal">0 mm/hr</span></div>
                    <input type="range" id="rainSlider" min="0" max="100" value="0" step="1" oninput="updateSlider('rainVal',this.value,' mm/hr');scheduleSimulation()">
                </div>
                <div class="scenario-group">
                    <div class="scenario-label"><span>Temperature</span><span class="scenario-val" id="tempVal">Live</span></div>
                    <input type="range" id="tempSlider" min="-10" max="50" value="25" step="1" oninput="updateSlider('tempVal',this.value,'°C');scheduleSimulation()">
                </div>
                <div class="scenario-group">
                    <div class="scenario-label"><span>Wind</span><span class="scenario-val" id="windVal">Live</span></div>
                    <input type="range" id="windSlider" min="0" max="35" value="3" step="0.5" oninput="updateSlider('windVal',this.value,' m/s');scheduleSimulation()">
                </div>
                <div class="scenario-group">
                    <div class="scenario-label"><span>Condition Override</span></div>
                    <select id="condSelect" class="dest-select" style="margin-bottom:0; padding:6px 10px;" onchange="scheduleSimulation()">
                        <option value="">— Use Live Data —</option>
                        <option value="Clear">Clear</option>
                        <option value="Clouds">Cloudy</option>
                        <option value="Rain">Rain</option>
                        <option value="Thunderstorm">Thunderstorm</option>
                        <option value="Snow">Snow</option>
                    </select>
                </div>
                <button class="btn-reset" onclick="resetSliders()">Reset to Live Data</button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Social Signals 
                <button onclick="loadSocialSignals()" style="background:none;border:none;cursor:pointer;color:var(--text-muted);">↻</button>
            </div>
            <div class="card-body" style="padding-top: var(--sp-1);">
                <div id="socialAlarm" style="font-size:0.75rem; font-weight:700; margin-bottom:var(--sp-1); color:var(--seaweed-primary);"></div>
                <div class="sidebar-list" id="socialPanel">
                    <div class="loader-block"><span class="spin"></span></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Eco Attractions</div>
            <div class="card-body" style="padding-top: var(--sp-1);">
                <div class="sidebar-list" id="poisPanel">
                    <div class="loader-block"><span class="spin"></span></div>
                </div>
            </div>
        </div>

    </div><!-- /dt-sidebar -->

</div><!-- /dt-wrapper -->

<script>
// ── STATE ──────────────────────────────────────────────────────
let currentLat   = <?= $destLat ?>;
let currentLon   = <?= $destLon ?>;
let currentDest  = "<?= addslashes($destName) ?>";
let currentSlug  = "<?= $destSlug ?>";
let simTimer     = null;
let leafletMap   = null;
let weatherLayer = null;
let poiLayer     = null;

// ── MAP ────────────────────────────────────────────────────────
function initMap() {
    leafletMap = L.map('dt-map', { zoomControl: true }).setView([currentLat, currentLon], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(leafletMap);

    weatherLayer = L.layerGroup().addTo(leafletMap);
    poiLayer     = L.layerGroup().addTo(leafletMap);

    addDestMarker(currentLat, currentLon, currentDest, 'normal');
}

function addDestMarker(lat, lon, name, severity) {
    weatherLayer.clearLayers();
    const colors = { normal:'#29AB87', moderate:'#d97706', high:'#ea580c', severe:'#dc2626', extreme:'#7f1d1d' };
    const c = colors[severity] || '#29AB87';

    const icon = L.divIcon({
        className: '',
        html: `<div style="background:${c};width:16px;height:16px;border-radius:50%;border:3px solid #fff;box-shadow:0 0 10px rgba(0,0,0,0.2);"></div>`,
        iconSize: [16,16], iconAnchor: [8,8]
    });

    L.marker([lat, lon], { icon })
     .addTo(weatherLayer)
     .bindPopup(`<b style="color:var(--seaweed-primary)">${name}</b><br><small>Severity: ${severity}</small>`);

    L.circle([lat, lon], {
        radius: 12000, color: c, fillColor: c,
        fillOpacity: 0.08, weight: 1.5, dashArray: '4,4'
    }).addTo(weatherLayer);
}

function addPoiMarkers(places) {
    poiLayer.clearLayers();
    (places || []).slice(0, 15).forEach(p => {
        if (!p.lat || !p.lon) return;
        const icon = L.divIcon({
            className: '',
            html: `<div style="background:#073B2A;width:8px;height:8px;border-radius:50%;border:1px solid #fff;"></div>`,
            iconSize: [8,8], iconAnchor: [4,4]
        });
        L.marker([p.lat, p.lon], { icon }).addTo(poiLayer)
         .bindPopup(`<b style="color:#073B2A;font-size:0.8rem">${p.name}</b>`);
    });
}

// ── DATA FETCHING ──────────────────────────────────────────────
function selectDestination(sel) {
    const opt = sel.options[sel.selectedIndex];
    currentLat  = parseFloat(opt.dataset.lat);
    currentLon  = parseFloat(opt.dataset.lon);
    currentDest = opt.dataset.name;
    currentSlug = opt.value;
    
    document.getElementById('chipDestName').textContent = currentDest;
    if (leafletMap) leafletMap.setView([currentLat, currentLon], 11);
    
    resetSliders();
    loadWeather();
    runSimulation();
    loadSocialSignals();
    loadPois();
}

function loadWeather() {
    fetch(`api/weather.php?lat=${currentLat}&lon=${currentLon}`)
        .then(r => r.json())
        .then(d => {
            if (d.status !== 'success') return;
            document.getElementById('statWeather').textContent = d.weather.main;
            document.getElementById('statTemp').textContent = `${d.temperature.current}°C, ${d.humidity}% Hum`;
            document.getElementById('statVis').textContent = `${d.visibility_km} km`;
            document.getElementById('statSolar').textContent = `${d.eco_impact.solar_efficiency_pct}%`;
        }).catch(() => {});
}

function scheduleSimulation() { clearTimeout(simTimer); simTimer = setTimeout(runSimulation, 600); }

function runSimulation() {
    const rain  = parseFloat(document.getElementById('rainSlider').value);
    const temp  = parseFloat(document.getElementById('tempSlider').value);
    const wind  = parseFloat(document.getElementById('windSlider').value);
    const cond  = document.getElementById('condSelect').value;
    const hasWhatIf = rain > 0 || cond !== '';

    const badge = document.getElementById('simBadge');
    badge.textContent = hasWhatIf ? 'WHAT-IF SCENARIO' : 'LIVE DATA';
    badge.className = `sim-badge sim-badge-${hasWhatIf ? 'whatif' : 'live'}`;
    
    document.getElementById('simLoading').style.display = 'inline-block';

    const payload = {
        lat: currentLat, lon: currentLon, dest: currentDest,
        whatIf: hasWhatIf ? {
            ...(rain > 0 ? { rain_mm: rain } : {}),
            temp_c: temp, wind_mps: wind,
            ...(cond ? { condition: cond } : {})
        } : {}
    };

    fetch('api/digital_twin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(renderSimulation)
    .catch(() => {})
    .finally(() => { document.getElementById('simLoading').style.display = 'none'; });
}

function renderSimulation(data) {
    if (data.status !== 'success') return;
    
    // Update Map Marker
    const sev = data.simulated_weather?.eco_impact?.severity || 'normal';
    addDestMarker(currentLat, currentLon, currentDest, sev);

    // Green Score
    const gs = data.green_score_impact || {};
    document.getElementById('greenScore').textContent = gs.adjusted ?? '—';
    const gl = document.getElementById('greenLabel');
    gl.textContent = gs.label || '';
    gl.style.color = (gs.adjusted >= 70) ? 'var(--seaweed-accent)' : (gs.adjusted >= 40 ? '#d97706' : '#dc2626');
    
    document.getElementById('scoreBaseline').textContent = gs.baseline || '—';
    const delta = gs.delta || 0;
    const dtEl = document.getElementById('scoreDelta');
    dtEl.textContent = delta > 0 ? `+${delta}` : delta;
    dtEl.style.color = delta < 0 ? '#dc2626' : 'var(--seaweed-accent)';

    // Conf
    document.getElementById('statConf').textContent = `${data.uncertainty?.confidence_pct || '--'}%`;

    // Cascading Flow
    const d = data.layers.direct;
    const c = data.layers.cascading;
    
    const nodes = [
        { lbl: 'Weather', val: data.simulated_weather?.weather?.main || 'Clear', sub: `Sev: ${sev}`, state: sev==='severe'?'alert':'good' },
        { lbl: 'Road Access', val: Math.round((d.transportation.road_access_factor||1)*100)+'%', sub: 'capacity', state: d.transportation.road_access_factor < 0.7 ? 'warn' : 'good' },
        { lbl: 'Accessibility', val: d.accessibility.wheelchair_mobility.replace('_',' '), sub: 'status', state: d.accessibility.wheelchair_mobility !== 'normal' ? 'warn' : 'good' },
        { lbl: 'Hotel Ops', val: c.eco_hotel_operations?.label || 'Normal', sub: 'impact', state: c.eco_hotel_operations?.severity==='high'?'warn':'good' },
        { lbl: 'Carbon ×', val: (d.carbon_footprint.multiplier||1).toFixed(2)+'x', sub: 'emissions', state: d.carbon_footprint.multiplier > 1.2 ? 'alert' : 'good' }
    ];
    
    document.getElementById('cascadeFlow').innerHTML = nodes.map((n, i) => `
        <div class="cascade-node node-${n.state}">
            <div class="cascade-node-header">${n.lbl}</div>
            <div class="cascade-node-value">${n.val}</div>
            <div class="cascade-node-sub">${n.sub}</div>
        </div>
        ${i < nodes.length - 1 ? `<div class="cascade-arrow">→</div>` : ''}
    `).join('');

    // AI Briefing
    const n = data.ai_narrative || {};
    
    const impacts = [
        `<span>Transport:</span> <strong>${n.transport_advisory || 'Normal'}</strong>`,
        `<span>Accessibility:</span> <strong>${n.accessibility_advisory || 'Normal'}</strong>`,
        `<span>Eco-Ops:</span> <strong>${n.eco_advisory || 'Normal'}</strong>`
    ];
    document.getElementById('briefingImpacts').innerHTML = impacts.map(i => `<li>${i}</li>`).join('');
    
    const actions = n.recommended_actions || ['Monitor conditions'];
    document.getElementById('briefingActions').innerHTML = actions.map(a => `<li>${a}</li>`).join('');
    
    document.getElementById('briefingRecovery').textContent = n.recovery_outlook || 'Not required';
}

function loadSocialSignals() {
    const ww = document.getElementById('condSelect').value || '';
    const url = `api/social_signals.php?dest=${encodeURIComponent(currentDest)}&condition=${encodeURIComponent(ww)}&lat=${currentLat}&lon=${currentLon}`;
    
    document.getElementById('socialPanel').innerHTML = `<div class="loader-block"><span class="spin"></span></div>`;
    
    fetch(url).then(r => r.json()).then(data => {
        if (data.status !== 'success') {
            document.getElementById('socialPanel').innerHTML = `<div class="text-xs text-muted">No signals available.</div>`;
            return;
        }
        document.getElementById('socialAlarm').textContent = `System Alarm: ${data.summary?.alarm_level?.toUpperCase() || 'NORMAL'}`;
        
        const posts = data.reddit?.posts || data.posts || [];
        if (!posts.length) {
            document.getElementById('socialPanel').innerHTML = `<div class="text-xs text-muted">No recent activity.</div>`;
            return;
        }
        
        document.getElementById('socialPanel').innerHTML = posts.slice(0,5).map(p => {
            const sc = p.sentiment === 'positive' ? 'sig-pos' : p.sentiment === 'negative' ? 'sig-neg' : 'sig-neu';
            return `
            <div class="list-item">
                <a href="${p.url}" target="_blank" class="list-title">${p.title}</a>
                <div class="list-meta">
                    <span>r/${p.subreddit}</span>
                    <span class="signal-chip ${sc}">${p.sentiment}</span>
                </div>
            </div>`;
        }).join('');
    }).catch(() => {
        document.getElementById('socialPanel').innerHTML = `<div class="text-xs text-muted">Fetch failed.</div>`;
    });
}

function loadPois() {
    document.getElementById('poisPanel').innerHTML = `<div class="loader-block"><span class="spin"></span></div>`;
    fetch(`api/places.php?dest=${currentSlug}&type=eco`)
        .then(r => r.json())
        .then(data => {
            const places = data.places || [];
            addPoiMarkers(places);
            if (!places.length) {
                document.getElementById('poisPanel').innerHTML = `<div class="text-xs text-muted">No eco attractions found.</div>`;
                return;
            }
            document.getElementById('poisPanel').innerHTML = places.slice(0,6).map(p => `
                <div class="list-item">
                    <div class="list-title">${p.name || 'Unknown'}</div>
                    <div class="list-meta">${p.kinds?.split(',').slice(0,2).join(', ').replace(/_/g,' ') || 'Nature'}</div>
                </div>
            `).join('');
        }).catch(() => {
            document.getElementById('poisPanel').innerHTML = `<div class="text-xs text-muted">Fetch failed.</div>`;
        });
}

// ── UI HELPERS ─────────────────────────────────────────────────
function updateSlider(id, val, unit) { document.getElementById(id).textContent = val === '0' && unit !== ' mm/hr' && unit !== ' cm' ? val+unit : (val === '25' && id==='tempVal') ? 'Live' : (val === '3' && id==='windVal') ? 'Live' : val + unit; }

function resetSliders() {
    document.getElementById('rainSlider').value  = 0;
    document.getElementById('tempSlider').value  = 25;
    document.getElementById('windSlider').value  = 3;
    document.getElementById('condSelect').value  = '';
    document.getElementById('rainVal').textContent  = '0 mm/hr';
    document.getElementById('tempVal').textContent  = 'Live';
    document.getElementById('windVal').textContent  = 'Live';
    scheduleSimulation();
}

// ── INIT ───────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initMap();
    loadWeather();
    runSimulation();
    loadSocialSignals();
    loadPois();
});
</script>
</body>
</html>
