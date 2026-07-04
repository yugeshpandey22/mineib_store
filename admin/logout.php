<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

logoutUser();
header("Location: ../index.php");
exit;
?>
