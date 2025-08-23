<?php
$host = 'dpg-d2j08i0gjchc73bv0img-a.oregon-postgres.render.com';
$db = 'udetails';
$user = 'helkay';
$pass = 'qqjbzqfcPN3UbWsgQC4qOlglxifWfLTj';
$charset = 'utf8mb4'; 

$users = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // fetch associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                   // use native prepared statements
];

try {
    $userdata = new PDO($users, $user, $pass, $options);
    echo "Connected successfully!";
} catch (\PDOException $e) {
    echo "Connection failed: " . $e->getMessage()." for database: ".$db;
}
