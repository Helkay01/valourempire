<?php
session_start();
require 'connections.php'; // Assumes $conn is a PDO instance

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
   // die("Unauthorized access.");
    header("Location: login.php");
}

$user_id = $_SESSION['user']['user_id'];



if(isset($_POST['pwd'])) {

    // Get and validate POST data
    $oldPassword = $_POST['oldPassword'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
        die("All fields are required.");
    }
    
    if ($newPassword !== $confirmPassword) {
        die("New passwords do not match.");
    }
    
    
    
    try {
        // Fetch hashed password from DB
        $stmt = $pdo->prepare("SELECT * FROM login WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
    
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$user) {
            die("User not found.");
        }
    
        // Verify old password
        if (!password_verify($oldPassword, $user['pwd'])) {
            die("Old password is incorrect.");
        }
    
        // Hash and update new password
        $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
        $update = $conn->prepare("UPDATE login SET pwd = :password WHERE id = :id");
        $update->bindParam(':password', $newHashedPassword);
        $update->bindParam(':id', $user_id);
        $update->execute();
    
        echo "Password updated successfully.";
    
    } catch (PDOException $e) {
        // In production, don't echo sensitive info; log it instead
        die("Database error: " . $e->getMessage());
    }


}

  
?>






<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Change Password</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center px-4">

  <div class="bg-white shadow-xl rounded-lg p-8 max-w-md w-full">

    <a href="file:///C:/Code/Acc/dashboard.html" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6">
        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
      </svg>
      Back to Dashboard
    </a>



    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Change Password</h2>

    <!-- Status Message -->
    <div id="statusMessage" class="hidden mb-4 px-4 py-3 rounded text-sm" role="alert"></div>

    <form id="passwordForm" class="space-y-5">
      <!-- Old Password -->
      <div>
        <label class="block text-gray-700 font-medium mb-1" for="oldPassword">Old Password</label>
        <input
          type="password"
          id="oldPassword"
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
          placeholder="Enter old password"
          required
        >
      </div>

      <!-- New Password -->
      <div>
        <label class="block text-gray-700 font-medium mb-1" for="newPassword">New Password</label>
        <input
          type="password"
          id="newPassword"
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
          placeholder="Enter new password"
          required
        >
      </div>

      <!-- Confirm New Password -->
      <div>
        <label class="block text-gray-700 font-medium mb-1" for="confirmPassword">Repeat New Password</label>
        <input
          type="password"
          id="confirmPassword"
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
          placeholder="Re-enter new password"
          required
        >
      </div>

      <!-- Save Button -->
      <button 
        name="pwd"
        type="submit"
        class="w-full bg-green-600 text-white font-semibold py-2 rounded-md hover:bg-green-700 transition duration-300"
      >
        Update Password
      </button>
    </form>
  </div>

  <!-- JavaScript -->
  <script>
    const form = document.getElementById('passwordForm');
    const statusMessage = document.getElementById('statusMessage');

    form.addEventListener('submit', function (e) {
      e.preventDefault();

      const oldPassword = document.getElementById('oldPassword').value.trim();
      const newPassword = document.getElementById('newPassword').value.trim();
      const confirmPassword = document.getElementById('confirmPassword').value.trim();

      // Reset status message
      statusMessage.classList.add('hidden');
      statusMessage.textContent = '';
      statusMessage.className = 'hidden mb-4 px-4 py-3 rounded text-sm';

      if (!oldPassword || !newPassword || !confirmPassword) {
        showStatus('All fields are required.', 'red');
        return;
      }

      if (newPassword !== confirmPassword) {
        showStatus('New passwords do not match.', 'red');
        return;
      }

      if (newPassword.length < 6) {
        showStatus('New password must be at least 6 characters.', 'red');
        return;
      }

      // Simulate success
      showStatus('Password updated successfully!', 'green');
      form.reset();
    });

    function showStatus(message, type) {
      statusMessage.textContent = message;
      statusMessage.classList.remove('hidden');
      if (type === 'green') {
        statusMessage.classList.add('bg-green-100', 'text-green-800', 'border', 'border-green-300');
      } else {
        statusMessage.classList.add('bg-red-100', 'text-red-800', 'border', 'border-red-300');
      }
    }
  </script>

</body>
</html>
