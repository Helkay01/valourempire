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


if(isset($_GET['id'])) {
    $id = $_GET['id'];
  
    $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ?");
    $stmtexec = $stmt->execute([$id]);
   

    if(isset($_GET['pm']) && isset($_GET['amount'])) {
        $pm = $_GET['pm'];
        $amt = str_replace(",", $_GET['amount']);
        $amount = (float)$amt;
        
        if($pm == "cash" || $pm == "Cash") {
             $selCashBal = $pdo->prepare("SELECT * FROM cash_bal");
             $selCashBal->execute();
             $assoc = $selCashBal->fetch(PDO::FETCH_ASSOC);
             $cash_bal = (float)$assoc['balance'] ?? 0;

             $new_bal = $cash_bal + $amount;
                       
              $updCashBal = $pdo->prepare("UPDATE cash_bal SET balance = :bal");
              $updCashBal->bindParam(':bal', $new_bal);
              $updCashBalExec = $updCashBal->execute();


            
            if($stmtexec && $updCashBalExec) {
                 $success = '<div class="mb-4 px-4 py-3 rounded text-green-700 bg-green-100">Expenses deleted!</div>';
            }
            
        }
    }
    
  
}




?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>DELETE EXPENES</title>
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
