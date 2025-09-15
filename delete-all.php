<?php
session_start();
require 'connections.php'; // Assumes $conn is a PDO instance

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
}


$user_id = $_SESSION['user']['user_id'];


$chk = $pdo->prepare("SELECT * FROM login WHERE user_id = :id");
$chk->bindParam(':id', $user_id);
$chk->execute();
$assoc = $chk->fetch(PDO::FETCH_ASSOC);
$em = $assoc['email'];
$addr = $assoc['biz_address'];
$biz_name = $assoc['biz_name'];

$success = "";

$arr = ["bank", "bank_bal", "cash", "cash_bal", "expenses", "invoice_items", "invoices", "main_bank", "receipts"];
$allSuccessful = true;

foreach($arr as $key) {
    // Properly prepare the DELETE query
    $stmt = $pdo->prepare("DELETE FROM `$key`"); // backticks to prevent SQL issues with table names
    $stexec = $stmt->execute();

    if (!$stexec) {
        $allSuccessful = false;
        break; // optional: stop on first failure
    }
}

// Show success message once, if all succeeded
if ($allSuccessful) {
    $success = '<div class="mb-4 px-4 py-3 rounded text-green-700 bg-green-100">All data deleted!</div>';
} else {
    $success = '<div class="mb-4 px-4 py-3 rounded text-red-700 bg-red-100">An error occurred while deleting data.</div>';
}





?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>DELETE ALL</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6 min-h-screen">

<div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">

    <a href="/" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6">
        &lt; Back to Dashboard
    </a>

    <br><br>

    <?= $success ?>

</div>

</body>
</html>
