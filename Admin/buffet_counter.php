<?php
session_start();
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include_once 'header.php'; ?>
  <title>Buffet Counter</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
    .container { max-width: 600px; margin: 0 auto; }
    .counters { display: flex; gap: 10px; justify-content: center; margin: 40px 0 20px; }
    .counter-box { background: #fff; border-radius: 6px; padding: 10px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .card { background: #fff; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
    .card-header { background: #4CAF50; color: #fff; padding: 15px; border-radius: 6px 6px 0 0; }
    .card-body { padding: 15px; }
    .vente-button { width: 100%; background: #ff5722; color: #fff; padding: 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
    .vente-button:hover { background: #e64a19; }
    /* Receipt styling */
    #receiptSection {
      display: none;
      width: 220px;
      margin: 0 auto;
      font-family: Arial, sans-serif;
      text-align: center;
    }
    #receiptSection img {
      width: 100px;
      margin-bottom: 5px;
    }
    #receiptSection h2 {
      margin: 0;
      font-size: 14px; /* Reduced from 16px */
    }
    #receiptSection p {
      margin: 5px 0;
      font-size: 10px; /* Reduced from 12px */
      line-height: 1.2;
    }
    #receiptSection hr {
      border: none;
      border-top: 1px dashed #000;
      margin: 6px 0; /* Reduced for compactness */
    }
  </style>
</head>
<body>
  <div class="container">
    <?php include_once 'navbar.php'; ?>
    <?php include_once 'sidebar.php'; ?>

    <div class="counters">
      <div class="counter-box">
        <h2 id="totalBuffets">0</h2>
        <p>Total Buffets (Aujourd'hui)</p>
      </div>
      <div class="counter-box">
        <h2 id="morningBuffets">0</h2>
        <p>Buffets du matin</p>
      </div>
      <div class="counter-box">
        <h2 id="noonBuffets">0</h2>
        <p>Buffets du midi</p>
      </div>
      <div class="counter-box">
        <h2 id="eveningBuffets">0</h2>
        <p>Buffets du soir</p>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3>Compteur des buffets</h3></div>
      <div class="card-body">
        <form id="buffetForm">
          <div class="mb-3">
            <label for="sale_date" class="form-label">Date</label>
            <input type="date" id="sale_date" name="sale_date" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="time_of_day" class="form-label">Time of Day</label>
            <select id="time_of_day" name="time_of_day" class="form-select" required>
              <option value="">Select</option>
              <option value="Morning">Matin</option>
              <option value="Noon">Midi</option>
              <option value="Evening">Soir</option>
            </select>
          </div>
          <button type="submit" class="vente-button">VENTE</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Hidden receipt for printing (POS small format) -->
  <div id="receiptSection">
    <img id="receiptLogo" alt="Logo" style="width:100px; margin-bottom:0px;" />
    <h2>Maza Resto-Bar</h2>
    <p>Address: Boulevard Melchior Ndadaye, Peace Corner, Bujumbura</p>
    <p>Phone: +257 69 80 58 98 | Email: barmazaresto@gmail.com</p>
    <hr>
    <p id="receiptNumber">Receipt #: --</p>
    <p id="buffetCount">Buffets (Time): --</p>
    <p id="receiptDate">Date: --</p>
    <hr>
    <p>Thank you for your purchase!</p>
  </div>

  <?php include_once 'footer.php'; ?>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const buffetForm = document.getElementById('buffetForm');
    const saleDateInput = document.getElementById('sale_date');
    const timeOfDaySelect = document.getElementById('time_of_day');

    const morningStart = 5 * 60;
    const morningEnd = 9 * 60 + 59;
    const noonStart = 10 * 60;
    const noonEnd = 16 * 60 + 59;

    function autoSelectDateTime() {
      const now = new Date();
      saleDateInput.valueAsDate = now;
      const mins = now.getHours() * 60 + now.getMinutes();
      
      if (mins >= morningStart && mins <= morningEnd) {
        timeOfDaySelect.value = 'Morning';
      } else if (mins >= noonStart && mins <= noonEnd) {
        timeOfDaySelect.value = 'Noon';
      } else {
        timeOfDaySelect.value = 'Evening';
      }
    }
    autoSelectDateTime();

    buffetForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(buffetForm);
      
      fetch('insert_buffet_sale.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire({ icon: 'success', title: 'Success', text: data.message })
              .then(() => {
                document.getElementById('receiptDate').textContent = 'Date: ' + saleDateInput.value;
                printReceipt();
                buffetForm.reset();
                autoSelectDateTime();
                loadBuffetCounts();
              });
          } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
          }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong!' }));
    });

    function loadBuffetCounts() {
      fetch('fetch_buffet_counts.php')
        .then(r => r.json())
        .then(d => {
          document.getElementById('totalBuffets').textContent = d.total;
          document.getElementById('morningBuffets').textContent = d.morning;
          document.getElementById('noonBuffets').textContent = d.noon;
          document.getElementById('eveningBuffets').textContent = d.evening;
        })
        .catch(err => console.error('Error fetching buffet counts:', err));
    }
    loadBuffetCounts();

    async function setupReceiptData() {
      const logoData = await window.printer.getLogoBase64();
      document.getElementById('receiptLogo').src = logoData;
      const receiptNum = Date.now();
      document.getElementById('receiptNumber').textContent = `Receipt #: ${receiptNum}`;
      
      const time = timeOfDaySelect.value;
      const count = document.getElementById(`${time.toLowerCase()}Buffets`).textContent;
      document.getElementById('buffetCount').textContent = `Buffets (${time}): ${count}`;
      
      document.getElementById('receiptDate').textContent = 'Date: ' + saleDateInput.value;
    }

    async function printReceipt() {
      await setupReceiptData();
      const content = document.getElementById('receiptSection').innerHTML;
      const html = `
        <html>
        <head>
          <style>
            @page { margin: 0; }
            body { margin: 0; padding: 0; }
            #receiptSection {
              margin: 0;
              padding: 0;
              width: 220px;
              font-family: Arial, sans-serif;
              text-align: center;
            }
            #receiptSection img,
            #receiptSection h2,
            #receiptSection p,
            #receiptSection hr {
              margin: 0;
              padding: 0;
            }
            #receiptSection img {
              width: 100px;
              margin-bottom: 5px;
            }
            #receiptSection h2 {
              font-size: 14px;
              font-weight: bold;
            }
            #receiptSection p {
              margin: 5px 0;
              font-size: 10px;
              line-height: 1.2;
            }
            #receiptSection hr {
              border: none;
              border-top: 1px dashed #000;
              margin: 6px 0;
            }
          </style>
        </head>
        <body>
          <div id="receiptSection">
            ${content}
          </div>
        </body>
        </html>
      `;
      window.printer.printHTML(html)
        .then(() => console.log('Print succeeded'))
        .catch(err => console.error('Print failed:', err));
    }
  });
  </script>
</body>
</html>