<?php
session_start();
session_unset();
session_destroy();
header("Location: /assessment_beginner/login.php");
exit;
?>