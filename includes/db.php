<?php
require_once __DIR__ . '/config.php';

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if (!$conn) {
    die('
        <div style="font-family:sans-serif;padding:2rem;background:#0e1b22;color:#fff;min-height:100vh;">
            <h2 style="color:#c95a5a;">Database Connection Failed</h2>
            <p>Could not connect to MySQL. Make sure:</p>
            <ul>
                <li>XAMPP MySQL is running</li>
                <li>The <code>var_cars</code> database exists (run <code>database/setup.sql</code> in phpMyAdmin)</li>
                <li>Credentials in <code>includes/config.php</code> are correct</li>
            </ul>
            <p style="color:#888;">Error: ' . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8') . '</p>
        </div>
    ');
}

mysqli_set_charset($conn, 'utf8mb4');
