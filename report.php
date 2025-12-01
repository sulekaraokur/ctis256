<?php
   // "report.php" is the entry point.
   // Normally, backend.php and frontend.php are inaccessible from the browser.
   require "./src/backend.php" ;   //  --> $out  (global variable created by backend)

   // render:
   require "./src/frontend.php" ;  // <--  $out 