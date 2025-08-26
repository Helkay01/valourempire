<?php
// Include DB connection
require_once 'connections.php';

// Default date range if none selected
if(isset($_GET['start_date']) & isset($_GET['end_date'])) {
    $startDate = $_GET['start_date'];
    $endDate   = $_GET['end_date'];

    // Prepare expenses array
    $expenses = [];
    
    try {
        $stmt = $pdo->prepare("
            SELECT *
            FROM expenses
            WHERE date BETWEEN :start_date AND :end_date
            ORDER BY id DESC
        ");
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date'   => $endDate
        ]);
        $expenses = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Database error: " . htmlspecialchars($e->getMessage());
    }

}
  
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>All Expenses - ERP Style</title>
  <script src="https://cdn.tailwindcss.com"></script>

<script>
window.onload = function() {
     // Get today's date in YYYY-MM-DD format
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('start-date').max = today;
}
</script>
    
</head>
<body class="bg-gray-50 min-h-screen text-gray-800 p-6 flex flex-col">

  <!-- Header -->
  <header class="max-w-6xl mx-auto mb-8">
    <div class="max-w-4xl mx-auto px-6 py-5">
        <a href="dashboard.php" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Dashboard
        </a>
    </div>
    <h1 class="text-3xl font-bold mb-1">📊 All Expenses</h1>
    <p class="text-gray-600">Showing recorded expenses between selected dates.</p>
  </header>

  <!-- Date Filter Form -->
  <div class="max-w-6xl mx-auto mb-6">
    <form method="get" class="flex flex-wrap items-end gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date:</label>
        <input type="date" id="start-date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" max="" class="border rounded px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">End Date:</label>
        <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="border rounded px-3 py-2">
      </div>
      <div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 mt-5">Filter</button>
      </div>
    </form>
  </div>

  <!-- Expenses Table -->
  <main class="max-w-6xl mx-auto flex-grow">
    <div class="overflow-x-auto bg-white rounded-lg shadow border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount (₦)</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <?php if (isset($error)): ?>
            <tr><td colspan="6" class="text-red-500 text-center py-4"><?= $error ?></td></tr>
          <?php elseif (empty($expenses)): ?>
            <tr><td colspan="6" class="text-center text-gray-500 py-6">No expenses found in the selected date range.</td></tr>
          <?php else: ?>
            <?php foreach ($expenses as $index => $expense): ?>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                  <?= date("M d, Y", strtotime($expense['date'])) ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                  <?= $expense['category'] ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                  <?= $expense['payment_method'] ?>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="<?= $expense['des'] ?>">
                  <?= $expense['des'] ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                  ₦<?= number_format($expense['amount'], 2) ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <a href="edit-expenses.php?id=<?= $expense['id'] ?>&cat=<?= $expense['category'] ?>&des=<?= $expense['des'] ?>&amount=<?= number_format($expense['amount'], 2) ?>&pm=<?= $expense['payment_method'] ?>" class="inline-flex items-center px-3 py-1.5 border border-blue-600 text-blue-600 rounded hover:bg-blue-50">
                    Edit
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>

</body>
</html>
