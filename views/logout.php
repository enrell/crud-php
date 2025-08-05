<?php
require_once "../lib/backend.php";

$currentPage = "logout";
include "header.php";

session_destroy();
header("Location: ../index.php");
exit();
?>
