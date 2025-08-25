<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Expense - ERP Style</title>
  <script src="https://cdn.tailwindcss.com"></script>
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

      <h1 class="text-3xl font-semibold text-gray-900">💸 Edit Expense</h1>
    </div>
  </header>

  <!-- Warning Alert -->
  <div class="m-6 bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded relative" role="alert">
    <strong class="font-bold">Info!</strong>
    <span class="block sm:inline">If expenses are duplicated, set the amount to 0 to balance the account.</span>
  </div>

  <!-- Main Content -->
  <main class="flex-grow flex items-center justify-center px-4 py-12">
    <div class="bg-white max-w-3xl w-full rounded-lg shadow-md border border-gray-300 p-8">
      <form id="expenseForm" class="space-y-6">

        <!-- Date & Category & Payment Method -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div>
            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
            <input
              type="date"
              id="date"
              name="date"
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
              <option value="Bank Transfer">Bank Transfer</option>
              <option value="Credit Card">Credit Card</option>
              <option value="Mobile Payment">Mobile Payment</option>
              <option value="Cheque">Cheque</option>
              <option value="Other">Other</option>
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
            type="submit"
            class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-6 py-2 text-white font-semibold
                   hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition"
          >
            Edit Expense
          </button>
        </div>

      </form>
    </div>
  </main>

  <script>
    
  </script>

</body>
</html>
