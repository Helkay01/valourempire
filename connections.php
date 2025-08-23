<?php
$host = getenv('dpg-d2j08i0gjchc73bv0img-a.oregon-postgres.render.com');
$db = getenv('udetails');
$user = getenv('helkay');
$password = getenv('qqjbzqfcPN3UbWsgQC4qOlglxifWfLTj');

$dsn = "pgsql:host=$host;port=5432;dbname=$db;";
try {
    $conn = new PDO($dsn, $user, $password);
    echo "Connected to PostgreSQL successfully!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
