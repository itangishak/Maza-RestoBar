<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">

<title>Maza Resto-Bar</title>
<meta content="" name="description">
<meta content="" name="keywords">

<!-- Critical: Include jQuery initialization script first -->
<script src="assets/js/init.js"></script>

<!-- Favicons -->
<link href="../Client/img/LogoMaza.png" rel="icon">
<link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

<!-- CSS Libraries -->
<link rel="stylesheet" href="assets/vendor/bootstrap/5.3.0/bootstrap.min.css">
   
<!-- Font Awesome -->
<link href="assets/vendor/fontawesome/css/all.min.css" rel="stylesheet">
<!-- Local Vendor CSS Files -->
<link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
<link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
<link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

<!-- Local DataTables CSS -->
<link href="assets/vendor/datatables/jquery.dataTables.min.css" rel="stylesheet">
<link href="assets/vendor/datatables/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="assets/vendor/datatables/buttons.dataTables.min.css" rel="stylesheet">

<!-- Local SweetAlert2 CSS -->
<link rel="stylesheet" href="assets/css/sweetalert2.min.css">

<!-- Local Select2 CSS -->
<link rel="stylesheet" href="assets/css/select2.min.css">

<!-- Template Main CSS File -->
<link href="assets/css/style.css" rel="stylesheet">

<!-- Local Bootstrap Icons -->
<link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

<!-- Local Bootstrap Bundle with Popper -->
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Local SweetAlert2 -->
<script src="assets/js/sweetalert2.min.js"></script>

<!-- Local Select2 -->
<script src="assets/js/select2.min.js"></script>

<!-- Local jsPDF -->
<script src="assets/js/jspdf.umd.min.js"></script>

<!-- IMPORTANT: Load DataTables and its dependencies in the correct order -->
<script>
  // Function to load scripts in sequence
  function loadScripts() {
    const scripts = [
      'assets/vendor/datatables/jquery.dataTables.min.js',
      'assets/vendor/datatables/dataTables.bootstrap5.min.js',
      'assets/vendor/datatables/dataTables.buttons.min.js',
      'assets/vendor/datatables/buttons.print.min.js'
    ];

    function loadScript(src) {
      return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = src;
        script.async = false; // Ensure synchronous loading
        script.onload = () => {
          console.log(`Loaded: ${src}`);
          resolve();
        };
        script.onerror = (error) => {
          console.error(`Failed to load: ${src}`, error);
          reject(error);
        };
        document.head.appendChild(script);
      });
    }

    // Load scripts sequentially
    scripts.reduce((promise, script) => {
      return promise.then(() => loadScript(script));
    }, Promise.resolve())
    .then(() => {
      console.log('All DataTables scripts loaded successfully');
      // Initialize DataTables after all scripts are loaded
      if (typeof window.initializeDataTables === 'function') {
        window.initializeDataTables();
      }
    })
    .catch(error => {
      console.error('Error loading DataTables scripts:', error);
    });
  }

  // Load scripts when jQuery is ready
  if (typeof jQuery !== 'undefined') {
    console.log('jQuery is available, loading DataTables scripts...');
    loadScripts();
  } else {
    console.log('Waiting for jQuery to be available...');
    document.addEventListener('jQueryReady', () => {
      console.log('jQuery is now available, loading DataTables scripts...');
      loadScripts();
    });
  }

  function loadDataTablesScripts() {
    console.log('jQuery is available, loading DataTables scripts...');
    
    const scripts = [
      'assets/vendor/datatables/jquery.dataTables.min.js',
      'assets/vendor/datatables/dataTables.bootstrap5.min.js',
      'assets/vendor/datatables/dataTables.buttons.min.js',
      'assets/vendor/datatables/buttons.print.min.js'
    ];
    
    function loadScriptSequentially(index) {
      if (index >= scripts.length) {
        console.log('All DataTables scripts loaded successfully');
        return;
      }
      
      const script = document.createElement('script');
      script.src = scripts[index];
      script.onload = function() {
        console.log('Loaded:', scripts[index]);
        // Load the next script only after this one is loaded
        loadScriptSequentially(index + 1);
      };
      document.body.appendChild(script);
    }
    
    // Start loading scripts sequentially
    loadScriptSequentially(0);
  }
</script>
   