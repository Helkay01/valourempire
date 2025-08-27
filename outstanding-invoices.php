<?php
require_once 'connections.php';

try {
    $status = "unpaid";
    $stmt = $pdo->prepare("SELECT * FROM invoices WHERE status = :status ORDER BY id DESC");
    $stmt->bindParam(':status', $status);
    $stmt->execute();
  
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

    <?php if (empty($invoices)): ?>
        <div class="text-gray-600">No invoices found.</div>
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
                                <a href="edit_invoice.php?invoice_id=<?= urlencode($invoice['invoice_id']) ?>" 
                                   class="inline-block bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700">
                                    Write receipt
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="mt-6">
        <a href="create_invoice.php" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">+ Create New Invoice</a>
    </div>
</div>

</body>
</html>
