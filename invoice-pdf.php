<?php
session_start();
require 'connections.php'; // Assumes $conn is a PDO instance

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
}


$user_id = $_SESSION['user']['user_id'];


$chk = $pdo->prepare("SELECT * FROM login WHERE user_id = :id");
$chk->bindParam(':id', $user_id);
$chk->execute();
$assoc = $chk->fetch(PDO::FETCH_ASSOC);
$em = $assoc['email'];
$addr = $assoc['biz_address'];
$biz_name = $assoc['biz_name'];


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




<!-- Inside your existing PHP code (after handling POST and GET) -->

<?php
// ... your existing PHP code above remains unchanged
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Print Invoice</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">

<div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
  <a href="/" class="text-indigo-600 hover:underline mb-6 inline-block">← Back to Dashboard</a>

  <h1 class="text-2xl font-bold mb-4">Download Invoice</h1>

  <?php if ($successMessage): ?>
    <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
      <?= htmlspecialchars($successMessage) ?>
    </div>
  <?php endif; ?>

  <?php if ($invoiceData): ?>
    <form hidden method="post" id="invoice-form">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
          <label class="block mb-1">Bill To</label>
          <input type="text" name="bill_to" readonly value="<?= htmlspecialchars($invoiceData['bill_to']) ?>" class="w-full border px-3 py-2 rounded">
        </div>
        <div>
          <label class="block mb-1">Invoice Number</label>
          <input type="text" name="invoice_number" value="<?= htmlspecialchars($invoiceData['invoice_id']) ?>" readonly class="w-full border px-3 py-2 rounded bg-gray-100">
        </div>
        <div>
          <label class="block mb-1">Issue Date</label>
          <input type="date" name="issue_date" value="<?= $invoiceData['issue_date'] ?>" class="w-full border px-3 py-2 rounded">
        </div>
        <div>
          <label class="block mb-1">Discount</label>
          <input type="number" name="discount" id="discount-inp" value="<?= $invoiceData['discount'] ?>" step="0.01" oninput="calculateTotals()" class="w-full border px-3 py-2 rounded">
        </div>
      </div>

      <!-- Items Table -->
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

    <div id="pdf-contents" class="w-full max-w-4xl mx-auto p-6 bg-white border border-gray-300 shadow text-gray-800 font-sans text-sm">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between border-b-2 border-gray-300 pb-4 mb-6">
        <div>
          <h2 class="text-2xl font-bold text-gray-800"><?php echo $biz_name; ?></h2>
          <p>Laundry, Cleaning, Fashion service</p>
          <p><?php echo $addr; ?><br>Ibadan, Oyo State, 200005<br><?php echo $em; ?> | 08103711753</p>
        </div>
        <div class="text-right mt-4 md:mt-0">
          <h1 class="text-3xl font-bold text-green-600">INVOICE</h1>
          <p><strong>Invoice #:</strong> <?= htmlspecialchars($invoiceData['invoice_id']) ?></p>
          <p><strong>Date:</strong> <?= htmlspecialchars($invoiceData['issue_date']) ?></p>
        </div>
      </div>

      <!-- Billing -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-700">Bill To:</h3>
        <p class="whitespace-pre-line"><?= htmlspecialchars($invoiceData['bill_to']) ?></p>
      </div>

      <!-- Item Table -->
      <div class="overflow-x-auto">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-green-600 text-white">
              <th class="p-2 border">Item</th>
              <th class="p-2 border text-right">Qty</th>
              <th class="p-2 border text-right">Unit Price</th>
              <th class="p-2 border text-right">Total</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($invoiceItems as $item): ?>
            <tr class="border-t">
              <td class="p-2 border"><?= htmlspecialchars($item['item_name']) ?></td>
              <td class="p-2 border text-right"><?= intval($item['quantity']) ?></td>
              <td class="p-2 border text-right">₦<?= number_format($item['unit_price'], 2) ?></td>
              <td class="p-2 border text-right">₦<?= number_format($item['quantity'] * $item['unit_price'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Totals -->
      <div class="mt-6 w-full md:w-1/2 ml-auto">
        <table class="w-full">
          <tr>
            <td class="py-1">Subtotal:</td>
            <td class="py-1 text-right">₦<?= number_format($invoiceData['subtotal'], 2) ?></td>
          </tr>
          <tr>
            <td class="py-1">Discount:</td>
            <td class="py-1 text-right">₦<?= number_format($invoiceData['discount'], 2) ?></td>
          </tr>
          <tr class="font-bold border-t-2 border-green-600">
            <td class="py-2">Total:</td>
            <td class="py-2 text-right">₦<?= number_format($invoiceData['total'], 2) ?></td>
          </tr>
        </table>
      </div>
    </div>

    <div class="text-center mt-6">
      <button id="dl" class="bg-indigo-600 text-white px-6 py-2 rounded">Download Invoice as Image</button>
    </div>

  <?php else: ?>
    <p class="text-red-600">Invoice not found.</p>
  <?php endif; ?>
</div>

<!-- JS Script -->
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

document.getElementById('dl')?.addEventListener('click', () => {
  const content = document.getElementById('pdf-contents');
  html2canvas(content, {
    scale: 2,
    useCORS: true,
    width: content.scrollWidth
  }).then(canvas => {
    const imgData = canvas.toDataURL('image/png');
    const link = document.createElement('a');
    link.href = imgData;
    link.download = 'invoice.png';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  });
});

window.onload = () => {
  if (invoiceItems.length > 0) {
    invoiceItems.forEach(item => addItemRow(item.item_name, item.quantity, item.unit_price));
  } else {
    addItemRow();
  }
  calculateTotals();
};
</script>

</body>
</html>


