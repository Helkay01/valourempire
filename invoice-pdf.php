<?php
session_start();
require 'connections.php'; // Assumes $conn is a PDO instance

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
}

$user_id = $_SESSION['user']['user_id'];

$invoiceData = null;
$invoiceItems = [];
$successMessage = '';

function loadInvoice($pdo, $invoiceId) {
    $stmt = $pdo->prepare("SELECT * FROM invoices WHERE invoice_id = ?");
    $stmt->execute([$invoiceId]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($invoice) {
        $stmtItems = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
        $stmtItems->execute([$invoiceId]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        return [$invoice, $items];
    }

    return [null, []];
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['invoice_id'])) {
    $invoiceId = $_GET['invoice_id'];
    list($invoiceData, $invoiceItems) = loadInvoice($pdo, $invoiceId);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoiceId = $_POST['invoice_number'];
    $billTo = $_POST['bill_to'] ?? '';
    $issueDate = $_POST['issue_date'] ?? '';
    $discount = floatval($_POST['discount'] ?? 0);
    $itemsJson = $_POST['items_json'] ?? '';
    $items = json_decode($itemsJson, true);

    if (!$billTo || !$issueDate || !is_array($items) || count($items) === 0) {
        die("Invalid input.");
    }

    $subtotal = 0;
    foreach ($items as $item) {
        $qty = intval($item['quantity']);
        $unitPrice = floatval($item['unit_price']);
        if ($qty <= 0 || $unitPrice < 0 || empty($item['name'])) {
            die("Invalid item.");
        }
        $subtotal += $qty * $unitPrice;
    }

    $total = max(0, $subtotal - $discount);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE invoices SET bill_to=?, issue_date=?, subtotal=?, discount=?, total=? WHERE invoice_id=?");
        $stmt->execute([$billTo, $issueDate, $subtotal, $discount, $total, $invoiceId]);

        $stmt = $pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
        $stmt->execute([$invoiceId]);

        $stmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, item_name, quantity, unit_price, total) VALUES (?, ?, ?, ?, ?)");
        foreach ($items as $item) {
            $qty = intval($item['quantity']);
            $unitPrice = floatval($item['unit_price']);
            $itemTotal = $qty * $unitPrice;
            $stmt->execute([$invoiceId, $item['name'], $qty, $unitPrice, $itemTotal]);
        }

        $pdo->commit();
        $successMessage = "Invoice updated successfully.";
        list($invoiceData, $invoiceItems) = loadInvoice($pdo, $invoiceId);
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Update failed: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Invoice</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">

<div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
    <a href="/" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6">
        Back to Dashboard
    </a>

    <h1 class="text-2xl font-bold mb-4">Edit Invoice</h1>

    <?php if ($successMessage): ?>
        <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
            <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php endif; ?>

    <?php if ($invoiceData): ?>
        <form method="post" id="invoice-form">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block mb-1">Bill To</label>
                    <input type="text" name="bill_to" readonly value="<?= htmlspecialchars($invoiceData['bill_to']) ?>" required class="w-full border px-3 py-2 rounded">
                </div>
                <div>
                    <label class="block mb-1">Invoice Number</label>
                    <input type="text" name="invoice_number" value="<?= htmlspecialchars($invoiceData['invoice_id']) ?>" readonly class="w-full border px-3 py-2 rounded bg-gray-100">
                </div>
                <div>
                    <label class="block mb-1">Issue Date</label>
                    <input type="date" name="issue_date" value="<?= $invoiceData['issue_date'] ?>" required class="w-full border px-3 py-2 rounded">
                </div>
                <div>
                    <label class="block mb-1">Discount</label>
                    <input type="number" name="discount" id="discount-inp" value="<?= $invoiceData['discount'] ?>" step="0.01" oninput="calculateTotals()" class="w-full border px-3 py-2 rounded">
                </div>
            </div>

            <table class="min-w-full text-sm mb-4">
                <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-2">Item</th>
                    <th class="px-4 py-2 text-right">Qty</th>
                    <th class="px-4 py-2 text-right">Unit Price</th>
                    <th class="px-4 py-2 text-right">Total</th>
                    <th></th>
                </tr>
                </thead>
                <tbody id="invoice-items"></tbody>
            </table>

            <button type="button" onclick="addItemRow()" class="bg-blue-600 text-white px-4 py-2 rounded mb-4">+ Add Item</button>

            <div class="text-right text-lg font-bold mb-4">
                Total: <span id="total">₦0.00</span>
            </div>

            <input type="hidden" name="items_json" id="items_json">
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded">Save Changes</button>
        </form>

        <!-- Invoice Preview -->
        <h2 class="text-xl font-bold mt-12 mb-4">Invoice Preview</h2>

        <div style="max-width: 900px; margin: 0 auto 40px; padding: 30px; background: #fff; border: 1px solid #ddd; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; box-shadow: 0 0 15px rgba(0,0,0,0.05);">
          <!-- Header -->
          <div style="display: flex; justify-content: space-between; border-bottom: 2px solid #ccc; padding-bottom: 20px; margin-bottom: 30px;">
            <div>
              <h2 style="margin: 0; font-size: 24px; color: #2c3e50;">Company Name</h2>
              <p style="margin: 5px 0;">1234 Address St.<br>City, State ZIP<br>email@example.com | (123) 456-7890</p>
            </div>
            <div style="text-align: right;">
              <h1 style="margin: 0; font-size: 28px; color: #27ae60;">INVOICE</h1>
              <p style="margin: 4px 0;"><strong>Invoice #:</strong> <?= htmlspecialchars($invoiceData['invoice_id']) ?></p>
              <p style="margin: 4px 0;"><strong>Date:</strong> <?= htmlspecialchars($invoiceData['issue_date']) ?></p>
              <p style="margin: 4px 0;"><strong>Due Date:</strong> <!-- You can add due date field if you have --> </p>
            </div>
          </div>

          <!-- Billing Information -->
          <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
            <div>
              <h3 style="margin-bottom: 10px; color: #34495e;">Bill To:</h3>
              <p style="margin: 0; white-space: pre-line;"><?= htmlspecialchars($invoiceData['bill_to']) ?></p>
            </div>
            <div>
              <h3 style="margin-bottom: 10px; color: #34495e;">From:</h3>
              <p style="margin: 0;">Sales Department<br>sales@company.com<br>(987) 654-3210</p>
            </div>
          </div>

          <!-- Table -->
          <div>
            <table style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr style="background-color: #27ae60; color: white; text-align: left;">
                  <th style="padding: 10px; border: 1px solid #ddd;">Item</th>
                  <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">Qty</th>
                  <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">Unit Price</th>
                  <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">Total</th>
                </tr>
              </thead>
              <tbody>
              <?php
                foreach ($invoiceItems as $item):
                    $itemTotal = $item['quantity'] * $item['unit_price'];
              ?>
                <tr style="border-bottom: 1px solid #ddd;">
                  <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($item['item_name']) ?></td>
                  <td style="padding: 10px; border: 1px solid #ddd; text-align: right;"><?= intval($item['quantity']) ?></td>
                  <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">₦<?= number_format($item['unit_price'], 2) ?></td>
                  <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">₦<?= number_format($itemTotal, 2) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Totals -->
          <div style="margin-top: 20px; max-width: 300px; margin-left: auto; font-size: 16px;">
            <table style="width: 100%;">
              <tr>
                <td style="padding: 8px 0;">Subtotal:</td>
                <td style="text-align: right; padding: 8px 0;">₦<?= number_format($invoiceData['subtotal'], 2) ?></td>
              </tr>
              <tr>
                <td style="padding: 8px 0;">Discount:</td>
                <td style="text-align: right; padding: 8px 0;">₦<?= number_format($invoiceData['discount'], 2) ?></td>
              </tr>
              <tr style="font-weight: bold; border-top: 2px solid #27ae60;">
                <td style="padding: 8px 0;">Total:</td>
                <td style="text-align: right; padding: 8px 0;">₦<?= number_format($invoiceData['total'], 2) ?></td>
              </tr>
            </table>
          </div>
        </div>

    <?php else: ?>
        <p class="text-red-600">Invoice not found.</p>
    <?php endif; ?>
</div>

<script>
    const invoiceItems = <?= json_encode($invoiceItems) ?>;

    function addItemRow(name = '', qty = 1, price = 0) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-4 py-2"><input type="text" value="${name}" class="item-name border px-2 py-1 w-full" required></td>
            <td class="px-4 py-2 text-right"><input type="number" value="${qty}" min="1" class="item-qty border px-2 py-1 w-20 text-right" oninput="calculateTotals()" required></td>
            <td class="px-4 py-2 text-right"><input type="number" value="${price}" min="0" step="0.01" class="item-price border px-2 py-1 w-24 text-right" oninput="calculateTotals()" required></td>
            <td class="px-4 py-2 text-right item-total">₦0.00</td>
            <td class="px-4 py-2 text-center"><button type="button" onclick="this.closest('tr').remove(); calculateTotals()" class="text-red-600">Remove</button></td>
        `;
        document.getElementById('invoice-items').appendChild(row);
        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('#invoice-items tr').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const total = qty * price;
            subtotal += total;
            row.querySelector('.item-total').textContent = `₦${total.toFixed(2)}`;
        });

        const discount = parseFloat(document.getElementById('discount-inp')?.value || 0);
        const grandTotal = subtotal - discount;
        document.getElementById('total').textContent = `₦${(grandTotal > 0 ? grandTotal : 0).toFixed(2)}`;
    }

    document.getElementById('invoice-form')?.addEventListener('submit', function (e) {
        const items = [];
        document.querySelectorAll('#invoice-items tr').forEach(row => {
            const name = row.querySelector('.item-name').value.trim();
            const qty = parseInt(row.querySelector('.item-qty').value);
            const price = parseFloat(row.querySelector('.item-price').value);

            if (!name || qty <= 0 || price < 0) {
                e.preventDefault();
                alert('Please enter valid item data.');
                return false;
            }

            items.push({ name, quantity: qty, unit_price: price });
        });

        if (items.length === 0) {
            e.preventDefault();
            alert('At least one item is required.');
            return false;
        }

        document.getElementById('items_json').value = JSON.stringify(items);
    });

    window.onload = () => {
        if (invoiceItems.length > 0) {
            invoiceItems.forEach(item => {
                addItemRow(item.item_name, item.quantity, item.unit_price);
            });
        } else {
            addItemRow();
        }
        calculateTotals();
    };
</script>

</body>
</html>
