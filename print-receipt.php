<?php
session_start();
require 'connections.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
}


$user_id = $_SESSION['user']['user_id'];


$chk = $pdo->prepare("SELECT * FROM login WHERE user_id = :id");
$chk->bindParam(':id', $user_id);
$chk->execute();
$assoc = $chk->fetch(PDO::FETCH_ASSOC);
$em = $assoc['email'];
$addr = $assoc['biz_address'];
$biz_name = $assoc['biz_name'];




?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Print Receipt</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">

<div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
  <a href="/" class="text-indigo-600 hover:underline mb-6 inline-block">← Back to Dashboard</a>



            
        <div id="receipt-content" style="max-width:700px;margin:20px auto;padding:24px;background:#fff;font-family:'Segoe UI',Tahoma,sans-serif;color:#333;border:1px solid #ddd;box-shadow:0 2px 6px rgba(0,0,0,0.05);">

              <h1 style="color:#2c3e50;"><b>Receipt</b></h1>
                  
              <!-- Header -->
              <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;">
                  
                <div>
                  <h2 style="margin:0 0 6px 0;color:#2c3e50;"><b><?php echo $biz_name; ?></b></h2>
                  <p style="margin:2px 0;"><?php echo $addr; ?></p>
                  <p style="margin:2px 0;">Ibadan, Oyo State, 200005</p>
                  <p style="margin:2px 0;">Email: <?php echo $em; ?></p>
                </div>
                <div style="text-align:right;font-size:0.9em;color:#666;">
                  <p style="margin:2px 0;"><strong>Receipt #: </strong><?php echo $_GET['receipt_id']; ?></p>
                  <p style="margin:2px 0;"><strong>Date: </strong><?php echo  $_GET['date']; ?></p>
                </div>
              </div>
            
              <!-- Customer Info -->
              <div style="margin-bottom:20px;border-top:1px solid #eee;padding-top:10px;">
                <p style="margin:4px 0;"><strong>Customer:</strong> <?php echo $_GET['client_name']; ?></p>
                <p style="margin:4px 0;"><strong>Email:</strong> <?php echo $_GET['client_email']; ?></p>
                <p style="margin:4px 0;"><strong>Payment Date:</strong> <?php echo $_GET['date']; ?></p>
                <p style="margin:4px 0;"><strong>Payment Method:</strong> <?php echo $_GET['payment_method']; ?></p>
              </div>
            
              <!-- Payment Details -->
              <div style="margin-bottom:20px;">
                <table style="width:100%;border-collapse:collapse;">
                  <thead>
                    <tr style="background:#f4f6f8;">
                      <th style="text-align:left;padding:10px 12px;border:1px solid #ddd;">Description</th>
                      <th style="text-align:right;padding:10px 12px;border:1px solid #ddd;">Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td style="padding:10px 12px;border:1px solid #ddd;"><?php echo $_GET['description']; ?></td>
                      <td style="padding:10px 12px;border:1px solid #ddd;text-align:right;"><?php echo $_GET['amount']; ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            
              <!-- Totals -->
              <div style="width:100%;max-width:300px;margin-left:auto;">               
                <div style="display:flex;justify-content:space-between;padding:6px 0;font-weight:bold;color:#2c3e50;">
                  <span>Total Paid:</span>
                  <span>₦<?php echo $_GET['amount']; ?></span>
                </div>
              </div>
            
              <!-- Footer -->
              <div style="text-align:center;margin-top:30px;font-size:0.85em;color:#888;">
                <p style="margin:4px 0;">Thank you for your business!</p>
              
              </div>
            
            </div>



    <div class="text-center mt-6">
      <button id="dl" class="bg-indigo-600 text-white px-6 py-2 rounded">Download Invoice as Image</button>
    </div>

    

</div>

    
    
    

<script>

window.onload = function() {
    
    document.getElementById('dl')?.addEventListener('click', () => {
          const content = document.getElementById('receipt-content');
          html2canvas(content, {
            scale: 2,
            useCORS: true,
            width: content.scrollWidth
          }).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const link = document.createElement('a');
            link.href = imgData;
            link.download = 'receipt.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
          });
    });




    
}
    
</script>


    
</body>
</html>


