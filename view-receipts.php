<?php
include "connections.php";

// Set default values
$startDate = '';
$endDate = '';
$receipts = [];

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['filter'])) {
    $startDate = $_GET['start_date'] ?? '';
    $endDate = $_GET['end_date'] ?? '';

    // Validate dates
    $errors = [];
    if ($startDate && $startDate > date('Y-m-d')) {
        $errors[] = "Start date cannot be in the future.";
    }
    if ($endDate && $endDate < $startDate) {
        $errors[] = "End date cannot be earlier than start date.";
    }

    if (empty($errors)) {
        $query = "
            SELECT r.*, c.name as client_name
            FROM receipts r
            JOIN customers c ON r.client_id = c.id
            WHERE (:startDate IS NULL OR r.payment_date >= :startDate)
              AND (:endDate IS NULL OR r.payment_date <= :endDate)
            ORDER BY r.payment_date DESC
        ";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':startDate' => $startDate ?: null,
            ':endDate' => $endDate ?: null,
        ]);

        $receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Receipt List</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 p-6">

  <div class="max-w-6xl mx-auto bg-white p-8 rounded shadow">
    <h1 class="text-2xl font-bold mb-6">All Receipts</h1>

    <!-- Filter Form -->
    <form method="GET" class="grid md:grid-cols-3 gap-4 mb-6">
      <div>
        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
        <input type="date" id="start_date" name="start_date" max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($startDate) ?>" class="mt-1 w-full border px-3 py-2 rounded" />
      </div>
      <div>
        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
        <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="mt-1 w-full border px-3 py-2 rounded" />
      </div>
      <div class="flex items-end">
        <button type="submit" name="filter" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Filter</button>
      </div>
    </form>

    <!-- Errors -->
    <?php if (!empty($errors)): ?>
      <div class="mb-4 bg-red-100 text-red-800 border border-red-300 px-4 py-3 rounded">
        <ul class="list-disc list-inside">
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- Results -->
    <?php if (!empty($receipts)): ?>
      <div class="overflow-x-auto">
        <table class="min-w-full table-auto border border-gray-300">
          <thead class="bg-gray-200">
            <tr>
              <th class="px-4 py-2 text-left">Date</th>
              <th class="px-4 py-2 text-left">Client</th>
              <th class="px-4 py-2 text-left">Email</th>
              <th class="px-4 py-2 text-left">Method</th>
              <th class="px-4 py-2 text-left">Description</th>
              <th class="px-4 py-2 text-right">Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($receipts as $receipt): ?>
              <tr class="border-t">
                <td class="px-4 py-2"><?= htmlspecialchars($receipt['payment_date']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($receipt['client_name']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($receipt['client_email']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($receipt['payment_method']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($receipt['description']) ?></td>
                <td class="px-4 py-2 text-right">₦<?= number_format($receipt['amount'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php elseif (isset($_GET['filter'])): ?>
      <p class="text-gray-600 mt-4">No receipts found for the selected date range.</p>
    <?php endif; ?>
  </div>

</body>
</html>
