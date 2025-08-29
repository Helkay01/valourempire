<?php
session_start();
require 'connections.php'; // Assumes $conn is a PDO instance

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
   // die("Unauthorized access.");
    header("Location: login.php");
}

$user_id = $_SESSION['user']['user_id'];


?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Comprehensive Income Statement</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

  <div class="bg-white shadow-xl rounded-lg p-8 w-full max-w-3xl space-y-6">

    <a href="/" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6">
        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
      </svg>
      Back to Dashboard
    </a>



    <!-- Header -->
    <div class="text-center">
      <h1 class="text-3xl font-bold text-gray-800">Comprehensive Income Statement</h1>
      <p class="text-gray-600">For the Year Ended December 31, 2025</p>
    </div>

    <!-- Income Section -->
    <div class="border border-gray-200 rounded-lg">
      <button onclick="toggleSection('incomeDetails')" class="w-full flex justify-between items-center p-4 bg-blue-100 hover:bg-blue-200 rounded-t-md">
        <span class="text-lg font-semibold text-blue-900">Income</span>
        <span>▼</span>
      </button>
      <div id="incomeDetails" class="hidden p-4 space-y-2">
        <div class="flex justify-between">
          <span>Sales Revenue</span>
          <span>₦120,000</span>
        </div>
        <div class="flex justify-between">
          <span>Service Revenue</span>
          <span>₦30,000</span>
        </div>
        <div class="flex justify-between font-semibold pt-2 border-t">
          <span>Total Income</span>
          <span id="totalIncome">₦150,000</span>
        </div>
      </div>
    </div>

    <!-- Expenses Section -->
    <div class="border border-gray-200 rounded-lg">
      <button onclick="toggleSection('expenseDetails')" class="w-full flex justify-between items-center p-4 bg-red-100 hover:bg-red-200 rounded-t-md">
        <span class="text-lg font-semibold text-red-900">Expenses</span>
        <span>▼</span>
      </button>
      <div id="expenseDetails" class="hidden p-4 space-y-2">
        <div class="flex justify-between">
          <span>COGS</span>
          <span>₦50,000</span>
        </div>
        <div class="flex justify-between">
          <span>Salaries</span>
          <span>₦25,000</span>
        </div>
        <div class="flex justify-between">
          <span>Rent</span>
          <span>₦10,000</span>
        </div>
        <div class="flex justify-between">
          <span>Utilities</span>
          <span>₦3,000</span>
        </div>
        <div class="flex justify-between">
          <span>Marketing</span>
          <span>₦4,000</span>
        </div>
        <div class="flex justify-between font-semibold pt-2 border-t">
          <span>Total Expenses</span>
          <span id="totalExpenses">₦92,000</span>
        </div>
      </div>
    </div>

    <!-- Net Profit/Loss -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
      <div class="flex justify-between text-xl font-bold">
        <span>Net Profit / Loss</span>
        <span id="netIncome" class="text-green-600">₦58,000</span>
      </div>
    </div>
  </div>

  <script>
    function toggleSection(id) {
      const section = document.getElementById(id);
      section.classList.toggle('hidden');
    }

    // Auto-calculate net income (hardcoded values for now)
    document.addEventListener('DOMContentLoaded', () => {
      const totalIncome = 120000 + 30000;
      const totalExpenses = 50000 + 25000 + 10000 + 3000 + 4000;
      const net = totalIncome - totalExpenses;

      document.getElementById('totalIncome').textContent = `₦${totalIncome.toLocaleString()}`;
      document.getElementById('totalExpenses').textContent = `₦${totalExpenses.toLocaleString()}`;

      const netIncomeElem = document.getElementById('netIncome');
      netIncomeElem.textContent = `₦${net.toLocaleString()}`;
      netIncomeElem.classList.toggle('text-green-600', net >= 0);
      netIncomeElem.classList.toggle('text-red-600', net < 0);
    });
  </script>

</body>
</html>
