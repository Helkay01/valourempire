<?php
require_once 'connections.php';

// Fetch transactions from DB
try {
    $stmt = $pdo->query("SELECT * FROM cash ORDER BY date DESC");
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
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
    <section class="bg-white p-6 rounded-lg shadow-md">
      <h2 class="text-xl font-semibold mb-4">📅 Filter by Date Range</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="startDate" class="block text-sm font-medium text-gray-700">Start Date</label>
          <input type="date" id="startDate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" max="">
        </div>
        <div>
          <label for="endDate" class="block text-sm font-medium text-gray-700">End Date</label>
          <input type="date" id="endDate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        </div>
      </div>
      <div class="mt-6">
        <button id="loadButton" class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md text-sm font-medium">
          🔍 Load Transactions
        </button>
      </div>
    </section>

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
          <tbody id="cashTable" class="divide-y divide-gray-200">
            <?php foreach ($transactions as $txn): ?>
              <tr>
                <td class="px-4 py-2"><?= htmlspecialchars(date("d M Y", strtotime($txn['date']))) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($txn['note']) ?></td>
                <td class="px-4 py-2">₦<?= $txn['amount']?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

  </main>

  <!-- JavaScript -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const today = new Date().toISOString().split("T")[0];
      document.getElementById("startDate").max = today;

      document.getElementById("loadButton").addEventListener("click", () => {
        const startDate = document.getElementById("startDate").value;
        const endDate = document.getElementById("endDate").value;
        const tableBody = document.getElementById("cashTable");
        const rows = Array.from(tableBody.querySelectorAll("tr"));

        if (!startDate || !endDate) {
          alert("Please select both start and end dates.");
          return;
        }

        const start = new Date(startDate);
        const end = new Date(endDate);

        if (start > end) {
          alert("Start Date cannot be after End Date.");
          return;
        }

        // Filter rows
        rows.forEach(row => {
          const dateText = row.children[0].textContent.trim();
          const txnDate = new Date(dateText);

          if (txnDate >= start && txnDate <= end) {
            row.style.display = "";
          } else {
            row.style.display = "none";
          }
        });
      });
    });
  </script>

</body>
</html>
