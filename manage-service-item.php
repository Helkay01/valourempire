<?php
include 'connections.php';


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit & Delete Service Items</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

</head>
<body class="bg-gray-100 text-gray-800 min-h-screen">

  <!-- Header -->
  <header class="bg-white shadow border-b">
    <div class="max-w-6xl mx-auto px-6 py-5">

       <a href="file:///C:/Code/Acc/dashboard.html" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
          </svg>
          Back to Dashboard
        </a>

        
      <h1 class="text-3xl font-bold">🛠️ Manage Service Items</h1>
    </div>
  </header>

  <!-- Main -->
  <main class="max-w-6xl mx-auto px-6 py-10 space-y-10">

    <!-- Service Table -->
    <div class="bg-white shadow rounded-lg p-6">
      <h2 class="text-xl font-semibold mb-4">📋 Service Items</h2>
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="bg-gray-50 text-gray-700 font-medium">
            <tr>
              <th class="px-4 py-2">Name</th>
              <th class="px-4 py-2">Category</th>
              <th class="px-4 py-2">Cost (₦)</th>
              <th class="px-4 py-2">Description</th>
              <th class="px-4 py-2">Date</th>
              <th class="px-4 py-2">Actions</th>
            </tr>
          </thead>
          <tbody id="serviceTable" class="divide-y divide-gray-200">
            <!-- JavaScript will populate -->

             <?php
                $stmt = $pdo->query("SELECT id, sn, cat, cost, des, date FROM service_items ORDER BY id DESC");
                $services = $stmt->fetchAll(PDO::FETCH_ASSOC);


                foreach($services as $service) {
                      $sn = htmlspecialchars($service['sn']);
                      $cat = htmlspecialchars($service['cat']);
                      $cost = htmlspecialchars($service['cost']);
                      $date = htmlspecialchars($service['date']);
                      $des = htmlspecialchars($service['des']);
                      $id = htmlspecialchars($service['id']);

                      echo '<tr>';
                      echo '<td class="px-4 py-2">'.$sn.'</td>';
                      echo '<td class="px-4 py-2">'.$cat.'</td>';
                      echo '<td class="px-4 py-2">'.$cost.'</td>';
                      echo '<td class="px-4 py-2">'.$des.'</td>';
                      echo '<td class="px-4 py-2">'.$date.'</td>';
                      echo '
                            <td class="px-4 py-2 space-x-3">
                                  <button onclick="editService(\''.$id.'\', \''.$sn.'\', \''.$cat.'\', \''.$cost.'\', \''.$des.'\')" class="text-blue-600 hover:underline text-sm">Edit</button>

                                  <button hidden onclick="deleteService(\''.$id.'\', \''.$sn.'\', \''.$cat.'\', \''.$cost.'\', \''.$des.'\')" class="text-red-600 hover:underline text-sm">Delete</button>
                            </td>
                        ';

                      echo '</tr>';
}



            ?>
          </tbody>
        </table>
      </div>
    </div>




<?php

if(isset($_POST['update'])) {
    $sn = $_POST['editName'];
    $cat = $_POST['editCategory'];
    $cost = $_POST['editCost'];
    $des = $_POST['editDescription'];
    $id = $_POST['editId'];


    $upd = "UPDATE service_items SET sn = :sn, cat = :ca, cost = :co, des = :des WHERE id = :id";
    $upd_res = $pdo->prepare($upd);
    $upd_res->bindParam(':sn', $sn);
    $upd_res->bindParam(':ca', $cat);
    $upd_res->bindParam(':co', $cost);
    $upd_res->bindParam(':des', $des);
    $upd_res->bindParam(':id', $id);

    $upd_res->execute();
      echo "Service saved successfully";

}





?>




    <!-- Edit Form -->
    <div id="editSection" class=" bg-white shadow rounded-lg p-6">
      <h2 class="text-xl font-semibold mb-4">✏️ Edit Service</h2>
      <form id="editForm" method="POST" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Service Name</label>
            <input name="editName" type="text" id="editName" required class="w-full px-3 py-2 border rounded-md" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <input name="editCategory" type="text" id="editCategory" class="w-full px-3 py-2 border rounded-md" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cost (₦)</label>
            <input name="editCost" type="number" id="editCost" min="0" step="0.01" required class="w-full px-3 py-2 border rounded-md" />
          </div>


          <div hidden>
            <label class="block text-sm font-medium text-gray-700 mb-1">Id</label>
            <input name="editId" type="number" id="editId" min="0" step="0.01" required class="w-full px-3 py-2 border rounded-md" />
          </div>
          
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea name="editDescription" id="editDescription" rows="3" class="w-full px-3 py-2 border rounded-md resize-none"></textarea>
        </div>

        <div class="text-right">
          <button name="update" type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">Update Service</button>
        </div>
      </form>
    </div>
  </main>

  <script>
   
      
 
  
        function editService(id, sn, cat, cost, des) {
              document.getElementById("editId").value = id;
              document.getElementById("editName").value = sn;
              document.getElementById("editCategory").value = cat;
              document.getElementById("editCost").value = cost;
              document.getElementById("editDescription").value = des;
          }
    

 
  </script>

</body>
</html>
 
