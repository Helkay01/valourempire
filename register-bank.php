<?php
// Include the PDO connection
require_once "connections.php";


// Handle form submission
if (isset($_POST['save_bank'])) {
    $accountName = $_POST['accountName'] ?? '';
    $bankName = $_POST['bankName'] ?? '';
    $acctNo = $_POST['acctNo'] ?? '';
    $accountType = $_POST['accountType'] ?? '';
    $balance = $_POST['balance'] ?? 0;

    // Insert into DB
    $stmt = $pdo->prepare("INSERT INTO bank (acct_name, bank_name, acct_type, balance, acct_no) VALUES (?, ?, ?, ?, ?)");
    $done = $stmt->execute([$accountName, $bankName, $accountType, $balance, $acctNo]);

    if($done) {
       // Sanitize: remove non-alphanumeric characters (table names must be safe)
        $acctNo = preg_replace('/[^a-zA-Z0-9_]/', '', $acctNo);
        
        // Prefix to ensure valid table name
        $tableName = "acct_" . $acctNo;
        
        // Build SQL with backticks
        $createTableSQL = "
            CREATE TABLE IF NOT EXISTS `$tableName` (
                id SERIAL PRIMARY KEY,
                bank_name TEXT NOT NULL,
                acct_no TEXT NOT NULL,
                des TEXT NOT NULL,
                date TEXT NOT NULL
            )";
        
        $created = $pdo->exec($createTableSQL);

            if($created) {
                $des = " ";
                $date = date('Y-m-d');
              
                $createTableSQL = $pdo->prepare("INSERT INTO `$tableName` (bank_name, acct_no, des, date) VALUES (?, ?, ?, ?)");
                $savedAll = $createTableSQL->execute([$bankName, $acctNo, $des, $date]);
                echo 'created';
            }
            

    }
}

// Fetch existing accounts
$accounts = $pdo->query("SELECT * FROM bank ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);



?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register Bank</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen flex flex-col">

  <!-- Header -->
  <header class="bg-white shadow border-b">
    <div class="max-w-4xl mx-auto px-6 py-5">
      <h1 class="text-3xl font-bold">🏦 Register Bank</h1>
    </div>
  </header>

  <!-- Main Section -->
  <main class="flex-grow max-w-4xl mx-auto px-6 py-10">

    <!-- Form -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-10">
      <a href="#" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6">
        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Dashboard
      </a>

      <h2 class="text-xl font-semibold mb-4">Add New Bank Account</h2>
      <form id="bankForm" method="POST" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
            <input type="text" name="accountName" required class="w-full border px-3 py-2 rounded-md" placeholder="e.g. John Doe" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
            <input type="text" name="bankName" required class="w-full border px-3 py-2 rounded-md" placeholder="e.g. Zenith Bank" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Acct No</label>
            <input type="number" name="acctNo" required class="w-full border px-3 py-2 rounded-md" placeholder="e.g. 0123456789" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Account Type</label>
            <select name="accountType" required class="w-full border px-3 py-2 rounded-md">
              <option value="" disabled selected>Select type</option>
              <option value="Savings">Savings</option>
              <option value="Current">Current</option>
              <option value="Fixed Deposit">Fixed Deposit</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Balance (₦)</label>
            <input type="number" name="balance" required min="0" step="0.01" class="w-full border px-3 py-2 rounded-md" placeholder="e.g. 50000" />
          </div>
        </div>

        <div class="text-right">
          <button name="save_bank" type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
            Add Bank
          </button>
        </div>
      </form>
    </div>

    <!-- Bank List -->
    <div class="bg-white shadow-md rounded-lg p-6">
      <h2 class="text-xl font-semibold mb-4">🏛️ Registered Banks</h2>
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="bg-gray-100 text-gray-700 font-medium">
            <tr>
              <th class="px-4 py-2">Bank Name</th>
              <th class="px-4 py-2">Account No</th>
              <th class="px-4 py-2">Description</th>
              <th class="px-4 py-2">Date</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <?php if ($accounts): ?>
              <?php foreach ($accounts as $acc): ?>
                <tr>
                  <td class="px-4 py-2"><?= htmlspecialchars($acc['bank_name']) ?></td>
                  <td class="px-4 py-2"><?= htmlspecialchars($acc['acct_no']) ?></td>
                  <td class="px-4 py-2"><?= htmlspecialchars($acc['acct_type']) ?></td>
                  <td class="px-4 py-2"><?= htmlspecialchars($acc['balance']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" class="px-4 py-2 text-center text-gray-500">No bank accounts found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</body>
</html>
