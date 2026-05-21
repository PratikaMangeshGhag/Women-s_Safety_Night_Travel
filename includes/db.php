<?php

if (!function_exists('load_env_file')) {
    function load_env_file($file_path)
    {
        static $loaded = [];

        if (isset($loaded[$file_path]) || !is_file($file_path)) {
            return;
        }

        $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $key = trim($parts[0]);
            $value = trim($parts[1]);

            if ($key === '') {
                continue;
            }

            if (
                (strlen($value) >= 2) &&
                (($value[0] === '"' && substr($value, -1) === '"') ||
                 ($value[0] === "'" && substr($value, -1) === "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv($key . '=' . $value);
        }

        $loaded[$file_path] = true;
    }
}

if (!function_exists('env_value')) {
    function env_value($key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        return $value === false || $value === null ? $default : $value;
    }
}

load_env_file(__DIR__ . '/../.env');

$db_host = env_value('DB_HOST');
$db_user = env_value('DB_USER');
$db_pass = env_value('DB_PASS', '');
$db_name = env_value('DB_NAME');

if ($db_host === null || $db_user === null || $db_name === null) {
    die('Database configuration is missing. Copy .env.example to .env and fill in your credentials.');
}

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

