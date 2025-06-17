<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
<!-- Template Main JS File -->

<!-- jQuery Check -->
<script>
  // Make sure jQuery is definitely available before loading dependent scripts
  (function() {
    if (typeof jQuery === 'undefined') {
      console.error('jQuery is still not available in footer. Loading it now...');
      var jq = document.createElement('script');
      jq.src = "assets/js/jquery-3.7.1.min.js";
      jq.onload = function() {
        console.log('jQuery loaded in footer successfully.');
        // Now load all jQuery-dependent scripts
        loadVendorScripts();
      };
      document.head.appendChild(jq);
    } else {
      // jQuery is available, load the scripts normally
      loadVendorScripts();
    }
    
    function loadVendorScripts() {
      // Helper to load scripts in sequence
      function loadScript(src, callback) {
        var script = document.createElement('script');
        script.src = src;
        script.onload = callback || function() {};
        document.head.appendChild(script);
      }
      
      // Load vendor scripts in sequence to ensure dependencies
      loadScript("assets/vendor/apexcharts/apexcharts.min.js");
      loadScript("assets/vendor/bootstrap/5.3.0/bootstrap.bundle.min.js");
      loadScript("assets/vendor/chart.js/chart.umd.js");
      loadScript("assets/vendor/echarts/echarts.min.js");
      loadScript("assets/vendor/quill/quill.js");
      loadScript("assets/vendor/simple-datatables/simple-datatables.js");
      loadScript("assets/vendor/tinymce/tinymce.min.js");
      loadScript("assets/vendor/php-email-form/validate.js");
      loadScript("assets/js/sweetalert2.min.js");
      loadScript("assets/js/translation.js");
      loadScript("assets/vendor/datatables/jquery.dataTables.min.js");
      loadScript("assets/vendor/datatables/dataTables.buttons.min.js");
      loadScript("assets/vendor/datatables/buttons.print.min.js");
    }
  })();
</script>

<!-- Original script tags left for reference, they won't execute due to our custom loader above -->
<!-- 
<script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/chart.js/chart.umd.js"></script>
<script src="assets/vendor/echarts/echarts.min.js"></script>
<script src="assets/vendor/quill/quill.js"></script>
<script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
<script src="assets/vendor/tinymce/tinymce.min.js"></script>
<script src="assets/vendor/php-email-form/validate.js"></script>


<script src="assets/js/sweetalert2.min.js"></script>
<script src="assets/js/translation.js"></script>


<script src="assets/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="assets/vendor/datatables/dataTables.buttons.min.js"></script>
<script src="assets/vendor/datatables/buttons.print.min.js"></script>
<script src="assets/vendor/bootstrap/5.3.0/bootstrap.bundle.min.js"></script>
-->
    