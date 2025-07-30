<?php
session_start();
$_SESSION = array();
session_destroy();
header("Location: buyer_Login.php");
exit();
?>