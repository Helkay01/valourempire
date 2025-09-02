<?php
session_start();
require 'connections.php'; // Assumes $conn is a PDO instance

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
   // die("Unauthorized access.");
    header("Location: login.php");
}

$user_id = $_SESSION['user']['user_id'];
$saved = "";
$cash_error = "";



if (isset($_POST['record'])) {
  
  
    $amount = $_POST['amount'] ?? '';
    $date = $_POST['date'] ?? '';
    $note = $_POST['note'] ?? '';
    $fromAccount = $_POST['fromAccount'] ?? '';

    // Basic validation
    if (empty($amount) || empty($date)) {
        die('Please fill all required fields.');
    }

    try {
       if($fromAccount === "cb") {
            
               /// UPDATE CASH ACCOUNT
                $selCashBal = $pdo->prepare("SELECT * FROM cash_bal");
                $selCashBal->execute();
               
                if($selCashBal->rowCount() > 0) {
                    $assoc = $selCashBal->fetch(PDO::FETCH_ASSOC);
                    $cash_bal = (int)$assoc['balance'];

                    if($cash_bal > $amount) {
                          $new_bal = $cash_bal - $amount;
                          // Prepare and execute query
                          $save = $pdo->prepare("UPDATE cash_bal SET balance = :bal");
                          $save->execute([
                           ':bal' => $new_bal         
                          ]);


                            // Prepare and execute query
                             $stmt = $pdo->prepare("
                                 INSERT INTO main_bank (amount, note, date)
                                 VALUES (:amount, :note, :date)
                             ");
      
                             $stmt->execute([
                                 ':amount' => $amount,
                                 ':note' => $note,
                                 ':date' => $date,            
                             ]);
      
      
                             // Prepare and execute query
                                $save = $pdo->prepare("INSERT INTO bank_bal (balance) VALUES (:bal)");
                                $save->execute([
                                 ':bal' => $amount       
                                ]);

                           // Redirect or show success
                           $saved = '<div class="mb-4 px-4 py-3 rounded text-green-700 bg-green-100">Added to bank account succesfully</div>';
                                            
                    }
                    else if($cash_bal < $amount) {
                        $cash_error = '<div class="mt-4 bg-red-100 text-red-700 p-3 rounded">Insufficient balance in cash account. <a style="color: blue; " href="petty-cash.php">Update cash account first.</a></div>';
                    }
                }
             
             
       }


       /// FROM PERSONAL ACCT
        if($fromAccount === "pa" || $fromAccount === "Bank") {
              // Prepare and execute query
              $stmt = $pdo->prepare("
                  INSERT INTO main_bank (amount, note, date)
                  VALUES (:amount, :note, :date)
              ");
      
              $stmt->execute([
                  ':amount' => $amount,
                  ':note' => $note,
                  ':date' => $date,            
              ]);
      
      
              // Prepare and execute query
                 $save = $pdo->prepare("INSERT INTO bank_bal (balance) VALUES (:bal)");
                 $save->execute([
                  ':bal' => $amount       
                 ]);
      
      
               
              // Redirect or show success
               $saved = '<div class="mb-4 px-4 py-3 rounded text-green-700 bg-green-100">Added to bank account succesfully</div>';
       }

       
       
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
  <title>Bank Transactions</title>
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


      
      <h1 class="text-3xl font-bold">💵 Add to bank</h1>
     
    </div>
  </header>

  <!-- Main Content -->
  <main class="max-w-4xl mx-auto px-6 py-10 space-y-12">

     
      <?php
            if (isset($_GET['status'])) {
                echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">'
                    . htmlspecialchars($_GET['status']) .
                     '</div>';
            }         
      ?>

        
    

    
    <!-- Form Section -->
    <section class="bg-white p-6 rounded-lg shadow-md">
      <h2 class="text-xl font-semibold mb-4">📝 New Entry</h2>
      <form id="cashForm" class="space-y-6" method="POST">
        
         <?php echo $saved; ?>
         <?php echo $cash_error; ?>

         <br>
         <br>
         
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="fromAccount" class="block text-sm font-medium text-gray-700 mb-1">From Account</label>
            <select id="fromAccount" name="fromAccount" required class="w-full border px-3 py-2 rounded-md">

               <option value="cb">Cash Balance</option>
               <option value="pa">Personal Account</option>
               <option value="Bank">Bank Balance</option>
               
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
