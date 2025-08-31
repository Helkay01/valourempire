<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sign Up</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center px-4">

  <div class="w-full max-w-xl bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold text-center text-gray-800 mb-6">Create Your Account</h2>

    <!-- Step 1: Personal Info -->
    <form id="step1" class="space-y-4">
      <div>
        <label for="firstName" class="block text-sm font-medium text-gray-700">First Name</label>
        <input type="text" id="firstName" name="firstName" required
               class="mt-1 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>

      <div>
        <label for="lastName" class="block text-sm font-medium text-gray-700">last Name</label>
        <input type="text" id="lastName" name="lastName" required
               class="mt-1 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>

      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
        <input type="email" id="email" name="email" required
               class="mt-1 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input type="password" id="password" name="password" required
               class="mt-1 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>


      <div class="text-gray-500">Data are encrypted using modern encryption</div>


      <button type="button" onclick="goToStep2()"
              class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
        Continue to Business Info →
      </button>
    </form>

    <!-- Step 2: Business Info (initially hidden) -->
    <form id="step2" method="POST" class="space-y-4 hidden">
      <div>
        <label for="businessName" class="block text-sm font-medium text-gray-700">Business Name</label>
        <input type="text" id="businessName" name="businessName" required
               class="mt-1 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>

      <div>
        <label for="businessType" class="block text-sm font-medium text-gray-700">Business Type</label>
        <input type="text" id="businessType" name="businessType" required
               class="mt-1 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>

      <div>
        <label for="address" class="block text-sm font-medium text-gray-700">Business Address</label>
        <textarea id="address" name="address" rows="3" required
                  class="mt-1 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </div>

      <div class="flex justify-between">
        <button type="button" onclick="goToStep1()"
                class="text-gray-600 hover:text-blue-600 transition">← Back</button>

        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
          Create Account
        </button>
      </div>
    </form>
  </div>

  <!-- Step Logic -->
  <script>
    const step1 = document.getElementById("step1");
    const step2 = document.getElementById("step2");

    function goToStep2() {
      step1.classList.add("hidden");
      step2.classList.remove("hidden");
    }

    function goToStep1() {
      step2.classList.add("hidden");
      step1.classList.remove("hidden");
    }



    $("document").ready(function() {
        

        $("form#step2").submit(function(e) {
            e.preventDefault();

            let fn = $("#firstName").val();
            let ln = $("#lastName").val();
            let email = $("#email").val();
            let pwd = $("#password").val();
            let bizName = $("#businessName").val();
            let bizType = $("#businessType").val();
            let addr = $("#address").val();



              //signup ajax
            $.ajax({
                url: 'https://valourempire.onrender.com/ajax/sign-up-ajax.php',
                method: 'POST',
                data: {fn:fn, ln:ln, email:email, pwd:pwd, bizName:bizName, bizType:bizType, addr:addr},
                success: function (data) {
                    if(data == "Registration successful") {
                        alert(data);
                        window.open("https://valourempire.onrender.com/login.php?status=success");
                    }
                    else {
                        alert(data)
                      console.log(data)
                    }
                  
                }

          })
        })


        
    });


  </script>

</body>
</html>
