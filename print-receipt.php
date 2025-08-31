<?php
session_start();
require 'connections.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
}


$user_id = $_SESSION['user']['user_id'];


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

</div>


<div style="max-width:700px;margin:20px auto;padding:24px;background:#fff;font-family:'Segoe UI',Tahoma,sans-serif;color:#333;border:1px solid #ddd;box-shadow:0 2px 6px rgba(0,0,0,0.05);">
  
  <!-- Header -->
  <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;">
    <div>
      <h2 style="margin:0 0 6px 0;color:#2c3e50;">Acme ERP Solutions</h2>
      <p style="margin:2px 0;">123 Business Rd, Suite 500</p>
      <p style="margin:2px 0;">Cityville, CA 90210</p>
      <p style="margin:2px 0;">Email: info@acmeerp.com</p>
    </div>
    <div style="text-align:right;font-size:0.9em;color:#666;">
      <p style="margin:2px 0;"><strong>Receipt #: </strong>RCPT-001234</p>
      <p style="margin:2px 0;"><strong>Date: </strong>2025-08-31</p>
    </div>
  </div>

  <!-- Customer Info -->
  <div style="margin-bottom:20px;border-top:1px solid #eee;padding-top:10px;">
    <p style="margin:4px 0;"><strong>Customer:</strong> John Doe</p>
    <p style="margin:4px 0;"><strong>Email:</strong> john.doe@example.com</p>
    <p style="margin:4px 0;"><strong>Payment Date:</strong> 2025-08-30</p>
    <p style="margin:4px 0;"><strong>Payment Method:</strong> Credit Card (Visa)</p>
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
          <td style="padding:10px 12px;border:1px solid #ddd;">ERP Subscription - Pro Plan (Aug 2025)</td>
          <td style="padding:10px 12px;border:1px solid #ddd;text-align:right;">$209.00</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Totals -->
  <div style="width:100%;max-width:300px;margin-left:auto;">
    <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #eee;">
      <span>Subtotal:</span>
      <span>$190.00</span>
    </div>
    <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #eee;">
      <span>Tax (10%):</span>
      <span>$19.00</span>
    </div>
    <div style="display:flex;justify-content:space-between;padding:6px 0;font-weight:bold;color:#2c3e50;">
      <span>Total Paid:</span>
      <span>$209.00</span>
    </div>
  </div>

  <!-- Footer -->
  <div style="text-align:center;margin-top:30px;font-size:0.85em;color:#888;">
    <p style="margin:4px 0;">Thank you for your business!</p>
    <p style="margin:4px 0;"><small>This receipt was generated automatically by Acme ERP.</small></p>
  </div>

</div>

</body>
</html>


