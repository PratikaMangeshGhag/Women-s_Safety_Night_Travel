<?php
session_start();
require_once __DIR__ . '/includes/db.php';
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $plain_password = $_POST['password'] ?? '';

    if ($name === '' || strlen($name) < 3) {
        $message = "Please enter a valid full name.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $message = "Phone number must be exactly 10 digits.";
    } elseif (strlen($plain_password) < 6) {
        $message = "Password must be at least 6 characters long.";
    } else {
        $check = mysqli_query($conn, "SELECT user_id FROM users WHERE email='$email'");
        if ($check && mysqli_num_rows($check) > 0) {
            $message = "An account with this email already exists.";
        } else {
            $password = password_hash($plain_password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password_hash, phone) VALUES ('$name', '$email', '$password', '$phone')";
            if (mysqli_query($conn, $sql)) {
                $_SESSION['user_id'] = mysqli_insert_id($conn);
                $_SESSION['user_name'] = $name;
                mysqli_close($conn);
                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Something went wrong. Please try again.";
            }
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
    <title>SafeNight - Register</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #1a1a2e;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            font-size: 28px;
            color: #c0392b;
            font-weight: 700;
        }
        .logo p {
            font-size: 13px;
            color: #888;
            margin-top: 4px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #444;
            margin-bottom: 6px;
        }
        input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            color: #333;
            transition: border 0.3s;
            outline: none;
        }
        input:focus {
            border-color: #c0392b;
        }
        .btn {
            width: 100%;
            padding: 14px;
            background: #c0392b;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.3s;
        }
        .btn:hover { background: #a93226; }
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #888;
        }
        .login-link a {
            color: #c0392b;
            text-decoration: none;
            font-weight: 600;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 13px;
            text-align: center;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 13px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="logo">
        <h1>SafeNight</h1>
        <p>Women's Safety Night Travel System</p>
    </div>

    <?php if($message == "success"): ?>
        <div class="success">Registration successful! <a href="login.php">Login here</a></div>
    <?php elseif($message != ""): ?>
        <div class="error"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" placeholder="Enter your full name" minlength="3" maxlength="60" pattern="[A-Za-z ]{3,60}" title="Use 3 to 60 letters only." required>
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="Enter your email" required>
        </div>
        <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" name="phone" placeholder="Enter your phone number" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" title="Enter a 10-digit phone number." required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Create a password" minlength="6" required>
        </div>
        <button type="submit" class="btn">Create Account</button>
    </form>

    <div class="login-link">
        Already have an account? <a href="login.php">Login</a>
    </div>
</div>
</body>
</html>
