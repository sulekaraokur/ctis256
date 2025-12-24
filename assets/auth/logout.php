
<?php
session_start();          
session_unset();          
session_destroy();        

header("Location: ../../index.php");   //path değiştir: ana sayfa lazım

?>
