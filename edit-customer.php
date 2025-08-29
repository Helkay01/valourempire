<?php
session_start();
require 'connections.php'; // Assumes $conn is a PDO instance

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
   // die("Unauthorized access.");
    header("Location: login.php");
}

$user_id = $_SESSION['user']['user_id'];


// Add new customer
if (isset($_POST['save_customer']) && empty($_POST['customer_id'])) {
    $name = $_POST['customerName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $notes = $_POST['notes'];

    $stmt = $pdo->prepare("INSERT INTO customers (name, email, phone, address, notes, date) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
    $stmt->execute([$name, $email, $phone, $address, $notes]);
    $message = "✅ Customer added successfully.";
}

// Update existing customer
if (isset($_POST['save_customer']) && !empty($_POST['customer_id'])) {
    $id = $_POST['customer_id'];
    $name = $_POST['customerName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $notes = $_POST['notes'];

    $stmt = $pdo->prepare("UPDATE customers SET name=?, email=?, phone=?, address=?, notes=? WHERE id=?");
    $stmt->execute([$name, $email, $phone, $address, $notes, $id]);
    $message = "✏️ Customer updated successfully.";
}

// Get single customer for editing
$editing = false;
if (isset($_GET['edit'])) {
    $editing = true;
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$id]);
    $editCustomer = $stmt->fetch();
}

// Get all customers
$customers = $pdo->query("SELECT * FROM customers ORDER BY date DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Manage Customers</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<header class="bg-white shadow-sm border-b border-gray-200">
  <div class="max-w-4xl mx-auto px-6 py-5">
    <h1 class="text-3xl font-semibold text-gray-900">👥 Manage Customers</h1>
  </div>
</header>

<main class="flex-grow px-4 py-8 max-w-4xl mx-auto space-y-10">

  <!-- Success message -->
  <?php if (!empty($message)): ?>
    <div class="bg-green-100 border border-green-300 text-green-800 p-4 rounded">
      <?= $message ?>
    </div>
  <?php endif; ?>

  <!-- Customer Form -->
  <div class="bg-white p-6 rounded-lg shadow-md border">
    <h2 class="text-2xl font-semibold mb-4"><?= $editing ? '✏️ Edit Customer' : '➕ Add Customer' ?></h2>
    <form method="POST" class="space-y-6">
      <input type="hidden" name="customer_id" value="<?= $editing ? htmlspecialchars($editCustomer['id']) : '' ?>">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name *</label>
          <input type="text" name="customerName" required
            value="<?= $editing ? htmlspecialchars($editCustomer['name']) : '' ?>"
            class="w-full border rounded px-3 py-2" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
          <input type="email" name="email" required
            value="<?= $editing ? htmlspecialchars($editCustomer['email']) : '' ?>"
            class="w-full border rounded px-3 py-2" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
          <input type="tel" name="phone"
            value="<?= $editing ? htmlspecialchars($editCustomer['phone']) : '' ?>"
            class="w-full border rounded px-3 py-2" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
          <input type="text" name="address"
            value="<?= $editing ? htmlspecialchars($editCustomer['address']) : '' ?>"
            class="w-full border rounded px-3 py-2" />
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
        <textarea name="notes" rows="4"
          class="w-full border rounded px-3 py-2"><?= $editing ? htmlspecialchars($editCustomer['notes']) : '' ?></textarea>
      </div>

      <div class="text-right pt-4">
        <button type="submit" name="save_customer"
          class="bg-blue-600 text-white font-semibold px-6 py-2 rounded hover:bg-blue-700 transition">
          <?= $editing ? "Update Customer" : "Save Customer" ?>
        </button>
      </div>
    </form>
  </div>

  <!-- Customer List -->
  <div class="bg-white p-6 rounded-lg shadow-md border">
    <h2 class="text-2xl font-semibold mb-4">📋 Customer List</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm text-left border">
        <thead class="bg-gray-100">
          <tr>
            <th class="p-2 border-b">Name</th>
            <th class="p-2 border-b">Email</th>
            <th class="p-2 border-b">Phone</th>
            <th class="p-2 border-b">Date</th>
            <th class="p-2 border-b">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($customers as $cust): ?>
            <tr class="hover:bg-gray-50">
              <td class="p-2 border-b"><?= htmlspecialchars($cust['name']) ?></td>
              <td class="p-2 border-b"><?= htmlspecialchars($cust['email']) ?></td>
              <td class="p-2 border-b"><?= htmlspecialchars($cust['phone']) ?></td>
              <td class="p-2 border-b"><?= htmlspecialchars($cust['date']) ?></td>
              <td class="p-2 border-b">
                <a href="?edit=<?= $cust['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</main>

</body>
</html>
