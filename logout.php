<?php
session_start();
session_unset();
session_destroy();

// Optional: prevent back button from returning to protected pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Location: login.php"); // Or redirect to login.php with a farewell message
exit;
?>
