
<?php
session_start();          
session_unset();          
session_destroy();        

header("Location: login.php");   //path değiştir: ana sayfa lazım

?>
