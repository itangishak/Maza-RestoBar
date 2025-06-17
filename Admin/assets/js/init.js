/**
 * jQuery initialization and dependency management
 * This file should be included before any other script to ensure jQuery is available
 */

// Immediately executed function to avoid global namespace pollution
(function() {
  // Function to load jQuery synchronously
  function loadJQuerySync() {
    console.log('Loading jQuery synchronously...');
    
    // First, check if jQuery is already defined
    if (typeof window.jQuery !== 'undefined') {
      console.log('jQuery is already loaded.');
      window.$ = window.jQuery;
      return true;
    }
    
    try {
      // Try to load jQuery synchronously using XMLHttpRequest
      var xhr = new XMLHttpRequest();
      xhr.open('GET', 'assets/js/jquery-3.7.1.min.js', false); // false makes it synchronous
      xhr.send();
      
      if (xhr.status === 200) {
        // Create and execute the script
        var script = document.createElement('script');
        script.text = xhr.responseText;
        document.head.appendChild(script);
        
        // Ensure jQuery is available globally
        window.jQuery = jQuery;
        window.$ = jQuery;
        console.log('jQuery loaded synchronously successfully');
        return true;
      }
    } catch (error) {
      console.error('Failed to load primary jQuery:', error);
    }
    
    // Try backup location if primary fails
    try {
      var xhr = new XMLHttpRequest();
      xhr.open('GET', 'assets/vendor/jquery/jquery-3.7.1.min.js', false);
      xhr.send();
      
      if (xhr.status === 200) {
        var script = document.createElement('script');
        script.text = xhr.responseText;
        document.head.appendChild(script);
        
        window.jQuery = jQuery;
        window.$ = jQuery;
        console.log('jQuery loaded from backup successfully');
        return true;
      }
    } catch (error) {
      console.error('Failed to load backup jQuery:', error);
    }
    
    return false;
  }

  // Load jQuery immediately
  if (loadJQuerySync()) {
    console.log('jQuery is ready. $ =', typeof window.$);
    // Trigger a custom event when jQuery is ready
    document.dispatchEvent(new Event('jQueryReady'));
  } else {
    console.error('Failed to load jQuery from all sources. Application may not function correctly.');
  }
})(); 