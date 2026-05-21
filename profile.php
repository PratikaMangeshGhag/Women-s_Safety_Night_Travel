<?php
session_start();
require_once __DIR__ . '/includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$logged_in = true;
$activeNav = '';
$showProfileLinkInNav = true;
$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if ($name === '' || strlen($name) < 3) {
        $error = "Please enter a valid full name.";
    } elseif ($phone !== '' && !preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Phone number must be exactly 10 digits.";
    } else {
        $sql = "UPDATE users SET name='$name', phone='$phone' WHERE user_id='$user_id'";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['user_name'] = $name;
            $success = "Profile updated successfully!";
        } else {
            $error = "Failed to update profile.";
        }
    }
}

if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $result = mysqli_query($conn, "SELECT password_hash FROM users WHERE user_id='$user_id'");
    $user = mysqli_fetch_assoc($result);
    if (strlen($new) < 6) {
        $error = "New password must be at least 6 characters long.";
    } elseif ($new !== $confirm) {
        $error = "New password and confirm password do not match.";
    } elseif (password_verify($current, $user['password_hash'])) {
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password_hash='$new_hash' WHERE user_id='$user_id'");
        $success = "Password changed successfully!";
    } else {
        $error = "Current password is incorrect!";
    }
}

$result = mysqli_query($conn, "SELECT * FROM users WHERE user_id='$user_id'");
$user = mysqli_fetch_assoc($result);

$trips_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM trips WHERE user_id='$user_id'");
$trips_count = mysqli_fetch_assoc($trips_result)['total'];

$contacts_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM emergency_contacts WHERE user_id='$user_id'");
$contacts_count = mysqli_fetch_assoc($contacts_result)['total'];

$sos_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM sos_alerts WHERE user_id='$user_id'");
$sos_count = mysqli_fetch_assoc($sos_result)['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeNight - Profile</title>
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

        .logo { font-size: 24px; font-weight: 700; color: #fff; text-decoration: none; }
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
        }

        .nav-links a:hover,
        .nav-links a.active { color: #e74c3c; }

        .nav-buttons { display: flex; align-items: center; gap: 12px; }

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

        .profile-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
        }

        .profile-link:hover { color: #e74c3c; }

        .profile-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(231,76,60,0.2);
            border: 1.5px solid rgba(231,76,60,0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e74c3c;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .page-wrapper {
            position: relative;
            z-index: 1;
            padding: 110px 60px 60px;
            max-width: 900px;
            margin: 0 auto;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 40px;
        }

        .profile-big-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(231,76,60,0.2);
            border: 2px solid rgba(231,76,60,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 700;
            color: #e74c3c;
            flex-shrink: 0;
        }

        .profile-header-info h2 {
            font-size: 28px;
            font-weight: 700;
            color: #fff;
        }

        .profile-header-info p {
            font-size: 14px;
            color: rgba(255,255,255,0.4);
            margin-top: 4px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(20,20,40,0.95), rgba(40,10,10,0.9));
            border: 1px solid rgba(231,76,60,0.2);
            border-radius: 14px;
            padding: 20px;
            text-align: center;
        }

        .stat-card h3 {
            font-size: 32px;
            font-weight: 700;
            color: #e74c3c;
            margin-bottom: 6px;
        }

        .stat-card p {
            font-size: 12px;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .card {
            background: linear-gradient(135deg, rgba(20,20,40,0.95), rgba(40,10,10,0.9));
            border: 1px solid rgba(231,76,60,0.2);
            border-radius: 18px;
            padding: 28px;
        }

        .card h3 {
            font-size: 15px;
            font-weight: 600;
            color: rgba(255,255,255,0.6);
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group { margin-bottom: 16px; }

        label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: rgba(255,255,255,0.4);
            margin-bottom: 7px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            font-size: 13px;
            color: #fff;
            outline: none;
            transition: all 0.3s;
        }

        input::placeholder { color: rgba(255,255,255,0.2); }
        input:focus { border-color: #e74c3c; background: rgba(255,255,255,0.09); }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 6px;
        }

        .submit-btn:hover {
            background: #c0392b;
            transform: translateY(-1px);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .alert.success {
            background: rgba(46,204,113,0.12);
            border: 1px solid rgba(46,204,113,0.35);
            color: #2ecc71;
        }

        .alert.error {
            background: rgba(231,76,60,0.12);
            border: 1px solid rgba(231,76,60,0.35);
            color: #e74c3c;
        }

        .danger-zone {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, rgba(20,20,40,0.95), rgba(40,10,10,0.9));
            border: 1px solid rgba(231,76,60,0.3);
            border-radius: 18px;
            padding: 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .danger-zone h3 {
            font-size: 15px;
            font-weight: 600;
            color: #fff;
        }

        .danger-zone p {
            font-size: 13px;
            color: rgba(255,255,255,0.4);
            margin-top: 4px;
        }

        .logout-btn {
            padding: 10px 28px;
            background: transparent;
            border: 1.5px solid #e74c3c;
            border-radius: 20px;
            color: #e74c3c;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .logout-btn:hover { background: #e74c3c; color: #fff; }
    </style>
</head>
<body>

<?php include 'includes/navigation.php'; ?>

<div class="page-wrapper">

    <?php if($success != ""): ?>
        <div class="alert success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if($error != ""): ?>
        <div class="alert error"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- PROFILE HEADER -->
    <div class="profile-header">
        <div class="profile-big-avatar">
            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
        </div>
        <div class="profile-header-info">
            <h2><?php echo $user['name']; ?></h2>
            <p><?php echo $user['email']; ?> · Member since <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
        </div>
    </div>

    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?php echo $trips_count; ?></h3>
            <p>Total Trips</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $contacts_count; ?></h3>
            <p>Emergency Contacts</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $sos_count; ?></h3>
            <p>SOS Alerts</p>
        </div>
    </div>

    <div class="grid">

        <!-- EDIT PROFILE -->
        <div class="card">
            <h3>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit Profile
            </h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo $user['name']; ?>" minlength="3" maxlength="60" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" value="<?php echo $user['phone']; ?>" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" title="Enter a 10-digit phone number.">
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" value="<?php echo $user['email']; ?>" disabled style="opacity:0.4; cursor:not-allowed;">
                </div>
                <button type="submit" name="update_profile" class="submit-btn">Save Changes</button>
            </form>
        </div>

        <!-- CHANGE PASSWORD -->
        <div class="card">
            <h3>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Change Password
            </h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" placeholder="Enter current password" minlength="6" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" placeholder="Enter new password" minlength="6" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" placeholder="Confirm new password" minlength="6" required>
                </div>
                <button type="submit" name="change_password" class="submit-btn">Update Password</button>
            </form>
        </div>

        <!-- DANGER ZONE -->
        <div class="danger-zone">
            <div>
                <h3>Sign out of SafeNight</h3>
                <p>You will be redirected to the homepage after logging out.</p>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

    </div>
</div>

</body>
</html>
