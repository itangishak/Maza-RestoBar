// Script to handle toggling of accompaniments and discount sections
document.addEventListener('DOMContentLoaded', function() {
  // Toggle button for accompaniments section
  const toggleAccompaniments = document.getElementById('toggle-accompaniments');
  if (toggleAccompaniments) {
    toggleAccompaniments.addEventListener('click', function() {
      const section = document.getElementById('accompaniments-section');
      const isHidden = section.classList.contains('hidden');
      
      // Toggle visibility
      section.classList.toggle('hidden');
      
      // Update button text
      if (isHidden) {
        this.innerHTML = '<i class="fas fa-minus-circle"></i> <span data-key="report.hideAccompaniments">Hide Accompaniments</span>';
      } else {
        this.innerHTML = '<i class="fas fa-plus-circle"></i> <span data-key="report.showAccompaniments">Show Accompaniments</span>';
        
        // Clear accompaniments when hiding
        const selects = section.querySelectorAll('.accompaniment-select');
        const prices = section.querySelectorAll('.accompaniment-price');
        
        // Remove all but the first row
        const rows = section.querySelectorAll('.accompaniment-row');
        for (let i = 1; i < rows.length; i++) {
          rows[i].remove();
        }
        
        // Clear first row values
        if (selects.length > 0) selects[0].value = '';
        if (prices.length > 0) prices[0].value = '';
        
        // Update totals
        if (typeof updateBuffetTotals === 'function') {
          updateBuffetTotals();
        }
      }
    });
  }
  
  // Toggle button for discount section
  const toggleDiscount = document.getElementById('toggle-discount');
  if (toggleDiscount) {
    toggleDiscount.addEventListener('click', function() {
      const section = document.getElementById('discount-section');
      const isHidden = section.classList.contains('hidden');
      
      // Toggle visibility
      section.classList.toggle('hidden');
      
      // Update button text
      if (isHidden) {
        this.innerHTML = '<i class="fas fa-minus-circle"></i> <span data-key="report.hideDiscount">Hide Discount</span>';
      } else {
        this.innerHTML = '<i class="fas fa-plus-circle"></i> <span data-key="report.showDiscount">Show Discount</span>';
        
        // Reset discount values when hiding
        const discountAmount = document.getElementById('discount-amount');
        const discountReason = document.getElementById('discount-reason');
        
        if (discountAmount) discountAmount.value = 0;
        if (discountReason) discountReason.value = '';
        
        // Update totals
        if (typeof updateBuffetTotals === 'function') {
          updateBuffetTotals();
        }
      }
    });
  }
});
