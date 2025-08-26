<?php
require_once 'connections.php';

$startDate = $_GET['startDate'] ?? '';
$endDate = $_GET['endDate'] ?? '';
$transactions = [];

if ($startDate && $endDate) {
    $sql = "SELECT * FROM cash WHERE date BETWEEN :start AND :end ORDER BY date DESC";
    $params = [
        ':start' => $startDate,
        ':end' => $endDate,
    ];

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>View Cash Transactions</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen">

  <!-- Header -->
  <header class="bg-white shadow border-b">
    <div class="max-w-4xl mx-auto px-6 py-5">
      <a href="dashboard.html" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6">
        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Dashboard
      </a>
      <h1 class="text-3xl font-bold">💵 View Cash Transactions</h1>
      <p class="text-gray-600 mt-1">View all Cash Transactions</p>
    </div>
  </header>

  <!-- Main Content -->
  <main class="max-w-4xl mx-auto px-6 py-10 space-y-12">

    <!-- Date Range Filter -->
    <form method="GET" class="bg-white p-6 rounded-lg shadow-md space-y-6">
      <h2 class="text-xl font-semibold mb-4">📅 Filter by Date Range</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="startDate" class="block text-sm font-medium text-gray-700">Start Date</label>
          <input type="date" name="startDate" id="startDate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                 value="<?= htmlspecialchars($startDate) ?>">
        </div>
        <div>
          <label for="endDate" class="block text-sm font-medium text-gray-700">End Date</label>
          <input type="date" name="endDate" id="endDate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                 value="<?= htmlspecialchars($endDate) ?>">
        </div>
      </div>
      <div class="mt-6">
        <button type="submit" class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md text-sm font-medium">
          🔍 Load Transactions
        </button>
      </div>
    </form>

    <?php if ($startDate && $endDate): ?>
      <!-- Table Section -->
      <section class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4">📋 Transaction History</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-700 font-medium">
              <tr>
                <th class="px-4 py-2">Date</th>
                <th class="px-4 py-2">Description</th>
                <th class="px-4 py-2">Amount (₦)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <?php if (count($transactions) === 0): ?>
                <tr>
                  <td colspan="3" class="text-center py-4 text-gray-500">No transactions found for the selected range.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($transactions as $txn): ?>
                  <tr>
                    <td class="px-4 py-2"><?= htmlspecialchars(date("d M Y", strtotime($txn['date']))) ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($txn['note']) ?></td>
                    <td class="px-4 py-2">₦<?= $txn['amount'] ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php else: ?>
      <p class="text-center text-gray-500 italic mt-10">Please select a start and end date to view transactions.</p>
    <?php endif; ?>

  </main>
</body>
</html>
