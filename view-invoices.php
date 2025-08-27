<?php
require_once 'connections.php';

$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

$query = "SELECT invoice_id, bill_to, issue_date, subtotal, discount, total FROM invoices";
$params = [];

if ($startDate && $endDate) {
    $query .= " WHERE issue_date BETWEEN :start_date AND :end_date";
    $params[':start_date'] = $startDate;
    $params[':end_date'] = $endDate;
}

$query .= " ORDER BY issue_date DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Failed to load invoices: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Invoices</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6 min-h-screen">

<div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">All Invoices</h1>

    <!-- Date Range Filter -->
    <form method="GET" class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
            <input
                type="date"
                id="start_date"
                name="start_date"
                value="<?= htmlspecialchars($startDate) ?>"
                max="<?= date('Y-m-d') ?>"
                class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                required
            >
        </div>
        <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
            <input
                type="date"
                id="end_date"
                name="end_date"
                value="<?= htmlspecialchars($endDate) ?>"
                class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                required
            >
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Filter
            </button>
        </div>
    </form>

    <?php if ($startDate && $endDate): ?>
        <?php if (empty($invoices)): ?>
            <div class="text-gray-600">No invoices found in the selected date range.</div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left border border-gray-300">
                    <thead class="bg-gray-200 text-gray-700 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 border">Invoice ID</th>
                            <th class="px-4 py-3 border">Bill To</th>
                            <th class="px-4 py-3 border">Issue Date</th>
                            <th class="px-4 py-3 border text-right">Subtotal</th>
                            <th class="px-4 py-3 border text-right">Discount</th>
                            <th class="px-4 py-3 border text-right">Total</th>
                            <th class="px-4 py-3 border text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($invoices as $invoice): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 border"><?= htmlspecialchars($invoice['invoice_id']) ?></td>
                                <td class="px-4 py-3 border"><?= htmlspecialchars($invoice['bill_to']) ?></td>
                                <td class="px-4 py-3 border"><?= htmlspecialchars($invoice['issue_date']) ?></td>
                                <td class="px-4 py-3 border text-right">₦<?= number_format($invoice['subtotal'], 2) ?></td>
                                <td class="px-4 py-3 border text-right">₦<?= number_format($invoice['discount'], 2) ?></td>
                                <td class="px-4 py-3 border text-right font-semibold">₦<?= number_format($invoice['total'], 2) ?></td>
                                <td class="px-4 py-3 border text-center">
                                    <a href="edit-invoice.php?invoice_id=<?= urlencode($invoice['invoice_id']) ?>"
                                       class="inline-block bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="mt-6">
        <a href="create-invoice.php" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">+ Create New Invoice</a>
    </div>
</div>

</body>
</html>
