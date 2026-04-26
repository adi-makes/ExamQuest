<?php
/**
 * db.php — Database Connection
 *
 * Included by every page via require_once 'db.php'.
 * Opens one shared MySQLi connection and stores it in $conn.
 * All DB credentials are defined as constants so they only
 * need to be changed in one place when deploying to a new server.
 *
 * Local (XAMPP)    : host=localhost, user=root, pass='', db=examquest_db
 * Production       : update constants below to match your hosting panel
 */

// ── Connection constants ──────────────────────────────────────────────────────

define('DB_HOST', 'localhost');    // MySQL server (usually 'localhost')
define('DB_USER', 'root');         // MySQL username
define('DB_PASS', '');             // Password — blank by default on XAMPP
define('DB_NAME', 'examquest_db'); // The database created for this project

// ── Open the connection ───────────────────────────────────────────────────────
// mysqli_connect() returns a connection resource on success, FALSE on failure.
// We use the MySQLi procedural style here for consistency with legacy PHP.

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// ── Connection failure — show a clean error page and stop ─────────────────────
// If MySQL isn't running or credentials are wrong, PHP would otherwise show
// cryptic warnings on every page. Instead, we catch the failure here once and
// display a helpful message, then call die() to halt all further execution.

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

// ── Force UTF-8 character encoding ────────────────────────────────────────────
// utf8mb4 is the full 4-byte Unicode variant that supports emojis and special
// characters. Without this, reading/writing non-ASCII text can produce garbled
// output or silent data corruption in MySQL's older 'utf8' (3-byte) mode.

mysqli_set_charset($conn, 'utf8mb4');
