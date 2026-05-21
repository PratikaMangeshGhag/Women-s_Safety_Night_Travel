<?php
session_start();
$logged_in = isset($_SESSION['user_id']);
$activeNav = 'about';
$showProfileLinkInNav = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeNight - About</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bg: #09111f;
            --panel: rgba(255,255,255,0.05);
            --border: rgba(255,255,255,0.08);
            --text: #ffffff;
            --muted: rgba(255,255,255,0.68);
            --soft: rgba(255,255,255,0.44);
            --accent: #e74c3c;
            --accent-blue: #3498db;
            --accent-green: #2ecc71;
        }

        body {
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(231,76,60,0.18), transparent 22%),
                radial-gradient(circle at 80% 18%, rgba(52,152,219,0.14), transparent 18%),
                linear-gradient(180deg, #0b1424 0%, #09111f 48%, #080d18 100%);
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
            background: rgba(0,0,0,0.42);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
        }

        .logo span { color: var(--accent); }

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
            color: var(--accent);
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
            color: #09111f;
        }

        .btn-solid {
            background: var(--accent);
            border: 1.5px solid var(--accent);
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
            color: var(--accent);
            font-weight: 700;
        }

        .page-wrapper {
            width: min(1120px, calc(100% - 48px));
            margin: 0 auto;
            padding: 108px 0 48px;
        }

        .hero {
            display: grid;
            grid-template-columns: 1.3fr 0.9fr;
            gap: 18px;
            margin-bottom: 22px;
        }

        .hero-main,
        .hero-side,
        .story-card,
        .feature-card,
        .commitment-card,
        .cta-card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 24px;
            backdrop-filter: blur(10px);
            box-shadow: 0 24px 50px rgba(0,0,0,0.24);
        }

        .hero-main {
            padding: 34px;
            position: relative;
            overflow: hidden;
        }

        .hero-main::after {
            content: '';
            position: absolute;
            right: -90px;
            top: -90px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(231,76,60,0.28), transparent 68%);
            pointer-events: none;
        }

        .eyebrow {
            display: inline-block;
            margin-bottom: 14px;
            font-size: 11px;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--soft);
        }

        .hero-main h1 {
            font-size: clamp(2.2rem, 4vw, 4rem);
            line-height: 0.98;
            margin-bottom: 14px;
            max-width: 620px;
        }

        .hero-main h1 span {
            color: var(--accent);
        }

        .hero-main p {
            max-width: 620px;
            font-size: 16px;
            line-height: 1.7;
            color: var(--muted);
        }

        .hero-side {
            padding: 28px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 24px;
        }

        .hero-side h3,
        .section-head h2 {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--soft);
        }

        .hero-side ul {
            list-style: none;
            display: grid;
            gap: 14px;
        }

        .hero-side li {
            padding-left: 18px;
            position: relative;
            color: var(--muted);
            line-height: 1.55;
        }

        .hero-side li::before {
            content: '';
            position: absolute;
            left: 0;
            top: 9px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 16px rgba(231,76,60,0.38);
        }

        .grid-two {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 22px;
        }

        .story-card,
        .commitment-card,
        .cta-card {
            padding: 28px;
        }

        .story-card p,
        .commitment-card p,
        .cta-card p {
            margin-top: 12px;
            color: var(--muted);
            line-height: 1.7;
        }

        .section-head {
            margin: 28px 0 16px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
        }

        .feature-card {
            padding: 24px 22px;
        }

        .feature-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .feature-card h3 {
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .feature-card p {
            color: var(--muted);
            line-height: 1.6;
            font-size: 14px;
        }

        .cta-card {
            margin-top: 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .cta-copy h2 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .cta-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-primary,
        .btn-secondary {
            display: inline-block;
            padding: 11px 20px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.25s ease;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
        }

        .btn-primary:hover {
            background: #cf3f31;
        }

        .btn-secondary {
            border: 1px solid rgba(255,255,255,0.14);
            color: #fff;
            background: rgba(255,255,255,0.04);
        }

        .btn-secondary:hover {
            border-color: rgba(255,255,255,0.28);
            background: rgba(255,255,255,0.08);
        }

        @media (max-width: 960px) {
            .hero,
            .grid-two,
            .features-grid {
                grid-template-columns: 1fr 1fr;
            }

            .hero {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .page-wrapper {
                width: min(100% - 24px, 1120px);
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

            .grid-two,
            .features-grid,
            .cta-card {
                grid-template-columns: 1fr;
                display: grid;
            }

            .cta-card {
                gap: 20px;
            }
        }
    </style>
</head>
<body>

<?php include 'includes/navigation.php'; ?>

<div class="page-wrapper">
    <section class="hero">
        <div class="hero-main">
            <span class="eyebrow">About SafeNight</span>
            <h1>Built to make <span>night travel</span> feel less uncertain.</h1>
            <p>SafeNight is a women’s safety travel project focused on giving users a clearer sense of control when moving through the city after dark. The idea is simple: combine route planning, quick emergency actions, trusted contacts, and community safety reporting in one place.</p>
        </div>
        <aside class="hero-side">
            <div>
                <h3>What This Project Tries To Solve</h3>
                <ul>
                    <li>Fear and uncertainty during late-night travel.</li>
                    <li>Difficulty informing trusted people quickly in emergencies.</li>
                    <li>Lack of shared visibility into unsafe areas reported by the community.</li>
                </ul>
            </div>
            <div>
                <h3>Core Idea</h3>
                <p style="color: var(--muted); line-height: 1.7;">Make safety tools feel immediate and usable, not hidden across different apps or delayed by confusion in stressful moments.</p>
            </div>
        </aside>
    </section>

    <section class="grid-two">
        <div class="story-card">
            <h2>Why SafeNight Exists</h2>
            <p>Many safety tools are reactive, fragmented, or too slow to reach when they are actually needed. SafeNight is designed as a compact project solution where planning a trip, storing emergency contacts, checking risky areas, and triggering SOS actions sit inside a single workflow.</p>
        </div>
        <div class="commitment-card">
            <h2>What The Experience Prioritizes</h2>
            <p>Clear navigation, fast access to the most important actions, and visible context around every journey. The project focuses on practical reassurance: knowing your route, having contacts ready, and being able to respond quickly if something feels wrong.</p>
        </div>
    </section>

    <div class="section-head">
        <h2>Key Features</h2>
    </div>
    <section class="features-grid">
        <article class="feature-card">
            <div class="feature-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
            </div>
            <h3>Route Planning</h3>
            <p>Plan journeys between two points and log those trips for later review inside the project.</p>
        </article>
        <article class="feature-card">
            <div class="feature-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3498db" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-6-4.35-6-10a6 6 0 0 1 12 0c0 5.65-6 10-6 10z"/><circle cx="12" cy="11" r="2.5"/></svg>
            </div>
            <h3>Location Awareness</h3>
            <p>View a map simulation with visible unsafe areas and route visuals for project demonstration purposes.</p>
        </article>
        <article class="feature-card">
            <div class="feature-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2ecc71" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <h3>Trusted Contacts</h3>
            <p>Store emergency contacts so the user has important people ready when support is needed.</p>
        </article>
        <article class="feature-card">
            <div class="feature-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f1c40f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <h3>SOS Access</h3>
            <p>Keep an emergency alert option visible and immediate so the response path stays simple under pressure.</p>
        </article>
    </section>

    <section class="cta-card">
        <div class="cta-copy">
            <h2>Explore the project flow.</h2>
            <p>Start with the safe map, log a trip, review it in My Trips, and use emergency contacts and SOS features to see the full SafeNight experience.</p>
        </div>
        <div class="cta-actions">
            <a href="map.php" class="btn-primary">Open Safe Map</a>
            <a href="trips.php" class="btn-secondary">View My Trips</a>
        </div>
    </section>
</div>

</body>
</html>
