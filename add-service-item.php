<?php
include 'connections.php';


if(isset($_POST['save_service'])) {
    $sn = $_POST['serviceName'];
    $cat = $_POST['category'] ?? '';
    $cost = $_POST['cost'] ?? 0;
    $des = $_POST['description'] ?? '';


    $stmt = $pdo->prepare("INSERT INTO service_items (sn, cat, cost, des, date) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)");
    $stmt->execute([$sn, $cat, $cost, $des]);

    echo "Service saved successfully";

}



?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Record Service Items</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen">

  <!-- Header -->
  <header class="bg-white shadow-sm border-b">
    <div class="max-w-5xl mx-auto px-6 py-5">
      <h1 class="text-3xl font-bold">🛠️ Record Service Items</h1>
    </div>
  </header>

  <!-- Main -->
  <main class="max-w-5xl mx-auto px-6 py-10">
    <!-- Form Section -->
    <div class="bg-white shadow rounded-lg p-6 mb-10">
      <a href="/dashboard.html" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6">
        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
             xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Dashboard
      </a>

      <form method="POST" id="serviceForm" class="space-y-6" action="">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Service Name <span class="text-red-500">*</span></label>
            <input type="text" id="serviceName" name="serviceName" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-blue-200" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <input type="text" id="category" name="category" placeholder="e.g. Maintenance, IT Support" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Unit Cost (₦) <span class="text-red-500">*</span></label>
            <input type="number" id="cost" name="cost" required min="0" step="0.01" placeholder="0.00" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Service Date <span class="text-red-500">*</span></label>
            <input type="month" id="serviceDate" name="serviceDate" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea id="description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm resize-none" placeholder="Describe the service..."></textarea>
        </div>

        <div class="text-right">
          <button name="save_service" type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
            Save Service
          </button>
        </div>
      </form>
    </div>

    <!-- Display Table -->
    <div class="bg-white shadow rounded-lg p-6">
      <h2 class="text-xl font-semibold mb-4">📋 Service Items</h2>
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="bg-gray-50 text-gray-700 font-medium">
            <tr>
              <th class="px-4 py-2">Name</th>
              <th class="px-4 py-2">Category</th>
              <th class="px-4 py-2">Cost (₦)</th>
              <th class="px-4 py-2">Date</th>
              <th class="px-4 py-2">Description</th>
            </tr>
          </thead>
          <tbody id="serviceTable" class="divide-y divide-gray-200">
            <?php
                $stmt = $biz->query("SELECT id, sn, cat, cost, des, date FROM service_items ORDER BY id DESC");
                $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach($services as $service) {
                    echo '<tr>';
                    echo '<td class="px-4 py-2">' . htmlspecialchars($service['sn']) . '</td>';
                    echo '<td class="px-4 py-2">' . htmlspecialchars($service['cat']) . '</td>';
                    echo '<td class="px-4 py-2">' . htmlspecialchars($service['cost']) . '</td>';
                    echo '<td class="px-4 py-2">' . htmlspecialchars($service['date']) . '</td>';
                    echo '<td class="px-4 py-2">' . htmlspecialchars($service['des']) . '</td>';
                    echo '</tr>';
                }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

</body>
</html>
