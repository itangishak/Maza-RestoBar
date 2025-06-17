// Example code to demonstrate the inventory error alert

// This simulates the AJAX response for testing purposes
const mockResponse = {
  status: 'error',
  message: 'Rice has zero stock available.'
};

// Log the response to console (for debugging)
console.log("AJAX Success - Response:", mockResponse);

// Display the error using SweetAlert
Swal.fire({
  icon: 'warning',
  title: 'Inventory Alert',
  text: mockResponse.message,
  confirmButtonText: 'OK',
  footer: '<a href="inventory.php">View Inventory</a>'
});
