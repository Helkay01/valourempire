<?php
session_start();
require 'connections.php'; // Assumes $conn is a PDO instance

$cash_bal = 0;
$bank_bal = 0;
  
/// CASH BALANCE
$selCashBal = $pdo->prepare("SELECT * FROM cash_bal");
$selCashBal->execute();

if($selCashBal->rowCount() > 0) {
  $assoc = $selCashBal->fetch(PDO::FETCH_ASSOC);
  $cash_bal = (float)$assoc['balance'];
}



/// BANK BALANCE
$selBankBal = $pdo->prepare("SELECT * FROM bank_bal");
$selBankBal->execute();
if($selBankBal->rowCount() > 0) {
  $bnk_assoc = $selBankBal->fetch(PDO::FETCH_ASSOC);
  $bank_bal = (float)$bnk_assoc['balance'];
}                                  
                    



// Ensure user is logged in
if (!isset($_SESSION['user'])) {
   // die("Unauthorized access.");
    header("Location: login.php");
}

$user_id = $_SESSION['user']['user_id'];
$fn = $_SESSION['user']['fn'];
$invAlert = "";

   
$start_date = date('Y-m-d', strtotime('first day of this month'));
$end_date = date('Y-m-d');

$selInv = $pdo->prepare("SELECT * FROM invoices WHERE issue_date BETWEEN :start_date AND :end_date");
$selInv->bindParam(':start_date', $start_date);
$selInv->bindParam(':end_date', $end_date);
$selInv->execute();
$incomeDetails = $selInv->fetchAll(PDO::FETCH_ASSOC);

$totalIncome = array_sum(array_column($incomeDetails, 'total'));
   
//iNVOICES
$status = "unpaid";
$stmt = $pdo->prepare("SELECT * FROM invoices WHERE status = :status ORDER BY id DESC");
$stmt->bindParam(':status', $status);
$stmt->execute();
$count = $stmt->rowCount();
if($count > 0) {
      $invAlert = '
            <div class="m-6 bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Info!</strong>
                '.$invAlert.' ?>
            </div>
      ';
  
}

/// JOBS
$job_status = "undelivered";
$jobs = $pdo->prepare("SELECT * FROM invoices WHERE job_status = :job_status");
$jobs->bindParam(':job_status', $job_status);
$jobs->execute();
$und_jobs = $jobs->rowCount();


//EXPENSES
$selExp = $pdo->prepare("SELECT * FROM expenses WHERE date BETWEEN :start_date AND :end_date");
$selExp->bindParam(':start_date', $start_date);
$selExp->bindParam(':end_date', $end_date);
$selExp->execute();

$ExpDetails = $selExp->fetchAll(PDO::FETCH_ASSOC);
$exp = array_sum(array_column($ExpDetails, 'amount'));
 

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

  <!-- Mobile Sidebar Overlay -->
  <div id="mobileSidebar" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden" onclick="toggleSidebar()"></div>

  <!-- Wrapper (Flex layout on desktop) -->
  <div class="md:flex">

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed md:static top-0 left-0 w-64 h-full bg-white shadow-lg z-50 overflow-y-auto transform -translate-x-full transition-transform duration-300 md:translate-x-0">
      <div class="p-6 border-b">
        <h2 class="text-xl font-semibold text-gray-800">My Dashboard</h2>
      </div>

      <nav class="mt-6 space-y-2">
        <a href="#" class="block py-2.5 px-4 rounded transition hover:bg-blue-100 hover:text-blue-600">🏠 Dashboard</a>

        <!-- Service Items 
        <div>
          <button onclick="toggleSubMenu('productsSubMenu')" class="w-full text-left py-2.5 px-4 rounded hover:bg-blue-100 hover:text-blue-600">📦 Service Items</button>
          <div id="productsSubMenu" class="pl-8 mt-1 hidden text-sm">
            <a href="add-service-item.php" class="block py-1 px-2 rounded hover:bg-gray-100">Add a service item</a>
            <a href="manage-service-item.php" class="block py-1 px-2 rounded hover:bg-gray-100">Manage service item</a>
          </div>
        </div>
       -->

         
        <!-- Financial Reports -->
        <div>
          <button onclick="toggleSubMenu('reportsSubMenu')" class="w-full text-left py-2.5 px-4 rounded hover:bg-blue-100 hover:text-blue-600">📈 Financial Reports</button>
          <div id="reportsSubMenu" class="pl-8 mt-1 hidden text-sm">
            <a href="view-expenses.php" class="block py-1 px-2 rounded hover:bg-gray-100">Expenses report</a>
           <!-- <a href="other-income.php" class="block py-1 px-2 rounded hover:bg-gray-100">Other income</a> -->
            <a href="income-statement.php" class="block py-1 px-2 rounded hover:bg-gray-100">Income statement</a>
          <!--  <a href="payables.php" class="block py-1 px-2 rounded hover:bg-gray-100">Payables</a> -->
            <a href="outstanding-invoices.php" class="block py-1 px-2 rounded hover:bg-gray-100">Receivables</a>
            
         <!--   <a href="financial-position.php" class="block py-1 px-2 rounded hover:bg-gray-100">Financial Position</a> -->
          </div>
        </div>

        <!-- Short-Term Assets -->
        <div>
          <button onclick="toggleSubMenu('shortTermSubMenu')" class="w-full text-left py-2.5 px-4 rounded hover:bg-blue-100 hover:text-blue-600">💵 Short-Term Assets</button>
          <div id="shortTermSubMenu" class="pl-8 mt-1 hidden text-sm">
            <a href="main-bank.php" class="block py-1 px-2 rounded hover:bg-gray-100">Bank</a>
            <a href="outstanding-invoices.php" class="block py-1 px-2 rounded hover:bg-gray-100">Receivables</a>
            <a href="petty-cash.php" class="block py-1 px-2 rounded hover:bg-gray-100">Cash</a>
            <a href="view-cash.php" class="block py-1 px-2 rounded hover:bg-gray-100">View cash transactions</a>
            <a href="view-bank.php" class="block py-1 px-2 rounded hover:bg-gray-100">View bank transactions</a>
          </div>
        </div>

        <!-- Customers 
        <div>
          <button onclick="toggleSubMenu('customersSubMenu')" class="w-full text-left py-2.5 px-4 rounded hover:bg-blue-100 hover:text-blue-600">👤 Customers</button>
          <div id="customersSubMenu" class="pl-8 mt-1 hidden text-sm">
            <a href="new-customer.php" class="block py-1 px-2 rounded hover:bg-gray-100">Manage customers</a>
          </div>
        </div>
       -->

         
        <!-- Invoices -->
        <div>
          <button onclick="toggleSubMenu('invoicesSubMenu')" class="w-full text-left py-2.5 px-4 rounded hover:bg-blue-100 hover:text-blue-600">📝 Invoices</button>
          <div id="invoicesSubMenu" class="pl-8 mt-1 hidden text-sm">
            <a href="create-invoice.php" class="block py-1 px-2 rounded hover:bg-gray-100">Create invoice</a>
           
            <a href="outstanding-invoices.php" class="block py-1 px-2 rounded hover:bg-gray-100">Outstanding</a>
            <a href="view-invoices.php" class="block py-1 px-2 rounded hover:bg-gray-100">View invoices</a>
          </div>
        </div>

        <!-- Receipts -->
        <div>
          <button onclick="toggleSubMenu('receiptsSubMenu')" class="w-full text-left py-2.5 px-4 rounded hover:bg-blue-100 hover:text-blue-600">📑 Receipts</button>
          <div id="receiptsSubMenu" class="pl-8 mt-1 hidden text-sm">
            <a href="receipts.php" class="block py-1 px-2 rounded hover:bg-gray-100">Create receipt</a>
            <a href="view-receipts.php" class="block py-1 px-2 rounded hover:bg-gray-100">View All</a>
          </div>
        </div>

        <!-- Expenses -->
        <div>
          <button onclick="toggleSubMenu('expensesSubMenu')" class="w-full text-left py-2.5 px-4 rounded hover:bg-blue-100 hover:text-blue-600">💸 Expenses</button>
          <div id="expensesSubMenu" class="pl-8 mt-1 hidden text-sm">
            <a href="expenses.php" class="block py-1 px-2 rounded hover:bg-gray-100">Record expenses</a>
            <a href="view-expenses.php" class="block py-1 px-2 rounded hover:bg-gray-100">View All</a>
          </div>
        </div>



         <!-- Jobs -->
        <div>
          <button onclick="toggleSubMenu('jobsSubMenu')" class="w-full text-left py-2.5 px-4 rounded hover:bg-blue-100 hover:text-blue-600">👤 Jobs</button>
          <div id="jobsSubMenu" class="pl-8 mt-1 hidden text-sm">
            <a href="jobs.php" class="block py-1 px-2 rounded hover:bg-gray-100">Check job status</a>
          </div>
        </div>


        
        <!-- Settings -->
        <div>
          <button onclick="toggleSubMenu('settingsSubMenu')" class="w-full text-left py-2.5 px-4 rounded hover:bg-blue-100 hover:text-blue-600">⚙️ Settings</button>
          <div id="settingsSubMenu" class="pl-8 mt-1 hidden text-sm">
            <a href="edit-profile.php" class="block py-1 px-2 rounded hover:bg-gray-100">Edit profile & business info</a>
            <a href="change-password.php" class="block py-1 px-2 rounded hover:bg-gray-100">Change password</a>
          </div>
        </div>

      </nav>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-h-screen">

      <!-- Header -->
      <header class="bg-white shadow p-4 flex justify-between items-center sticky top-0 z-30">
        <button class="md:hidden text-gray-700 text-xl" onclick="toggleSidebar()">☰</button>
        <h1 class="text-lg font-semibold">Dashboard</h1>
        <span class="text-gray-600">Hi, <?php echo $fn; ?></span>
      </header>

      <!-- Alert -->
      
        <?php echo $invAlert; ?>
  


      <!-- Wipe All data -->
      <div class="m-6 bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Wipe Data! </strong>
        <p onclick="
            let del = window.confirm('You are about to delete all financial data. Click OK to confirm');
            if(del) {
              window.location.href = 'https://valourempire.onrender.com/delete-all.php';
            }
            "
          
          style="color: blue; font-weight: bold">Delete all data</p>
      </div>


      
      <!-- Dashboard Cards -->
      <main class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition">
          <div class="text-gray-500">Total sales (This month)</div>
          <div class="text-2xl font-semibold mt-2">₦<?php echo number_format($totalIncome, 2); ?></div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition">
          <div class="text-gray-500">Invoices (Unpaid)</div>
          <div class="text-2xl font-semibold mt-2"><?php echo $count; ?></div>
        </div>

         <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition">
          <div class="text-gray-500">Expenses (This month)</div>
          <div class="text-2xl font-semibold mt-2">₦<?php echo number_format($exp, 2); ?></div>
        </div>

         <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition">
          <div class="text-gray-500">Cash Balance</div>
          <div class="text-2xl font-semibold mt-2">₦<?php echo number_format($cash_bal, 2); ?></div>
        </div>

         <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition">
          <div class="text-gray-500">Bank Balance</div>
          <div class="text-2xl font-semibold mt-2">₦<?php echo number_format($bank_bal, 2); ?></div>
        </div>
         
        <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition">
          <div class="text-gray-500">Undelivered jobs</div>
          <div class="text-2xl font-semibold mt-2"><?php echo $und_jobs; ?></div>
        </div>
      </main>

    </div>
  </div>

  <!-- JavaScript -->
  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('mobileSidebar');
      sidebar.classList.toggle('-translate-x-full');
      overlay.classList.toggle('hidden');
    }

    function toggleSubMenu(id) {
      const submenu = document.getElementById(id);
      submenu.classList.toggle('hidden');
    }
  </script>
</body>
</html>
