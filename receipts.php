<?php
include "connections.php";

$successMessage = "";
$errorMessage = "";

// Check if invoice_id is set
$invoiceId = $_GET['invoice_id'] ?? null;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $clientId = $_POST["clientId"];
        $clientEmail = $_POST["clientEmail"];
        $paymentDate = $_POST["paymentDate"];
        $description = $_POST["description"];
        $paymentMethod = $_POST["paymentMethod"];
        $amount = $_POST["amount"];

        $stmt = $pdo->prepare("INSERT INTO receipts (client_id, client_email, payment_date, description, payment_method, amount) 
                               VALUES (:client_id, :client_email, :payment_date, :description, :payment_method, :amount)");

        $stmt->execute([
            ':client_id' => $clientId,
            ':client_email' => $clientEmail,
            ':payment_date' => $paymentDate,
            ':description' => $description,
            ':payment_method' => $paymentMethod,
            ':amount' => $amount
        ]);

        $successMessage = "Receipt saved successfully.";
      
    } catch (Exception $e) {
        $errorMessage = "Error saving receipt: " . $e->getMessage();
    }
}

// Fetch clients
$sel = "SELECT * FROM customers";
$res = $pdo->query($sel);
$dets = $res->fetchAll(PDO::FETCH_ASSOC);
$clientMap = [];
foreach ($dets as $det) {
    $clientMap[$det['name']] = $det['id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create & Send Receipt</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <style>
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
      -webkit-appearance: none; margin: 0;
    }
    input[type=number] {
      -moz-appearance: textfield;
    }
  </style>
</head>
<body class="bg-white text-gray-800 p-6">

<?php if ($invoiceId): ?>
  <div id="receiptContent" class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow border border-gray-200">

    <?php if ($successMessage): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        <?= htmlspecialchars($successMessage) ?>
      </div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <?= htmlspecialchars($errorMessage) ?>
      </div>
    <?php endif; ?>

    <h1 class="text-2xl font-bold mb-6 text-gray-800">Create Receipt</h1>

     <!-- Warning Alert -->
    <div class="m-6 bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded relative" role="alert">
      <strong class="font-bold">Caution!</strong>
      <span class="block sm:inline">Please proceed carefully. Receipts cannot be edited or deleted. Errors may impact your financial records.</span>
    </div>
    
    <form method="POST">
      <div class="grid md:grid-cols-2 gap-6 mb-6">
        <div>
          <label for="searchClient" class="block text-sm font-medium text-gray-700">Client Name</label>
          <input list="clientName" required id="searchClient" 
              value="<?php $cName = $_GET['client_name'] ?? "";  echo $cName; ?>"
              placeholder="Select or type client name..." class="px-4 py-2 border border-gray-300 rounded-md w-full focus:ring-2 focus:ring-blue-500 outline-none" />
          <input type="hidden" id="clientId" name="clientId" />
          <datalist id="clientName">
            <?php foreach ($dets as $det): ?>
              <option value="<?= htmlspecialchars($det['name']) ?>"></option>
            <?php endforeach; ?>
          </datalist>
        </div>

        <div>
          <label for="clientEmail" class="block text-sm font-medium text-gray-700">Email (optional)</label>
          <input type="text" name="clientEmail" id="clientEmail" class="mt-1 w-full border px-4 py-2 rounded focus:ring-2 focus:ring-blue-500"/>
        </div>

        <div>
          <label for="paymentDate" class="block text-sm font-medium text-gray-700">Payment Date</label>
          <input required type="date" name="paymentDate" id="paymentDate" class="mt-1 w-full border px-4 py-2 rounded focus:ring-2 focus:ring-blue-500"/>
        </div>

        <div>
          <label for="paymentMethod" class="block text-sm font-medium text-gray-700">Payment Method</label>
          <select name="paymentMethod" id="paymentMethod" class="mt-1 w-full border px-4 py-2 rounded focus:ring-2 focus:ring-blue-500">
            <option>Cash</option>
            <option>Bank Transfer</option>
            <option>Credit Card</option>
            <option>Mobile Payment</option>
          </select>
        </div>
      </div>

      <div class="mb-6">
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea required name="description" id="description" rows="3" class="mt-1 w-full border px-4 py-2 rounded focus:ring-2 focus:ring-blue-500"></textarea>
      </div>

      <div class="mb-6">
        <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
        <input required type="text"
            value="<?php $amt = $_GET['amount'] ?? "";  echo $amt; ?>"
            step="0.01" name="amount" id="amount" class="mt-1 w-64 border px-4 py-2 rounded focus:ring-2 focus:ring-blue-500"/>
      </div>

      <div class="flex justify-end">
        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition">
          Save Receipt
        </button>
        <button type="button" onclick="downloadPDF()" class="ml-4 bg-gray-200 text-gray-800 px-6 py-2 rounded hover:bg-gray-300 transition">
          Download PDF
        </button>
      </div>
    </form>
  </div> <!-- End of #receiptContent -->

<?php else: ?>
  <div class="max-w-2xl mx-auto mt-10 bg-yellow-100 border border-yellow-400 text-yellow-800 px-6 py-4 rounded">
    <strong class="font-bold">Invoice ID not set.</strong>
    <p class="mt-1">Please provide an <code>invoice_id</code> in the URL to access the receipt form.</p>
    <p class="text-sm mt-2 text-gray-600">Example: <code>?invoice_id=123&client_name=John&amount=250</code></p>
  </div>
<?php endif; ?>

<script>
  const clientMap = <?= json_encode($clientMap); ?>;

  document.getElementById('searchClient')?.addEventListener('input', function () {
    const name = this.value.trim();
    const clientId = clientMap[name] || "";
    document.getElementById('clientId').value = clientId;
  });

  const paymentDateInput = document.getElementById("paymentDate");
  if (paymentDateInput) {
    paymentDateInput.valueAsDate = new Date();
  }

  async function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const element = document.getElementById("receiptContent");
    const canvas = await html2canvas(element, { scale: 2, backgroundColor: "#ffffff" });
    const imgData = canvas.toDataURL("image/png");
    const pdf = new jsPDF("p", "mm", "a4");
    const pdfWidth = pdf.internal.pageSize.getWidth();
    const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
    pdf.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);
    pdf.save("receipt.pdf");
  }
</script>
</body>
</html>
