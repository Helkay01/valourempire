<?php
session_start();
require 'connections.php'; // Assumes $pdo is a PDO instance

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['user_id'];
$success = "";

if (isset($_GET['type'], $_GET['invoice_id'])) {
    $type = $_GET['type'];
    $id = $_GET['invoice_id'];

    if ($type === "delivered") {
        $status = "delivered";
    } elseif ($type === "undelivered") {
        $status = "undelivered";
    } else {
        $status = null;
    }

    if ($status !== null) {
        $del = $pdo->prepare("UPDATE invoices SET job_status = :status WHERE invoice_id = :ivid");
        $del->bindParam(':ivid', $id);
        $del->bindParam(':status', $status);

        if ($del->execute()) {
            $success = '<div class="mb-4 px-4 py-3 rounded text-green-700 bg-green-100">Marked as ' . htmlspecialchars($status) . '</div>';
        } else {
            $success = '<div class="mb-4 px-4 py-3 rounded text-red-700 bg-red-100">Failed to update status.</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>All outstanding invoices</title>
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
