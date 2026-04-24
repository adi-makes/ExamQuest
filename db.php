<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'examquest_db');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die('
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Connection Error – ExamQuest</title>
        <style>
            body { font-family: system-ui, sans-serif; display: flex; align-items: center;
                   justify-content: center; min-height: 100vh; margin: 0; background: #faf8ff; }
            .box { background: #fff; border: 1px solid #fecaca; border-radius: 10px;
                   padding: 40px; max-width: 480px; text-align: center; }
            h2 { color: #dc2626; margin: 0 0 12px; }
            p  { color: #374151; line-height: 1.6; margin: 0 0 8px; }
            code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 13px; }
        </style>
    </head>
    <body>
        <div class="box">
            <h2>Database Connection Failed</h2>
            <p>' . mysqli_connect_error() . '</p>
            <p>Please ensure XAMPP MySQL is running and the database
            <code>examquest_db</code> exists.</p>
            <p>Run <code>database.sql</code> in phpMyAdmin to set it up.</p>
        </div>
    </body>
    </html>
    ');
}

mysqli_set_charset($conn, 'utf8mb4');
