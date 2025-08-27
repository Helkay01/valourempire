<?php
// Include your existing PDO connection
require_once 'connections.php';

function generateInvoiceNumber($pdo) {
    // Simple: get max invoice_number, increment or create a new one
    // Here just a timestamp-based unique number (safe for example)
    return 'INV-' . date('YmdHis');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data safely and validate
    $billTo = $_POST['bill_to'] ?? '';
    $issueDate = $_POST['issue_date'] ?? '';
    $discount = floatval($_POST['discount'] ?? 0);
    $items = $_POST['items'] ?? [];

    // Validate basic inputs
    if (!$billTo || !$issueDate || !is_array($items) || count($items) === 0) {
        die("Invalid input. Please fill all required fields.");
    }

    // Calculate subtotal from items (validate items)
    $subtotal = 0;
    foreach ($items as $item) {
        $qty = intval($item['quantity'] ?? 0);
        $unitPrice = floatval($item['unit_price'] ?? 0);
        if ($qty <= 0 || $unitPrice < 0 || empty($item['name'])) {
            die("Invalid item data.");
        }
        $subtotal += $qty * $unitPrice;
    }

    $total = $subtotal - $discount;
    if ($total < 0) $total = 0;

    // Generate invoice number
    $invoiceNumber = generateInvoiceNumber($pdo);

    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Insert invoice
        $stmt = $pdo->prepare("INSERT INTO invoices (bill_to, issue_date, subtotal, discount, total, invoice_id) VALUES (?, ?, ?, ?, ?, ?) RETURNING id");
        $stmt->execute([$billTo, $issueDate, $subtotal, $discount, $total, $invoiceNumber]);
        $invoiceId = $stmt->fetchColumn();

        // Insert items
        $stmtItem = $pdo->prepare("INSERT INTO invoice_items (invoice_id, item_name, quantity, unit_price, total) VALUES (?, ?, ?, ?, ?)");
        foreach ($items as $item) {
            $qty = intval($item['quantity']);
            $unitPrice = floatval($item['unit_price']);
            $itemTotal = $qty * $unitPrice;
            $stmtItem->execute([$invoiceId, $item['name'], $qty, $unitPrice, $itemTotal]);
        }

        // Commit
        $pdo->commit();

        $successMessage = "Invoice saved successfully with Invoice Number: $invoiceNumber";

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error saving invoice: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create Invoice</title>
  <script src="https://cdn.tailwindcss.com"></script>
<style>
  /* Remove arrows in Chrome, Safari, Edge */
  input::-webkit-outer-spin-button,
  input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }
  /* Remove arrows in Firefox */
  input[type=number] {
    -moz-appearance: textfield;
  }
</style>
</head>
<body class="bg-gray-100 min-h-screen p-6">

  <div class="max-w-5xl mx-auto bg-white p-8 rounded shadow">

     <a href="file:///C:/Code/Acc/dashboard.html" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
          </svg>
          Back to Dashboard
        </a>

    <h1 class="text-2xl font-bold mb-6 text-gray-800">Create Invoice</h1>

    <?php if (!empty($successMessage)): ?>
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php endif; ?>

    <form method="post" id="invoice-form">

      <!-- Invoice Header -->
      <div class="grid md:grid-cols-2 gap-6 mb-6">
        <div>
          <label class="block text-sm font-medium text-gray-700">Bill To</label>
          <input 
              list="browsers"
              id="bill_to"
              name="bill_to"
              placeholder="Select or type a browser..."
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"/>
              
              <datalist id="browsers">
                  <option value="Chrome">
                  <option value="Firefox">
                  <option value="Safari">
                  <option value="Edge">
                  <option value="Opera">
              </datalist>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Invoice Number</label>
          <input type="text" name="invoice_number" value="(auto-generated)" readonly style="background: lightgrey;" 
                 class="mt-1 w-full border px-4 py-2 rounded focus:ring-2 focus:ring-blue-500"/>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Issue Date</label>
          <input type="date" name="issue_date" required
                 class="mt-1 w-full border px-4 py-2 rounded focus:ring-2 focus:ring-blue-500"/>
        </div>
        
      </div>

      <!-- Invoice Items -->
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left mb-4">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-2">Item</th>
              <th class="px-4 py-2 text-right">Qty</th>
              <th class="px-4 py-2 text-right">Unit Price</th>
              <th class="px-4 py-2 text-right">Total</th>
              <th class="px-4 py-2 text-center">Action</th>
            </tr>
          </thead>
          <tbody id="invoice-items">
            <!-- Dynamic Rows -->
          </tbody>
        </table>

        <button type="button" onclick="addItemRow()"
                class="mb-6 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
          + Add Item
        </button>
      </div>

      <!-- Totals -->
      <div class="flex flex-col items-end space-y-2 mb-8">
        <div class="flex justify-between w-full max-w-xs">
          <span>Subtotal:</span>
          <span id="subtotal">₦0.00</span>
        </div>
        <div class="flex justify-between w-full max-w-xs">
          <label for="discount-inp" class="mr-2">Discount:</label>
          <input type="number" id="discount-inp" name="discount" value="0" min="0" step="0.01"
             oninput="calculateTotals()"
             class="mt-1 border px-4 py-2 rounded focus:ring-2 focus:ring-blue-500 no-spinner"
             style="width: 80px;" />
          <span id="discount">₦0.00</span>
        </div>
        <div class="flex justify-between w-full max-w-xs text-lg font-bold border-t pt-2">
          <span>Total:</span>
          <span id="total">₦0.00</span>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-4">
        <button type="submit"
                class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition">
          Save Invoice
        </button>
        <button type="button" onclick="window.print()"
                class="bg-gray-200 text-gray-800 px-6 py-2 rounded hover:bg-gray-300 transition">
          Download PDF
        </button>
      </div>

      <!-- Hidden input to send items as JSON -->
      <input type="hidden" name="items_json" id="items_json" />

    </form>
  </div>

  <!-- JS Logic -->
  <script>
    function addItemRow(name = '', qty = 1, unitPrice = 0) {
      const tbody = document.getElementById("invoice-items");
      const row = document.createElement("tr");
      row.className = "border-t";

      row.innerHTML = `
        <td class="px-4 py-2">
          <input type="text" placeholder="Item name" class="w-full border px-2 py-1 rounded item-name" value="${name}" required/>
        </td>
        <td class="px-4 py-2 text-right">
          <input type="number" value="${qty}" min="1" class="w-20 text-right border px-2 py-1 rounded item-qty" oninput="calculateTotals()" required/>
        </td>
        <td class="px-4 py-2 text-right">
          <input type="number" value="${unitPrice}" min="0" class="w-24 text-right border px-2 py-1 rounded item-price" oninput="calculateTotals()" required/>
        </td>
        <td class="px-4 py-2 text-right">
          <span class="item-total">₦0.00</span>
        </td>
        <td class="px-4 py-2 text-center">
          <button type="button" onclick="this.closest('tr').remove(); calculateTotals()" class="text-red-600 hover:text-red-800">Remove</button>
        </td>
      `;

      tbody.appendChild(row);
      calculateTotals();
    }

    function calculateTotals() {
      const rows = document.querySelectorAll("#invoice-items tr");
      let subtotal = 0;

      rows.forEach(row => {
        const qtyInput = row.querySelector(".item-qty");
        const priceInput = row.querySelector(".item-price");

        const qty = qtyInput?.valueAsNumber || 0;
        const price = priceInput?.valueAsNumber || 0;
        const total = qty * price;

        row.querySelector(".item-total").textContent = `₦${total.toFixed(2)}`;
        subtotal += total;
      });

      const discountInput = document.getElementById("discount-inp");
      const discountValue = parseFloat(discountInput.value) || 0;
      const finalTotal = subtotal - discountValue;

      document.getElementById("subtotal").textContent = `₦${subtotal.toFixed(2)}`;
      document.getElementById("discount").textContent = `₦${discountValue.toFixed(2)}`;
      document.getElementById("total").textContent = `₦${(finalTotal < 0 ? 0 : finalTotal).toFixed(2)}`;
    }

    // Before submitting the form, serialize invoice items into a hidden field as JSON
    document.getElementById('invoice-form').addEventListener('submit', function(e) {
      const rows = document.querySelectorAll("#invoice-items tr");
      const items = [];

      for (const row of rows) {
        const name = row.querySelector(".item-name").value.trim();
        const qty = parseInt(row.querySelector(".item-qty").value);
        const price = parseFloat(row.querySelector(".item-price").value);

        if (!name || qty <= 0 || price < 0) {
          alert("Please fill all item fields correctly.");
          e.preventDefault();
          return false;
        }

        items.push({
          name,
          quantity: qty,
          unit_price: price
        });
      }

      if (items.length === 0) {
        alert("Please add at least one invoice item.");
        e.preventDefault();
        return false;
      }

      // Set hidden input
      document.getElementById('items_json').value = JSON.stringify(items);
    });

    // Add first row by default on load
    window.onload = () => addItemRow();
  </script>
</body>
</html>
