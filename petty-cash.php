<?php
session_start();
require 'connections.php'; // Assumes $conn is a PDO instance

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
   // die("Unauthorized access.");
    header("Location: login.php");
}

$user_id = $_SESSION['user']['user_id'];




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
        // Prepare and execute query
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

        // Redirect or show success
         echo '
            <script>
                window.location.href = "petty-cash.php?status=success";
            </script>
        ';


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


      
      <h1 class="text-3xl font-bold">💵 Petty Cash Register</h1>
      <p class="text-gray-600 mt-1">Track funds moved from bank to cash and running balances.</p>
    </div>
  </header>

  <!-- Main Content -->
  <main class="max-w-4xl mx-auto px-6 py-10 space-y-12">

    <!-- Form Section -->
    <section class="bg-white p-6 rounded-lg shadow-md">
      <h2 class="text-xl font-semibold mb-4">📝 New Entry</h2>
      <form id="cashForm" class="space-y-6" method="POST">


        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="bankAccount" class="block text-sm font-medium text-gray-700 mb-1">From Bank Account</label>
            <select id="bankAccount" name="bankAccount" required class="w-full border px-3 py-2 rounded-md">
             
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
