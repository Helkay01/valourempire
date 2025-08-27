<form method="get" class="mb-6 flex flex-wrap items-end gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
        <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>"
               max="<?= date('Y-m-d') ?>"
               class="border border-gray-300 rounded px-3 py-2 w-full">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
        <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>"
               class="border border-gray-300 rounded px-3 py-2 w-full">
    </div>

    <div>
        <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 mt-5">
            Filter
        </button>
    </div>
</form>
