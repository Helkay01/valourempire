<?php
session_start();
require 'connections.php'; // Ensure this defines $pdo as a PDO instance

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['user_id'];
$saved = "";
$cash_error = "";

if (isset($_POST['record'])) {
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $date = $_POST['date'] ?? '';
    $note = trim($_POST['note'] ?? '');
    $fromAccount = $_POST['fromAccount'] ?? '';

    if ($amount <= 0 || empty($date) || empty($fromAccount)) {
        die('❌ Please fill all required fields correctly.');
    }

    try {
        $pdo->beginTransaction();

        // Get current bank balance
        $stmt = $pdo->prepare("SELECT * FROM bank_bal LIMIT 1");
        $stmt->execute();
        $bankRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $bankBal = $bankRow ? (float)$bankRow['balance'] : 0;
        $newBankBal = $bankBal + $amount;

        // If source is cash
        if ($fromAccount === "cb") {
            $cashStmt = $pdo->prepare("SELECT * FROM cash_bal LIMIT 1");
            $cashStmt->execute();
            $cashRow = $cashStmt->fetch(PDO::FETCH_ASSOC);
            $cashBal = $cashRow ? (float)$cashRow['balance'] : 0;

            if ($cashBal >= $amount) {
                // Deduct from cash
                $updateCash = $pdo->prepare("UPDATE cash_bal SET balance = :bal");
                $updateCash->execute([':bal' => $cashBal - $amount]);

                 $cash_stmt = $pdo->prepare("
                     INSERT INTO cash (from_bk, amount, note, date, type)
                     VALUES (:bank_account, :amount, :note, :date, :type)
                 ");

                 $cashAccount = "Cash Account";
                 $trans_type = "Contra";
                
                 $cash_stmt->execute([
                     ':bank_account' => $cashAccount,
                     ':amount' => $amount,
                     ':note' => $note,
                     ':date' => $date, 
                     ':type' => $trans_type, 
                 ]);

                 
            } else {
                $pdo->rollBack();
                $cash_error = '<div class="mt-4 bg-red-100 text-red-700 p-3 rounded">Insufficient balance in cash account. <a style="color: blue;" href="petty-cash.php">Update cash account first.</a></div>';
                goto skip_processing;
            }
        }

        else if ($fromAccount === "pa") {
            $bank_trans_type = "From Personal Account";
            $insert = $pdo->prepare("INSERT INTO main_bank (amount, note, date, type) VALUES (:amount, :note, :date, :type)");
            $insert->execute([
                ':amount' => $amount,
                ':note' => $note,
                ':date' => $date,
                ':type' => $bank_trans_type
            ]);

            // Update or insert bank balance
            if ($bankRow) {
                $updateBank = $pdo->prepare("UPDATE bank_bal SET balance = :bal");
                $updateBank->execute([':bal' => $newBankBal]);
            } else {
                $insertBank = $pdo->prepare("INSERT INTO bank_bal (balance) VALUES (:bal)");
                $insertBank->execute([':bal' => $newBankBal]);
            }

            $pdo->commit();
            $saved = '<div class="mb-4 px-4 py-3 rounded text-green-700 bg-green-100">✅ Added to bank account successfully.</div>';
        

        }
        
        // Process transaction (for all account types)
        if (in_array($fromAccount, ["cb", "Bank"])) {
            // Insert into main bank table
            $bank_trans_type = "Contra";
            $insert = $pdo->prepare("INSERT INTO main_bank (amount, note, date, type) VALUES (:amount, :note, :date, :type)");
            $insert->execute([
                ':amount' => $amount,
                ':note' => $note,
                ':date' => $date,
                ':type' => $bank_trans_type
            ]);

            // Update or insert bank balance
            if ($bankRow) {
                $updateBank = $pdo->prepare("UPDATE bank_bal SET balance = :bal");
                $updateBank->execute([':bal' => $newBankBal]);
            } else {
                $insertBank = $pdo->prepare("INSERT INTO bank_bal (balance) VALUES (:bal)");
                $insertBank->execute([':bal' => $newBankBal]);
            }

            $pdo->commit();
            $saved = '<div class="mb-4 px-4 py-3 rounded text-green-700 bg-green-100">✅ Added to bank account successfully.</div>';
        
        
        } else {     
           // $pdo->rollBack();
           // die('❌ Invalid account type.');
        }


     


        
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "❌ Database error: " . $e->getMessage();
    }
}

skip_processing:
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
  <header class="bg-white shadow border-b">
    <div class="max-w-4xl mx-auto px-6 py-5">
      <a href="/" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6">
        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Dashboard
      </a>
      <h1 class="text-3xl font-bold">💵 Add to Bank</h1>
    </div>
  </header>

  <main class="max-w-4xl mx-auto px-6 py-10 space-y-12">
   
    <section class="bg-white p-6 rounded-lg shadow-md">
      <h2 class="text-xl font-semibold mb-4">📝 New Entry</h2>
      <form method="POST" class="space-y-6">
         <?php if (!empty($saved)) echo $saved; ?>
         <?php if (!empty($cash_error)) echo $cash_error; ?>

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
            <input type="number" id="amount" name="amount" required min="0" step="0.01" class="w-full border px-3 py-2 rounded-md" />
          </div>

          <div>
            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
            <input type="date" name="date" id="date" required class="w-full border px-3 py-2 rounded-md" />
          </div>
        </div>

        <div>
          <label for="note" class="block text-sm font-medium text-gray-700 mb-1">Note / Description</label>
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
</body>
</html>
