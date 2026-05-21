<?php
session_start();
require_once __DIR__ . '/includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$logged_in = true;
$activeNav = 'trips';
$showProfileLinkInNav = true;
$user_id = $_SESSION['user_id'];
$trips = [];

$result = mysqli_query($conn, "SELECT trip_id, start_location, end_location, status FROM trips WHERE user_id='$user_id' ORDER BY trip_id DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $trips[] = $row;
}

$totalTrips = count($trips);
$ongoingTrips = 0;
$completedTrips = 0;

foreach ($trips as $trip) {
    $status = strtolower($trip['status'] ?? '');
    if ($status === 'completed') {
        $completedTrips++;
    } else {
        $ongoingTrips++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeNight - My Trips</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(231,76,60,0.12), transparent 24%),
                radial-gradient(circle at bottom right, rgba(52,152,219,0.1), transparent 24%),
                #0d0d1a;
            color: #fff;
        }

        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 50px;
            background: rgba(0,0,0,0.44);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
        }

        .logo span { color: #e74c3c; }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 28px;
        }

        .nav-links a {
            color: rgba(255,255,255,0.82);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.25s ease;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: #e74c3c;
        }

        .nav-buttons {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-outline,
        .btn-solid {
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.25s ease;
        }

        .btn-outline {
            border: 1.5px solid #fff;
            color: #fff;
        }

        .btn-outline:hover {
            background: #fff;
            color: #0d0d1a;
        }

        .btn-solid {
            background: #e74c3c;
            border: 1.5px solid #e74c3c;
            color: #fff;
        }

        .btn-solid:hover {
            background: #cf3f31;
        }

        .profile-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }

        .profile-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(231,76,60,0.18);
            border: 1px solid rgba(231,76,60,0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e74c3c;
            font-weight: 700;
        }

        .page-wrapper {
            width: min(1100px, calc(100% - 48px));
            margin: 0 auto;
            padding: 110px 0 48px;
        }

        .hero {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 18px;
            margin-bottom: 22px;
        }

        .hero-card,
        .stat-card,
        .trip-card,
        .empty-state {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 24px 50px rgba(0,0,0,0.22);
        }

        .hero-card {
            padding: 28px;
            position: relative;
            overflow: hidden;
        }

        .hero-card::after {
            content: '';
            position: absolute;
            width: 240px;
            height: 240px;
            right: -70px;
            top: -90px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(231,76,60,0.25), transparent 68%);
            pointer-events: none;
        }

        .eyebrow {
            display: inline-block;
            margin-bottom: 12px;
            font-size: 11px;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.45);
        }

        .hero-card h1 {
            font-size: clamp(2rem, 3.8vw, 3.4rem);
            line-height: 1.02;
            margin-bottom: 10px;
        }

        .hero-card h1 span {
            color: #e74c3c;
        }

        .hero-card p {
            max-width: 560px;
            color: rgba(255,255,255,0.7);
            line-height: 1.6;
            font-size: 15px;
        }

        .hero-side {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        .stat-card {
            padding: 22px;
            min-height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .stat-card h3 {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,0.46);
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .stat-card strong {
            font-size: 2.1rem;
            color: #fff;
        }

        .stat-card p {
            font-size: 13px;
            color: rgba(255,255,255,0.58);
            line-height: 1.5;
        }

        .stat-card.red strong { color: #e74c3c; }
        .stat-card.green strong { color: #2ecc71; }

        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 28px 0 16px;
        }

        .section-head h2 {
            font-size: 1.2rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.82);
        }

        .section-head a {
            color: #e74c3c;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .trips-grid {
            display: grid;
            gap: 16px;
        }

        .trip-card {
            padding: 22px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 18px;
            align-items: start;
        }

        .trip-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
            color: rgba(255,255,255,0.42);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .trip-badge {
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            border: 1px solid transparent;
        }

        .trip-badge.ongoing {
            color: #f5b041;
            background: rgba(245,176,65,0.12);
            border-color: rgba(245,176,65,0.28);
        }

        .trip-badge.completed {
            color: #2ecc71;
            background: rgba(46,204,113,0.12);
            border-color: rgba(46,204,113,0.28);
        }

        .trip-route {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 16px;
            align-items: start;
        }

        .route-line {
            width: 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 4px;
            gap: 7px;
        }

        .route-line span {
            display: block;
            border-radius: 50%;
        }

        .route-line .start-dot {
            width: 12px;
            height: 12px;
            background: #3498db;
            box-shadow: 0 0 18px rgba(52,152,219,0.45);
        }

        .route-line .connector {
            width: 2px;
            height: 46px;
            background: linear-gradient(180deg, rgba(255,255,255,0.16), rgba(231,76,60,0.7));
            border-radius: 999px;
        }

        .route-line .end-dot {
            width: 12px;
            height: 12px;
            background: #e74c3c;
            box-shadow: 0 0 18px rgba(231,76,60,0.45);
        }

        .trip-route h3 {
            font-size: 13px;
            color: rgba(255,255,255,0.42);
            text-transform: uppercase;
            letter-spacing: 0.12em;
            margin-bottom: 5px;
        }

        .trip-route p {
            font-size: 1rem;
            color: #fff;
            line-height: 1.5;
            margin-bottom: 14px;
        }

        .trip-route p:last-child {
            margin-bottom: 0;
        }

        .trip-card small {
            display: block;
            font-size: 12px;
            color: rgba(255,255,255,0.5);
            margin-top: 10px;
        }

        .empty-state {
            padding: 38px 30px;
            text-align: center;
        }

        .empty-state h3 {
            font-size: 1.45rem;
            margin-bottom: 10px;
        }

        .empty-state p {
            max-width: 520px;
            margin: 0 auto 20px;
            color: rgba(255,255,255,0.66);
            line-height: 1.6;
        }

        .empty-state a {
            display: inline-block;
            padding: 11px 20px;
            border-radius: 999px;
            background: #e74c3c;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
        }

        @media (max-width: 900px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .hero-side {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 720px) {
            .page-wrapper {
                width: min(100% - 24px, 1100px);
                padding-top: 96px;
            }

            nav {
                padding: 16px 18px;
                flex-wrap: wrap;
                gap: 12px;
            }

            .nav-links {
                flex-wrap: wrap;
                gap: 14px;
            }

            .hero-side {
                grid-template-columns: 1fr;
            }

            .trip-card {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php include 'includes/navigation.php'; ?>

<div class="page-wrapper">
    <section class="hero">
        <div class="hero-card">
            <span class="eyebrow">Trip History</span>
            <h1>Your <span>SafeNight</span> routes.</h1>
            <p>Review the trips you have logged so far. This page keeps your saved start and destination details visible in one place.</p>
        </div>
        <div class="hero-side">
            <div class="stat-card">
                <h3>Total Trips</h3>
                <strong><?php echo $totalTrips; ?></strong>
                <p>All routes you have logged from the map page.</p>
            </div>
            <div class="stat-card red">
                <h3>Ongoing</h3>
                <strong><?php echo $ongoingTrips; ?></strong>
                <p>Trips that are still marked as active.</p>
            </div>
            <div class="stat-card green">
                <h3>Completed</h3>
                <strong><?php echo $completedTrips; ?></strong>
                <p>Trips that have been marked as completed.</p>
            </div>
            <div class="stat-card">
                <h3>Next Step</h3>
                <p>Plan a new route from the map and use <strong style="font-size: inherit; color: #fff;">Log This Trip</strong> to save it here.</p>
            </div>
        </div>
    </section>

    <div class="section-head">
        <h2>Saved Trips</h2>
        <a href="map.php">Plan Another Trip</a>
    </div>

    <?php if ($totalTrips > 0): ?>
        <div class="trips-grid">
            <?php foreach ($trips as $trip): ?>
                <?php $status = strtolower($trip['status'] ?? 'ongoing'); ?>
                <article class="trip-card">
                    <div>
                        <div class="trip-meta">
                            <span>Trip #<?php echo (int) $trip['trip_id']; ?></span>
                            <span class="trip-badge <?php echo $status === 'completed' ? 'completed' : 'ongoing'; ?>">
                                <?php echo htmlspecialchars($trip['status'] ?: 'ongoing'); ?>
                            </span>
                        </div>
                        <div class="trip-route">
                            <div class="route-line" aria-hidden="true">
                                <span class="start-dot"></span>
                                <span class="connector"></span>
                                <span class="end-dot"></span>
                            </div>
                            <div>
                                <h3>Starting Point</h3>
                                <p><?php echo htmlspecialchars($trip['start_location']); ?></p>
                                <h3>Destination</h3>
                                <p><?php echo htmlspecialchars($trip['end_location']); ?></p>
                                <small>Status reflects the value saved when the trip was logged.</small>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h3>No trips logged yet.</h3>
            <p>Open the safe map, enter your route details, and use the <strong>Log This Trip</strong> button to create your first trip entry.</p>
            <a href="map.php">Go To Safe Map</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
