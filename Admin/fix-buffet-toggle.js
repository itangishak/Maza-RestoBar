// This script fixes two issues:
// 1. Makes the buffet section properly hide when unchecked
// 2. Ensures the discount checkbox is always unchecked by default

$(document).ready(function() {
  // Fix 1: Make sure buffet section responds to checkbox
  function setupBuffetToggle() {
    // First, ensure the discount checkbox is unchecked by default
    $("#allow-discount").prop("checked", false);
    
    // Now fix the buffet section toggle behavior
    $("#includeBuffet").on('change', function() {
      if ($(this).is(':checked')) {
        enableSection("buffetSection");
        makeBuffetRequired();
        autoSelectDateAndDayTime();
        updateBuffetPrice();
      } else {
        disableSection("buffetSection");
      }
    });
    
    // Trigger the change event once to set initial state
    $("#includeBuffet").trigger('change');
  }
  
  // Run the fix after the page has fully loaded
  setTimeout(setupBuffetToggle, 500);
});
