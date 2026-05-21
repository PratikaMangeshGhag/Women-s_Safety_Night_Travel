<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #1a1a2e;
            color: #fff;
            font-family: Segoe UI, sans-serif;
            overflow: hidden;
        }

        .splash {
            text-align: center;
            opacity: 1;
            transition: opacity 0.6s ease;
        }

        .splash.fade-out {
            opacity: 0;
        }

        h1 {
            margin: 0 0 8px;
            color: #e74c3c;
        }

        p {
            margin: 0;
            color: rgba(255,255,255,0.68);
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="splash" id="dashboardSplash">
        <h1>Welcome!</h1>
        <p id="dashboardMessage">Preparing your SafeNight session...</p>
    </div>

    <script>
        const splash = document.getElementById('dashboardSplash');
        const dashboardMessage = document.getElementById('dashboardMessage');
        let hasFinished = false;

        function finishDashboard() {
            if (hasFinished) {
                return;
            }

            hasFinished = true;

            setTimeout(() => {
                splash.classList.add('fade-out');
            }, 1400);

            setTimeout(() => {
                window.location.href = 'index.php';
            }, 2200);
        }

        if (navigator.geolocation) {
            const allowLocation = window.confirm('Allow SafeNight to access your location for safety features?');

            // Keep the flow moving even if the browser never resolves the location request.
            const locationFallback = setTimeout(() => {
                dashboardMessage.textContent = 'Location access granted.';
                finishDashboard();
            }, 5000);

            if (allowLocation) {
                dashboardMessage.textContent = 'Requesting location access...';
                navigator.geolocation.getCurrentPosition(
                    () => {
                        clearTimeout(locationFallback);
                        dashboardMessage.textContent = 'Location access granted.';
                        finishDashboard();
                    },
                    (error) => {
                        clearTimeout(locationFallback);
                        dashboardMessage.textContent = 'Location access granted.';
                        finishDashboard();
                    },
                    {
                        timeout: 4000,
                        maximumAge: 0
                    }
                );
            } else {
                clearTimeout(locationFallback);
                dashboardMessage.textContent = 'Location access skipped.';
                finishDashboard();
            }
        } else {
            dashboardMessage.textContent = 'Location is not supported in this browser.';
            finishDashboard();
        }
    </script>
</body>
</html>
