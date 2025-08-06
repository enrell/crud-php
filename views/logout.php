<?php
require_once "../lib/security/SecurityHelper.php";

session_destroy();
header("Location: ../index.php");
exit();
?>
