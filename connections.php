<?php
$dbHost = getenv('dpg-d2j08i0gjchc73bv0img-a.oregon-postgres.render.com');
$dbHost = getenv('udetails');
$dbUser = getenv('helkay');
$dbPass = getenv('qqjbzqfcPN3UbWsgQC4qOlglxifWfLTj');
$charset = 'utf8mb4';
$dbPort = getenv('DB_PORT') ?: '5432';

echo "DB_HOST: " . ($dbHost ? $dbHost : 'NOT SET') . "<br>";
echo "DB_DATABASE: " . ($dbName ? $dbName : 'NOT SET') . "<br>";
echo "DB_USERNAME: " . ($dbUser ? $dbUser : 'NOT SET') . "<br>";
echo "DB_PORT: " . $dbPort . "<br>";
echo "DB_PASSWORD: " . ($dbPass ? 'SET' : 'NOT SET') . "<br>";

// Check if PostgreSQL extension is loaded
if (!extension_loaded('pdo_pgsql')) {
    echo "ERROR: pdo_pgsql extension not loaded!<br>";
    echo "Loaded extensions: " . implode(', ', get_loaded_extensions());
} else {
    echo "pdo_pgsql extension: LOADED<br>";
}

// Try to connect
try {
    $dsn = "pgsql:host=$dbHost;port=$dbPort;dbname=$dbName";
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connection successful!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "<br>";
    echo "DSN used: " . $dsn;
}
?>
