<?php
session_start();
require 'connections.php';

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
   // die("Unauthorized access.");
    header("Location: login.php");
}

$user_id = $_SESSION['user']['user_id'];
$saved = "";
$bank_error = "";
$bank_bal = "";

if (isset($_POST['record'])) {
  
    $bank_account = $_POST['bankAccount'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $date = $_POST['date'] ?? '';
    $note = $_POST['note'] ?? '';

    // Basic validation
    if (empty($bank_account) || empty($amount) || empty($date)) {
        die('Please fill all required fields.');
    }

    try {

       /// UPDATE BANK BALANCE
       $selBankBal = $pdo->query("SELECT * FROM bank_bal LIMIT 1");

      if ($selBankBal) {
          $bnk_assoc = $selBankBal->fetch(PDO::FETCH_ASSOC);
      
          if ($bnk_assoc && isset($bnk_assoc['balance'])) {
              $bank_bal = (float)$bnk_assoc['balance'];
              $new_bnk_bal = $bank_bal - $amount;
          } else {
              $bank_bal = 0.0; // or handle as needed if no balance found
          }
      } else {
          // Query failed — handle the error
          $bank_bal = 0.0;
          error_log("Failed to fetch bank balance from DB.");
      }

                                     
        
                          



       /// PERSONAL ACCOUNT
       if($bank_account === "pa" || $bank_account === "cih") {
          $Ttype = ($bank_account === "pa") ? "From Personal Account" : "Cash balance";

                // Prepare and execute query
                 $stmt = $pdo->prepare("
                     INSERT INTO cash (from_bk, amount, note, date, type)
                     VALUES (:bank_account, :amount, :note, :date, :type)
                 ");
         
                 $stmt->execute([
                     ':bank_account' => $bank_account,
                     ':amount' => $amount,
                     ':note' => $note,
                     ':date' => $date, 
                     ':type' => $Ttype
                 ]);
         
         
         
                $selCashBal = $pdo->prepare("SELECT * FROM cash_bal");
                $selCashBal->execute();
               
                if($selCashBal->rowCount() > 0) {
                    $assoc = $selCashBal->fetch(PDO::FETCH_ASSOC);
                    $cash_bal = (float)$assoc['balance'];
         
                    $new_bal = $cash_bal + $amount;
                    // Prepare and execute query
                    $save = $pdo->prepare("UPDATE cash_bal SET balance = :bal");
                    $save->execute([
                     ':bal' => $new_bal         
                    ]);
         
                }
                else {
                   // Prepare and execute query
                    $save = $pdo->prepare("INSERT INTO cash_bal (balance) VALUES (:bal)");
                    $save->execute([
                     ':bal' => $amount       
                    ]);
                }
                
                                
                 // Redirect or show success
                   $saved = ' <div class="mb-4 px-4 py-3 rounded text-green-700 bg-green-100">Cash saved succesfully</div>';  
            
          }



       //// CASH ACCOUNT
       
       if($bank_account === "pa" || $bank_account === "cih") {
          $Ttype = ($bank_account === "pa") ? "From Personal Account" : "Cash balance";

                // Prepare and execute query
                 $stmt = $pdo->prepare("
                     INSERT INTO cash (from_bk, amount, note, date, type)
                     VALUES (:bank_account, :amount, :note, :date, :type)
                 ");
         
                 $stmt->execute([
                     ':bank_account' => $bank_account,
                     ':amount' => $amount,
                     ':note' => $note,
                     ':date' => $date, 
                     ':type' => $Ttype
                 ]);
         
         
         
                $selCashBal = $pdo->prepare("SELECT * FROM cash_bal");
                $selCashBal->execute();
               
                if($selCashBal->rowCount() > 0) {
                    $assoc = $selCashBal->fetch(PDO::FETCH_ASSOC);
                    $cash_bal = (float)$assoc['balance'];
         
                    $new_bal = $cash_bal + $amount;
                    // Prepare and execute query
                    $save = $pdo->prepare("UPDATE cash_bal SET balance = :bal");
                    $save->execute([
                     ':bal' => $new_bal         
                    ]);
         
                }
                else {
                   // Prepare and execute query
                    $save = $pdo->prepare("INSERT INTO cash_bal (balance) VALUES (:bal)");
                    $save->execute([
                     ':bal' => $amount       
                    ]);
                }
                
                                
                 // Redirect or show success
                   $saved = ' <div class="mb-4 px-4 py-3 rounded text-green-700 bg-green-100">Cash saved succesfully</div>';  
            
          }



       
       

               /// IF TRANSFER FROM BIZ ACCT - START
               if($bank_account === "Bank") {
                    /// INSERT INTO BANK

                    $bnkTy = "Contra (Transfer to Cash Account)";
                    $stmt = $pdo->prepare("INSERT INTO main_bank (amount, note, date, type) VALUES (:amount, :note, :date, :type)");                
                    $stmt->bindParam(':amount', $amount);
                    $stmt->bindParam(':note', $note);
                    $stmt->bindParam(':date', $date);
                    $stmt->bindParam(':type', $bnkTy);
                    $stmt->execute();


                  
                  if($bank_bal >= $amount) {
                      
                        $updBankBal = $pdo->prepare("UPDATE bank_bal SET balance = :bal");
                        $updBankBal->bindParam(':bal', $new_bnk_bal);
                        $updBankBal->execute();

                     
                           /// UPDATE CASH
                          $stmt = $pdo->prepare("
                              INSERT INTO cash (from_bk, amount, note, date)
                              VALUES (:bank_account, :amount, :note, :date)
                          ");
                  
                          $stmt->execute([
                              ':bank_account' => $bank_account,
                              ':amount' => $amount,
                              ':note' => $note,
                              ':date' => $date,            
                          ]);
                  
                  
                  
                         $selCashBal = $pdo->prepare("SELECT * FROM cash_bal");
                         $selCashBal->execute();
                        
                         if($selCashBal->rowCount() === 1) {
                             $assoc = $selCashBal->fetch(PDO::FETCH_ASSOC);
                             $cash_bal = (float)$assoc['balance'];
                  
                             $new_bal = $cash_bal + $amount;
                             // Prepare and execute query
                             $save = $pdo->prepare("UPDATE cash_bal SET balance = :bal");
                             $save->execute([
                              ':bal' => $new_bal         
                             ]);
                  
                         }
                         else {
                            // Prepare and execute query
                             $save = $pdo->prepare("INSERT INTO cash_bal (balance) VALUES (:bal)");
                             $save->execute([
                              ':bal' => $amount       
                             ]);
                         }
                         
                             $saved = ' <div class="mb-4 px-4 py-3 rounded text-green-700 bg-green-100">Cash saved succesfully</div>';  
                  }  
                  else if($bank_bal < $amount) {
                     $bank_error = '<div class="mt-4 bg-red-100 text-red-700 p-3 rounded">Insufficient balance in bank account. <a style="color: blue;" href="main-bank.php">Update bank account first.</a></div>';
                  }
               }
               /// IF TRANSFER FROM BIZ ACCT - END
                
         
      } catch (PDOException $e) {
         echo "❌ Database error: " . $e->getMessage();
      }
} 

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Petty Cash Transactions</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen">



  <!-- Header -->
  <header class="bg-white shadow border-b">
    <div class="max-w-4xl mx-auto px-6 py-5">


      <a href="/" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6">
        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
      </svg>
      Back to Dashboard
    </a>

      
      
      <h1 class="text-3xl font-bold">💵 Cash Register</h1>
     </div>
  </header>

  <!-- Main Content -->
  <main class="max-w-4xl mx-auto px-6 py-10 space-y-12">

    <!-- Form Section -->
    <section class="bg-white p-6 rounded-lg shadow-md">
         <?php echo $saved; ?>
          <?php echo $bank_error; ?>
         <br>
          <br>
      <h2 class="text-xl font-semibold mb-4">📝 Tranfer to cash</h2>
      <form id="cashForm" class="space-y-6" method="POST">


        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
           
         
           
          <div>
            <label for="bankAccount" class="block text-sm font-medium text-gray-700 mb-1">From Account</label>
            <select id="bankAccount" name="bankAccount" required class="w-full border px-3 py-2 rounded-md">

               <option value="cih">Cash in hand</option>
               <option value="pa">Personal Account</option>
               <option value="Bank">Bank</option>
               
            </select>
          </div>

         

          <div>
            <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount (₦)</label>
            <input type="text" id="amount" name="amount" required min="0" step="0.01" class="w-full border px-3 py-2 rounded-md" />
          </div>

          <div>
            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
            <input type="date" name="date" id="date" required class="w-full border px-3 py-2 rounded-md" />
          </div>
        </div>

        <div>
          <label for="note" class="block text-sm font-medium text-gray-700 mb-1">Note / Description </label>
          <textarea id="note" rows="3" name="note" class="w-full border px-3 py-2 rounded-md resize-none" required placeholder="e.g. Float for operations"></textarea>
        </div>

        <div class="text-right">
          <button name="record" type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
            Record Entry
          </button>
        </div>

      </form>
    </section>

 

  </main>

  <!-- JavaScript -->
  <script>
   
    function formatCurrency(amount) {
      return "₦" + parseFloat(amount).toLocaleString("en-NG", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    }

    function formatDate(d) {
      const date = new Date(d);
      return date.toLocaleDateString("en-NG", {
        year: "numeric",
        month: "short",
        day: "2-digit",
      });
    }

  
  </script>

</body>
</html>
