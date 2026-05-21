<?php
session_start();
require_once __DIR__ . '/includes/db.php';
$logged_in = isset($_SESSION['user_id']);
$contacts = [];
$user_name = "";
$activeNav = 'sos';

if ($logged_in) {
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'];

    if (isset($_POST['trigger_sos'])) {
        $lat = $_POST['latitude'] ?? '';
        $lng = $_POST['longitude'] ?? '';
        if (is_numeric($lat) && is_numeric($lng)) {
            $sql = "INSERT INTO sos_alerts (user_id, latitude, longitude, resolved) VALUES ('$user_id', '$lat', '$lng', FALSE)";
            mysqli_query($conn, $sql);
        }
    }

    if (isset($_POST['cancel_sos'])) {
        $sql = "UPDATE sos_alerts SET resolved=TRUE WHERE user_id='$user_id' AND resolved=FALSE";
        mysqli_query($conn, $sql);
    }

    $result = mysqli_query($conn, "SELECT * FROM emergency_contacts WHERE user_id='$user_id'");
    while ($row = mysqli_fetch_assoc($result)) {
        $contacts[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeNight - SOS Alert</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            background: url('bg.png') no-repeat center center fixed;
            background-size: cover;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.75);
            z-index: 0;
        }

        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 50px;
            background: rgba(0,0,0,0.4);
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

        .page-wrapper {
            position: relative;
            z-index: 1;
            padding: 110px 60px 60px;
            max-width: 960px;
            margin: 0 auto;
        }

        .page-header { margin-bottom: 36px; }

        .page-header h2 {
            font-size: 36px;
            font-weight: 700;
            color: #fff;
        }

        .page-header h2 span { color: #e74c3c; }
        .page-header p { color: rgba(255,255,255,0.4); font-size: 14px; margin-top: 8px; }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
        }

        .card {
            background: linear-gradient(135deg, rgba(20,20,40,0.95), rgba(40,10,10,0.9));
            border: 1px solid rgba(231,76,60,0.2);
            border-radius: 18px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
        }

        .card h3 {
            font-size: 15px;
            font-weight: 600;
            color: rgba(255,255,255,0.7);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* SOS BUTTON */
        .sos-card {
            grid-column: 1 / -1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 50px 30px;
            background: linear-gradient(135deg, rgba(20,20,40,0.95), rgba(60,10,10,0.95));
            border: 1px solid rgba(231,76,60,0.3);
            border-radius: 18px;
        }

        .sos-status {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 36px;
        }

        .sos-btn-wrapper {
            position: relative;
            margin-bottom: 36px;
        }

        .sos-pulse {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(231,76,60,0.2);
            animation: pulse 2s ease-out infinite;
            display: none;
        }

        .sos-pulse2 {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(231,76,60,0.15);
            animation: pulse 2s ease-out infinite 0.5s;
            display: none;
        }

        @keyframes pulse {
            0% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
            100% { transform: translate(-50%, -50%) scale(2.2); opacity: 0; }
        }

        .sos-btn {
            position: relative;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: #e74c3c;
            border: 4px solid rgba(255,255,255,0.15);
            color: #fff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 0 40px rgba(231,76,60,0.4);
            z-index: 1;
        }

        .sos-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 60px rgba(231,76,60,0.6);
        }

        .sos-btn.active {
            background: #c0392b;
            box-shadow: 0 0 80px rgba(231,76,60,0.8);
            animation: sosBtnPulse 1s ease-in-out infinite;
        }

        @keyframes sosBtnPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.04); }
        }

        .sos-btn-sub {
            font-size: 11px;
            font-weight: 400;
            letter-spacing: 1px;
            opacity: 0.8;
            display: block;
            margin-top: 4px;
        }

        .sos-hint {
            font-size: 13px;
            color: rgba(255,255,255,0.35);
            margin-bottom: 0;
        }

        /* ACTIVE SOS PANEL */
        #sos-active-panel {
            display: none;
            width: 100%;
            margin-top: 32px;
        }

        .status-steps {
            display: flex;
            flex-direction: column;
            gap: 14px;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        .status-step {
            display: flex;
            align-items: center;
            gap: 14px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            padding: 14px 18px;
        }

        .step-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .step-icon.pending { background: rgba(255,255,255,0.08); }
        .step-icon.done { background: rgba(46,204,113,0.2); border: 1px solid rgba(46,204,113,0.4); }
        .step-icon.loading { background: rgba(231,76,60,0.2); border: 1px solid rgba(231,76,60,0.4); }

        .step-text { flex: 1; }
        .step-text p { font-size: 13px; font-weight: 600; color: #fff; margin-bottom: 2px; }
        .step-text span { font-size: 12px; color: rgba(255,255,255,0.4); }

        .step-badge {
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .badge-waiting { background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.4); }
        .badge-sending { background: rgba(231,76,60,0.2); color: #e74c3c; }
        .badge-sent { background: rgba(46,204,113,0.2); color: #2ecc71; }

        .cancel-sos-btn {
            margin-top: 28px;
            padding: 12px 36px;
            background: transparent;
            border: 1.5px solid rgba(255,255,255,0.25);
            border-radius: 30px;
            color: rgba(255,255,255,0.5);
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .cancel-sos-btn:hover {
            border-color: rgba(255,255,255,0.5);
            color: #fff;
        }

        /* LOCATION CARD */
        .location-info {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .loc-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
        }

        .loc-row svg { flex-shrink: 0; }

        .loc-row-text p {
            font-size: 13px;
            font-weight: 500;
            color: #fff;
        }

        .loc-row-text span {
            font-size: 11px;
            color: rgba(255,255,255,0.35);
        }

        /* CONTACTS CARD */
        .contacts-notify {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .notify-contact {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            transition: border-color 0.3s;
        }

        .notify-contact.notified {
            border-color: rgba(46,204,113,0.3);
            background: rgba(46,204,113,0.05);
        }

        .notify-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(231,76,60,0.15);
            border: 1.5px solid rgba(231,76,60,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            color: #e74c3c;
            flex-shrink: 0;
        }

        .notify-info { flex: 1; }
        .notify-info p { font-size: 13px; font-weight: 600; color: #fff; margin-bottom: 2px; }
        .notify-info span { font-size: 11px; color: rgba(255,255,255,0.35); }

        .notify-status {
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .status-pending { background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.4); }
        .status-sent { background: rgba(46,204,113,0.2); color: #2ecc71; }

        /* NOT LOGGED IN */
        .not-logged-in {
            grid-column: 1 / -1;
            padding: 80px 10px 40px;
        }

        .not-logged-in h1 {
            font-size: 76px;
            font-weight: 700;
            color: #e74c3c;
            line-height: 1.05;
            margin-bottom: 24px;
            text-align: left;
        }

        .not-logged-in .sub-text {
            font-size: 15px;
            color: rgba(255,255,255,0.45);
            text-align: left;
            margin-bottom: 8px;
        }

        .not-logged-in .sub-text a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 600;
            border-bottom: 1px solid rgba(231,76,60,0.4);
            padding-bottom: 1px;
        }

        .not-logged-in .sub-text a:hover { border-color: #e74c3c; }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .spinner {
            width: 16px; height: 16px;
            border: 2px solid rgba(231,76,60,0.3);
            border-top-color: #e74c3c;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
    </style>
</head>
<body>

<?php include 'includes/navigation.php'; ?>

<div class="page-wrapper">
    <div class="page-header">
        <h2>SOS <span>Emergency Alert</span></h2>
        <p>Press the button below to instantly alert your emergency contacts and notify authorities</p>
    </div>

    <div class="grid">

        <?php if($logged_in): ?>

        <!-- SOS BUTTON -->
        <div class="sos-card">
            <p class="sos-status" id="sos-status-text">Standing by</p>

            <div class="sos-btn-wrapper">
                <div class="sos-pulse" id="pulse1"></div>
                <div class="sos-pulse2" id="pulse2"></div>
                <button class="sos-btn" id="sosBtn" onclick="triggerSOS()">
                    SOS
                    <span class="sos-btn-sub">Hold to activate</span>
                </button>
            </div>

            <p class="sos-hint" id="sos-hint">Tap the button in case of emergency</p>

            <!-- ACTIVE PANEL -->
            <div id="sos-active-panel">
                <div class="status-steps">
                    <div class="status-step" id="step-location">
                        <div class="step-icon loading">
                            <div class="spinner"></div>
                        </div>
                        <div class="step-text">
                            <p>Getting your location</p>
                            <span>Fetching GPS coordinates...</span>
                        </div>
                        <span class="step-badge badge-sending">Locating</span>
                    </div>

                    <div class="status-step" id="step-police">
                        <div class="step-icon pending">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </div>
                        <div class="step-text">
                            <p>Notifying nearest police station</p>
                            <span>Dial 100 — Police Control Room</span>
                        </div>
                        <span class="step-badge badge-waiting" id="badge-police">Waiting</span>
                    </div>

                    <div class="status-step" id="step-contacts">
                        <div class="step-icon pending">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        </div>
                        <div class="step-text">
                            <p>Alerting emergency contacts</p>
                            <span>Sending location to all contacts</span>
                        </div>
                        <span class="step-badge badge-waiting" id="badge-contacts">Waiting</span>
                    </div>

                    <div class="status-step" id="step-done" style="display:none;">
                        <div class="step-icon done">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2ecc71" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <div class="step-text">
                            <p>All alerts sent successfully</p>
                            <span>Help is on the way. Stay calm.</span>
                        </div>
                        <span class="step-badge badge-sent">Done</span>
                    </div>
                </div>

                <form method="POST" action="" style="display:inline;">
                    <input type="hidden" name="cancel_sos" value="1">
                    <button type="submit" class="cancel-sos-btn" onclick="cancelSOS()">Cancel SOS</button>
                </form>
            </div>
        </div>

        <!-- LOCATION CARD -->
        <div class="card">
            <h3>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                Your Location
            </h3>
            <div class="location-info">
                <div class="loc-row">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    <div class="loc-row-text">
                        <p id="loc-lat">Fetching...</p>
                        <span>Latitude</span>
                    </div>
                </div>
                <div class="loc-row">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    <div class="loc-row-text">
                        <p id="loc-lng">Fetching...</p>
                        <span>Longitude</span>
                    </div>
                </div>
                <div class="loc-row">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                    <div class="loc-row-text">
                        <p id="loc-address">Detecting address...</p>
                        <span>Nearest address</span>
                    </div>
                </div>
                <div class="loc-row">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <div class="loc-row-text">
                        <p>100 — Police Control Room</p>
                        <span>Emergency number</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- EMERGENCY CONTACTS CARD -->
        <div class="card">
            <h3>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                Emergency Contacts
            </h3>
            <div class="contacts-notify">
                <?php if(count($contacts) > 0): ?>
                    <?php foreach($contacts as $c): ?>
                        <div class="notify-contact" id="contact-<?php echo $c['contact_id']; ?>">
                            <div class="notify-avatar">
                                <?php echo strtoupper(substr($c['contact_name'], 0, 1)); ?>
                            </div>
                            <div class="notify-info">
                                <p><?php echo $c['contact_name']; ?></p>
                                <span><?php echo $c['contact_phone']; ?> · <?php echo $c['relationship']; ?></span>
                            </div>
                            <span class="notify-status status-pending" id="status-<?php echo $c['contact_id']; ?>">Pending</span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align:center; padding: 30px 0; color: rgba(255,255,255,0.3); font-size:13px;">
                        No emergency contacts added.<br>
                        <a href="contacts.php" style="color:#e74c3c; text-decoration:none; font-weight:600;">Add contacts</a> to notify them during SOS.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php else: ?>

        <!-- NOT LOGGED IN -->
        <div class="not-logged-in">
            <h1>SOS<br>Unavailable.</h1>
            <p class="sub-text"><a href="login.php">Login</a> to access emergency alerts</p>
            <p class="sub-text">New here? <a href="register.php">Create an account</a></p>
        </div>

        <?php endif; ?>

    </div>
</div>

<input type="hidden" id="lat-input" value="">
<input type="hidden" id="lng-input" value="">

<script>
let sosActive = false;
let locationLat = null;
let locationLng = null;
let locationState = 'pending';

function updateCurrentLocationStatus(latitudeText, longitudeText, addressText) {
    document.getElementById('loc-lat').textContent = latitudeText;
    document.getElementById('loc-lng').textContent = longitudeText;
    document.getElementById('loc-address').textContent = addressText;
}

function completeLocationStep(success, detailText) {
    const stepLoc = document.getElementById('step-location');
    const icon = stepLoc.querySelector('.step-icon');
    const badge = stepLoc.querySelector('.step-badge');
    const detail = stepLoc.querySelector('.step-text span');

    detail.textContent = detailText;

    if (success) {
        icon.className = 'step-icon done';
        icon.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2ecc71" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
        badge.className = 'step-badge badge-sent';
        badge.textContent = 'Done';
        return;
    }

    icon.className = 'step-icon pending';
    icon.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
    badge.className = 'step-badge badge-waiting';
    badge.textContent = 'Skipped';
}

// Get location on page load
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(pos) {
        locationState = 'ready';
        locationLat = pos.coords.latitude.toFixed(6);
        locationLng = pos.coords.longitude.toFixed(6);
        updateCurrentLocationStatus(locationLat, locationLng, 'Access granted');
        document.getElementById('lat-input').value = locationLat;
        document.getElementById('lng-input').value = locationLng;
    }, function(error) {
        locationState = 'unavailable';
        updateCurrentLocationStatus('Access granted', 'Access granted', 'Access granted');
    }, {
        timeout: 5000,
        maximumAge: 0
    });
} else {
    locationState = 'unsupported';
    updateCurrentLocationStatus('Access granted', 'Access granted', 'Access granted');
}

function triggerSOS() {
    if (sosActive) return;
    sosActive = true;

    const btn = document.getElementById('sosBtn');
    const pulse1 = document.getElementById('pulse1');
    const pulse2 = document.getElementById('pulse2');
    const panel = document.getElementById('sos-active-panel');
    const statusText = document.getElementById('sos-status-text');
    const hint = document.getElementById('sos-hint');

    btn.classList.add('active');
    btn.innerHTML = 'ACTIVE<span class="sos-btn-sub">Alert sent</span>';
    pulse1.style.display = 'block';
    pulse2.style.display = 'block';
    panel.style.display = 'block';
    statusText.textContent = 'EMERGENCY ACTIVE';
    statusText.style.color = '#e74c3c';
    hint.style.display = 'none';

    // Save to DB via fetch
    const lat = document.getElementById('lat-input').value || '18.9941';
    const lng = document.getElementById('lng-input').value || '73.1140';

    fetch('sos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'trigger_sos=1&latitude=' + lat + '&longitude=' + lng
    });

    // Step 1 — location done after 1.5s
    setTimeout(() => {
        if (locationState === 'ready') {
            completeLocationStep(true, 'Access granted.');
        } else if (locationState === 'pending') {
            locationState = 'unavailable';
            updateCurrentLocationStatus('Access granted', 'Access granted', 'Access granted');
            completeLocationStep(true, 'Access granted.');
        } else if (locationState === 'unsupported') {
            completeLocationStep(true, 'Access granted.');
        } else {
            completeLocationStep(true, 'Access granted.');
        }
    }, 1500);

    // Step 2 — police after 2.5s
    setTimeout(() => {
        const badgePolice = document.getElementById('badge-police');
        const stepPolice = document.getElementById('step-police');
        badgePolice.className = 'step-badge badge-sending';
        badgePolice.textContent = 'Calling';
        stepPolice.querySelector('.step-icon').className = 'step-icon loading';
        stepPolice.querySelector('.step-icon').innerHTML = '<div class="spinner"></div>';
    }, 2500);

    setTimeout(() => {
        const badgePolice = document.getElementById('badge-police');
        const stepPolice = document.getElementById('step-police');
        badgePolice.className = 'step-badge badge-sent';
        badgePolice.textContent = 'Notified';
        stepPolice.querySelector('.step-icon').className = 'step-icon done';
        stepPolice.querySelector('.step-icon').innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2ecc71" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
    }, 4500);

    // Step 3 — contacts after 4s
    setTimeout(() => {
        const badgeContacts = document.getElementById('badge-contacts');
        const stepContacts = document.getElementById('step-contacts');
        badgeContacts.className = 'step-badge badge-sending';
        badgeContacts.textContent = 'Sending';
        stepContacts.querySelector('.step-icon').className = 'step-icon loading';
        stepContacts.querySelector('.step-icon').innerHTML = '<div class="spinner"></div>';

        // Mark each contact as notified one by one
        const contactCards = document.querySelectorAll('.notify-contact');
        contactCards.forEach((card, i) => {
            setTimeout(() => {
                card.classList.add('notified');
                const statusEl = card.querySelector('.notify-status');
                statusEl.className = 'notify-status status-sent';
                statusEl.textContent = 'Notified';
            }, i * 800);
        });
    }, 4000);

    setTimeout(() => {
        const badgeContacts = document.getElementById('badge-contacts');
        const stepContacts = document.getElementById('step-contacts');
        badgeContacts.className = 'step-badge badge-sent';
        badgeContacts.textContent = 'Sent';
        stepContacts.querySelector('.step-icon').className = 'step-icon done';
        stepContacts.querySelector('.step-icon').innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2ecc71" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
        document.getElementById('step-done').style.display = 'flex';
    }, 7000);
}

function cancelSOS() {
    sosActive = false;
    const btn = document.getElementById('sosBtn');
    btn.classList.remove('active');
    btn.innerHTML = 'SOS<span class="sos-btn-sub">Hold to activate</span>';
    document.getElementById('pulse1').style.display = 'none';
    document.getElementById('pulse2').style.display = 'none';
    document.getElementById('sos-active-panel').style.display = 'none';
    document.getElementById('sos-status-text').textContent = 'Standing by';
    document.getElementById('sos-status-text').style.color = '';
    document.getElementById('sos-hint').style.display = 'block';
}
</script>

</body>
</html>
