<?php
session_start();
require 'connections.php';

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['user_id'];

// Check if date range is set in URL parameters
if (!isset($_GET['start_date']) || !isset($_GET['end_date']) || empty($_GET['start_date']) || empty($_GET['end_date'])) {
    // If the date range is not set, don't display the page content
    echo "<div class='bg-red-100 text-red-700 border-l-4 border-red-600 p-4 rounded-lg shadow-xl w-full max-w-3xl mx-auto'>
            <h2 class='text-center font-semibold text-xl'>Please select a valid date range to proceed.</h2>
            <p class='mt-2 text-center'>You need to specify a start and end date to view the data.</p>
          </div>";
    exit;
}

// If date range is set, proceed with fetching data
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];

// Fetch income details from the database
$incomeQuery = $pdo->prepare("
    SELECT * FROM invoices 
    WHERE issue_date BETWEEN :start_date AND :end_date
");
$incomeQuery->execute(['start_date' => $start_date, 'end_date' => $end_date]);
$incomeDetails = $incomeQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch expense details from the database
$expenseQuery = $pdo->prepare("
    SELECT * FROM expenses 
    WHERE date BETWEEN :start_date AND :end_date
");
$expenseQuery->execute(['start_date' => $start_date, 'end_date' => $end_date]);
$expenseDetails = $expenseQuery->fetchAll(PDO::FETCH_ASSOC);

// Calculate total income and total expenses
$totalIncome = array_sum(array_column($incomeDetails, 'total'));
$totalExpenses = array_sum(array_column($expenseDetails, 'amount'));

// Calculate net profit/loss
$netProfitLoss = $totalIncome - $totalExpenses;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Comprehensive Income Statement</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

  <div class="bg-white shadow-xl rounded-lg p-8 w-full max-w-3xl space-y-6">

    <a href="/" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6">
        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
      </svg>
      Back to Dashboard
    </a>

    <!-- Date Range Selection -->
    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center space-x-4">
        <label for="start_date" class="font-medium text-gray-700">Start Date</label>
        <input id="start_date" name="start_date" type="date" class="border border-gray-300 rounded p-2" value="<?php echo $start_date; ?>" max="<?php echo date('Y-m-d'); ?>" onchange="updateDateRange()" />
      </div>
      <div class="flex items-center space-x-4">
        <label for="end_date" class="font-medium text-gray-700">End Date</label>
        <input id="end_date" name="end_date" type="date" class="border border-gray-300 rounded p-2" value="<?php echo $end_date; ?>" onchange="updateDateRange()" />
      </div>
    </div>

    <!-- Header -->
    <div class="text-center">
      <h1 class="text-3xl font-bold text-gray-800">Comprehensive Income Statement</h1>
      <p class="text-gray-600">For the period: <?php echo date('F j, Y', strtotime($start_date)) . ' - ' . date('F j, Y', strtotime($end_date)); ?></p>
    </div>

    <!-- Income Section -->
    <div class="border border-gray-200 rounded-lg">
      <button onclick="toggleSection('incomeDetails')" class="w-full flex justify-between items-center p-4 bg-blue-100 hover:bg-blue-200 rounded-t-md">
        <span class="text-lg font-semibold text-blue-900">Income</span>
        <span>▼</span>
      </button>
      <div id="incomeDetails" class="hidden p-4 space-y-2">
        <div class="flex justify-between font-semibold pt-2 border-t">
          <span>Total Income</span>
          <span id="totalIncome">₦<?php echo number_format($totalIncome); ?></span>
        </div>
      </div>
    </div>

    <!-- Expenses Section -->
    <div class="border border-gray-200 rounded-lg">
      <button onclick="toggleSection('expenseDetails')" class="w-full flex justify-between items-center p-4 bg-red-100 hover:bg-red-200 rounded-t-md">
        <span class="text-lg font-semibold text-red-900">Expenses</span>
        <span>▼</span>
      </button>
      <div id="expenseDetails" class="hidden p-4 space-y-2">
        <?php foreach ($expenseDetails as $expense) { ?>
          <div class="flex justify-between">
            <span><?php echo htmlspecialchars($expense['category']); ?></span>
            <span>₦<?php echo number_format($expense['amount']); ?></span>
          </div>
        <?php } ?>
        <div class="flex justify-between font-semibold pt-2 border-t">
          <span>Total Expenses</span>
          <span id="totalExpenses">₦<?php echo number_format($totalExpenses); ?></span>
        </div>
      </div>
    </div>

    <!-- Net Profit/Loss -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
      <div class="flex justify-between text-xl font-bold">
        <span>Net Profit / Loss</span>
        <span id="netIncome" class="<?php echo $netProfitLoss >= 0 ? 'text-green-600' : 'text-red-600'; ?>">₦<?php echo number_format($netProfitLoss); ?></span>
      </div>
    </div>
  </div>

  <script>
    // Function to toggle income and expense sections
    function toggleSection(id) {
      const section = document.getElementById(id);
      section.classList.toggle('hidden');
    }

    // Function to update the date range when dates change
    function updateDateRange() {
      const startDate = document.getElementById('start_date').value;
      const endDate = document.getElementById('end_date').value;
      window.location.href = `?start_date=${startDate}&end_date=${endDate}`;
    }
  </script>

</body>
</html>
