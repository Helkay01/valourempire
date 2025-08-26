<?php
// Include your database connection file
include 'connections.php';

// Only accept POST requests
if (isset($_POST['expenses'])) {
    // Validate required fields
    $required_fields = ['date', 'category', 'paymentMethod', 'amount'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: $field"]);
            exit;
        }
    }


  
    // Sanitize and assign values
    $date = $_POST['date'];
    $category = trim($_POST['category']);
    $paymentMethod = trim($_POST['paymentMethod']);
    $description = trim($_POST['description'] ?? '');
    $amount = floatval($_POST['amount']);

    try {
        $stmt = $pdo->prepare("INSERT INTO expenses (category, payment_method, des, amount, date)
                               VALUES (:category, :payment_method, :description, :amount, :date)");

        $stmt->execute([
            ':category' => $category,
            ':payment_method' => $paymentMethod,
            ':description' => $description,
            ':amount' => $amount,
            ':date' => $date
        ]);

        echo 'Expense saved successfully';
      
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
   // http_response_code(405); // Method Not Allowed
  //  echo json_encode(['error' => 'Only POST requests are allowed']);
}


?>


<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Record Expense - ERP Style</title>
  <script src="https://cdn.tailwindcss.com"></script>

<script>
window.onload = function() {
     // Get today's date in YYYY-MM-DD format
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('date').max = today;
}
</script>
    
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

  <!-- Header -->
  <header class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-4xl mx-auto px-6 py-5">

         <a href="file:///C:/Code/Acc/dashboard.html" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
          </svg>
          Back to Dashboard
        </a>


      <h1 class="text-3xl font-semibold text-gray-900">💸 Record Expense</h1>
      <p class="text-gray-600 mt-1 text-sm">Fill out the form below to save a new expense.</p>
    </div>
  </header>

  <!-- Main Content -->
  <main class="flex-grow flex items-center justify-center px-4 py-12">
    <div class="bg-white max-w-3xl w-full rounded-lg shadow-md border border-gray-300 p-8">
      <form id="expenseForm" method="POST" class="space-y-6">

        <!-- Date & Category in one row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div>
            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
            <input
              type="date"
              id="date"
              name="date"
              max=""
              required
              class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm placeholder-gray-400
                     focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition"
            />
          </div>

          <div>
            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
            <input
              type="text"
              id="category"
              name="category"
              placeholder="e.g. Office Supplies"
              required
              class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm placeholder-gray-400
                     focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition"
            />
          </div>

          <div>
            <label for="paymentMethod" class="block text-sm font-medium text-gray-700 mb-1">Payment Method <span class="text-red-500">*</span></label>
            <select
              id="paymentMethod"
              name="paymentMethod"
              required
              class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm placeholder-gray-400
                     focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition"
            >
              <option value="" disabled selected>Select method</option>
              <option value="Cash">Cash</option>
              <?php
                    // Fetch existing accounts
                    $accounts = $pdo->query("SELECT * FROM bank ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
                    foreach($accounts as $acct) {
                        echo '<option value="'.$acct['acct_num'].'">'.$bank_name. - '.$acct_num.'</option>';
                    }
              ?>
              
            </select>
          </div>
        </div>

        <!-- Description -->
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea
            id="description"
            name="description"
            rows="4"
            placeholder="What was this expense for?"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm placeholder-gray-400
                   focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition resize-none"
          ></textarea>
        </div>

        <!-- Amount -->
        <div>
          <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount (₦) <span class="text-red-500">*</span></label>
          <input
            type="number"
            id="amount"
            name="amount"
            min="0"
            step="0.01"
            required
            placeholder="0.00"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm placeholder-gray-400
                   focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition"
          />
        </div>

        <!-- Submit Button -->
        <div class="pt-4 text-right">
          <button
            name="expenses"
            type="submit"
            class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-6 py-2 text-white font-semibold
                   hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition"
          >
            Save Expense
          </button>
        </div>

      </form>
    </div>
  </main>

  <script>
   
  </script>

</body>
</html>
