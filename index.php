<?php session_start(); $logged_in = isset($_SESSION['user_name']); $activeNav = 'home'; $showProfileLinkInNav = true; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeNight - Women's Safety Night Travel System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            background: url('images/home/bg.png') no-repeat center center fixed;
            background-size: cover;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.15);
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
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            letter-spacing: 1px;
            text-decoration: none;
        }

        .logo span { color: #e74c3c; }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 24px;
        }

        .nav-links a {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
            white-space: nowrap;
        }

        .nav-links a:hover { color: #e74c3c; }

        .nav-buttons {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-outline {
            padding: 8px 20px;
            border: 1.5px solid #fff;
            border-radius: 20px;
            color: #fff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
            white-space: nowrap;
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
            font-weight: 500;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .btn-solid:hover { background: #c0392b; border-color: #c0392b; }

        .profile-link {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: rgba(255,255,255,0.85);
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
            white-space: nowrap;
        }

        .profile-link:hover { color: #e74c3c; }

        .profile-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(231,76,60,0.2);
            border: 1.5px solid rgba(231,76,60,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            color: #e74c3c;
            flex-shrink: 0;
        }

        .hero {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 100px 20px 40px;
        }

        .hero h1 {
            font-size: 52px;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
            margin-bottom: 16px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }

        .hero h1 span { color: #e74c3c; }

        .hero p {
            font-size: 18px;
            color: rgba(255,255,255,0.8);
            margin-bottom: 36px;
            max-width: 500px;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            margin-bottom: 60px;
        }

        .hero-btn-main {
            padding: 14px 36px;
            background: #e74c3c;
            color: #fff;
            border-radius: 30px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .hero-btn-main:hover { background: #c0392b; transform: translateY(-2px); }

        .hero-btn-sec {
            padding: 14px 36px;
            border: 2px solid #fff;
            color: #fff;
            border-radius: 30px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .hero-btn-sec:hover { background: rgba(255,255,255,0.15); transform: translateY(-2px); }

        .carousel-wrapper {
            width: 100%;
            max-width: 750px;
            position: relative;
        }

        .carousel { overflow: hidden; border-radius: 16px; }

        .carousel-track {
            display: flex;
            transition: transform 0.5s ease;
        }

        .slide {
            min-width: 100%;
            background: linear-gradient(135deg, rgba(20,20,40,0.85), rgba(40,10,10,0.75));
            backdrop-filter: blur(16px);
            border: 1px solid rgba(231,76,60,0.3);
            border-radius: 16px;
            padding: 36px 40px;
            display: flex;
            align-items: center;
            gap: 24px;
            box-shadow: 0 8px 32px rgba(231,76,60,0.15);
        }

        .slide-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .slide-icon.red { background: rgba(231,76,60,0.25); border: 1.5px solid rgba(231,76,60,0.5); }
        .slide-icon.blue { background: rgba(52,152,219,0.25); border: 1.5px solid rgba(52,152,219,0.5); }
        .slide-icon.green { background: rgba(46,204,113,0.25); border: 1.5px solid rgba(46,204,113,0.5); }

        .slide-content h3 {
            font-size: 20px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 8px;
        }

        .slide-content p {
            font-size: 14px;
            color: rgba(255,255,255,0.75);
            line-height: 1.6;
            margin: 0;
        }

        .carousel-dots {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 16px;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,0.4);
            cursor: pointer;
            transition: all 0.3s;
        }

        .dot.active { background: #e74c3c; width: 24px; border-radius: 4px; }

        .carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            color: #fff;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            z-index: 10;
            transition: background 0.3s;
        }

        .carousel-btn:hover { background: rgba(231,76,60,0.5); }
        .carousel-btn.prev { left: -19px; }
        .carousel-btn.next { right: -19px; }
    </style>
</head>
<body>

<?php include 'includes/navigation.php'; ?>

<section class="hero">
    <h1>Travel Safe,<br>Travel <span>Smart</span></h1>
    <p>Your trusted companion for safe night travel.</p>
    <div class="hero-buttons">
        <a href="contacts.php" class="hero-btn-main">Get Started</a>
        <a href="#features" class="hero-btn-sec">Learn More</a>
    </div>

    <div class="carousel-wrapper">
        <button class="carousel-btn prev" onclick="moveSlide(-1)">&#8592;</button>
        <div class="carousel">
            <div class="carousel-track" id="track">

                <div class="slide">
                    <div class="slide-icon red">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div class="slide-content">
                        <h3>Live Location Sharing</h3>
                        <p>Share your real-time location with trusted contacts while travelling at night so they always know you're safe.</p>
                    </div>
                </div>

                <div class="slide" onclick="window.location.href='sos.php'" style="cursor:pointer;">
                    <div class="slide-icon red">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </div>
                    <div class="slide-content">
                        <h3>SOS Emergency Alert</h3>
                        <p>One tap sends an instant emergency alert with your exact location to all your emergency contacts simultaneously.</p>
                    </div>
                </div>

                <div class="slide" onclick="window.location.href='map.php'" style="cursor:pointer;">
                    <div class="slide-icon blue">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#3498db" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                    </div>
                    <div class="slide-content">
                        <h3>Safe Route Finder</h3>
                        <p>Find the safest routes to your destination based on community reports and well-lit, verified safe paths.</p>
                    </div>
                </div>

                <div class="slide" onclick="window.location.href='map.php'" style="cursor:pointer;">
                    <div class="slide-icon green">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#2ecc71" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div class="slide-content">
                        <h3>Report Unsafe Spots</h3>
                        <p>Help your community by marking and describing dangerous areas so other women can stay informed and safe.</p>
                    </div>
                </div>

            </div>
        </div>
        <button class="carousel-btn next" onclick="moveSlide(1)">&#8594;</button>

        <div class="carousel-dots">
            <div class="dot active" onclick="goToSlide(0)"></div>
            <div class="dot" onclick="goToSlide(1)"></div>
            <div class="dot" onclick="goToSlide(2)"></div>
            <div class="dot" onclick="goToSlide(3)"></div>
        </div>
    </div>
</section>

<script>
    let current = 0;
    const total = 4;
    const track = document.getElementById('track');
    const dots = document.querySelectorAll('.dot');

    function updateCarousel() {
        track.style.transform = `translateX(-${current * 100}%)`;
        dots.forEach((d, i) => d.classList.toggle('active', i === current));
    }

    function moveSlide(dir) {
        current = (current + dir + total) % total;
        updateCarousel();
    }

    function goToSlide(index) {
        current = index;
        updateCarousel();
    }

    setInterval(() => moveSlide(1), 4000);
</script>

</body>
</html>
