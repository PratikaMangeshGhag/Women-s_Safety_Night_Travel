<?php
session_start();
require_once __DIR__ . '/includes/db.php';
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($password === '') {
        $error = "Password is required.";
    } else {
        $sql = "SELECT * FROM users WHERE email='$email'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Incorrect password!";
            }
        } else {
            $error = "No account found with that email!";
        }
    }
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeNight - Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            background: url('bg.png') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }

        .container {
            position: relative;
            z-index: 1;
            background: linear-gradient(135deg, rgba(20,20,40,0.95), rgba(40,10,10,0.9));
            border: 1px solid rgba(231,76,60,0.3);
            border-radius: 20px;
            padding: 48px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }

        .logo {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo h1 {
            font-size: 32px;
            font-weight: 700;
            color: #fff;
        }

        .logo h1 span { color: #e74c3c; }

        .logo p {
            font-size: 13px;
            color: rgba(255,255,255,0.5);
            margin-top: 6px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: rgba(255,255,255,0.6);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 10px;
            font-size: 14px;
            color: #fff;
            transition: all 0.3s;
            outline: none;
        }

        input::placeholder { color: rgba(255,255,255,0.3); }

        input:focus {
            border-color: #e74c3c;
            background: rgba(255,255,255,0.1);
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: all 0.3s;
            letter-spacing: 0.5px;
        }

        .btn:hover {
            background: #c0392b;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(231,76,60,0.4);
        }

        .register-link {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: rgba(255,255,255,0.5);
        }

        .register-link a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover { text-decoration: underline; }

        .error {
            background: rgba(231,76,60,0.15);
            border: 1px solid rgba(231,76,60,0.4);
            color: #e74c3c;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
            text-align: center;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 24px 0;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,0.1);
        }

        .divider span {
            font-size: 12px;
            color: rgba(255,255,255,0.3);
        }

        .back-home {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            color: rgba(255,255,255,0.4);
            text-decoration: none;
            font-size: 13px;
            margin-top: 20px;
            transition: color 0.3s;
        }

        .back-home:hover { color: rgba(255,255,255,0.8); }
    </style>
</head>
<body>

<div class="container">
    <div class="logo">
        <h1>Safe<span>Night</span></h1>
        <p>Women's Safety Night Travel System</p>
    </div>

    <?php if($error != ""): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="Enter your email" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password" minlength="6" required>
        </div>
        <button type="submit" class="btn">Login to SafeNight</button>
    </form>

    <div class="divider"><span>New here?</span></div>

    <div class="register-link">
        Don't have an account? <a href="register.php">Create one now</a>
    </div>

    <a href="index.php" class="back-home">
        &#8592; Back to Home
    </a>
</div>

</body>
</html>
