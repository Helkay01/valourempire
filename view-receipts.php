<?php
session_start();
require 'connections.php'; // Assumes $conn is a PDO instance

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
   // die("Unauthorized access.");
    header("Location: login.php");
}

$user_id = $_SESSION['user']['user_id'];




$receipts = [];
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["start_date"], $_GET["end_date"])) {
    $start = $_GET["start_date"];
    $end = $_GET["end_date"];

    if (strtotime($start) > strtotime($end)) {
        $errorMessage = "Start date cannot be after end date.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM receipts WHERE payment_date BETWEEN :start AND :end ORDER BY payment_date DESC");
            $stmt->execute([
                ':start' => $start,
                ':end' => $end
            ]);
            $receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $errorMessage = "Error retrieving receipts: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Receipts Report</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8 text-gray-800">


    
  <div class="max-w-5xl mx-auto bg-white p-6 rounded shadow border">

      <a href="/" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6">
      <!-- back icon -->
        < Back to Dashboard
    </a>

     
    <h1 class="text-2xl font-bold mb-6">Receipt Report</h1>

    <?php if ($errorMessage): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($errorMessage) ?>
      </div>
    <?php endif; ?>

    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div>
        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
        <input type="date" id="start_date" name="start_date" required
               max="<?= date('Y-m-d') ?>"
               value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>"
               class="mt-1 w-full border px-4 py-2 rounded focus:ring-2 focus:ring-blue-500" />
      </div>

      <div>
        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
        <input type="date" id="end_date" name="end_date" required
               value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>"
               class="mt-1 w-full border px-4 py-2 rounded focus:ring-2 focus:ring-blue-500" />
      </div>

      <div class="flex items-end">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition w-full">
          Filter Receipts
        </button>
      </div>
    </form>

    <?php if (!empty($receipts)): ?>
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded">
          <thead class="bg-gray-100">
            <tr>
              <th class="py-2 px-4 border-b text-left">Date</th>
              <th class="py-2 px-4 border-b text-left">Client name</th>
              <th class="py-2 px-4 border-b text-left">Email</th>
              <th class="py-2 px-4 border-b text-left">Description</th>
              <th class="py-2 px-4 border-b text-left">Method</th>
              <th class="py-2 px-4 border-b text-left">Amount</th>
              <th class="py-2 px-4 border-b text-left">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($receipts as $receipt): ?>
              <tr>
                   <td class="py-2 px-4 border-b"><?= htmlspecialchars($receipt['payment_date']) ?></td>
                   <td class="py-2 px-4 border-b"><?= htmlspecialchars($receipt['client_name']) ?></td>
                   <td class="py-2 px-4 border-b"><?= htmlspecialchars($receipt['client_email']) ?></td>
                   <td class="py-2 px-4 border-b"><?= htmlspecialchars($receipt['description']) ?></td>
                   <td class="py-2 px-4 border-b"><?= htmlspecialchars($receipt['payment_method']) ?></td>
                   <td class="py-2 px-4 border-b"><?= number_format($receipt['amount'], 2) ?></td>
                   <td class="px-4 py-3 border text-center">
                        <a href="print-receipt.php?receipt_id=<?= urlencode($receipt['receipt_id']) ?>&description=<?= urlencode($receipt['description']) ?>&client_name=<?= urlencode($receipt['client_name']) ?>&client_email=<?= urlencode($receipt['client_email']) ?? ''?>&amount=<?= urlencode($receipt['amount']) ?>&date=<?= urlencode($receipt['payment_date']) ?>&payment_method=<?= urlencode($receipt['payment_method']) ?>" 
                              class="inline-block bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700">
                               Download Receipt
                        </a>
                     </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php elseif ($_SERVER["REQUEST_METHOD"] === "GET"): ?>
      <p class="mt-4 text-gray-500">No receipts found for the selected date range.</p>
    <?php endif; ?>

  </div>

  <script>
    // Prevent start date from selecting future dates
    const today = new Date().toISOString().split("T")[0];
    document.getElementById("start_date").setAttribute("max", today);
  </script>
</body>
</html>
