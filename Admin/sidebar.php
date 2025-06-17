<!-- ======= Sidebar ======= -->
<?php

if($_SESSION['privilege']=='Manager'){
    include_once './sidebarmanager.php';
}
else if($_SESSION['privilege']=='Boss'){
    include_once './sidebarboss.php';
}
else if($_SESSION['privilege']=='Stock'){
    include_once './sidebarstock.php';
}
else{
    include_once './sidebaruser.php';
}

?>
<!-- End Sidebar --> "