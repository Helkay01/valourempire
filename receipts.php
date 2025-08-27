<?php
include "connections.php";

?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create & Send Receipt</title>
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- jsPDF + html2canvas -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>



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
<body class="bg-white text-gray-800 p-6">

  <!-- Main Content -->
  <div id="receiptContent" class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow border border-gray-200">

     <a href="file:///C:/Code/Acc/dashboard.html" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
          </svg>
          Back to Dashboard
        </a>


    <h1 class="text-2xl font-bold mb-6 text-gray-800">Create Receipt</h1>


     <!-- Warning Alert -->
    <div class="m-6 bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded relative" role="alert">
      <strong class="font-bold">Caution!</strong>
      <span class="block sm:inline">Please proceed carefully. Receipts cannot be edited or deleted. Errors may impact your financial record. If payment is undercharged, please create a <a  style="color: blue; font-weight: bold;" href="file:///C:/Code/Acc/debit-note.html">debit note</a>, if overcharged, create a <a  style="color: blue; font-weight: bold;" href="file:///C:/Code/Acc/credit-note.html">credit note</a> to balance the transaction.</span>
    </div>



    <!-- Client & Receipt Info -->
    <div class="grid md:grid-cols-2 gap-6 mb-6">

        <div>
            <label for="clientName" class="block text-sm font-medium text-gray-700">Client Name</label>
            <input list="clientName" required id="searchClient" placeholder="Select or type client name..." class="px-4 py-2 border border-gray-300 rounded-md w-full focus:ring-2 focus:ring-blue-500 outline-none" />
            <input type="hidden" id="clientId" name="clientId" />
            <datalist id="clientName">
              <?php
                $sel = "SELECT * FROM customers"; $res = $pdo->query($sel);
                $dets = $res->fetchAll(PDO::FETCH_ASSOC);
                $clientMap = [];
                foreach ($dets as $det) {
                    echo '<option value="' . htmlspecialchars($det['name']) . '"></option>';
                    $clientMap[$det['name']] = $det['id'];
                }
              ?>
            </datalist>
      </div>    
    </div>

      <div>
        <label for="clientEmail" class="block text-sm font-medium text-gray-700">Client Email (optional) </label>
        <input type="email" id="clientEmail" placeholder="Client Email or phone no" class="mt-1 w-full border px-4 py-2 rounded focus:ring-2 focus:ring-blue-500"/>
      </div>

    
      <div>
        <label for="paymentDate" class="block text-sm font-medium text-gray-700">Payment Date</label>
        <input required type="date" required id="paymentDate" class="mt-1 w-full border px-4 py-2 rounded focus:ring-2 focus:ring-blue-500"/>
      </div>



      <div class="md:col-span-2">
          <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
          <textarea required id="description" rows="3" required placeholder="Enter description here..." class="mt-1 w-full border px-4 py-2 rounded focus:ring-2 focus:ring-blue-500"></textarea>
      </div>



      <div>
        <label for="paymentMethod" class="block text-sm font-medium text-gray-700">Payment Method</label>
        <select id="paymentMethod" class="mt-1 w-full border px-4 py-2 rounded focus:ring-2 focus:ring-blue-500">
          <option>Cash</option>
          <option>Bank Transfer</option>
          <option>Credit Card</option>
          <option>Mobile Payment</option>
        </select>
      </div>
    </div>



      <div>
        <label for="receiptNumber" class="block text-sm font-medium text-gray-700">Amount</label>
        <input style="width: 200px" required type="number" id="amount" value="" class="mt-1 w-full border px-4 py-2 rounded focus:ring-2 focus:ring-blue-500"/>
      </div>


      <!-- Unpaid Invoices Section -->
    <div class="mt-6">
      <h2 class="text-lg font-semibold mb-2 text-gray-700">Unpaid Invoices (₦0)</h2>
      <ul id="unpaidInvoicesList" class="list-disc list-inside space-y-1 text-gray-700">
        <!-- Invoices will be populated dynamically -->
      </ul>
    </div>




    <!-- Items Paid For -->

    <div id="receipt-items" class="space-y-6 mb-6">
      <!-- Items will be added here dynamically or statically -->
    </div>


    <br>
    <br>


    <!-- Total -->
    <div class="flex justify-end mb-6">
      <div class="w-full max-w-xs">
        <div class="flex justify-between text-lg font-semibold border-t pt-3">
          <span>Total Received:</span>
          <span id="totalAmount">₦0.00</span>
        </div>
      </div>
    </div>



    <!-- Notes -->
    <div hidden class="mb-6">
      <label for="receiptNotes" class="block text-sm font-medium text-gray-700">Notes (optional)</label>
      <textarea id="receiptNotes" rows="3" class="mt-1 w-full border px-4 py-2 rounded focus:ring-2 focus:ring-blue-500" placeholder="Thank you for your payment."></textarea>
    </div>
  </div>

  <!-- Actions -->
  <div class="max-w-4xl mx-auto flex gap-4 mt-4">
    <button onclick="sendReceipt()" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition">
      Save Receipt
    </button>
    <button onclick="downloadPDF()" class="bg-gray-200 text-gray-800 px-6 py-2 rounded hover:bg-gray-300 transition">
      Download PDF
    </button>
  </div>

<!-- JavaScript -->
<script>


  const clientMap = <?php echo json_encode($clientMap); ?>;

  document.getElementById('searchClient').addEventListener('input', function () {
    const name = this.value.trim();
    const clientId = clientMap[name] || "";
    document.getElementById('clientId').value = clientId;
    console.log("Selected Client ID:", clientId);
  });







    
      function setAmount() {
          const totalAmount = document.getElementById("totalAmount");
          const amountInput = document.getElementById("amount");

          amountInput.onkeyup = function () {
            const rawValue = this.value.replace(/[^0-9.]/g, '');
            const value = parseFloat(rawValue);

            if (!isNaN(value)) {
              totalAmount.textContent = `₦${value.toLocaleString('en-NG', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
              })}`;
            } else {
              totalAmount.textContent = "₦0.00";
            }
          };
      }




    async function downloadPDF() {
      const { jsPDF } = window.jspdf;
      const element = document.getElementById("receiptContent");

      const canvas = await html2canvas(element, {
        scale: 2,
        backgroundColor: "#ffffff"
      });

      const imgData = canvas.toDataURL("image/png");
      const pdf = new jsPDF("p", "mm", "a4");

      const pageWidth = pdf.internal.pageSize.getWidth();
      const pageHeight = pdf.internal.pageSize.getHeight();

      const imgProps = pdf.getImageProperties(imgData);
      const pdfWidth = pageWidth;
      const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

      pdf.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);
      pdf.save("receipt.pdf");
    }

    function sendReceipt() {
      alert("Receipt saved! (placeholder function)");
    }

    window.onload = () => {
      document.getElementById("paymentDate").valueAsDate = new Date();
      setAmount();
     
     
    };
  </script>

</body>
</html>
