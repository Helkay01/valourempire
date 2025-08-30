<?php
session_start();
include 'connections.php';  // Your existing DB connection

// Assume user ID is stored in session
$user_id = $_SESSION['user']['user_id'] ?? null;
if (!$user_id) {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}




if(isset($_POST['saveProfile'])) {
    
    // Grab POST data and sanitize (basic)
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $businessName = trim($_POST['businessName'] ?? '');
    $businessType = trim($_POST['businessType'] ?? '');
    $businessAddress = trim($_POST['businessAddress'] ?? '');
    
    if ($firstName === '' || $lastName === '' || $email === '' || $businessName === '' || $businessType === '' || $businessAddress === '') {
        die("Please fill all required fields.");
    }
    
    // Prepare update query
    $sql = "UPDATE LOGIN SET fn = ?, ln = ?, email = ?, biz_name = ?, biz_type = ?, biz_address = ? WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
   
    $stmt->execute([$firstName, $lastName, $email, $businessName, $businessType, $businessAddress, $user_id]);
    if ($stmt) {
        
        header("Location: edit-profile.php?status=success");
        exit();
    } else {
        die("Error updating profile: " . $pdo->error);
    }

}




?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Profile & Business Info</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center px-4">
  <div class="bg-white shadow-xl rounded-lg p-8 max-w-2xl w-full space-y-10">
    <a href="/" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6">
      <!-- back icon -->
     < Back to Dashboard
    </a>

    <h1 class="text-3xl font-bold text-gray-800 text-center">Edit Profile & Business Info</h1>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
      <div class="mb-4 px-4 py-3 rounded text-green-700 bg-green-100">Profile updated successfully!</div>
    <?php endif; ?>

    <form method="POST" class="space-y-10">
      <!-- Personal Info -->
      <section>
        <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Personal Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label for="firstName">First Name</label>
            <input type="text" name="firstName" id="firstName" value="<?= htmlspecialchars($_SESSION['user']['fn'] ?? '') ?>" required class="w-full px-4 py-2 border rounded-md">
          </div>
          <div>
            <label for="lastName">Last Name</label>
            <input type="text" name="lastName" id="lastName" value="<?= htmlspecialchars($_SESSION['user']['ln'] ?? '') ?>" required class="w-full px-4 py-2 border rounded-md">
          </div>
          <div hidden class="md:col-span-2">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>" required class="w-full px-4 py-2 border rounded-md">
          </div>
        </div>
      </section>

      <!-- Business Info -->
      <section>
        <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Business Information</h2>
        <div class="space-y-5">
          <div>
            <label for="businessName">Business Name</label>
            <input type="text" name="businessName" id="businessName" value="<?= htmlspecialchars($_SESSION['user']['biz_name'] ?? '') ?>" required class="w-full px-4 py-2 border rounded-md">
          </div>
          <div>
            <label for="businessType">Business Type</label>
            <input type="text" name="businessType" id="businessType" value="<?= htmlspecialchars($_SESSION['user']['biz_type'] ?? '') ?>" required class="w-full px-4 py-2 border rounded-md">
          </div>
          <div>
            <label for="businessAddress">Business Address</label>
            <textarea name="businessAddress" id="businessAddress" rows="3" required class="w-full px-4 py-2 border rounded-md"><?= htmlspecialchars($_SESSION['user']['biz_address'] ?? '') ?></textarea>
          </div>
        </div>
      </section>

      <button type="submit" name="saveProfile" class="w-full bg-indigo-600 text-white font-semibold py-3 rounded-md hover:bg-indigo-700">
        Save All Changes
      </button>
    </form>
  </div>
</body>
</html>
