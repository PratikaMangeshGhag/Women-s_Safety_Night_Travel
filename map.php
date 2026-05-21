<?php
session_start();
require_once __DIR__ . '/includes/db.php';
$logged_in = isset($_SESSION['user_id']);
$unsafe_spots = [];
$activeNav = 'map';

// fetch unsafe spots for the map
$result = mysqli_query($conn, "SELECT * FROM unsafe_spots");
while ($row = mysqli_fetch_assoc($result)) {
    $unsafe_spots[] = $row;
}

// handle new trip
if ($logged_in && isset($_POST['start_trip'])) {
    $user_id = $_SESSION['user_id'];
    $start = trim($_POST['start_location'] ?? '');
$end = trim($_POST['end_location'] ?? '');

$start_value = $start;
$end_value = $end;
    if ($start !== '' && $end !== '') {
        $sql = "INSERT INTO trips (user_id, start_location, end_location, status) VALUES ('$user_id', '$start', '$end', 'ongoing')";
        mysqli_query($conn, $sql);
    }
}

// handle end trip
if ($logged_in && isset($_POST['end_trip'])) {
    $user_id = $_SESSION['user_id'];

    // update latest ongoing trip to completed
    $sql = "UPDATE trips 
            SET status='completed' 
            WHERE user_id='$user_id' 
            AND status='ongoing' 
            ORDER BY trip_id DESC 
            LIMIT 1";

    mysqli_query($conn, $sql);

}
// handle report unsafe spot
if ($logged_in && isset($_POST['report_spot'])) {
    $user_id = $_SESSION['user_id'];
    $lat = $_POST['spot_lat'] ?? '';
    $lng = $_POST['spot_lng'] ?? '';
    $desc = trim($_POST['spot_desc'] ?? '');
    if (is_numeric($lat) && is_numeric($lng) && $desc !== '' && strlen($desc) >= 5) {
        $sql = "INSERT INTO unsafe_spots (reported_by, latitude, longitude, description) VALUES ('$user_id', '$lat', '$lng', '$desc')";
        mysqli_query($conn, $sql);
    }
    header("Location: map.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeNight - Safe Map</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            background: #0d0d1a;
        }

        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 50px;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .logo { font-size: 24px; font-weight: 700; color: #fff; }
        .logo span { color: #e74c3c; }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 30px;
        }

        .nav-links a {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover { color: #e74c3c; }
        .nav-links a.active { color: #e74c3c; }
        .nav-buttons { display: flex; gap: 12px; }

        .btn-outline {
            padding: 8px 20px;
            border: 1.5px solid #fff;
            border-radius: 20px;
            color: #fff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-outline:hover { background: #fff; color: #1a1a2e; }

        .btn-solid {
            padding: 8px 20px;
            background: #e74c3c;
            border: 1.5px solid #e74c3c;
            border-radius: 20px;
            color: #fff;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s;
        }

        .btn-solid:hover { background: #c0392b; }

        .page-layout {
            display: flex;
            height: 100vh;
            padding-top: 70px;
        }

        /* SIDEBAR */
        .sidebar {
            width: 340px;
            flex-shrink: 0;
            background: linear-gradient(180deg, #0d0d1a 0%, #1a0a0a 100%);
            border-right: 1px solid rgba(231,76,60,0.15);
            overflow-y: auto;
            padding: 24px 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(231,76,60,0.3); border-radius: 2px; }

        .sidebar-section {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(231,76,60,0.15);
            border-radius: 14px;
            padding: 18px;
        }

        .sidebar-section h3 {
            font-size: 12px;
            font-weight: 600;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group { margin-bottom: 12px; }

        label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: rgba(255,255,255,0.4);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input, textarea {
            width: 100%;
            padding: 10px 12px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            font-size: 13px;
            color: #fff;
            outline: none;
            transition: all 0.3s;
            font-family: 'Segoe UI', sans-serif;
        }

        textarea { resize: none; height: 70px; }
        input::placeholder, textarea::placeholder { color: rgba(255,255,255,0.2); }
        input:focus, textarea:focus { border-color: #e74c3c; background: rgba(255,255,255,0.09); }

        .map-btn {
            width: 100%;
            padding: 11px;
            background: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 4px;
        }

        .map-btn:hover {
            background: #c0392b;
            transform: translateY(-1px);
        }

        .map-btn.secondary {
            background: transparent;
            border: 1px solid rgba(231,76,60,0.4);
            color: #e74c3c;
        }

        .map-btn.secondary:hover {
            background: rgba(231,76,60,0.1);
        }

        /* LEGEND */
        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 13px;
            color: rgba(255,255,255,0.6);
        }

        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .dot-red { background: #e74c3c; }
        .dot-blue { background: #3498db; }
        .dot-green { background: #2ecc71; }
        .dot-yellow { background: #f1c40f; }

        /* SPOT LIST */
        .spot-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: 200px;
            overflow-y: auto;
        }

        .spot-item {
            background: rgba(231,76,60,0.06);
            border: 1px solid rgba(231,76,60,0.15);
            border-radius: 8px;
            padding: 10px 12px;
            cursor: pointer;
            transition: border-color 0.3s;
        }

        .spot-item:hover { border-color: rgba(231,76,60,0.4); }

        .spot-item p {
            font-size: 12px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 3px;
        }

        .spot-item span {
            font-size: 11px;
            color: rgba(255,255,255,0.35);
        }

        /* MAP */
        #map {
            position: relative;
            flex: 1;
            height: 100%;
            overflow: hidden;
            background:
                radial-gradient(circle at 18% 22%, rgba(52, 152, 219, 0.12) 0, rgba(52, 152, 219, 0) 18%),
                radial-gradient(circle at 78% 68%, rgba(231, 76, 60, 0.08) 0, rgba(231, 76, 60, 0) 20%),
                linear-gradient(180deg, #101827 0%, #0f1a2e 42%, #0b1322 100%);
        }

        #map::before,
        #map::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        #map::before {
            background-image:
                linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 70px 70px;
            opacity: 0.22;
        }

        #map::after {
            background:
                linear-gradient(120deg, transparent 10%, rgba(255,255,255,0.03) 12%, transparent 14%),
                linear-gradient(35deg, transparent 28%, rgba(255,255,255,0.025) 30%, transparent 32%),
                linear-gradient(165deg, transparent 60%, rgba(255,255,255,0.025) 62%, transparent 64%);
            mix-blend-mode: screen;
            opacity: 0.8;
        }

        .map-water,
        .map-road,
        .map-label,
        .map-pill,
        .map-pin,
        .unsafe-blob,
        .unsafe-core,
        .unsafe-tooltip,
        .report-indicator,
        .route-status {
            position: absolute;
        }

        .map-water {
            background: radial-gradient(circle at 30% 30%, rgba(57, 182, 255, 0.22), rgba(57, 182, 255, 0.04) 58%, transparent 70%);
            border-radius: 50%;
            filter: blur(4px);
            opacity: 0.65;
        }

        .map-road {
            height: 6px;
            border-radius: 999px;
            background: linear-gradient(90deg, rgba(255,255,255,0.05), rgba(255,255,255,0.22), rgba(255,255,255,0.05));
            box-shadow: 0 0 20px rgba(255,255,255,0.08);
            transform-origin: left center;
        }

        .map-label {
            font-size: 12px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.38);
            font-weight: 600;
        }

        .map-pill {
            top: 22px;
            right: 24px;
            padding: 9px 12px;
            border-radius: 999px;
            background: rgba(8, 14, 25, 0.78);
            border: 1px solid rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.7);
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            z-index: 8;
        }

        .route-status {
            left: 24px;
            bottom: 22px;
            z-index: 8;
            min-width: 220px;
            padding: 14px 16px;
            border-radius: 14px;
            background: rgba(8, 14, 25, 0.8);
            border: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(8px);
            box-shadow: 0 20px 45px rgba(0,0,0,0.28);
        }

        .route-status h4 {
            margin: 0 0 6px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: rgba(255,255,255,0.45);
        }

        .route-status p {
            margin: 0;
            font-size: 14px;
            color: #fff;
        }

        .route-status p span {
            color: #e74c3c;
            font-weight: 600;
        }

        .map-route-layer {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 4;
            pointer-events: none;
        }

        .route-shadow {
            fill: none;
            stroke: rgba(231, 76, 60, 0.18);
            stroke-width: 2.6;
        }

        .route-path {
            fill: none;
            stroke: #ff6f61;
            stroke-width: 1.25;
            stroke-linecap: round;
            stroke-linejoin: round;
            filter: drop-shadow(0 0 10px rgba(231, 76, 60, 0.45));
            opacity: 0;
        }

        .route-path.animate {
            opacity: 1;
            animation: drawRoute 1.4s ease forwards;
        }

       /*ANIMATION FOR ROUTE DRAWING*/
        @keyframes drawRoute {
            from {
                stroke-dashoffset: var(--route-length, 0);
            }
            to {
                stroke-dashoffset: 0;
            }
        }

        .map-pin {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 3px solid #fff;
            transform: translate(-50%, -50%);
            box-shadow: 0 0 0 10px rgba(255,255,255,0.06), 0 0 30px rgba(0,0,0,0.25);
            z-index: 6;
        }

        .map-pin::after {
            content: '';
            position: absolute;
            inset: 50% auto auto 50%;
            width: 48px;
            height: 48px;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            opacity: 0.45;
        }

        .map-pin.user {
            background: #3498db;
        }

        .map-pin.user::after {
            background: radial-gradient(circle, rgba(52,152,219,0.3) 0, rgba(52,152,219,0.06) 48%, transparent 72%);
        }

        .map-pin.destination {
            background: #2ecc71;
            opacity: 0;
            transition: opacity 0.35s ease;
        }

        .map-pin.destination.visible {
            opacity: 1;
            animation: destinationPulse 1.6s ease infinite;
        }

        .map-pin.destination::after {
            background: radial-gradient(circle, rgba(46,204,113,0.35) 0, rgba(46,204,113,0.08) 45%, transparent 70%);
        }

        .map-pin.report {
            width: 16px;
            height: 16px;
            background: #f1c40f;
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .map-pin.report.visible {
            opacity: 1;
        }

        .map-pin.report::after {
            background: radial-gradient(circle, rgba(241,196,15,0.3) 0, rgba(241,196,15,0.06) 44%, transparent 70%);
        }

        @keyframes destinationPulse {
            0%, 100% { box-shadow: 0 0 0 10px rgba(255,255,255,0.06), 0 0 30px rgba(0,0,0,0.25); }
            50% { box-shadow: 0 0 0 16px rgba(46,204,113,0.12), 0 0 36px rgba(46,204,113,0.25); }
        }

        .unsafe-zone {
            position: absolute;
            inset: 0;
            z-index: 5;
        }

        .unsafe-blob {
            transform: translate(-50%, -50%);
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 99, 99, 0.3) 0, rgba(231,76,60,0.2) 26%, rgba(231,76,60,0.08) 58%, transparent 74%);
            filter: blur(2px);
            animation: blobPulse 3.2s ease-in-out infinite;
            cursor: pointer;
        }

        .unsafe-core {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #ff5b5b;
            border: 2px solid rgba(255,255,255,0.9);
            transform: translate(-50%, -50%);
            box-shadow: 0 0 24px rgba(231,76,60,0.45);
            z-index: 2;
            cursor: pointer;
        }

        .unsafe-tooltip {
            transform: translate(-50%, calc(-100% - 18px));
            min-width: 180px;
            max-width: 220px;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(18, 10, 12, 0.92);
            border: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.78);
            font-size: 12px;
            line-height: 1.45;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
            z-index: 3;
        }

        .unsafe-tooltip strong {
            display: block;
            color: #ff6f61;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 4px;
        }

        .unsafe-node:hover .unsafe-tooltip,
        .unsafe-node.active .unsafe-tooltip {
            opacity: 1;
        }

        @keyframes blobPulse {
            0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.88; }
            50% { transform: translate(-50%, -50%) scale(1.08); opacity: 1; }
        }

        /* NOT LOGGED IN */
        .not-logged-in {
            padding: 80px 40px;
        }

        .not-logged-in h1 {
            font-size: 72px;
            font-weight: 700;
            color: #e74c3c;
            line-height: 1.05;
            margin-bottom: 24px;
        }

        .not-logged-in .sub-text {
            font-size: 15px;
            color: rgba(255,255,255,0.45);
            margin-bottom: 8px;
        }

        .not-logged-in .sub-text a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 600;
            border-bottom: 1px solid rgba(231,76,60,0.4);
        }

        /* COORD DISPLAY */
        .coord-display {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 12px;
            color: rgba(255,255,255,0.5);
            margin-bottom: 12px;
            min-height: 38px;
        }

        .coord-display span { color: #e74c3c; font-weight: 600; }

        .alert-success {
            background: rgba(46,204,113,0.12);
            border: 1px solid rgba(46,204,113,0.3);
            color: #2ecc71;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 12px;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>

<?php include 'includes/navigation.php'; ?>

<?php if($logged_in): ?>

<div class="page-layout">

    <!-- SIDEBAR -->
    <div class="sidebar">

        <!-- PLAN A TRIP -->
        <div class="sidebar-section">
            <h3>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                Plan a Trip
            </h3>
            <!---START AND END POINT FORMS--->
            <form method="POST" action="" id="trip-form">
                <div class="form-group">
                    <label>Starting Point</label>
                    <input type="text" name="start_location" id="start_location"
       value="<?php echo htmlspecialchars($start_value); ?>"
       placeholder="e.g. Panvel Station" required>
                </div>
                <div class="form-group">
                    <label>Destination</label>
                    <input type="text" name="end_location" id="end_location"
       value="<?php echo htmlspecialchars($end_value); ?>"
       placeholder="e.g. Kharghar Sector 12" required>
                </div>
                <button type="button" class="map-btn" onclick="planRoute()">Show Safe Route</button>
                <button type="submit" name="start_trip" class="map-btn secondary" style="margin-top:8px;">Log This Trip</button>
                <button type="submit" name="end_trip" class="map-btn" style="margin-top:8px;">
    End Trip
</button>
                
            </form>
        </div>

        <!-- REPORT UNSAFE SPOT -->
        <div class="sidebar-section">
            <h3>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                Report Unsafe Spot
            </h3>
            <p style="font-size:12px; color:rgba(255,255,255,0.35); margin-bottom:12px;">Click anywhere on the map to pin a location</p>
            <div class="coord-display" id="coord-display">
                Click on the map to select a location
            </div>
            <form method="POST" action="">
                <input type="hidden" name="spot_lat" id="spot_lat">
                <input type="hidden" name="spot_lng" id="spot_lng">
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="spot_desc" placeholder="Describe why this area is unsafe..." minlength="5" maxlength="250" required></textarea>
                </div>
                <button type="submit" name="report_spot" class="map-btn">Report This Spot</button>
            </form>
        </div>

        <!-- LEGEND -->
        <div class="sidebar-section">
            <h3>Map Legend</h3>
            <div class="legend-item">
                <div class="legend-dot dot-red"></div>
                Unsafe spots reported by community
            </div>
            <div class="legend-item">
                <div class="legend-dot dot-blue"></div>
                Your current location
            </div>
            <div class="legend-item">
                <div class="legend-dot dot-green"></div>
                Trip destination
            </div>
            <div class="legend-item">
                <div class="legend-dot dot-yellow"></div>
                Selected spot to report
            </div>
        </div>

        <!-- REPORTED SPOTS LIST -->
        <div class="sidebar-section">
            <h3>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                Reported Unsafe Spots
            </h3>
            <div class="spot-list">
                <?php if(count($unsafe_spots) > 0): ?>
                    <?php foreach($unsafe_spots as $spot): ?>
                        <div class="spot-item" onclick="focusSpot(<?php echo $spot['latitude']; ?>, <?php echo $spot['longitude']; ?>)">
                            <p><?php echo substr($spot['description'], 0, 45); ?>...</p>
                            <span><?php echo $spot['latitude']; ?>, <?php echo $spot['longitude']; ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="font-size:12px; color:rgba(255,255,255,0.3);">No unsafe spots reported yet.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- MAP -->
    <div id="map">
        <div class="map-pill">Demo Safe Map</div>
        <div class="map-water" style="left: 9%; top: 14%; width: 210px; height: 210px;"></div>
        <div class="map-water" style="right: 6%; bottom: 10%; width: 280px; height: 210px;"></div>

        <div class="map-road" style="left: 8%; top: 18%; width: 42%; transform: rotate(12deg);"></div>
        <div class="map-road" style="left: 36%; top: 32%; width: 46%; transform: rotate(-18deg);"></div>
        <div class="map-road" style="left: 18%; top: 61%; width: 52%; transform: rotate(-6deg);"></div>
        <div class="map-road" style="left: 54%; top: 72%; width: 26%; transform: rotate(10deg);"></div>
        <div class="map-road" style="left: 20%; top: 44%; width: 30%; transform: rotate(84deg);"></div>
        <div class="map-road" style="left: 64%; top: 18%; width: 36%; transform: rotate(88deg);"></div>

        <div class="map-label" style="left: 13%; top: 12%;">Harbor Edge</div>
        <div class="map-label" style="left: 58%; top: 20%;">Central Corridor</div>
        <div class="map-label" style="left: 26%; top: 54%;">Market District</div>
        <div class="map-label" style="left: 66%; top: 65%;">Sector Nine</div>

        <svg class="map-route-layer" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
            <path id="routeShadow" class="route-shadow" d=""></path>
            <path id="routePath" class="route-path" d=""></path>
        </svg>

        <div id="unsafeZoneLayer" class="unsafe-zone"></div>
        <div id="userMarker" class="map-pin user"></div>
        <div id="destinationMarker" class="map-pin destination"></div>
        <div id="reportMarker" class="map-pin report"></div>

        <div class="route-status" id="routeStatus">
            <h4>Map Status</h4>
            <p>Enter a start and destination to animate a demo route.</p>
        </div>
    </div>

</div>

<?php else: ?>

<div style="background: url('bg.png') no-repeat center center fixed; background-size: cover; min-height: 100vh;">
    <div style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.75);"></div>
    <div class="not-logged-in" style="position:relative; z-index:1; padding-top: 160px;">
        <h1>Safe Map<br>Unavailable.</h1>
        <p class="sub-text"><a href="login.php">Login</a> to access the safe map</p>
        <p class="sub-text">New here? <a href="register.php">Create an account</a></p>
    </div>
</div>

<?php endif; ?>

<!-- Pass PHP unsafe spots to JS -->
<script>
const unsafeSpots = <?php echo json_encode($unsafe_spots); ?>;
</script>

<script>
const demoMap = document.getElementById('map');
const unsafeZoneLayer = document.getElementById('unsafeZoneLayer');
const userMarker = document.getElementById('userMarker');
const destinationMarker = document.getElementById('destinationMarker');
const reportMarker = document.getElementById('reportMarker');
//actual path
const routePath = document.getElementById('routePath');
const routeShadow = document.getElementById('routeShadow');
const routeStatus = document.getElementById('routeStatus');
//map boundaries
const demoBounds = {
    latMin: 19.0000,
    latMax: 19.0900,
    lngMin: 72.9500,
    lngMax: 73.0800
};
let currentRoute = null;

document.addEventListener('DOMContentLoaded', () => {
    if (!demoMap) return;
    setupTripForm();
    renderUnsafeSpots();
    setupDemoMapInteractions();
    setMarkerPosition(userMarker, { x: 18, y: 58 });
});

function setupTripForm() {
    const tripForm = document.getElementById('trip-form');
    if (!tripForm) return;

    tripForm.addEventListener('keydown', event => {
        if (event.key === 'Enter' && event.target.tagName !== 'TEXTAREA') {
            event.preventDefault();
            planRoute();
        }
    });
}

function planRoute() {
    const start = document.getElementById('start_location').value;
    const end = document.getElementById('end_location').value;
    if (!start || !end) return alert('Please enter both start and destination!');

    const startPoint = derivePointFromText(start, 18, 58);
    const endPoint = derivePointFromText(end, 78, 30);
    const route = buildRoutePath(startPoint, endPoint, start, end);

    currentRoute = route;
    setMarkerPosition(userMarker, startPoint);
    setMarkerPosition(destinationMarker, endPoint);
    destinationMarker.classList.add('visible');

    routeShadow.setAttribute('d', route.path);
    routePath.setAttribute('d', route.path);
    routePath.classList.remove('animate');

    const pathLength = routePath.getTotalLength();
    routePath.style.setProperty('--route-length', pathLength);
    routePath.style.strokeDasharray = pathLength;
    routePath.style.strokeDashoffset = pathLength;

    void routePath.getBoundingClientRect();
    routePath.classList.add('animate');

    routeStatus.innerHTML = `<h4>Route Active</h4><p><span>${escapeHtml(start)}</span> to <span>${escapeHtml(end)}</span></p>`;
}

function focusSpot(lat, lng) {
    if (!demoMap) return;
    const point = latLngToPercent(parseFloat(lat), parseFloat(lng));
    setMarkerPosition(destinationMarker, point);
    destinationMarker.classList.add('visible');
    routeStatus.innerHTML = '<h4>Unsafe Spot Focus</h4><p>Centered the demo map on the selected community report.</p>';
    demoMap.animate([
        { transform: 'scale(1)' },
        { transform: 'scale(1.018)' },
        { transform: 'scale(1)' }
    ], {
        duration: 520,
        easing: 'ease'
    });
}

function renderUnsafeSpots() {
    if (!unsafeZoneLayer) return;

    unsafeZoneLayer.innerHTML = '';
    unsafeSpots.forEach((spot, index) => {
        const point = latLngToPercent(parseFloat(spot.latitude), parseFloat(spot.longitude), index);
        const size = 90 + ((index % 4) * 24);

        const node = document.createElement('div');
        node.className = 'unsafe-node';
        node.style.position = 'absolute';
        node.style.left = `${point.x}%`;
        node.style.top = `${point.y}%`;

        const blob = document.createElement('div');
        blob.className = 'unsafe-blob';
        blob.style.width = `${size}px`;
        blob.style.height = `${size}px`;
        blob.style.animationDelay = `${index * 0.35}s`;

        const core = document.createElement('div');
        core.className = 'unsafe-core';

        const tooltip = document.createElement('div');
        tooltip.className = 'unsafe-tooltip';
        tooltip.innerHTML = `<strong>Unsafe Area</strong>${escapeHtml(spot.description || 'Community report')}`;

        const activate = () => {
            document.querySelectorAll('.unsafe-node.active').forEach(item => item.classList.remove('active'));
            node.classList.add('active');
            routeStatus.innerHTML = `<h4>Unsafe Spot</h4><p>${escapeHtml(spot.description || 'Community report')}</p>`;
        };

        blob.addEventListener('click', activate);
        core.addEventListener('click', activate);

        node.appendChild(blob);
        node.appendChild(core);
        node.appendChild(tooltip);
        unsafeZoneLayer.appendChild(node);
    });
}

function setupDemoMapInteractions() {
    if (!demoMap) return;

    demoMap.addEventListener('click', event => {
        if (event.target.closest('.unsafe-node') || event.target.closest('.route-status') || event.target.closest('.map-pill')) {
            return;
        }

        const rect = demoMap.getBoundingClientRect();
        const xPercent = ((event.clientX - rect.left) / rect.width) * 100;
        const yPercent = ((event.clientY - rect.top) / rect.height) * 100;
        const latLng = percentToLatLng(xPercent, yPercent);

        document.getElementById('spot_lat').value = latLng.lat;
        document.getElementById('spot_lng').value = latLng.lng;
        document.getElementById('coord-display').innerHTML = `Selected: <span>${latLng.lat}, ${latLng.lng}</span>`;

        setMarkerPosition(reportMarker, { x: xPercent, y: yPercent });
        reportMarker.classList.add('visible');
    });
}

function derivePointFromText(text, fallbackX, fallbackY) {
    const hash = hashText(text);
    const x = 14 + (hash % 70);
    const y = 18 + (Math.floor(hash / 31) % 60);

    return {
        x: clamp((x + fallbackX) / 2, 10, 88),
        y: clamp((y + fallbackY) / 2, 12, 88)
    };
}
//cubic beizer curve 
function buildRoutePath(startPoint, endPoint, start, end) {
    const routeHash = hashText(`${start}:${end}`);
    const bendX = clamp(((startPoint.x + endPoint.x) / 2) + ((routeHash % 15) - 7), 12, 88);
    const bendY = clamp(((startPoint.y + endPoint.y) / 2) + ((Math.floor(routeHash / 17) % 18) - 9), 12, 88);
    const secondBendX = clamp((bendX + endPoint.x) / 2 + ((routeHash % 9) - 4), 12, 88);
    const secondBendY = clamp((bendY + startPoint.y) / 2 + ((Math.floor(routeHash / 11) % 10) - 5), 12, 88);

    return {
        path: `M ${startPoint.x} ${startPoint.y} C ${bendX} ${startPoint.y}, ${bendX} ${bendY}, ${secondBendX} ${secondBendY} S ${endPoint.x} ${endPoint.y}, ${endPoint.x} ${endPoint.y}`
    };
}

function latLngToPercent(lat, lng, offsetSeed = 0) {
    const normalizedX = ((lng - demoBounds.lngMin) / (demoBounds.lngMax - demoBounds.lngMin)) * 100;
    const normalizedY = 100 - (((lat - demoBounds.latMin) / (demoBounds.latMax - demoBounds.latMin)) * 100);

    return {
        x: clamp(normalizedX + ((offsetSeed % 3) - 1) * 1.4, 8, 92),
        y: clamp(normalizedY + ((offsetSeed % 4) - 1.5) * 1.1, 8, 92)
    };
}

function percentToLatLng(xPercent, yPercent) {
    const lat = demoBounds.latMin + ((100 - yPercent) / 100) * (demoBounds.latMax - demoBounds.latMin);
    const lng = demoBounds.lngMin + (xPercent / 100) * (demoBounds.lngMax - demoBounds.lngMin);

    return {
        lat: lat.toFixed(6),
        lng: lng.toFixed(6)
    };
}

function setMarkerPosition(element, point) {
    if (!element) return;
    element.style.left = `${point.x}%`;
    element.style.top = `${point.y}%`;
}

function hashText(text) {
    let hash = 0;
    const input = text.trim().toLowerCase();

    for (let i = 0; i < input.length; i += 1) {
        hash = ((hash << 5) - hash) + input.charCodeAt(i);
        hash |= 0;
    }

    return Math.abs(hash);
}

function clamp(value, min, max) {
    return Math.min(Math.max(value, min), max);
}

function escapeHtml(value) {
    return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
</script>

</body>
</html>
